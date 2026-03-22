<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
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
    
    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="p-4 text-xl font-bold border-b border-gray-800"><?php echo __('Admin Dashboard'); ?></div>
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
                
                <div class="border-t border-gray-800 my-2"></div>
                <div class="px-4 py-2">
                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-2">Idioma / Language</span>
                    <div class="flex gap-2">
                        <a href="?lang=en" class="text-xs px-2 py-1 rounded <?php echo ($_SESSION['lang'] ?? 'en') === 'en' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'; ?>">EN</a>
                        <a href="?lang=pt" class="text-xs px-2 py-1 rounded <?php echo ($_SESSION['lang'] ?? 'en') === 'pt' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white'; ?>">PT</a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto p-8">
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
                                <input type="text" x-model="order.customer_whatsapp" class="w-full border-gray-300 rounded-md shadow-sm p-2 border" oninput="maskPhone(event)">
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-md font-semibold mb-2"><?php echo __('Address Details'); ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">CEP</label>
                                    <input type="text" x-model="order.customer_cep" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Street</label>
                                    <input type="text" x-model="order.customer_street" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Number</label>
                                    <input type="text" x-model="order.customer_number" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Neighborhood</label>
                                    <input type="text" x-model="order.customer_neighborhood" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">City</label>
                                    <input type="text" x-model="order.customer_city" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">State</label>
                                    <input type="text" x-model="order.customer_state" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                                </div>
                            </div>
                            <div class="mt-2">
                                <label class="block text-sm font-medium text-gray-700"><?php echo __('Full Address String (Legacy/Display)'); ?></label>
                                <textarea x-model="order.customer_address" rows="2" class="w-full border-gray-300 rounded-md shadow-sm p-2 border bg-gray-50"></textarea>
                            </div>
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

    <script>
        function orderEditor() {
            return {
                order: {
                    id: <?php echo json_encode($order['id']); ?>,
                    customer_name: <?php echo json_encode($order['customer_name']); ?>,
                    customer_email: <?php echo json_encode($order['customer_email']); ?>,
                    customer_whatsapp: <?php echo json_encode($order['customer_whatsapp']); ?>,
                    customer_address: <?php echo json_encode($order['customer_address']); ?>,
                    customer_cep: <?php echo json_encode($order['customer_cep'] ?? ''); ?>,
                    customer_street: <?php echo json_encode($order['customer_street'] ?? ''); ?>,
                    customer_number: <?php echo json_encode($order['customer_number'] ?? ''); ?>,
                    customer_neighborhood: <?php echo json_encode($order['customer_neighborhood'] ?? ''); ?>,
                    customer_city: <?php echo json_encode($order['customer_city'] ?? ''); ?>,
                    customer_state: <?php echo json_encode($order['customer_state'] ?? ''); ?>,
                    status: <?php echo json_encode($order['status'] ?? 'pending'); ?>,
                    tracking_number: <?php echo json_encode($order['tracking_number'] ?? ''); ?>,
                    items: <?php echo $order['items_json'] ?: '[]'; ?>, 
                    total_amount: <?php echo floatval($order['total_amount']); ?>
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
