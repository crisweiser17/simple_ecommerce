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
    $excludeId = $excludeId !== null ? (int)$excludeId : null;

    $likeSlug = $baseSlug . '-%';
    
    if ($excludeId !== null) {
        $stmt = $pdo->prepare("SELECT slug FROM products WHERE (slug = ? OR slug LIKE ?) AND id != ?");
        $stmt->execute([$baseSlug, $likeSlug, $excludeId]);
    } else {
        $stmt = $pdo->prepare("SELECT slug FROM products WHERE slug = ? OR slug LIKE ?");
        $stmt->execute([$baseSlug, $likeSlug]);
    }
    
    $existingSlugs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $existingSlugsMap = array_flip($existingSlugs);
    
    if (!isset($existingSlugsMap[$baseSlug])) {
        return $baseSlug;
    }
    
    $suffix = 2;
    while (isset($existingSlugsMap[$baseSlug . '-' . $suffix])) {
        $suffix++;
    }
    
    return $baseSlug . '-' . $suffix;
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

function productsSchemaFastPathLooksReady(PDO $pdo) {
    $requiredObjects = [
        'products' => 'table',
        'product_images' => 'table',
        'global_variations' => 'table',
        'idx_products_slug_unique' => 'index',
        'idx_products_sku' => 'index',
        'idx_products_category_id' => 'index',
        'idx_product_images_product_id' => 'index',
        'idx_product_images_primary' => 'index',
    ];

    $placeholders = implode(', ', array_fill(0, count($requiredObjects), '?'));
    $stmt = $pdo->prepare("SELECT name, type FROM sqlite_master WHERE name IN ($placeholders)");
    $stmt->execute(array_keys($requiredObjects));

    $existingObjects = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $existingObjects[$row['name']] = $row['type'];
    }

    foreach ($requiredObjects as $name => $type) {
        if (($existingObjects[$name] ?? null) !== $type) {
            return false;
        }
    }

    $pdo->query("SELECT category_id, pdf_label, pdf_active, slug, variations_json FROM products LIMIT 0");
    $pdo->query("SELECT product_id, image_url, is_primary, sort_order FROM product_images LIMIT 0");
    $pdo->query("SELECT name, options_json FROM global_variations LIMIT 0");

    return true;
}

