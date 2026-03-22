<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/orders.php';

function ensurePaymentsSchema()
{
    global $pdo;
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        provider TEXT NOT NULL,
        provider_payment_id TEXT,
        provider_reference TEXT,
        amount REAL NOT NULL,
        currency TEXT DEFAULT 'BRL',
        status TEXT DEFAULT 'pending',
        pix_qr_code TEXT,
        pix_copy_paste TEXT,
        pix_expires_at DATETIME,
        gateway_payload TEXT,
        gateway_last_event TEXT,
        paid_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE
    )");
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_payments_provider_payment_id ON payments(provider, provider_payment_id)");
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_payments_provider_reference ON payments(provider, provider_reference)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_payments_order_id ON payments(order_id)");

    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        provider TEXT NOT NULL,
        event_id TEXT NOT NULL,
        payment_id TEXT,
        payload TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_payment_events_provider_event_id ON payment_events(provider, event_id)");

    ensureOrdersPaymentColumns();
}

function ensureOrdersPaymentColumns()
{
    global $pdo;
    $requiredColumns = [
        'payment_status' => "ALTER TABLE orders ADD COLUMN payment_status TEXT DEFAULT 'pending'",
        'payment_provider' => "ALTER TABLE orders ADD COLUMN payment_provider TEXT",
        'paid_at' => "ALTER TABLE orders ADD COLUMN paid_at DATETIME",
        'tracking_number' => "ALTER TABLE orders ADD COLUMN tracking_number TEXT",
        'customer_cep' => "ALTER TABLE orders ADD COLUMN customer_cep TEXT",
        'customer_street' => "ALTER TABLE orders ADD COLUMN customer_street TEXT",
        'customer_number' => "ALTER TABLE orders ADD COLUMN customer_number TEXT",
        'customer_neighborhood' => "ALTER TABLE orders ADD COLUMN customer_neighborhood TEXT",
        'customer_city' => "ALTER TABLE orders ADD COLUMN customer_city TEXT",
        'customer_state' => "ALTER TABLE orders ADD COLUMN customer_state TEXT"
    ];

    $columns = [];
    $stmt = $pdo->query("PRAGMA table_info(orders)");
    foreach ($stmt->fetchAll() as $column) {
        $columns[$column['name']] = true;
    }

    foreach ($requiredColumns as $name => $sql) {
        if (!isset($columns[$name])) {
            $pdo->exec($sql);
        }
    }
}

function createPaymentForOrder(int $orderId, string $provider, float $amount, array $paymentData): int
{
    global $pdo;
    ensurePaymentsSchema();
    $stmt = $pdo->prepare("INSERT INTO payments (order_id, provider, provider_payment_id, provider_reference, amount, currency, status, pix_qr_code, pix_copy_paste, pix_expires_at, gateway_payload, gateway_last_event) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $orderId,
        $provider,
        $paymentData['transaction_id'] ?? null,
        $paymentData['reference'] ?? null,
        $amount,
        $paymentData['currency'] ?? 'BRL',
        $paymentData['status'] ?? 'pending',
        $paymentData['pix_qr_code'] ?? null,
        $paymentData['pix_copy_paste'] ?? null,
        $paymentData['pix_expires_at'] ?? null,
        json_encode($paymentData['payload'] ?? [], JSON_UNESCAPED_UNICODE),
        null
    ]);
    return (int)$pdo->lastInsertId();
}

function updatePaymentByProviderPaymentId(string $provider, string $providerPaymentId, array $data): bool
{
    global $pdo;
    ensurePaymentsSchema();
    $payment = getPaymentByProviderPaymentId($provider, $providerPaymentId);
    if (!$payment) {
        return false;
    }
    return updatePayment((int)$payment['id'], $data);
}

