<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo __('Customer Details'); ?> - <?php echo htmlspecialchars($customer['email'] ?? ''); ?> - <?php echo htmlspecialchars(getSetting('store_name', 'R2 Research Labs')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans flex flex-col h-screen">
    <div class="flex flex-1 overflow-hidden">
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
        <div class="flex-1 overflow-auto p-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold"><?php echo __('Customer Details'); ?></h1>
                    <div>
                        <?php if (!empty($customer['has_orders'])): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"><?php echo __('Has Orders'); ?></span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700"><?php echo __('No Orders'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-500"><?php echo __('Orders Count'); ?></p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo (int)($customer['orders_count'] ?? 0); ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 md:col-span-2">
                            <p class="text-sm text-gray-500"><?php echo __('Last Order Date'); ?></p>
                            <p class="text-lg font-semibold text-gray-900">
                                <?php echo !empty($customer['last_order_at']) ? date('M j, Y H:i', strtotime($customer['last_order_at'])) : __('No Orders'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold mb-4 border-b pb-2"><?php echo __('Customer Information'); ?></h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500"><?php echo __('Name'); ?></p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars(($customer['name'] ?? '') !== '' ? $customer['name'] : '—'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500"><?php echo __('Email'); ?></p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($customer['email'] ?? '—'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500"><?php echo __('WhatsApp'); ?></p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars(($customer['whatsapp'] ?? '') !== '' ? $customer['whatsapp'] : '—'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500"><?php echo __('Customer ID'); ?></p>
                                <p class="text-gray-900 font-medium">#<?php echo (int)($customer['id'] ?? 0); ?></p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold mb-4 border-b pb-2"><?php echo __('Address Details'); ?></h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-500"><?php echo __('CEP'); ?></p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars(($customer['cep'] ?? '') !== '' ? $customer['cep'] : '—'); ?></p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-500"><?php echo __('Street'); ?></p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars(($customer['street'] ?? '') !== '' ? $customer['street'] : '—'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500"><?php echo __('Number'); ?></p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars(($customer['number'] ?? '') !== '' ? $customer['number'] : '—'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500"><?php echo __('Neighborhood'); ?></p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars(($customer['neighborhood'] ?? '') !== '' ? $customer['neighborhood'] : '—'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500"><?php echo __('City'); ?></p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars(($customer['city'] ?? '') !== '' ? $customer['city'] : '—'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500"><?php echo __('State'); ?></p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars(($customer['state'] ?? '') !== '' ? $customer['state'] : '—'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Powered By -->
    <div class="w-full bg-black h-[35px] flex items-center justify-center shrink-0">
        <span class="text-white text-xs">Powered by LojaSimples</span>
    </div>
</body>
</html>