function ensureProductsSchema() {
    static $checked = false;
    if ($checked) {
        return;
    }

    global $pdo;

    try {
        if (productsSchemaFastPathLooksReady($pdo)) {
            $checked = true;
            return;
        }
    } catch (Throwable) {
        // Fall back to the full schema bootstrap when the fast path cannot prove integrity.
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        sku TEXT,
        slug TEXT UNIQUE,
        price REAL,
        image_url TEXT,
        category TEXT,
        category_id INTEGER,
        short_desc TEXT,
        long_desc TEXT,
        pdf_url TEXT,
        pdf_label TEXT,
        pdf_active INTEGER DEFAULT 0,
        type TEXT DEFAULT 'physical',
        digital_delivery INTEGER DEFAULT 0,
        download_limit INTEGER DEFAULT 0,
        download_expiry_days INTEGER DEFAULT 0,
        file_url TEXT,
        variations_json TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->query("PRAGMA table_info(products)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);

    if (!in_array('category_id', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN category_id INTEGER");
    }

    if (!in_array('pdf_label', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN pdf_label TEXT DEFAULT ''");
    }
    if (!in_array('pdf_active', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN pdf_active INTEGER DEFAULT 0");
    }
    if (!in_array('slug', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN slug TEXT");
    }

    if (!in_array('variations_json', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN variations_json TEXT");
    }

    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_products_slug_unique ON products(slug)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_products_sku ON products(sku)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_products_category_id ON products(category_id)");

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

    $pdo->exec("CREATE TABLE IF NOT EXISTS global_variations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        options_json TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

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

    $existingStmt = $pdo->prepare("
        SELECT image_url, is_primary, sort_order
        FROM product_images
        WHERE product_id = ?
        ORDER BY sort_order ASC, id ASC
    ");
    $existingStmt->execute([(int)$productId]);
    $existingImages = $existingStmt->fetchAll(PDO::FETCH_ASSOC);

    $imagesChanged = count($existingImages) !== count($normalizedUrls);
    if (!$imagesChanged) {
        foreach ($existingImages as $index => $existingImage) {
            $expectedUrl = $normalizedUrls[$index];
            $expectedIsPrimary = $expectedUrl === $selectedPrimary ? 1 : 0;

            if (
                (string)$existingImage['image_url'] !== $expectedUrl ||
                (int)$existingImage['is_primary'] !== $expectedIsPrimary ||
                (int)$existingImage['sort_order'] !== $index
            ) {
                $imagesChanged = true;
                break;
            }
        }
    }

    if (!$imagesChanged) {
        return $selectedPrimary;
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
        $conditions[] = "p.name LIKE ?";
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
        $conditions[] = "p.name LIKE ?";
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
    $stmt = $pdo->prepare("INSERT INTO products (name, sku, slug, price, image_url, category_id, short_desc, long_desc, pdf_url, pdf_label, pdf_active, type, digital_delivery, download_limit, download_expiry_days, file_url, variations_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
        isset($data['pdf_active']) ? (int)$data['pdf_active'] : 0,
        $data['type'] ?? 'physical',
        isset($data['digital_delivery']) ? 1 : 0,
        isset($data['download_limit']) && $data['download_limit'] !== '' ? (int)$data['download_limit'] : null,
        isset($data['download_expiry_days']) && $data['download_expiry_days'] !== '' ? (int)$data['download_expiry_days'] : null,
        trim((string)($data['file_url'] ?? '')),
        trim((string)($data['variations_json'] ?? '[]'))
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
    $normalizedRequestedSlug = normalizeProductSlug($requestedSlug);
    if ($existingSlug !== '' && $normalizedRequestedSlug === $existingSlug) {
        $slug = $existingSlug;
    } else {
        $slug = getUniqueProductSlug($requestedSlug, (int)$id);
    }

    $stmt = $pdo->prepare("UPDATE products SET name=?, sku=?, slug=?, price=?, image_url=?, category_id=?, short_desc=?, long_desc=?, pdf_url=?, pdf_label=?, pdf_active=?, type=?, digital_delivery=?, download_limit=?, download_expiry_days=?, file_url=?, variations_json=? WHERE id=?");
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
        isset($data['pdf_active']) ? (int)$data['pdf_active'] : 0,
        $data['type'] ?? 'physical',
        isset($data['digital_delivery']) ? 1 : 0,
        isset($data['download_limit']) && $data['download_limit'] !== '' ? (int)$data['download_limit'] : null,
        isset($data['download_expiry_days']) && $data['download_expiry_days'] !== '' ? (int)$data['download_expiry_days'] : null,
        trim((string)($data['file_url'] ?? '')),
        trim((string)($data['variations_json'] ?? '[]')),
        $id
    ]);
}

function assignCategoryToProducts(array $productIds, $categoryId) {
    ensureProductsSchema();
    global $pdo;

    $normalizedProductIds = [];
    foreach ($productIds as $productId) {
        $productId = (int)$productId;
        if ($productId > 0 && !in_array($productId, $normalizedProductIds, true)) {
            $normalizedProductIds[] = $productId;
        }
    }

    if (empty($normalizedProductIds)) {
        return 0;
    }

    $normalizedCategoryId = null;
    if ($categoryId !== null && $categoryId !== '') {
        $normalizedCategoryId = (int)$categoryId;
        if ($normalizedCategoryId < 1) {
            throw new InvalidArgumentException('Invalid category id.');
        }

        $categoryCheck = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE id = ?");
        $categoryCheck->execute([$normalizedCategoryId]);
        if ((int)$categoryCheck->fetchColumn() < 1) {
            throw new InvalidArgumentException('Category not found.');
        }
    }

    $placeholders = implode(', ', array_fill(0, count($normalizedProductIds), '?'));
    $sql = "UPDATE products SET category_id = ? WHERE id IN ($placeholders)";
    $params = array_merge([$normalizedCategoryId], $normalizedProductIds);

    $startedTransaction = false;
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
        $startedTransaction = true;
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($startedTransaction) {
            $pdo->commit();
        }

        return (int)$stmt->rowCount();
    } catch (Throwable $e) {
        if ($startedTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
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

// === Global Variations Logic ===

function getGlobalVariations() {
    ensureProductsSchema();
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM global_variations ORDER BY name ASC");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as &$row) {
        $row['options'] = json_decode($row['options_json'] ?? '[]', true);
    }
    return $results;
}

function getGlobalVariation($id) {
    ensureProductsSchema();
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM global_variations WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $row['options'] = json_decode($row['options_json'] ?? '[]', true);
    }
    return $row;
}

function saveGlobalVariation($id, $name, $options) {
    ensureProductsSchema();
    global $pdo;
    $optionsJson = json_encode($options);
    
    if (empty($id)) {
        // Create
        $stmt = $pdo->prepare("INSERT INTO global_variations (name, options_json) VALUES (?, ?)");
        try {
            $stmt->execute([$name, $optionsJson]);
        } catch (PDOException $e) {
            // Ignore unique constraint error
        }
    } else {
        // Update
        $stmt = $pdo->prepare("UPDATE global_variations SET name = ?, options_json = ? WHERE id = ?");
        $stmt->execute([$name, $optionsJson, $id]);
    }
}

function deleteGlobalVariation($id) {
    ensureProductsSchema();
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM global_variations WHERE id = ?");
    $stmt->execute([$id]);
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