function updatePayment(int $paymentId, array $data): bool
{
    global $pdo;
    ensurePaymentsSchema();
    $fields = [];
    $values = [];

    if (isset($data['status'])) {
        $fields[] = 'status = ?';
        $values[] = $data['status'];
    }
    if (array_key_exists('pix_qr_code', $data)) {
        $fields[] = 'pix_qr_code = ?';
        $values[] = $data['pix_qr_code'];
    }
    if (array_key_exists('pix_copy_paste', $data)) {
        $fields[] = 'pix_copy_paste = ?';
        $values[] = $data['pix_copy_paste'];
    }
    if (array_key_exists('pix_expires_at', $data)) {
        $fields[] = 'pix_expires_at = ?';
        $values[] = $data['pix_expires_at'];
    }
    if (array_key_exists('gateway_payload', $data)) {
        $fields[] = 'gateway_payload = ?';
        $values[] = is_string($data['gateway_payload']) ? $data['gateway_payload'] : json_encode($data['gateway_payload'], JSON_UNESCAPED_UNICODE);
    }
    if (array_key_exists('gateway_last_event', $data)) {
        $fields[] = 'gateway_last_event = ?';
        $values[] = $data['gateway_last_event'];
    }
    if (array_key_exists('paid_at', $data)) {
        $fields[] = 'paid_at = ?';
        $values[] = $data['paid_at'];
    }

    if (empty($fields)) {
        return false;
    }

    $fields[] = 'updated_at = CURRENT_TIMESTAMP';
    $values[] = $paymentId;
    $stmt = $pdo->prepare('UPDATE payments SET ' . implode(', ', $fields) . ' WHERE id = ?');
    return $stmt->execute($values);
}

function getPaymentByOrderId(int $orderId)
{
    global $pdo;
    ensurePaymentsSchema();
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$orderId]);
    return $stmt->fetch();
}

function getPaymentByProviderPaymentId(string $provider, string $providerPaymentId)
{
    global $pdo;
    ensurePaymentsSchema();
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE provider = ? AND provider_payment_id = ? LIMIT 1");
    $stmt->execute([$provider, $providerPaymentId]);
    return $stmt->fetch();
}

function getPaymentByProviderReference(string $provider, string $reference)
{
    global $pdo;
    ensurePaymentsSchema();
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE provider = ? AND provider_reference = ? LIMIT 1");
    $stmt->execute([$provider, $reference]);
    return $stmt->fetch();
}

function registerPaymentEvent(string $provider, string $eventId, ?string $paymentId, array $payload): bool
{
    global $pdo;
    ensurePaymentsSchema();
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO payment_events (provider, event_id, payment_id, payload) VALUES (?, ?, ?, ?)");
    return $stmt->execute([
        $provider,
        $eventId,
        $paymentId,
        json_encode($payload, JSON_UNESCAPED_UNICODE)
    ]);
}

function hasProcessedPaymentEvent(string $provider, string $eventId): bool
{
    global $pdo;
    ensurePaymentsSchema();
    $stmt = $pdo->prepare("SELECT id FROM payment_events WHERE provider = ? AND event_id = ? LIMIT 1");
    $stmt->execute([$provider, $eventId]);
    return (bool)$stmt->fetchColumn();
}

function markOrderAsPaidByPayment(array $payment, string $finalOrderStatus = 'paid'): bool
{
    global $pdo;
    ensurePaymentsSchema();

    if (empty($payment['order_id'])) {
        return false;
    }

    $paidAt = date('Y-m-d H:i:s');
    try {
        $pdo->beginTransaction();

        $okPayment = updatePayment((int)$payment['id'], [
            'status' => 'paid',
            'paid_at' => $paidAt
        ]);
        if (!$okPayment) {
            $pdo->rollBack();
            return false;
        }

        $okOrder = updateOrder((int)$payment['order_id'], [
            'status' => $finalOrderStatus,
            'payment_status' => 'paid',
            'payment_provider' => $payment['provider'] ?? '',
            'paid_at' => $paidAt
        ]);
        if (!$okOrder) {
            $pdo->rollBack();
            return false;
        }

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}

function getPaymentEventsByProviderPaymentId(string $provider, string $paymentId): array
{
    global $pdo;
    ensurePaymentsSchema();
    $stmt = $pdo->prepare("SELECT * FROM payment_events WHERE provider = ? AND payment_id = ? ORDER BY created_at DESC");
    $stmt->execute([$provider, $paymentId]);
    return $stmt->fetchAll();
}
