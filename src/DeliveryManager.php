<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/orders.php';
require_once __DIR__ . '/products.php';

class DeliveryManager
{
    /**
     * Gera tokens de entrega digital para os produtos de um pedido que são digitais.
     * Retorna os tokens gerados.
     */
    public function generateDeliveriesForOrder(int $orderId): array
    {
        global $pdo;

        $order = getOrder($orderId);
        if (!$order) {
            return [];
        }

        $items = json_decode($order['items_json'] ?? '[]', true);
        if (!is_array($items) || empty($items)) {
            return [];
        }

        $deliveries = [];

        foreach ($items as $item) {
            $productId = $item['id'] ?? null;
            if (!$productId) {
                continue;
            }

            $product = getProduct($productId);
            if (!$product) {
                continue;
            }

            // Check if product is digital
            $isDigital = ($product['type'] === 'digital') || (!empty($product['digital_delivery']));
            if (!$isDigital) {
                continue;
            }

            // Check if delivery already exists for this order and product
            $stmt = $pdo->prepare("SELECT * FROM order_digital_deliveries WHERE order_id = ? AND product_id = ?");
            $stmt->execute([$orderId, $productId]);
            $existing = $stmt->fetch();

            if ($existing) {
                $existing['product_name'] = $product['name'];
                $existing['product_slug'] = $product['slug'];
                $deliveries[] = $existing;
                continue;
            }

            $token = bin2hex(random_bytes(16)); // 32 chars random token
            $maxDownloads = (int)($product['download_limit'] ?? 0);
            $expiryDays = (int)($product['download_expiry_days'] ?? 0);
            
            $expiresAt = null;
            if ($expiryDays > 0) {
                $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiryDays days"));
            }

            $insertStmt = $pdo->prepare("
                INSERT INTO order_digital_deliveries 
                (order_id, product_id, token, max_downloads, expires_at) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $insertStmt->execute([
                $orderId,
                $productId,
                $token,
                $maxDownloads,
                $expiresAt
            ]);

            $deliveryId = $pdo->lastInsertId();
            
            $fetchStmt = $pdo->prepare("SELECT * FROM order_digital_deliveries WHERE id = ?");
            $fetchStmt->execute([$deliveryId]);
            $newDelivery = $fetchStmt->fetch();
            
            if ($newDelivery) {
                // Add product info to the delivery array for easier email building
                $newDelivery['product_name'] = $product['name'];
                $newDelivery['product_slug'] = $product['slug'];
                $deliveries[] = $newDelivery;
            }
        }

        return $deliveries;
    }

    /**
     * Marca as entregas como enviadas (delivered_at)
     */
    public function markAsDelivered(array $deliveryIds): void
    {
        global $pdo;
        if (empty($deliveryIds)) {
            return;
        }

        $placeholders = str_repeat('?,', count($deliveryIds) - 1) . '?';
        $sql = "UPDATE order_digital_deliveries SET delivered_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders) AND delivered_at IS NULL";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($deliveryIds);
    }

    /**
     * Retorna as entregas digitais para um determinado pedido.
     */
    public function getDeliveriesForOrder(int $orderId): array
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT d.*, p.name as product_name, p.slug as product_slug
            FROM order_digital_deliveries d
            JOIN products p ON d.product_id = p.id
            WHERE d.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
