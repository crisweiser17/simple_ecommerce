<?php
require_once __DIR__ . '/src/db.php';
global $pdo;

// Check if user exists
$stmt = $pdo->prepare("SELECT id, email FROM users LIMIT 1");
$stmt->execute();
$user = $stmt->fetch();

if (!$user) {
    // Create one
    $pdo->exec("INSERT INTO users (email, name) VALUES ('test@example.com', 'Test User')");
    $userId = $pdo->lastInsertId();
    $email = 'test@example.com';
} else {
    $userId = $user['id'];
    $email = $user['email'];
}

// Create an order for this user
$stmt = $pdo->prepare("INSERT INTO orders (customer_email, customer_name, total_amount, status) VALUES (?, ?, ?, ?)");
$stmt->execute([$email, 'Test User', 100.00, 'paid']);
$orderId = $pdo->lastInsertId();

// Create a digital product
$pdo->exec("INSERT INTO products (name, slug, price, type, digital_delivery, download_limit, download_expiry_days) VALUES ('Test Digital Product', 'test-digital', 100.00, 'digital', 1, 3, 7)");
$productId = $pdo->lastInsertId();

// Create order delivery
$token = bin2hex(random_bytes(16));
$stmt = $pdo->prepare("INSERT INTO order_digital_deliveries (order_id, product_id, token, download_count, max_downloads, expires_at) VALUES (?, ?, ?, 1, 3, datetime('now', '+7 days'))");
$stmt->execute([$orderId, $productId, $token]);

echo "Created test order $orderId for user $email\n";
