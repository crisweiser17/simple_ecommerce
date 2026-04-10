<?php
require_once __DIR__ . '/db.php';

function ensureOrdersSchema() {
    global $pdo;
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_name TEXT,
        customer_whatsapp TEXT,
        customer_email TEXT,
        customer_address TEXT,
        customer_cep TEXT,
        customer_street TEXT,
        customer_number TEXT,
        customer_complement TEXT,
        customer_neighborhood TEXT,
        customer_city TEXT,
        customer_state TEXT,
        items_json TEXT,
        total_amount REAL,
        status TEXT DEFAULT 'pending',
        payment_status TEXT DEFAULT 'pending',
        payment_provider TEXT,
        paid_at DATETIME,
        tracking_number TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

function createOrder($customerData, $items, $total) {
    ensureOrdersSchema();
    global $pdo;
    $status = $customerData['status'] ?? 'pending_payment';
    $paymentStatus = $customerData['payment_status'] ?? 'pending';
    $paymentProvider = $customerData['payment_provider'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_whatsapp, customer_email, customer_address, customer_cep, customer_street, customer_number, customer_complement, customer_neighborhood, customer_city, customer_state, items_json, total_amount, status, payment_status, payment_provider) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $customerData['name'],
        $customerData['whatsapp'],
        $customerData['email'],
        $customerData['address'],
        $customerData['cep'] ?? '',
        $customerData['street'] ?? '',
        $customerData['number'] ?? '',
        $customerData['complement'] ?? '',
        $customerData['neighborhood'] ?? '',
        $customerData['city'] ?? '',
        $customerData['state'] ?? '',
        json_encode($items),
        $total,
        $status,
        $paymentStatus,
        $paymentProvider
    ]);
    
    $orderId = (int)$pdo->lastInsertId();

    // Send Order Created Email
    require_once __DIR__ . '/EmailOrder.php';
    $emailOrder = new EmailOrder();
    $orderData = getOrder($orderId);
    if ($orderData) {
        $emailOrder->sendOrderCreatedEmail($orderData, $items, $total);
    }

    return $orderId;
}

function getAllOrders() {
    ensureOrdersSchema();
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function getOrder($id) {
    ensureOrdersSchema();
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function updateOrder($id, $data) {
    ensureOrdersSchema();
    global $pdo;
    
    // Fetch old order to compare status
    $oldOrder = getOrder($id);

    // Build query dynamically based on provided data
    $fields = [];
    $values = [];
    
    if (isset($data['customer_name'])) {
        $fields[] = "customer_name = ?";
        $values[] = $data['customer_name'];
    }
    if (isset($data['customer_whatsapp'])) {
        $fields[] = "customer_whatsapp = ?";
        $values[] = $data['customer_whatsapp'];
    }
    if (isset($data['customer_email'])) {
        $fields[] = "customer_email = ?";
        $values[] = $data['customer_email'];
    }
    if (isset($data['customer_address'])) {
        $fields[] = "customer_address = ?";
        $values[] = $data['customer_address'];
    }
    if (isset($data['customer_cep'])) {
        $fields[] = "customer_cep = ?";
        $values[] = $data['customer_cep'];
    }
    if (isset($data['customer_street'])) {
        $fields[] = "customer_street = ?";
        $values[] = $data['customer_street'];
    }
    if (isset($data['customer_number'])) {
        $fields[] = "customer_number = ?";
        $values[] = $data['customer_number'];
    }
    if (isset($data['customer_complement'])) {
        $fields[] = "customer_complement = ?";
        $values[] = $data['customer_complement'];
    }
    if (isset($data['customer_neighborhood'])) {
        $fields[] = "customer_neighborhood = ?";
        $values[] = $data['customer_neighborhood'];
    }
    if (isset($data['customer_city'])) {
        $fields[] = "customer_city = ?";
        $values[] = $data['customer_city'];
    }
    if (isset($data['customer_state'])) {
        $fields[] = "customer_state = ?";
        $values[] = $data['customer_state'];
    }
    if (isset($data['items_json'])) {
        $fields[] = "items_json = ?";
        $values[] = $data['items_json'];
    }
    if (isset($data['total_amount'])) {
        $fields[] = "total_amount = ?";
        $values[] = $data['total_amount'];
    }
    if (isset($data['status'])) {
        $fields[] = "status = ?";
        $values[] = $data['status'];
    }
    if (isset($data['payment_status'])) {
        $fields[] = "payment_status = ?";
        $values[] = $data['payment_status'];
    }
    if (array_key_exists('payment_provider', $data)) {
        $fields[] = "payment_provider = ?";
        $values[] = $data['payment_provider'];
    }
    if (array_key_exists('paid_at', $data)) {
        $fields[] = "paid_at = ?";
        $values[] = $data['paid_at'];
    }
    if (array_key_exists('tracking_number', $data)) {
        $fields[] = "tracking_number = ?";
        $values[] = $data['tracking_number'];
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $values[] = $id;
    $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($values);

    if ($result && isset($data['status']) && $oldOrder && $oldOrder['status'] !== $data['status']) {
        // Status has changed, send email
        require_once __DIR__ . '/EmailOrder.php';
        $emailOrder = new EmailOrder();
        // Use updated order data for the email
        $updatedOrder = getOrder($id);
        if ($updatedOrder) {
            $emailOrder->sendOrderStatusEmail($updatedOrder, $data['status']);
        }
    }

    return $result;
}

function deleteOrder($id) {
    ensureOrdersSchema();
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    return $stmt->execute([$id]);
}

function getOrdersByEmail($email) {
    ensureOrdersSchema();
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_email = ? ORDER BY created_at DESC");
    $stmt->execute([$email]);
    return $stmt->fetchAll();
}
