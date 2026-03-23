<?php
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/functions.php';

class EmailOrder
{
    /**
     * Envia o email de confirmação de criação de pedido para o cliente.
     */
    public function sendOrderCreatedEmail(array $order, array $items, float $total): array
    {
        $to = $order['customer_email'] ?? '';
        if (!$to) {
            return ['success' => false, 'message' => 'Email do cliente não encontrado.'];
        }

        $customerName = $order['customer_name'] ?? 'Cliente';
        $orderId = $order['id'] ?? 'N/A';
        $brand = getSetting('store_name', 'R2 Research Labs');
        $subject = 'Pedido Recebido - #' . $orderId . ' - ' . $brand;
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

        $currencySymbol = getSetting('store_currency_symbol', 'R$');

        $content = '<h1 style="margin: 0 0 16px 0; font-size: 20px; color: #111827;">Olá, ' . htmlspecialchars($customerName) . '!</h1>';
        $content .= '<p style="margin: 0 0 16px 0; line-height: 1.55;">Recebemos o seu pedido <strong>#' . $orderId . '</strong> e já estamos processando.</p>';
        
        $content .= '<h2 style="margin: 24px 0 12px 0; font-size: 16px; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px;">Resumo do Pedido</h2>';
        $content .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">';
        
        foreach ($items as $item) {
            $name = htmlspecialchars($item['name'] ?? 'Item');
            $qty = (int)($item['quantity'] ?? 1);
            $price = (float)($item['price'] ?? 0);
            $itemTotal = number_format($qty * $price, 2, ',', '.');
            
            $content .= '<tr>';
            $content .= '<td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6; color: #374151;">' . $qty . 'x ' . $name . '</td>';
            $content .= '<td style="padding: 8px 0; border-bottom: 1px solid #f3f4f6; text-align: right; color: #374151;">' . $currencySymbol . ' ' . $itemTotal . '</td>';
            $content .= '</tr>';
        }
        
        $content .= '<tr>';
        $content .= '<td style="padding: 12px 0; font-weight: bold; color: #111827;">Total</td>';
        $content .= '<td style="padding: 12px 0; font-weight: bold; text-align: right; color: #111827;">' . $currencySymbol . ' ' . number_format($total, 2, ',', '.') . '</td>';
        $content .= '</tr>';
        $content .= '</table>';

        $content .= '<p style="margin: 0 0 24px 0; line-height: 1.55;">Para acompanhar o status do seu pedido ou realizar o pagamento (se pendente), acesse sua conta no nosso site:</p>';
        
        $content .= '<div style="text-align: center; margin-bottom: 24px;">';
        $content .= '<a href="' . $baseUrl . '/account#orders" style="display: inline-block; background-color: #f97316; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-family: Helvetica, Arial, sans-serif;">Acompanhar Pedido</a>';
        $content .= '</div>';

        $html = renderEmailLayout('Pedido Recebido', $content);

        $altText = "Olá, {$customerName}!\n\nRecebemos o seu pedido #{$orderId}.\n\nTotal: {$currencySymbol} " . number_format($total, 2, ',', '.') . "\n\nAcesse {$baseUrl}/account para acompanhar o status.";

        return sendMailSMTP($to, $subject, $html, $altText);
    }

    /**
     * Envia o email de atualização de status do pedido para o cliente.
     */
    public function sendOrderStatusEmail(array $order, string $newStatus): array
    {
        $to = $order['customer_email'] ?? '';
        if (!$to) {
            return ['success' => false, 'message' => 'Email do cliente não encontrado.'];
        }

        $customerName = $order['customer_name'] ?? 'Cliente';
        $orderId = $order['id'] ?? 'N/A';
        $brand = getSetting('store_name', 'R2 Research Labs');
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

        $translatedStatus = ucfirst(__(strtolower($newStatus)));
        $subject = 'Atualização do Pedido #' . $orderId . ' - ' . $translatedStatus;

        $content = '<h1 style="margin: 0 0 16px 0; font-size: 20px; color: #111827;">Olá, ' . htmlspecialchars($customerName) . '!</h1>';
        $content .= '<p style="margin: 0 0 16px 0; line-height: 1.55;">O status do seu pedido <strong>#' . $orderId . '</strong> foi atualizado para:</p>';
        
        $content .= '<div style="margin: 18px 0; padding: 18px 22px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fafafa; text-align: center;">';
        $content .= '<span style="font-size: 22px; font-weight: bold; color: #111827;">' . htmlspecialchars($translatedStatus) . '</span>';
        $content .= '</div>';

        if (in_array(strtolower($newStatus), ['shipped', 'completed']) && !empty($order['tracking_number'])) {
            $content .= '<p style="margin: 0 0 16px 0; line-height: 1.55;"><strong>Código de Rastreio:</strong> ' . htmlspecialchars($order['tracking_number']) . '</p>';
        }

        $content .= '<p style="margin: 16px 0 24px 0; line-height: 1.55;">Você pode visualizar os detalhes do seu pedido na sua conta:</p>';
        
        $content .= '<div style="text-align: center; margin-bottom: 24px;">';
        $content .= '<a href="' . $baseUrl . '/account#orders" style="display: inline-block; background-color: #f97316; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-family: Helvetica, Arial, sans-serif;">Ver Pedido</a>';
        $content .= '</div>';

        $html = renderEmailLayout('Atualização de Pedido', $content);

        $altText = "Olá, {$customerName}!\n\nO status do seu pedido #{$orderId} foi atualizado para: {$translatedStatus}.\n\nAcesse {$baseUrl}/account para mais detalhes.";

        return sendMailSMTP($to, $subject, $html, $altText);
    }
}
