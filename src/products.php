<?php
require_once __DIR__ . '/db.php';

function normalizeProductSlug($value) {
    $slug = strtolower(trim((string)$value));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', (string)$slug);
    $slug = trim((string)$slug, '-');
    if ($slug === '') {
        $slug = 'product';
    }
    return $slug;
}

function getUniqueProductSlug($value, $excludeId = null) {
    global $pdo;

    $baseSlug = normalizeProductSlug($value);
    $slug = $baseSlug;
    $suffix = 2;
    $excludeId = $excludeId !== null ? (int)$excludeId : null;

    while (true) {
        if ($excludeId !== null) {
            $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ? LIMIT 1");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
        }
        if (!$stmt->fetchColumn()) {
            return $slug;
        }
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }
}

function getProductUrl($product) {
    $slug = trim((string)($product['slug'] ?? ''));
    if ($slug !== '') {
        return '/product/' . rawurlencode($slug);
    }

    $id = (int)($product['id'] ?? 0);
    if ($id > 0) {
        return '/product?id=' . $id;
    }
    return '/product';
}

function ensureProductsSchema() {
    static $checked = false;
    if ($checked) {
        return;
    }

    global $pdo;
    $stmt = $pdo->query("PRAGMA table_info(products)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);

    if (!in_array('category_id', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN category_id INTEGER");
    }

    if (!in_array('pdf_label', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN pdf_label TEXT DEFAULT ''");
    }

    if (!in_array('slug', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN slug TEXT");
    }

    $rows = $pdo->query("SELECT id, name, sku, slug FROM products ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $usedSlugs = [];
    $updateSlugStmt = $pdo->prepare("UPDATE products SET slug = ? WHERE id = ?");
    foreach ($rows as $row) {
        $currentSlug = trim((string)($row['slug'] ?? ''));
        $seed = $currentSlug !== '' ? $currentSlug : ((string)($row['name'] ?? '') !== '' ? $row['name'] : ($row['sku'] ?? 'product'));
        $baseSlug = normalizeProductSlug($seed);
        $slug = $baseSlug;
        $suffix = 2;
        while (isset($usedSlugs[$slug])) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
        $usedSlugs[$slug] = true;
        if ($currentSlug !== $slug) {
            $updateSlugStmt->execute([$slug, (int)$row['id']]);
        }
    }

    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_products_slug_unique ON products(slug)");

    $pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        image_url TEXT NOT NULL,
        is_primary INTEGER DEFAULT 0,
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_product_images_product_id ON product_images(product_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_product_images_primary ON product_images(product_id, is_primary, sort_order)");

    $checked = true;
}

function getProductPrimaryImageUrl(array $product) {
    $primary = trim((string)($product['primary_image_url'] ?? ''));
    if ($primary !== '') {
        return $primary;
    }

    return trim((string)($product['image_url'] ?? ''));
}

function getProductImages($productId) {
    ensureProductsSchema();
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT id, product_id, image_url, is_primary, sort_order
        FROM product_images
        WHERE product_id = ?
        ORDER BY is_primary DESC, sort_order ASC, id ASC
    ");
    $stmt->execute([(int)$productId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function saveProductImages($productId, array $imageUrls, $primaryImageUrl = null) {
    ensureProductsSchema();
    global $pdo;

    $normalizedUrls = [];
    foreach ($imageUrls as $url) {
        $value = trim((string)$url);
        if ($value !== '' && !in_array($value, $normalizedUrls, true)) {
            $normalizedUrls[] = $value;
        }
    }

    $selectedPrimary = trim((string)$primaryImageUrl);
    if ($selectedPrimary === '' || !in_array($selectedPrimary, $normalizedUrls, true)) {
        $selectedPrimary = $normalizedUrls[0] ?? '';
    }

    $pdo->beginTransaction();
    try {
        $deleteStmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
        $deleteStmt->execute([(int)$productId]);

        if (!empty($normalizedUrls)) {
            $insertStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES (?, ?, ?, ?)");
            foreach ($normalizedUrls as $index => $url) {
                $insertStmt->execute([
                    (int)$productId,
                    $url,
                    $url === $selectedPrimary ? 1 : 0,
                    $index
                ]);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    return $selectedPrimary;
}

function getAllProducts($categorySlug = null, $limit = null, $offset = 0, $searchTerm = null) {
    ensureProductsSchema();
    global $pdo;
    $sql = "
        SELECT p.*, c.name as category_name,
               COALESCE(
                    (
                        SELECT pi.image_url
                        FROM product_images pi
                        WHERE pi.product_id = p.id
                        ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
                        LIMIT 1
                    ),
                    p.image_url
               ) as primary_image_url
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
    ";
    
    $params = [];
    $conditions = [];
    if ($categorySlug) {
        $conditions[] = "c.slug = ?";
        $params[] = $categorySlug;
    }

    $searchTerm = trim((string)$searchTerm);
    if ($searchTerm !== '') {
        $likeTerm = '%' . $searchTerm . '%';
        $conditions[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.short_desc LIKE ? OR p.long_desc LIKE ?)";
        $params[] = $likeTerm;
        $params[] = $likeTerm;
        $params[] = $likeTerm;
        $params[] = $likeTerm;
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY p.created_at DESC";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countProducts($categorySlug = null, $searchTerm = null) {
    ensureProductsSchema();
    global $pdo;
    $sql = "
        SELECT COUNT(*) 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
    ";
    
    $params = [];
    $conditions = [];
    if ($categorySlug) {
        $conditions[] = "c.slug = ?";
        $params[] = $categorySlug;
    }

    $searchTerm = trim((string)$searchTerm);
    if ($searchTerm !== '') {
        $likeTerm = '%' . $searchTerm . '%';
        $conditions[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.short_desc LIKE ? OR p.long_desc LIKE ?)";
        $params[] = $likeTerm;
        $params[] = $likeTerm;
        $params[] = $likeTerm;
        $params[] = $likeTerm;
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function getProduct($id) {
    ensureProductsSchema();
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug,
               COALESCE(
                    (
                        SELECT pi.image_url
                        FROM product_images pi
                        WHERE pi.product_id = p.id
                        ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
                        LIMIT 1
                    ),
                    p.image_url
               ) as primary_image_url
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        return false;
    }

    $product['images'] = getProductImages((int)$id);
    $product['primary_image_url'] = getProductPrimaryImageUrl($product);
    return $product;
}

function getProductBySlug($slug) {
    ensureProductsSchema();
    global $pdo;

    $normalizedSlug = trim((string)$slug);
    if ($normalizedSlug === '') {
        return false;
    }

    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug,
               COALESCE(
                    (
                        SELECT pi.image_url
                        FROM product_images pi
                        WHERE pi.product_id = p.id
                        ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
                        LIMIT 1
                    ),
                    p.image_url
               ) as primary_image_url
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ?
        LIMIT 1
    ");
    $stmt->execute([$normalizedSlug]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        return false;
    }

    $product['images'] = getProductImages((int)$product['id']);
    $product['primary_image_url'] = getProductPrimaryImageUrl($product);
    return $product;
}

function createProduct($data) {
    ensureProductsSchema();
    global $pdo;
    $slug = getUniqueProductSlug($data['slug'] ?? ($data['name'] ?? ''));
    $stmt = $pdo->prepare("INSERT INTO products (name, sku, slug, price, image_url, category_id, short_desc, long_desc, pdf_url, pdf_label, type, digital_delivery, download_limit, download_expiry_days, file_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        trim((string)($data['name'] ?? '')),
        trim((string)($data['sku'] ?? '')),
        $slug,
        ($data['price'] === '' || !isset($data['price'])) ? null : (float)$data['price'],
        trim((string)($data['image_url'] ?? '')),
        ($data['category_id'] === '' || !isset($data['category_id'])) ? null : (int)$data['category_id'],
        (string)($data['short_desc'] ?? ''),
        (string)($data['long_desc'] ?? ''),
        trim((string)($data['pdf_url'] ?? '')),
        $data['pdf_label'] ?? '',
        $data['type'] ?? 'physical',
        isset($data['digital_delivery']) ? 1 : 0,
        isset($data['download_limit']) && $data['download_limit'] !== '' ? (int)$data['download_limit'] : null,
        isset($data['download_expiry_days']) && $data['download_expiry_days'] !== '' ? (int)$data['download_expiry_days'] : null,
        trim((string)($data['file_url'] ?? ''))
    ]);
}

function updateProduct($id, $data) {
    ensureProductsSchema();
    global $pdo;
    $existingSlugStmt = $pdo->prepare("SELECT slug FROM products WHERE id = ? LIMIT 1");
    $existingSlugStmt->execute([(int)$id]);
    $existingSlug = (string)($existingSlugStmt->fetchColumn() ?: '');
    $requestedSlug = array_key_exists('slug', $data) ? (string)$data['slug'] : $existingSlug;
    if (trim($requestedSlug) === '') {
        $requestedSlug = (string)($data['name'] ?? '');
    }
    $slug = getUniqueProductSlug($requestedSlug, (int)$id);

    $stmt = $pdo->prepare("UPDATE products SET name=?, sku=?, slug=?, price=?, image_url=?, category_id=?, short_desc=?, long_desc=?, pdf_url=?, pdf_label=?, type=?, digital_delivery=?, download_limit=?, download_expiry_days=?, file_url=? WHERE id=?");
    return $stmt->execute([
        trim((string)($data['name'] ?? '')),
        trim((string)($data['sku'] ?? '')),
        $slug,
        ($data['price'] === '' || !isset($data['price'])) ? null : (float)$data['price'],
        trim((string)($data['image_url'] ?? '')),
        ($data['category_id'] === '' || !isset($data['category_id'])) ? null : (int)$data['category_id'],
        (string)($data['short_desc'] ?? ''),
        (string)($data['long_desc'] ?? ''),
        trim((string)($data['pdf_url'] ?? '')),
        $data['pdf_label'] ?? '',
        $data['type'] ?? 'physical',
        isset($data['digital_delivery']) ? 1 : 0,
        isset($data['download_limit']) && $data['download_limit'] !== '' ? (int)$data['download_limit'] : null,
        isset($data['download_expiry_days']) && $data['download_expiry_days'] !== '' ? (int)$data['download_expiry_days'] : null,
        trim((string)($data['file_url'] ?? '')),
        $id
    ]);
}

function updateProductImageUrl($id, $imageUrl) {
    ensureProductsSchema();
    global $pdo;
    $stmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
    return $stmt->execute([trim((string)$imageUrl), (int)$id]);
}

function deleteProduct($id) {
    ensureProductsSchema();
    global $pdo;
    $stmtImages = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmtImages->execute([$id]);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    return $stmt->execute([$id]);
}

function getProductBySku($sku) {
    ensureProductsSchema();
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE sku = ? LIMIT 1");
    $stmt->execute([trim((string)$sku)]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getProductAutocompleteSuggestions($searchTerm, $limit = 8) {
    ensureProductsSchema();
    global $pdo;

    $searchTerm = trim((string)$searchTerm);
    if (strlen($searchTerm) < 3) {
        return [];
    }

    $safeLimit = max(1, min((int)$limit, 20));
    $prefixTerm = strtolower($searchTerm) . '%';
    $likeTerm = '%' . $searchTerm . '%';

    $sql = "
        SELECT p.id, p.name, p.slug
        FROM products p
        WHERE p.name LIKE ? OR p.sku LIKE ?
        ORDER BY CASE WHEN LOWER(p.name) LIKE ? THEN 0 ELSE 1 END, p.name ASC
        LIMIT $safeLimit
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$likeTerm, $likeTerm, $prefixTerm]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
