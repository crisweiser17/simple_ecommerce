<?php
require_once __DIR__ . '/db.php';

function ensureUsersSchema() {
    global $pdo;
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        login_token TEXT,
        token_expiry DATETIME,
        remember_selector TEXT UNIQUE,
        remember_token_hash TEXT,
        remember_expires_at DATETIME,
        is_admin INTEGER DEFAULT 0,
        admin_bypass_token TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        name TEXT,
        whatsapp TEXT,
        cep TEXT,
        street TEXT,
        number TEXT,
        complement TEXT,
        neighborhood TEXT,
        city TEXT,
        state TEXT
    )");

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (email, is_admin) VALUES (?, ?)");
    $stmt->execute(['admin@r2.com', 1]);
}

function getUser($id) {
    ensureUsersSchema();
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAdminCustomers() {
    ensureUsersSchema();
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
    ensureUsersSchema();
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
    ensureUsersSchema();
    global $pdo;
    
    // Whitelist allowed fields
    $allowed = ['name', 'whatsapp', 'email', 'cep', 'street', 'number', 'complement', 'neighborhood', 'city', 'state'];
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
    ensureUsersSchema();
    global $pdo;
    $stmt = $pdo->query("SELECT id, name, email, admin_bypass_token, created_at FROM users WHERE is_admin = 1 ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function promoteUserToAdmin($email, $name = null, $token = null) {
    ensureUsersSchema();
    global $pdo;
    
    // First check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $tokenValue = trim((string)$token) === '' ? null : trim((string)$token);
    
    if ($user) {
        $updateQuery = "UPDATE users SET is_admin = 1";
        $params = [];
        
        if (!empty($name)) {
            $updateQuery .= ", name = ?";
            $params[] = $name;
        }
        
        if ($tokenValue !== null || isset($token)) { // allow setting null token
            $updateQuery .= ", admin_bypass_token = ?";
            $params[] = $tokenValue;
        }
        
        $updateQuery .= " WHERE id = ?";
        $params[] = $user['id'];
        
        $updateStmt = $pdo->prepare($updateQuery);
        return $updateStmt->execute($params);
    } else {
        // Create new user as admin
        $insertQuery = "INSERT INTO users (email, is_admin";
        $valuesQuery = "VALUES (?, 1";
        $params = [$email];
        
        if (!empty($name)) {
            $insertQuery .= ", name";
            $valuesQuery .= ", ?";
            $params[] = $name;
        }
        
        if ($tokenValue !== null) {
            $insertQuery .= ", admin_bypass_token";
            $valuesQuery .= ", ?";
            $params[] = $tokenValue;
        }
        
        $insertQuery .= ") ";
        $valuesQuery .= ")";
        
        $insertStmt = $pdo->prepare($insertQuery . $valuesQuery);
        return $insertStmt->execute($params);
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
