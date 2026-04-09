<?php
if (!isset($order) || !$order) {
    header('Location: /');
    exit;
}

// Fix for old orders using qrserver.com
if (!empty($payment) && ($payment['provider'] ?? '') === 'manual_pix' && strpos($payment['pix_qr_code'] ?? '', 'qrserver.com') !== false && !empty($payment['pix_copy_paste']) && class_exists('\chillerlan\QRCode\QRCode')) {
    $qrOptions = new \chillerlan\QRCode\QROptions([
        'scale' => 5,
        'eccLevel' => \chillerlan\QRCode\Common\EccLevel::M,
        'addQuietzone' => true,
    ]);
    $payment['pix_qr_code'] = (new \chillerlan\QRCode\QRCode($qrOptions))->render($payment['pix_copy_paste']);
}

$items = json_decode($order['items_json'], true);
$storeWhatsapp = getSetting('store_whatsapp');
$payment = $payment ?? null;
$enabledPaymentProviders = isset($enabledPaymentProviders) && is_array($enabledPaymentProviders) ? $enabledPaymentProviders : [];
$paymentStatus = $payment['status'] ?? ($order['payment_status'] ?? 'pending');
$pixQrCode = $payment['pix_qr_code'] ?? '';
$pixCopyPaste = $payment['pix_copy_paste'] ?? '';
$pixExpiresAt = $payment['pix_expires_at'] ?? '';
$paymentErrorMessage = $paymentErrorMessage ?? '';
$gatewayPayload = [];
if (!empty($payment['gateway_payload']) && is_string($payment['gateway_payload'])) {
    $decodedGatewayPayload = json_decode($payment['gateway_payload'], true);
    if (is_array($decodedGatewayPayload)) {
        $gatewayPayload = $decodedGatewayPayload;
    }
}
$whatsappEnabled = getSetting('enable_whatsapp_button', '1') === '1';
$showWhatsappButton = !empty($storeWhatsapp) && $whatsappEnabled;
$hasEnabledPaymentModules = !empty($enabledPaymentProviders);
$showPaymentSection = $hasEnabledPaymentModules && (($payment !== false && $payment !== null) || $paymentErrorMessage !== '');

$isManualPix = ($payment['provider'] ?? '') === 'manual_pix';
$isMercadoPago = ($payment['provider'] ?? '') === 'mercadopago';

$customInstructionsEnabled = getSetting('payment_instructions_enabled', '0') === '1';
$customInstructionsText = getSetting('payment_instructions_text', '');

$message = "*New Order #{$order['id']}*\n";
$message .= "Date: " . date('Y-m-d', strtotime($order['created_at'])) . "\n\n";
$message .= "*Customer:*\n";
$message .= "Name: {$order['customer_name']}\n";
$message .= "Email: {$order['customer_email']}\n";
$message .= "WhatsApp: {$order['customer_whatsapp']}\n";
$message .= "Address: {$order['customer_address']}\n\n";
$message .= "*Items:*\n";
foreach ($items as $item) {
    $message .= "- {$item['quantity']}x {$item['name']} (SKU: {$item['sku']})\n";
    if (!empty($item['selected_variations']) && is_array($item['selected_variations'])) {
        foreach ($item['selected_variations'] as $k => $v) {
            $message .= "  ↳ $k: $v\n";
        }
    }
}
$message .= "\n*Total: " . formatMoney($order['total_amount']) . "*";

$whatsappUrl = "https://wa.me/{$storeWhatsapp}?text=" . urlencode($message);
?>

