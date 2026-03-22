<?php
require_once __DIR__ . '/db.php';

function getUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAdminCustomers() {
    global $pdo;
    $sql = "
        SELECT
            u.id,
            COALESCE(NULLIF(TRIM(u.name), ''), '—') AS name,
            u.email,
            COALESCE(NULLIF(TRIM(u.whatsapp), ''), '—') AS whatsapp,
            COUNT(o.id) AS orders_count,
            MAX(o.created_at) AS last_order_at
        FROM users u
        LEFT JOIN orders o ON o.customer_email = u.email
        GROUP BY u.id, u.name, u.email, u.whatsapp
        ORDER BY
            CASE WHEN COUNT(o.id) > 0 THEN 0 ELSE 1 END ASC,
            MAX(o.created_at) DESC,
            u.id DESC
    ";
    $stmt = $pdo->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($customers as &$customer) {
        $customer['orders_count'] = (int)($customer['orders_count'] ?? 0);
        $customer['has_orders'] = $customer['orders_count'] > 0;
    }
    unset($customer);
    return $customers;
}

function getAdminCustomerDetails($id) {
    global $pdo;
    $sql = "
        SELECT
            u.*,
            COUNT(o.id) AS orders_count,
            MAX(o.created_at) AS last_order_at
        FROM users u
        LEFT JOIN orders o ON o.customer_email = u.email
        WHERE u.id = ?
        GROUP BY u.id
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$customer) {
        return null;
    }
    $customer['orders_count'] = (int)($customer['orders_count'] ?? 0);
    $customer['has_orders'] = $customer['orders_count'] > 0;
    return $customer;
}

function updateUser($id, $data) {
    global $pdo;
    
    // Whitelist allowed fields
    $allowed = ['name', 'whatsapp', 'email', 'cep', 'street', 'number', 'neighborhood', 'city', 'state'];
    $updates = [];
    $params = [];
    
    foreach ($allowed as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        return false;
    }
    
    $params[] = $id;
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function getAdminUsers() {
    global $pdo;
    $stmt = $pdo->query("SELECT id, name, email, admin_bypass_token, created_at FROM users WHERE is_admin = 1 ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function promoteUserToAdmin($email) {
    global $pdo;
    
    // First check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $updateStmt = $pdo->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
        return $updateStmt->execute([$user['id']]);
    } else {
        // Create new user as admin
        $insertStmt = $pdo->prepare("INSERT INTO users (email, is_admin) VALUES (?, 1)");
        return $insertStmt->execute([$email]);
    }
}

function revokeAdminAccess($id) {
    global $pdo;
    // Don't let the primary admin (id=1) be revoked to ensure system is never locked out
    if ($id == 1) return false;
    
    $stmt = $pdo->prepare("UPDATE users SET is_admin = 0, admin_bypass_token = NULL WHERE id = ?");
    return $stmt->execute([$id]);
}

function setAdminBypassToken($id, $token) {
    global $pdo;
    
    // Convert empty string to null to clear the token
    $tokenValue = trim((string)$token) === '' ? null : trim((string)$token);
    
    $stmt = $pdo->prepare("UPDATE users SET admin_bypass_token = ? WHERE id = ? AND is_admin = 1");
    return $stmt->execute([$tokenValue, $id]);
}
