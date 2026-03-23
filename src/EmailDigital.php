<?php
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/functions.php';

class EmailDigital
{
    /**
     * Envia o email de entrega digital para o cliente.
     */
    public function sendDeliveryEmail(array $order, array $deliveries): array
    {
        if (empty($deliveries)) {
            return ['success' => false, 'message' => 'Nenhuma entrega digital para enviar.'];
        }

        $to = $order['customer_email'];
        if (!$to) {
            return ['success' => false, 'message' => 'Email do cliente não encontrado no pedido.'];
        }

        $customerName = $order['customer_name'] ?? 'Cliente';
        $brand = getSetting('store_name', 'R2 Research Labs');
        $subject = 'Seus produtos digitais chegaram! - Pedido #' . $order['id'];

        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        
        $htmlLinks = '';
        $textLinks = '';

        foreach ($deliveries as $delivery) {
            $productName = $delivery['product_name'] ?? 'Produto Digital';
            $downloadUrl = $baseUrl . '/download/' . $delivery['token'];
            
            $htmlLinks .= '<div style="margin: 18px 0; padding: 18px 22px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fafafa;">';
            $htmlLinks .= '<h3 style="margin: 0 0 12px 0; font-size: 16px; color: #111827;">' . htmlspecialchars($productName) . '</h3>';
            $htmlLinks .= '<a href="' . htmlspecialchars($downloadUrl) . '" style="display: inline-block; padding: 10px 20px; background-color: #000; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">Baixar Arquivo</a>';
            
            if ($delivery['max_downloads'] > 0) {
                $htmlLinks .= '<p style="margin: 10px 0 0 0; font-size: 12px; color: #6b7280;">Limite de ' . $delivery['max_downloads'] . ' download(s).</p>';
            }
            if (!empty($delivery['expires_at'])) {
                $expiryDate = date('d/m/Y H:i', strtotime($delivery['expires_at']));
                $htmlLinks .= '<p style="margin: 5px 0 0 0; font-size: 12px; color: #6b7280;">Expira em: ' . $expiryDate . '</p>';
            }
            $htmlLinks .= '</div>';

            $textLinks .= "- " . $productName . "\n";
            $textLinks .= "  Link para baixar: " . $downloadUrl . "\n";
            if ($delivery['max_downloads'] > 0) {
                $textLinks .= "  (Limite de " . $delivery['max_downloads'] . " download(s))\n";
            }
            if (!empty($delivery['expires_at'])) {
                $textLinks .= "  (Expira em: " . date('d/m/Y H:i', strtotime($delivery['expires_at'])) . ")\n";
            }
            $textLinks .= "\n";
        }

        $content = '<h1 style="margin: 0 0 16px 0; font-size: 20px; color: #111827;">Olá, ' . htmlspecialchars($customerName) . '!</h1>';
        $content .= '<p style="margin: 0 0 16px 0; line-height: 1.55;">Obrigado por sua compra! Seus produtos digitais do pedido <strong>#' . htmlspecialchars($order['id'] ?? '') . '</strong> já estão disponíveis para download.</p>';
        $content .= $htmlLinks;
        $content .= '<p style="margin: 24px 0 0 0; line-height: 1.55; font-size: 14px; color: #4b5563;">Caso tenha qualquer dúvida ou problema com o download, por favor entre em contato respondendo a este e-mail.</p>';

        $html = renderEmailLayout('Produtos Digitais', $content);

        $altText = "Olá, " . $customerName . "!\n\n";
        $altText .= "Obrigado por sua compra. Seus produtos digitais já estão disponíveis para download.\n\n";
        $altText .= $textLinks;
        $altText .= "Caso tenha qualquer dúvida ou problema com o download, por favor entre em contato respondendo a este e-mail.\n";

        return sendMailSMTP($to, $subject, $html, $altText);
    }
}