<div class="container mx-auto px-4 py-16 text-center" x-init="$store.cart.clear()">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl mx-auto">
        <div class="text-green-500 mb-6">
            <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold mb-4 text-gray-900"><?php echo __('Order Received!'); ?></h1>
        <p class="text-gray-600 mb-8"><?php echo __('Thank you for your order. Your order number is'); ?> <span class="font-bold text-gray-900">#<?php echo $order['id']; ?></span></p>

        <?php if ($paymentErrorMessage !== ''): ?>
            <div class="bg-red-50 border border-red-200 rounded p-3 text-red-700 text-sm mb-6">
                <?php echo htmlspecialchars($paymentErrorMessage); ?>
            </div>
        <?php endif; ?>

        <?php if ($showPaymentSection): ?>
        <div class="text-left bg-gray-50 rounded p-4 mb-6 border border-gray-200">
            <div class="flex justify-between items-center">
                <p class="font-semibold text-gray-800"><?php echo __('Status do pagamento'); ?></p>
                <span id="payment-status-badge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $paymentStatus === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                    <?php echo htmlspecialchars(ucfirst(__(strtolower($paymentStatus)))); ?>
                </span>
            </div>
            <?php if ($pixExpiresAt !== ''): ?>
                <p class="text-xs text-gray-500 mt-2"><?php echo __('Expira em'); ?>: <?php echo htmlspecialchars($pixExpiresAt); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($showPaymentSection && $isManualPix): ?>
            <div class="text-left bg-white border border-gray-200 rounded p-4 mb-6">
                <p class="text-lg font-semibold text-gray-800 mb-2"><?php echo __('Pagamento via PIX'); ?></p>
                <p class="text-md text-gray-700 mb-4"><?php echo __('Valor a pagar'); ?>: <strong class="text-xl"><?php echo formatMoney($order['total_amount']); ?></strong></p>
                
                <?php if ($pixQrCode !== ''): ?>
                    <p class="text-sm font-semibold text-gray-800 mb-2"><?php echo __('Escaneie o QR Code'); ?></p>
                    <img src="<?php echo htmlspecialchars($pixQrCode); ?>" alt="QR Code PIX" class="mx-auto max-w-xs border rounded mb-4">
                <?php endif; ?>

                <p class="text-sm font-semibold text-gray-800 mb-2"><?php echo __('Ou pague com a Chave PIX:'); ?></p>
                <?php $recipientName = $gatewayPayload['recipient_name'] ?? getSetting('payment_manual_pix_recipient_name', ''); ?>
                <?php if (!empty($recipientName)): ?>
                    <p class="text-sm text-gray-600 mb-2"><?php echo __('Nome do recebedor (Titular): '); ?><strong><?php echo htmlspecialchars($recipientName); ?></strong></p>
                <?php endif; ?>
                <div class="flex gap-2">
                    <input type="text" id="manual-pix-key" readonly class="w-full border rounded p-2 text-sm text-gray-700 bg-gray-50" value="<?php echo htmlspecialchars($gatewayPayload['pix_key'] ?? getSetting('payment_manual_pix_key', '')); ?>">
                    <button type="button" onclick="copyManualPix()" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-700 text-sm whitespace-nowrap"><?php echo __('Copiar Chave'); ?></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($showPaymentSection && $isMercadoPago): ?>
            <div class="text-left bg-white border border-gray-200 rounded p-4 mb-6">
                <p class="text-lg font-semibold text-gray-800 mb-2"><?php echo __('Pagamento via PIX'); ?></p>
                <p class="text-md text-gray-700 mb-4"><?php echo __('Valor a pagar'); ?>: <strong class="text-xl"><?php echo formatMoney($order['total_amount']); ?></strong></p>
                
                <div id="payment-success-message" class="mb-4 bg-green-50 border border-green-200 rounded p-4 text-center" style="display: <?php echo $paymentStatus === 'paid' ? 'block' : 'none'; ?>;">
                    <svg class="w-12 h-12 mx-auto text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-lg text-green-800 font-bold"><?php echo __('Pagamento Confirmado!'); ?></p>
                    <p class="text-sm text-green-700 mt-1"><?php echo __('Obrigado! O seu pagamento foi identificado e o seu pedido já está sendo processado.'); ?></p>
                </div>

                <?php if ($paymentStatus !== 'paid'): ?>
                <?php 
                    $createdAtIso = str_replace(' ', 'T', $order['created_at']) . 'Z';
                ?>
                <div x-data="pixTimer('<?php echo $createdAtIso; ?>')" id="pix-timer-container" class="mb-4 bg-red-50 border border-red-200 rounded p-3 text-center">
                    <p class="text-sm text-red-800 font-semibold mb-1"><?php echo __('Tempo restante para pagamento:'); ?></p>
                    <div class="text-3xl font-bold text-red-600 tabular-nums" x-text="timeDisplay">10:00</div>
                    <div x-show="expired" style="display: none;" class="text-sm text-red-600 font-bold mt-2"><?php echo __('O tempo para pagamento expirou. Por favor, faça um novo pedido.'); ?></div>
                </div>
                <?php endif; ?>

                <?php if ($pixQrCode !== '' && $paymentStatus !== 'paid'): ?>
                    <div x-data="{ expired: false }" @timer-expired.window="expired = true" x-show="!expired">
                        <p class="text-sm font-semibold text-gray-800 mb-2">QR Code</p>
                        <?php if (strpos($pixQrCode, 'data:image') === 0 || strpos($pixQrCode, 'http') === 0): ?>
                            <img src="<?php echo htmlspecialchars($pixQrCode); ?>" alt="QR Code PIX" class="mx-auto max-w-xs border rounded mb-4">
                        <?php elseif (preg_match('/^[A-Za-z0-9+\/=]+$/', $pixQrCode)): ?>
                            <img src="data:image/png;base64,<?php echo htmlspecialchars($pixQrCode); ?>" alt="QR Code PIX" class="mx-auto max-w-xs border rounded mb-4">
                        <?php else: ?>
                            <p class="text-xs text-gray-600 break-all mb-4"><?php echo htmlspecialchars($pixQrCode); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($pixCopyPaste !== '' && $paymentStatus !== 'paid'): ?>
                    <div x-data="{ expired: false }" @timer-expired.window="expired = true" x-show="!expired">
                        <p class="text-sm font-semibold text-gray-800 mb-2"><?php echo __('PIX copia e cola'); ?></p>
                        <textarea id="pix-copy-paste" readonly class="w-full border rounded p-2 text-xs text-gray-700 h-28"><?php echo htmlspecialchars($pixCopyPaste); ?></textarea>
                        <button type="button" onclick="copyPixCode()" class="mt-2 w-full sm:w-auto bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-700"><?php echo __('Copiar código PIX'); ?></button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($customInstructionsEnabled && !empty($customInstructionsText)): ?>
            <div class="text-left bg-blue-50 border border-blue-200 rounded p-4 mb-6">
                <h3 class="text-md font-semibold text-blue-900 mb-2"><?php echo __('Instruções de Pagamento'); ?></h3>
                <div class="text-sm text-blue-800 prose prose-sm max-w-none">
                    <?php echo $customInstructionsText; // Output HTML directly from WYSIWYG ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="space-y-4">
            <a href="/download-pdf?id=<?php echo $order['id']; ?>" class="block w-full bg-gray-800 text-white font-bold py-3 px-6 rounded hover:bg-gray-700 transition-colors">
                <?php echo __('Download Order PDF'); ?>
            </a>
            
            <?php if ($showWhatsappButton): ?>
            <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="block w-full bg-green-500 text-white font-bold py-3 px-6 rounded hover:bg-green-600 transition-colors flex items-center justify-center gap-2">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.017-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                <?php echo __('Send to WhatsApp'); ?>
            </a>
            <?php endif; ?>

            <a href="/" class="block text-orange-500 hover:text-orange-600 mt-4">
                <?php echo __('Return to Home'); ?>
            </a>
        </div>
    </div>
