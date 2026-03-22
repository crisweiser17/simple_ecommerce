<?php

$dbFile = __DIR__ . '/data/database.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create Categories Table
echo "Creating categories table...\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Create Pages Table
echo "Creating pages table...\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS pages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    content TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Check if category_id exists in products
$stmt = $pdo->query("PRAGMA table_info(products)");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);

if (!in_array('category_id', $columns)) {
    echo "Adding category_id to products table...\n";
    $pdo->exec("ALTER TABLE products ADD COLUMN category_id INTEGER");
    
    // Migrate existing categories
    echo "Migrating existing categories...\n";
    $stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
    $existingCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($existingCategories as $catName) {
        // Insert into categories table
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $catName)));
        
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$catName, $slug]);
        
        // Get the ID
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        $catId = $stmt->fetchColumn();
        
        if ($catId) {
            // Update products
            $stmt = $pdo->prepare("UPDATE products SET category_id = ? WHERE category = ?");
            $stmt->execute([$catId, $catName]);
            echo "Migrated category '$catName' to ID $catId\n";
        }
    }
} else {
    echo "category_id column already exists in products table.\n";
}

if (!in_array('pdf_label', $columns)) {
    echo "Adding pdf_label to products table...\n";
    $pdo->exec("ALTER TABLE products ADD COLUMN pdf_label TEXT DEFAULT ''");
} else {
    echo "pdf_label column already exists in products table.\n";
}

echo "Creating product_images table...\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    image_url TEXT NOT NULL,
    is_primary INTEGER DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_product_images_product_id ON product_images(product_id)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_product_images_primary ON product_images(product_id, is_primary, sort_order)");

echo "Database update complete!\n";
