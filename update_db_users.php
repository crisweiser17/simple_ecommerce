<?php
require_once __DIR__ . '/src/db.php';

echo "Updating users table...\n";

$columns = [
    'name' => 'TEXT',
    'whatsapp' => 'TEXT',
    'cep' => 'TEXT',
    'street' => 'TEXT',
    'number' => 'TEXT',
    'neighborhood' => 'TEXT',
    'city' => 'TEXT',
    'state' => 'TEXT',
    'remember_selector' => 'TEXT',
    'remember_token_hash' => 'TEXT',
    'remember_expires_at' => 'DATETIME',
    'admin_bypass_token' => 'TEXT'
];

foreach ($columns as $column => $type) {
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN $column $type");
        echo "Added column: $column\n";
    } catch (PDOException $e) {
        // Column likely exists
        echo "Column $column might already exist or error: " . $e->getMessage() . "\n";
    }
}

try {
    $pdo->exec("CREATE UNIQUE INDEX idx_users_remember_selector ON users(remember_selector)");
    echo "Created index: idx_users_remember_selector\n";
} catch (PDOException $e) {
    echo "Index idx_users_remember_selector might already exist or error: " . $e->getMessage() . "\n";
}

echo "Update complete.\n";
