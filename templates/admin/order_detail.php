<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo __('Edit Order'); ?> #<?php echo $order['id']; ?> - <?php echo htmlspecialchars(getSetting('store_name', 'R2 Research Labs')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        function maskPhone(event) {
            let input = event.target;
            let value = input.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 10) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else if (value.length > 0) {
                value = value.replace(/^(\d{0,2})/, '($1');
            }
            input.value = value;
        }
    </script>
</head>
<body class="bg-gray-100 font-sans flex flex-col h-screen" x-data="orderEditor()">
    
    <div class="flex flex-1 overflow-hidden relative">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" class="fixed inset-0 z-20 bg-black bg-opacity-50 md:hidden" @click="sidebarOpen = false" style="display: none;"></div>

        <!-- Sidebar -->
        <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed md:static inset-y-0 left-0 z-30 w-64 bg-gray-900 text-white flex flex-col transition-transform duration-300 md:translate-x-0 h-full overflow-y-auto">
            <div class="p-4 text-xl font-bold border-b border-gray-800 flex justify-between items-center">
                <span><?php echo __('Admin Dashboard'); ?></span>
                <button @click="sidebarOpen = false" class="md:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="/admin" class="block w-full text-left px-4 py-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded">
                    &larr; <?php echo __('Back to Dashboard'); ?>
                </a>
                <a href="/" class="block w-full text-left px-4 py-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded">
                    <?php echo __('Back to Site'); ?>
                </a>
                <a href="/logout" class="block w-full text-left px-4 py-2 text-red-400 hover:text-red-300 hover:bg-gray-800 rounded">
                    <?php echo __('Logout'); ?>
                </a>
                
                <?php if (isset($isMultilangEnabled) && $isMultilangEnabled): ?>
                <div class="border-t border-gray-800 my-2"></div>
                <div class="px-4 py-2">
                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-2"><?php echo __('Language'); ?></span>
                    <div class="flex gap-2">
                        <a href="<?php $q = $_GET; $q['lang'] = 'en'; echo '?' . http_build_query($q); ?>" class="text-xs px-2 py-1 rounded <?php echo ($_SESSION['lang'] ?? 'en') === 'en' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'; ?>">EN</a>
                        <a href="<?php $q = $_GET; $q['lang'] = 'pt'; echo '?' . http_build_query($q); ?>" class="text-xs px-2 py-1 rounded <?php echo ($_SESSION['lang'] ?? 'en') === 'pt' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'; ?>">PT</a>
                    </div>
                </div>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Mobile Header -->
            <div class="md:hidden bg-white border-b border-gray-200 flex items-center justify-between p-4 flex-shrink-0 shadow-sm z-10">
                <span class="font-bold text-lg text-gray-800"><?php echo __('Edit Order'); ?> #<?php echo $order['id']; ?></span>
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 hover:text-gray-900 focus:outline-none p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-auto p-4 md:p-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold"><?php echo __('Order'); ?> #<?php echo $order['id']; ?></h1>
                </div>

                <form @submit.prevent="saveOrder" class="bg-white rounded-lg shadow-md p-6 space-y-6">
                    
                    <!-- Status & Tracking -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Status'); ?></label>
                            <select x-model="order.status" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                <option value="pending_payment"><?php echo htmlspecialchars(ucfirst(__('pending_payment'))); ?></option>
                                <option value="paid"><?php echo htmlspecialchars(ucfirst(__('paid'))); ?></option>
                                <option value="shipped"><?php echo htmlspecialchars(ucfirst(__('shipped'))); ?></option>
                                <option value="completed"><?php echo htmlspecialchars(ucfirst(__('completed'))); ?></option>
                                <option value="cancelled"><?php echo htmlspecialchars(ucfirst(__('cancelled'))); ?></option>
                                <option value="pending"><?php echo htmlspecialchars(ucfirst(__('pending'))); ?></option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2">Payment status: <?php echo htmlspecialchars(ucfirst(__(strtolower($order['payment_status'] ?? 'pending')))); ?></p>
                        </div>
                        <div x-show="['shipped', 'completed'].includes(order.status)">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Tracking Number'); ?></label>
                            <input type="text" x-model="order.tracking_number" class="w-full border-gray-300 rounded-md shadow-sm p-2 border" placeholder="<?php echo __('Enter tracking code'); ?>">
                        </div>
                    </div>

                    <!-- Items -->
                    <div>
                        <h2 class="text-xl font-semibold mb-4 border-b pb-2"><?php echo __('Order Items'); ?></h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"><?php echo __('Item'); ?></th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Qty</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Price</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Total</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase"><?php echo __('Actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="(item, index) in order.items" :key="index">
                                        <tr>
                                            <td class="px-4 py-2">
                                                <input type="text" x-model="item.name" class="w-full border-gray-300 rounded-sm p-1 text-sm border">
                                                <template x-if="item.selected_variations">
                                                    <div class="text-xs text-gray-500 mt-1 flex flex-wrap gap-1">
                                                        <template x-for="[vName, vOpt] in Object.entries(item.selected_variations)" :key="vName">
                                                            <span class="bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200"><span x-text="vName"></span>: <strong x-text="vOpt"></strong></span>
                                                        </template>
                                                    </div>
                                                </template>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" x-model.number="item.quantity" @input="calculateTotal" min="1" class="w-full border-gray-300 rounded-sm p-1 text-sm border">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" x-model.number="item.price" @input="calculateTotal" step="0.01" class="w-full border-gray-300 rounded-sm p-1 text-sm border">
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900" x-text="'<?php echo getSetting('store_currency_symbol', 'R$'); ?> ' + (item.quantity * item.price).toFixed(2)"></td>
                                            <td class="px-4 py-2 text-right">
                                                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900 text-sm"><?php echo __('Remove'); ?></button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-700"><?php echo __('Total Amount'); ?>:</td>
                                        <td class="px-4 py-3 font-bold text-green-700 text-lg" x-text="'<?php echo getSetting('store_currency_symbol', 'R$'); ?> ' + order.total_amount.toFixed(2)"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Customer Details -->
                    <div>
                        <h2 class="text-xl font-semibold mb-4 border-b pb-2"><?php echo __('Customer Information'); ?></h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700"><?php echo __('Name'); ?></label>
                                <input type="text" x-model="order.customer_name" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700"><?php echo __('Email'); ?></label>
                                <input type="email" x-model="order.customer_email" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">WhatsApp</label>
                                <div class="flex gap-2">
                                    <input type="text" x-model="order.customer_whatsapp" class="flex-1 border-gray-300 rounded-md shadow-sm p-2 border" oninput="maskPhone(event)">
                                    <template x-if="order.customer_whatsapp">
                                        <a :href="'https://wa.me/55' + order.customer_whatsapp.replace(/\D/g, '')" target="_blank" class="inline-flex items-center justify-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" title="<?php echo __('Contact on WhatsApp'); ?>">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-md font-semibold mb-2"><?php echo __('Address Details'); ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('CEP'); ?></label>
                                    <input type="text" x-model="order.customer_cep" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Street'); ?></label>
                                    <input type="text" x-model="order.customer_street" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Number'); ?></label>
                                    <input type="text" x-model="order.customer_number" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Complement'); ?></label>
                                    <input type="text" x-model="order.customer_complement" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Neighborhood'); ?></label>
                                    <input type="text" x-model="order.customer_neighborhood" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('City'); ?></label>
                                    <input type="text" x-model="order.customer_city" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('State'); ?></label>
                                    <input type="text" x-model="order.customer_state" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                            </div>
                            <div class="mt-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Full Address String (Legacy/Display)'); ?></label>
                                    <button type="button" @click="copyAddress()" class="text-sm bg-indigo-100 text-indigo-700 hover:bg-indigo-200 px-3 py-1 rounded transition-colors flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        <span x-text="copied ? '<?php echo __('Copied!'); ?>' : '<?php echo __('Copy'); ?>'"></span>
                                    </button>
                                </div>
                                <div class="bg-white p-3 rounded border border-gray-300 text-gray-800 font-mono text-sm whitespace-pre-wrap" x-text="formattedAddress"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment & Audit Information -->
                    <?php if (isset($payment) && $payment): ?>
                    <div>
                        <h2 class="text-xl font-semibold mb-4 border-b pb-2"><?php echo __('Payment & Audit Information'); ?></h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-sm text-gray-500 font-medium"><?php echo __('Provider'); ?></p>
                                <p class="text-gray-900"><?php echo htmlspecialchars(ucfirst($payment['provider'])); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium"><?php echo __('Provider Payment ID'); ?></p>
                                <p class="text-gray-900"><?php echo htmlspecialchars($payment['provider_payment_id'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium"><?php echo __('Amount / Currency'); ?></p>
                                <p class="text-gray-900"><?php echo number_format((float)$payment['amount'], 2); ?> <?php echo htmlspecialchars($payment['currency'] ?? 'BRL'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium"><?php echo __('Payment Status'); ?></p>
                                <p class="text-gray-900"><?php echo htmlspecialchars($payment['status']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium"><?php echo __('Created At'); ?></p>
                                <p class="text-gray-900"><?php echo htmlspecialchars($payment['created_at']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium"><?php echo __('Paid At'); ?></p>
                                <p class="text-gray-900"><?php echo htmlspecialchars($payment['paid_at'] ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <h3 class="text-lg font-medium text-gray-800 mb-3"><?php echo __('Webhook Events'); ?></h3>
                        <?php if (isset($paymentEvents) && !empty($paymentEvents)): ?>
                            <div class="space-y-4 mb-6">
                                <?php foreach ($paymentEvents as $event): ?>
                                    <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-medium text-indigo-700">Event ID: <?php echo htmlspecialchars($event['event_id']); ?></span>
                                            <span class="text-sm text-gray-500"><?php echo htmlspecialchars($event['created_at']); ?></span>
                                        </div>
                                        <details class="text-sm">
                                            <summary class="cursor-pointer text-gray-600 hover:text-gray-900 font-medium select-none"><?php echo __('View Payload'); ?></summary>
                                            <div class="mt-2 bg-gray-900 text-green-400 p-3 rounded overflow-x-auto">
                                                <pre><code><?php 
                                                    $payloadArr = json_decode($event['payload'], true);
                                                    echo htmlspecialchars($payloadArr ? json_encode($payloadArr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $event['payload']); 
                                                ?></code></pre>
                                            </div>
                                        </details>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 italic mb-6"><?php echo __('No webhook events recorded for this payment.'); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="flex justify-between pt-6 border-t">
                        <button type="button" @click="deleteOrder" class="text-red-600 hover:text-red-900 border border-red-200 px-4 py-2 rounded-md hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                            <?php echo __('Delete Order'); ?>
                        </button>
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                            <?php echo __('Save Changes'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <script>
        function orderEditor() {
            return {
                sidebarOpen: false,
                copied: false,
                order: {
                    id: <?php echo json_encode($order['id']); ?>,
                    customer_name: <?php echo json_encode($order['customer_name']); ?>,
                    customer_email: <?php echo json_encode($order['customer_email']); ?>,
                    customer_whatsapp: <?php echo json_encode($order['customer_whatsapp']); ?>,
                    customer_address: <?php echo json_encode($order['customer_address']); ?>,
                    customer_cep: <?php echo json_encode($order['customer_cep'] ?? ''); ?>,
                    customer_street: <?php echo json_encode($order['customer_street'] ?? ''); ?>,
                    customer_number: <?php echo json_encode($order['customer_number'] ?? ''); ?>,
                    customer_complement: <?php echo json_encode($order['customer_complement'] ?? ''); ?>,
                    customer_neighborhood: <?php echo json_encode($order['customer_neighborhood'] ?? ''); ?>,
                    customer_city: <?php echo json_encode($order['customer_city'] ?? ''); ?>,
                    customer_state: <?php echo json_encode($order['customer_state'] ?? ''); ?>,
                    status: <?php echo json_encode($order['status'] ?? 'pending'); ?>,
                    tracking_number: <?php echo json_encode($order['tracking_number'] ?? ''); ?>,
                    items: <?php echo $order['items_json'] ?: '[]'; ?>, 
                    total_amount: <?php echo floatval($order['total_amount']); ?>
                },
                get formattedAddress() {
                    let addr = this.order.customer_name + '\n';
                    addr += (this.order.customer_street || '') + ', ' + (this.order.customer_number || '') + '\n';
                    let neigh = this.order.customer_neighborhood || '';
                    let comp = this.order.customer_complement || '';
                    if (neigh && comp) {
                        addr += neigh + ' - ' + comp + '\n';
                    } else if (neigh || comp) {
                        addr += neigh + comp + '\n';
                    }
                    addr += (this.order.customer_city || '') + ' ' + (this.order.customer_state || '') + ' ' + (this.order.customer_cep || '');
                    
                    // Fallback to legacy string if completely empty structured fields
                    if (!this.order.customer_street && !this.order.customer_city && this.order.customer_address) {
                        return this.order.customer_name + '\n' + this.order.customer_address;
                    }
                    return addr.trim();
                },
                copyAddress() {
                    navigator.clipboard.writeText(this.formattedAddress).then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    });
                },
                calculateTotal() {
                    this.order.total_amount = this.order.items.reduce((sum, item) => {
                        return sum + (item.quantity * item.price);
                    }, 0);
                },
                removeItem(index) {
                    if (confirm('<?php echo __('Are you sure you want to remove this item?'); ?>')) {
                        this.order.items.splice(index, 1);
                        this.calculateTotal();
                    }
                },
                saveOrder() {
                    fetch('/admin/order/update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(this.order)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('<?php echo __('Order updated successfully!'); ?>');
                            window.location.reload();
                        } else {
                            alert('<?php echo __('Error updating order'); ?>: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('<?php echo __('An error occurred while saving.'); ?>');
                    });
                },
                deleteOrder() {
                    if (confirm('<?php echo __('Are you sure you want to PERMANENTLY delete this order? This action cannot be undone.'); ?>')) {
                        fetch('/admin/order/delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: this.order.id })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('<?php echo __('Order deleted successfully!'); ?>');
                                window.location.href = '/admin';
                            } else {
                                alert('<?php echo __('Error deleting order'); ?>: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('<?php echo __('An error occurred while deleting.'); ?>');
                        });
                    }
                }
            }
        }
    </script>

    <!-- Powered By -->
    <div class="w-full bg-black h-[35px] flex items-center justify-center shrink-0">
        <span class="text-white text-xs">Powered by LojaSimples</span>
    </div>
</body>
</html>
