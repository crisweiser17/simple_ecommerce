<?php

$dbFile = __DIR__ . '/data/database.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Add columns to products table
echo "Checking products table for new columns...\n";
$stmt = $pdo->query("PRAGMA table_info(products)");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);

$newColumns = [
    'type' => "TEXT DEFAULT 'physical'",
    'digital_delivery' => "INTEGER DEFAULT 0",
    'download_limit' => "INTEGER DEFAULT 0",
    'download_expiry_days' => "INTEGER DEFAULT 0",
    'file_url' => "TEXT"
];

foreach ($newColumns as $column => $definition) {
    if (!in_array($column, $columns)) {
        echo "Adding $column to products table...\n";
        $pdo->exec("ALTER TABLE products ADD COLUMN $column $definition");
    } else {
        echo "$column already exists in products table.\n";
    }
}

// 2. Create order_digital_deliveries table
echo "Creating order_digital_deliveries table...\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS order_digital_deliveries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    token TEXT NOT NULL UNIQUE,
    download_count INTEGER DEFAULT 0,
    max_downloads INTEGER DEFAULT 0,
    expires_at DATETIME,
    delivered_at DATETIME,
    downloaded_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_order_digital_deliveries_token ON order_digital_deliveries(token)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_order_digital_deliveries_order_id ON order_digital_deliveries(order_id)");

// 3. Create embed_sessions table
echo "Creating embed_sessions table...\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS embed_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_token TEXT NOT NULL UNIQUE,
    product_id INTEGER NOT NULL,
    status TEXT DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_embed_sessions_token ON embed_sessions(session_token)");

echo "Digital products database update complete!\n";
