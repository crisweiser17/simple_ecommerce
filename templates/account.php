<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-6 text-gray-800"><?php echo __('Account'); ?></h1>

        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <aside class="lg:col-span-1">
                <nav class="bg-white rounded-lg shadow-md p-4 sticky top-24">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3"><?php echo __('Account'); ?></h2>
                    <a href="#profile" data-account-tab-link="profile" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                        <?php echo __('Profile'); ?>
                    </a>
                    <a href="#orders" data-account-tab-link="orders" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors mt-1">
                        <?php echo __('Orders'); ?>
                    </a>
                </nav>
            </aside>

            <div class="lg:col-span-3 space-y-6">
                <section data-account-tab-section="profile" class="bg-white p-8 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-6 text-gray-700 border-b pb-2"><?php echo __('Profile'); ?></h2>

                    <form action="/account" method="POST" class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4 text-gray-700"><?php echo __('Personal Information'); ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Full Name'); ?></label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('WhatsApp'); ?></label>
                                    <input type="text" name="whatsapp" value="<?php echo htmlspecialchars($user['whatsapp'] ?? ''); ?>"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2" oninput="maskPhone(event)">
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Email Address'); ?></label>
                                <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2">
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold mb-4 text-gray-700"><?php echo __('Delivery Address'); ?></h3>

                            <div class="grid grid-cols-1 gap-4">
                                <div class="md:w-1/3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('CEP'); ?></label>
                                    <input type="text" name="cep" id="cep" maxlength="9" placeholder="00000-000"
                                        value="<?php echo htmlspecialchars($user['cep'] ?? ''); ?>"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2"
                                        onblur="fetchAddress(this.value)">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Street'); ?></label>
                                        <input type="text" name="street" id="street" value="<?php echo htmlspecialchars($user['street'] ?? ''); ?>"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Number'); ?></label>
                                        <input type="text" name="number" id="number" value="<?php echo htmlspecialchars($user['number'] ?? ''); ?>"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Complement'); ?></label>
                                        <input type="text" name="complement" id="complement" value="<?php echo htmlspecialchars($user['complement'] ?? ''); ?>"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Neighborhood'); ?></label>
                                        <input type="text" name="neighborhood" id="neighborhood" value="<?php echo htmlspecialchars($user['neighborhood'] ?? ''); ?>"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('City'); ?></label>
                                        <input type="text" name="city" id="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2">
                                    </div>
                                </div>

                                <div class="md:w-1/4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('State'); ?></label>
                                    <input type="text" name="state" id="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 border p-2">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-orange-600 text-white px-6 py-2 rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                                <?php echo __('Save Changes'); ?>
                            </button>
                        </div>
                    </form>
                </section>

                <section data-account-tab-section="orders" class="bg-white p-8 rounded-lg shadow-md hidden">
                    <h2 class="text-xl font-semibold mb-6 text-gray-700"><?php echo __('Orders'); ?></h2>
                    <?php if (empty($orders)): ?>
                        <p class="text-gray-500 italic"><?php echo __('No orders found.'); ?></p>
                    <?php else: ?>
                        <div class="overflow-x-auto w-full">
                            <table class="min-w-full divide-y divide-gray-200 border rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Order ID'); ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Date'); ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Total'); ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Status'); ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Tracking'); ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Downloads'); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $order['id']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold"><?php echo formatMoney($order['total_amount']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    <?php
                                                    echo match($order['status'] ?? 'pending') {
                                                        'pending_payment' => 'bg-amber-100 text-amber-800',
                                                        'paid' => 'bg-green-100 text-green-800',
                                                        'shipped' => 'bg-purple-100 text-purple-800',
                                                        'completed' => 'bg-gray-100 text-gray-800',
                                                        'cancelled' => 'bg-red-100 text-red-800',
                                                        default => 'bg-yellow-100 text-yellow-800'
                                                    };
                                                    ?>">
                                                    <?php echo htmlspecialchars(ucfirst(__(strtolower($order['status'] ?? 'pending')))); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php if (!empty($order['tracking_number'])): ?>
                                                    <span class="font-mono text-gray-700 bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($order['tracking_number']); ?></span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <?php if (!empty($order['digital_deliveries'])): ?>
                                                    <div class="flex flex-col gap-2 min-w-[200px]">
                                                        <?php foreach ($order['digital_deliveries'] as $delivery): ?>
                                                            <div class="border border-gray-200 rounded p-3 bg-gray-50">
                                                                <p class="font-medium text-gray-900 mb-1 text-sm"><?php echo htmlspecialchars($delivery['product_name']); ?></p>
                                                                
                                                                <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
                                                                    <?php if ($delivery['max_downloads'] > 0): ?>
                                                                        <span><?php echo __('Downloads:'); ?> <?php echo $delivery['download_count']; ?>/<?php echo $delivery['max_downloads']; ?></span>
                                                                    <?php else: ?>
                                                                        <span><?php echo __('Downloads: Unlimited'); ?></span>
                                                                    <?php endif; ?>
                                                                    
                                                                    <?php if (!empty($delivery['expires_at'])): ?>
                                                                        <?php 
                                                                            $isExpired = strtotime($delivery['expires_at']) < time();
                                                                        ?>
                                                                        <span class="<?php echo $isExpired ? 'text-red-500' : 'text-gray-500'; ?>">
                                                                            <?php echo __('Exp:'); ?> <?php echo date('d/m/Y', strtotime($delivery['expires_at'])); ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <?php 
                                                                    $canDownload = true;
                                                                    if ($delivery['max_downloads'] > 0 && $delivery['download_count'] >= $delivery['max_downloads']) {
                                                                        $canDownload = false;
                                                                    }
                                                                    if (!empty($delivery['expires_at']) && strtotime($delivery['expires_at']) < time()) {
                                                                        $canDownload = false;
                                                                    }
                                                                ?>

                                                                <?php if ($canDownload): ?>
                                                                    <a href="/download/<?php echo htmlspecialchars($delivery['token']); ?>" class="inline-flex items-center justify-center w-full px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                                                                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                                        </svg>
                                                                        <?php echo __('Download'); ?>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <button disabled class="inline-flex items-center justify-center w-full px-3 py-1.5 border border-transparent text-xs font-medium rounded text-gray-400 bg-gray-200 cursor-not-allowed">
                                                                        <?php echo __('Unavailable'); ?>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-gray-400">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const profileSection = document.querySelector('[data-account-tab-section="profile"]');
        const ordersSection = document.querySelector('[data-account-tab-section="orders"]');
        const profileLink = document.querySelector('[data-account-tab-link="profile"]');
        const ordersLink = document.querySelector('[data-account-tab-link="orders"]');

        const activeClass = ['bg-orange-100', 'text-orange-700', 'font-medium'];
        const inactiveClass = ['text-gray-700'];

        const normalizeTab = (hash) => {
            const tab = (hash || '').replace('#', '').toLowerCase();
            return tab === 'orders' ? 'orders' : 'profile';
        };

        const setLinkState = (link, isActive) => {
            if (!link) return;
            if (isActive) {
                link.classList.add(...activeClass);
                link.classList.remove(...inactiveClass);
            } else {
                link.classList.remove(...activeClass);
                link.classList.add(...inactiveClass);
            }
        };

        const setTab = (tab, updateHash = false) => {
            const isOrders = tab === 'orders';
            profileSection.classList.toggle('hidden', isOrders);
            ordersSection.classList.toggle('hidden', !isOrders);
            setLinkState(profileLink, !isOrders);
            setLinkState(ordersLink, isOrders);
            if (updateHash) {
                const nextUrl = `${window.location.pathname}${window.location.search}#${tab}`;
                window.history.replaceState(null, '', nextUrl);
            }
        };

        profileLink?.addEventListener('click', (event) => {
            event.preventDefault();
            setTab('profile', true);
        });

        ordersLink?.addEventListener('click', (event) => {
            event.preventDefault();
            setTab('orders', true);
        });

        window.addEventListener('hashchange', () => {
            setTab(normalizeTab(window.location.hash), false);
        });

        setTab(normalizeTab(window.location.hash), false);

        // Apply phone mask on load if field has a value
        const whatsappInput = document.querySelector('input[name="whatsapp"]');
        if (whatsappInput && whatsappInput.value && typeof maskPhone === 'function') {
            maskPhone({ target: whatsappInput });
        }
    });

    function fetchAddress(cep) {
        // Remove non-digits
        cep = cep.replace(/\D/g, '');
        
        if (cep.length === 8) {
            // Show loading state if desired
            document.getElementById('street').placeholder = "<?php echo __('Loading...'); ?>";
            
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('street').value = data.logradouro;
                        document.getElementById('neighborhood').value = data.bairro;
                        document.getElementById('city').value = data.localidade;
                        document.getElementById('state').value = data.uf;
                        document.getElementById('number').focus();
                    } else {
                        alert("<?php echo __('CEP not found.'); ?>");
                    }
                })
                .catch(error => {
                    console.error('Error fetching CEP:', error);
                    alert("<?php echo __('Error fetching address details.'); ?>");
                })
                .finally(() => {
                    document.getElementById('street').placeholder = "";
                });
        }
    }
</script>