</div>

<script>
    function copyPixCode() {
        const field = document.getElementById('pix-copy-paste');
        if (!field) return;
        field.select();
        field.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(field.value);
    }

    function copyManualPix() {
        const field = document.getElementById('manual-pix-key');
        if (!field) return;
        field.select();
        field.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(field.value);
    }

    (function startPaymentPolling() {
        const badge = document.getElementById('payment-status-badge');
        if (!badge) return;
        if (badge.textContent.trim().toLowerCase() === 'paid') return;
        const orderId = <?php echo (int)$order['id']; ?>;

        const tick = () => {
            fetch('/api/orders/payment-status?id=' + orderId)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const status = (data.payment_status || 'pending').toLowerCase();
                    const statusMap = {
                        'pending': '<?php echo ucfirst(__('pending')); ?>',
                        'paid': '<?php echo ucfirst(__('paid')); ?>',
                        'cancelled': '<?php echo ucfirst(__('cancelled')); ?>',
                        'shipped': '<?php echo ucfirst(__('shipped')); ?>',
                        'completed': '<?php echo ucfirst(__('completed')); ?>'
                    };
                    badge.textContent = statusMap[status] || status;
                    badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' + (status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800');
                    if (status === 'paid') {
                        clearInterval(loop);
                        const timerContainer = document.getElementById('pix-timer-container');
                        if (timerContainer) timerContainer.style.display = 'none';
                        const successMessage = document.getElementById('payment-success-message');
                        if (successMessage) successMessage.style.display = 'block';
                        window.dispatchEvent(new CustomEvent('timer-expired'));
                    }
                });
        };
        const loop = setInterval(tick, 5000);
        tick();
    })();

    document.addEventListener('alpine:init', () => {
        Alpine.data('pixTimer', (createdAtIso) => ({
            timeDisplay: '10:00',
            expired: false,
            interval: null,
            init() {
                const createdAtTime = new Date(createdAtIso).getTime();
                const expiresAt = createdAtTime + (10 * 60 * 1000);
                
                this.updateTimer(expiresAt);
                this.interval = setInterval(() => this.updateTimer(expiresAt), 1000);
            },
            updateTimer(expiresAt) {
                const now = new Date().getTime();
                const distance = expiresAt - now;

                if (distance <= 0) {
                    clearInterval(this.interval);
                    this.timeDisplay = '00:00';
                    if (!this.expired) {
                        this.expired = true;
                        window.dispatchEvent(new CustomEvent('timer-expired'));
                    }
                    return;
                }

                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                this.timeDisplay = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            }
        }));
    });
</script>
