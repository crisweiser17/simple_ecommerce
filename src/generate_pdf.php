<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generateOrderPDF($orderId, $customer, $items, $total) {
    require_once __DIR__ . '/orders.php';
    $order = getOrder($orderId);
    $isQuote = ($order && $order['status'] === 'quote');

    $storeName = getSetting('store_name', 'R2 Research Labs');
    // Configure Dompdf
    $options = new Options();
    // $options->set('defaultFont', 'Helvetica'); // Removed to fix font metrics error
    $options->set('isRemoteEnabled', true); // For images
    $dompdf = new Dompdf($options);

    // Build HTML for PDF
    $html = '
    <html>
    <head>
        <style>
            body { font-family: Helvetica, sans-serif; color: #333; }
            .header { text-align: center; margin-bottom: 40px; }
            .logo { font-size: 24px; font-weight: bold; }
            .details { margin-bottom: 30px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #f4f4f4; }
            .total { text-align: right; font-size: 18px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="logo">' . htmlspecialchars(strtoupper($storeName)) . '</div>
            <p>' . ($isQuote ? __('Orçamento / Lista de Desejos') : 'Order #' . $orderId) . '</p>
            <p>' . __('Date') . ': ' . date('Y-m-d') . '</p>
        </div>

        <div class="details">
            <h3>' . __('Customer Details') . '</h3>
            <p><strong>' . __('Full Name') . ':</strong> ' . htmlspecialchars($customer['name']) . '</p>
            <p><strong>' . __('Email') . ':</strong> ' . htmlspecialchars($customer['email']) . '</p>
            <p><strong>' . __('WhatsApp') . ':</strong> ' . htmlspecialchars($customer['whatsapp']) . '</p>
            <p><strong>' . __('Address') . ':</strong><br>';
            
    if (!empty($customer['street'])) {
        $html .= htmlspecialchars($customer['street']);
        if (!empty($customer['number'])) $html .= ', ' . htmlspecialchars($customer['number']);
        $html .= '<br>';
        if (!empty($customer['neighborhood'])) $html .= htmlspecialchars($customer['neighborhood']) . ' - ';
        if (!empty($customer['city'])) $html .= htmlspecialchars($customer['city']);
        if (!empty($customer['state'])) $html .= '/' . htmlspecialchars($customer['state']);
        $html .= '<br>';
        if (!empty($customer['cep'])) $html .= 'CEP: ' . htmlspecialchars($customer['cep']);
    } else {
        $html .= nl2br(htmlspecialchars($customer['address']));
    }
    
    $html .= '</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>' . __('Product') . '</th>
                    <th>' . __('SKU') . '</th>';
    if (!$isQuote) {
        $html .= '
                    <th>' . __('Price') . '</th>';
    }
    $html .= '
                    <th>' . __('Qty') . '</th>';
    if (!$isQuote) {
        $html .= '
                    <th>' . __('Total') . '</th>';
    }
    $html .= '
                </tr>
            </thead>
            <tbody>';
    
    foreach ($items as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        
        $variationsHtml = '';
        if (!empty($item['selected_variations']) && is_array($item['selected_variations'])) {
            $variationsHtml .= '<br><span style="font-size: 11px; color: #666;">';
            $vars = [];
            foreach ($item['selected_variations'] as $k => $v) {
                $vars[] = htmlspecialchars($k) . ': <strong>' . htmlspecialchars($v) . '</strong>';
            }
            $variationsHtml .= implode(', ', $vars);
            $variationsHtml .= '</span>';
        }
        
        $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['name']) . $variationsHtml . '</td>
                    <td>' . htmlspecialchars($item['sku'] ?? '') . '</td>';
        if (!$isQuote) {
            $html .= '
                    <td>' . formatMoney($item['price']) . '</td>';
        }
        $html .= '
                    <td>' . $item['quantity'] . '</td>';
        if (!$isQuote) {
            $html .= '
                    <td>' . formatMoney($itemTotal) . '</td>';
        }
        $html .= '
                </tr>';
    }

    $html .= '
            </tbody>
        </table>';

    if (!$isQuote) {
        $html .= '
        <div class="total">
            ' . __('Total Amount') . ': ' . formatMoney($total) . '
        </div>';
    }

    $html .= '
        <div style="margin-top: 50px; text-align: center; font-size: 12px; color: #777;">
            <p>' . __('Thank you for your business!') . '</p>
            <p>' . __('For research use only. Not for human consumption.') . '</p>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Stream the PDF
    $filePrefix = preg_replace('/[^a-z0-9]+/i', '_', $storeName);
    $filePrefix = trim($filePrefix, '_');
    if ($filePrefix === '') {
        $filePrefix = 'Order';
    }
    $dompdf->stream("Order_{$filePrefix}_$orderId.pdf", ["Attachment" => true]);
}
