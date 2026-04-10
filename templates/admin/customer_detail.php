<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo __('Customer Details'); ?> - <?php echo htmlspecialchars($customer['email'] ?? ''); ?> - <?php echo htmlspecialchars(getSetting('store_name', 'R2 Research Labs')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100 font-sans flex flex-col h-screen" x-data="{ sidebarOpen: false, editMode: false }">
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
                <span class="font-bold text-lg text-gray-800 text-truncate overflow-hidden whitespace-nowrap"><?php echo __('Customer Details'); ?></span>
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 hover:text-gray-900 focus:outline-none p-1 ml-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-auto p-4 md:p-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold"><?php echo __('Customer Details'); ?></h1>
                    <div>
                        <button @click="editMode = !editMode" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm font-medium mr-2" x-text="editMode ? '<?php echo __('Cancel'); ?>' : '<?php echo __('Edit'); ?>'"></button>
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
                    <div x-show="!editMode">
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
                                <div class="text-gray-900 font-medium">
                                    <?php if (!empty($customer['whatsapp']) && $customer['whatsapp'] !== '—'): 
                                        $wa_number = preg_replace('/\D/', '', $customer['whatsapp']);
                                        $wa_link = 'https://wa.me/55' . $wa_number;
                                    ?>
                                        <a href="<?php echo $wa_link; ?>" target="_blank" class="text-green-600 hover:text-green-800 flex items-center gap-1 w-fit mt-1" title="Falar no WhatsApp">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                            <?php echo htmlspecialchars($customer['whatsapp']); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars(($customer['whatsapp'] ?? '') !== '' ? $customer['whatsapp'] : '—'); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500"><?php echo __('Customer ID'); ?></p>
                                <p class="text-gray-900 font-medium">#<?php echo (int)($customer['id'] ?? 0); ?></p>
                            </div>
                        </div>
                    </div>
                    <div x-show="!editMode">
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
                            <div class="md:col-span-3">
                                <p class="text-sm text-gray-500"><?php echo __('Complement'); ?></p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars(($customer['complement'] ?? '') !== '' ? $customer['complement'] : '—'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div x-show="editMode" style="display: none;">
                        <form action="/admin/customer/edit" method="POST" class="space-y-6">
                            <input type="hidden" name="id" value="<?php echo (int)$customer['id']; ?>">
                            
                            <div>
                                <h2 class="text-xl font-semibold mb-4 border-b pb-2"><?php echo __('Customer Information'); ?></h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Name'); ?></label>
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Email'); ?></label>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('WhatsApp'); ?></label>
                                        <input type="text" name="whatsapp" value="<?php echo htmlspecialchars($customer['whatsapp'] ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h2 class="text-xl font-semibold mb-4 border-b pb-2"><?php echo __('Address Details'); ?></h2>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('CEP'); ?></label>
                                        <input type="text" name="cep" value="<?php echo htmlspecialchars($customer['cep'] ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Street'); ?></label>
                                        <input type="text" name="street" value="<?php echo htmlspecialchars($customer['street'] ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Number'); ?></label>
                                        <input type="text" name="number" value="<?php echo htmlspecialchars($customer['number'] ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Complement'); ?></label>
                                        <input type="text" name="complement" value="<?php echo htmlspecialchars($customer['complement'] ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Neighborhood'); ?></label>
                                        <input type="text" name="neighborhood" value="<?php echo htmlspecialchars($customer['neighborhood'] ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('City'); ?></label>
                                        <input type="text" name="city" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('State'); ?></label>
                                        <input type="text" name="state" value="<?php echo htmlspecialchars($customer['state'] ?? ''); ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end pt-4">
                                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 font-medium shadow-sm">
                                    <?php echo __('Save Changes'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Powered By -->
    <div class="w-full bg-black h-[35px] flex items-center justify-center shrink-0">
        <span class="text-white text-xs"><?php echo htmlspecialchars(getSetting('store_footer_text', 'R2 Research Labs - All Rights Reserved.')); ?></span>
    </div>
</body>
</html>
