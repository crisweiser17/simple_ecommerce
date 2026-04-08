<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo __('Admin Dashboard'); ?> - <?php echo htmlspecialchars(getSetting('store_name', 'R2 Research Labs')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
    <style>
        #payment_instructions_editor .ql-editor {
            min-height: 200px;
        }
    </style>
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
<body class="bg-gray-100 font-sans flex flex-col h-screen" x-data="{ 
    sidebarOpen: false,
    tab: localStorage.getItem('admin_tab') || 'products', 
    categoryModalOpen: false,
    pageModalOpen: false,
    editCategory: {},
    editPage: {},
    testEmail: 'e@crisweiser.com',
    smtpTesting: false,
    smtpTestMessage: '',
    smtpTestSuccess: null,
    initQuill() {
        if (!this.quill) {
            this.quill = new Quill('#editor', {
                theme: 'snow'
            });
            this.quill.on('text-change', () => {
                this.editPage.content = this.quill.root.innerHTML;
                document.getElementById('pageContent').value = this.quill.root.innerHTML;
            });
        }
    },
    init() {
        this.$watch('tab', value => localStorage.setItem('admin_tab', value));
    },
    testSMTP() {
        this.smtpTesting = true;
        this.smtpTestMessage = '';
        this.smtpTestSuccess = null;
        fetch('/admin/test-smtp?to=' + encodeURIComponent(this.testEmail))
            .then(r => r.json())
            .then(data => {
                this.smtpTestSuccess = !!data.success;
                this.smtpTestMessage = data.success ? '<?php echo __('Test email sent successfully.'); ?>' : ('<?php echo __('Send failed'); ?>: ' + (data.message || '<?php echo __('check credentials.'); ?>'));
            })
            .catch(err => {
                this.smtpTestSuccess = false;
                this.smtpTestMessage = '<?php echo __('Unexpected error'); ?>: ' + err;
            })
            .finally(() => {
                this.smtpTesting = false;
            });
    }
}">

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
                <a href="/" class="block w-full text-left px-4 py-2 text-xs text-gray-400 hover:text-white"><?php echo __('Back to Site'); ?></a>
                <div class="border-t border-gray-800 my-1"></div>
                <button @click="tab = 'products'; sidebarOpen = false;" :class="tab === 'products' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white'" class="w-full text-left px-4 py-2 rounded">
                    <?php echo __('Products'); ?>
                </button>
                <button @click="tab = 'categories'; sidebarOpen = false;" :class="tab === 'categories' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white'" class="w-full text-left px-4 py-2 rounded">
                    <?php echo __('Categories'); ?>
                </button>
                <div class="border-t border-gray-800 my-1"></div>
                <button @click="tab = 'orders'; sidebarOpen = false;" :class="tab === 'orders' ? 'bg-gray-700 text-white font-semibold border border-gray-600' : 'text-gray-400 hover:text-white'" class="w-full text-left px-4 py-2 rounded">
                    <?php echo __('Orders'); ?>
                </button>
                <button @click="tab = 'customers'; sidebarOpen = false;" :class="tab === 'customers' ? 'bg-gray-700 text-white font-semibold border border-gray-600' : 'text-gray-400 hover:text-white'" class="w-full text-left px-4 py-2 rounded">
                    <?php echo __('Customers'); ?>
                </button>
                <div class="border-t border-gray-800 my-1"></div>
                <button @click="tab = 'settings'; sidebarOpen = false;" :class="tab === 'settings' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white'" class="w-full text-left px-4 py-2 rounded">
                    <?php echo __('Settings'); ?>
                </button>
                <button @click="tab = 'admins'; sidebarOpen = false;" :class="tab === 'admins' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white'" class="w-full text-left px-4 py-2 rounded">
                    <?php echo __('Admins'); ?>
                </button>
                <a href="/logout" class="block w-full text-left px-4 py-2 text-red-400 hover:text-red-300"><?php echo __('Logout'); ?></a>
                
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

        <!-- Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Mobile Header -->
            <div class="md:hidden bg-white border-b border-gray-200 flex items-center justify-between p-4 flex-shrink-0 shadow-sm z-10">
                <span class="font-bold text-lg text-gray-800"><?php echo __('Admin Dashboard'); ?></span>
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 hover:text-gray-900 focus:outline-none p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-auto p-4 md:p-8">
            
            <!-- Products Tab -->
            <div x-show="tab === 'products'">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                    <h1 class="text-2xl font-bold"><?php echo __('Products'); ?></h1>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="/admin/products/csv-template" class="bg-gray-700 text-white px-3 py-2 text-sm rounded hover:bg-gray-800">CSV Template</a>
                        <a href="/admin/products/export-csv" class="bg-blue-600 text-white px-3 py-2 text-sm rounded hover:bg-blue-700">Export CSV</a>
                        
                        <form id="csvImportForm" action="/admin/products/import-csv" method="POST" enctype="multipart/form-data" class="hidden">
                            <input type="file" id="csvFileInput" name="products_csv" accept=".csv,text/csv" required onchange="document.getElementById('csvImportForm').submit()">
                        </form>
                        <button type="button" onclick="document.getElementById('csvFileInput').value = ''; document.getElementById('csvFileInput').click()" class="bg-indigo-600 text-white px-3 py-2 text-sm rounded hover:bg-indigo-700">Import CSV</button>
                        
                        <a href="/admin/product-form" class="bg-green-600 text-white px-3 py-2 text-sm rounded hover:bg-green-700">Add Product</a>
                    </div>
                </div>

                <?php if (!empty($productsCsvReport)): ?>
                    <div class="mb-4 rounded border border-gray-200 bg-white p-4">
                        <h2 class="text-sm font-semibold text-gray-900 mb-2">CSV Import Result</h2>
                        <p class="text-sm text-gray-700">
                            Total: <?php echo (int)($productsCsvReport['total'] ?? 0); ?> |
                            Inserted: <?php echo (int)($productsCsvReport['inserted'] ?? 0); ?> |
                            Updated: <?php echo (int)($productsCsvReport['updated'] ?? 0); ?> |
                            Ignored: <?php echo (int)($productsCsvReport['ignored'] ?? 0); ?>
                        </p>
                        <?php if (!empty($productsCsvReport['errors'])): ?>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                <?php foreach ($productsCsvReport['errors'] as $csvError): ?>
                                    <li><?php echo htmlspecialchars($csvError); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded shadow overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Image'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Name'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($products as $p): ?>
                            <?php $adminImageUrl = getProductPrimaryImageUrl($p); ?>
                            <?php if ($adminImageUrl === '') $adminImageUrl = 'https://placehold.co/100x100?text=No+Image'; ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <img src="<?php echo htmlspecialchars($adminImageUrl ?? ''); ?>" class="h-10 w-10 object-contain rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?php echo htmlspecialchars($p['name'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?php echo htmlspecialchars($p['sku'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?php echo formatMoney($p['price']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?php echo htmlspecialchars($p['category_name'] ?? __('Uncategorized')); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="/product/<?php echo $p['slug']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900 mr-4" title="<?php echo __('View Product'); ?>"><i class="fa-solid fa-up-right-from-square"></i></a>
                                    <a href="/produto/<?php echo $p['slug']; ?>/single" target="_blank" class="text-green-600 hover:text-green-900 mr-4" title="Página Única de Checkout">Single</a>
                                    <a href="/admin/product-form?id=<?php echo $p['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-4"><?php echo __('Edit'); ?></a>
                                    <a href="/admin/delete-product?id=<?php echo $p['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('<?php echo __('Are you sure?'); ?>')"><?php echo __('Delete'); ?></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Categories Tab -->
            <div x-show="tab === 'categories'" style="display: none;">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold"><?php echo __('Categories'); ?></h1>
                    <button @click="categoryModalOpen = true; editCategory = {}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"><?php echo __('Add Category'); ?></button>
                </div>

                <div class="bg-white rounded shadow overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Name'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($categories as $c): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?php echo htmlspecialchars($c['name'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?php echo htmlspecialchars($c['slug'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="categoryModalOpen = true; editCategory = <?php echo htmlspecialchars(json_encode($c)); ?>" class="text-indigo-600 hover:text-indigo-900 mr-4"><?php echo __('Edit'); ?></button>
                                    <a href="/admin/delete-category?id=<?php echo $c['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('<?php echo __('Are you sure?'); ?>')"><?php echo __('Delete'); ?></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pages Tab -->
            <div x-show="tab === 'pages'" style="display: none;">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold"><?php echo __('Pages'); ?></h1>
                    <button @click="pageModalOpen = true; editPage = {}; setTimeout(() => { initQuill(); if(this.quill) this.quill.root.innerHTML = ''; }, 100);" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"><?php echo __('Add Page'); ?></button>
                </div>

                <div class="bg-white rounded shadow overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Title'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($pages as $pg): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?php echo htmlspecialchars($pg['title'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?php echo htmlspecialchars($pg['slug'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="pageModalOpen = true; editPage = <?php echo htmlspecialchars(json_encode($pg)); ?>; setTimeout(() => { initQuill(); if(this.quill) this.quill.root.innerHTML = editPage.content || ''; }, 100);" class="text-indigo-600 hover:text-indigo-900 mr-4"><?php echo __('Edit'); ?></button>
                                    <a href="/admin/delete-page?id=<?php echo $pg['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('<?php echo __('Are you sure?'); ?>')"><?php echo __('Delete'); ?></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Orders Tab -->
            <div x-show="tab === 'orders'" style="display: none;">
                <h1 class="text-2xl font-bold mb-6"><?php echo __('Orders'); ?></h1>
                <div class="bg-white rounded shadow overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Customer'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Date'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Status'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($orders as $o): ?>
                            <tr>
                                <td class="px-6 py-2 whitespace-nowrap text-gray-500 text-sm">#<?php echo $o['id']; ?></td>
                                <td class="px-6 py-2">
                                    <div class="text-sm font-medium text-gray-900 leading-tight"><?php echo htmlspecialchars($o['customer_name'] ?? ''); ?></div>
                                    <div class="text-xs text-gray-500 leading-tight mt-1 flex flex-col gap-1">
                                        <span><?php echo htmlspecialchars($o['customer_email'] ?? ''); ?></span>
                                        <?php if (!empty($o['customer_whatsapp'])): 
                                            $wa_number = preg_replace('/\D/', '', $o['customer_whatsapp']);
                                            $wa_link = 'https://wa.me/55' . $wa_number;
                                        ?>
                                            <a href="<?php echo $wa_link; ?>" target="_blank" class="text-green-600 hover:text-green-800 flex items-center gap-1 w-fit" title="Falar no WhatsApp">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                                <?php echo htmlspecialchars($o['customer_whatsapp']); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-2 whitespace-nowrap font-bold text-gray-900 text-sm"><?php echo formatMoney($o['total_amount']); ?></td>
                                <td class="px-6 py-2 whitespace-nowrap text-gray-500 text-sm"><?php echo date('M j, Y', strtotime($o['created_at'])); ?></td>
                                <td class="px-6 py-2 text-sm text-gray-500">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium w-fit
                                            <?php 
                                            echo match($o['status'] ?? 'pending') {
                                                'pending_payment' => 'bg-amber-100 text-amber-800',
                                                'paid' => 'bg-green-100 text-green-800',
                                                'shipped' => 'bg-purple-100 text-purple-800',
                                                'completed' => 'bg-gray-100 text-gray-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                default => 'bg-yellow-100 text-yellow-800'
                                            };
                                            ?>">
                                            <?php echo htmlspecialchars(ucfirst(__(strtolower($o['status'] ?? 'pending')))); ?>
                                        </span>
                                        <span class="text-xs text-gray-500">Pagamento: <?php echo htmlspecialchars(ucfirst(__(strtolower($o['payment_status'] ?? 'pending')))); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-2 text-sm">
                                    <div class="flex items-center gap-4 whitespace-nowrap">
                                        <a href="/admin/order/<?php echo $o['id']; ?>" class="text-indigo-600 hover:text-indigo-900 font-medium inline-flex items-center gap-1">
                                            <?php echo __('View Order'); ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                        <form action="/admin/order/delete" method="POST" class="inline" onsubmit="return confirm('<?php echo __('Are you sure you want to delete this order?'); ?>');">
                                            <input type="hidden" name="id" value="<?php echo $o['id']; ?>">
                                            <input type="hidden" name="redirect_to_admin" value="1">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                                <?php echo __('Delete Order'); ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="tab === 'customers'" style="display: none;">
                <h1 class="text-2xl font-bold mb-6"><?php echo __('Customers'); ?></h1>
                <div class="bg-white rounded shadow overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Name'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Email'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('WhatsApp'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Orders Count'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Orders Status'); ?></th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (!empty($customers)): ?>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?php echo htmlspecialchars($customer['name'] ?? '—'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($customer['email'] ?? '—'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                            <?php if (!empty($customer['whatsapp']) && $customer['whatsapp'] !== '—'): 
                                                $wa_number = preg_replace('/\D/', '', $customer['whatsapp']);
                                                $wa_link = 'https://wa.me/55' . $wa_number;
                                            ?>
                                                <a href="<?php echo $wa_link; ?>" target="_blank" class="text-green-600 hover:text-green-800 flex items-center gap-1 w-fit" title="Falar no WhatsApp">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                                    <?php echo htmlspecialchars($customer['whatsapp']); ?>
                                                </a>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-semibold"><?php echo (int)($customer['orders_count'] ?? 0); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($customer['has_orders'])): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><?php echo __('Has Orders'); ?></span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700"><?php echo __('No Orders'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="/admin/customer/<?php echo (int)$customer['id']; ?>" class="text-indigo-600 hover:text-indigo-900"><?php echo __('View Customer'); ?></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500"><?php echo __('No customers found.'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Settings Tab -->
            <div x-show="tab === 'settings'" style="display: none;">
                <h1 class="text-2xl font-bold mb-6">Settings</h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- General Settings -->
                    <div class="bg-white rounded shadow p-6">
                        <h2 class="text-xl font-semibold mb-4 border-b pb-2">General Settings</h2>
                        <form action="/admin/save-settings" method="POST" enctype="multipart/form-data" x-data="{ i18nMulti: <?php echo getSetting('i18n_multilang_enabled', '1') === '1' ? 'true' : 'false'; ?> }">
                            <div class="border-b pb-4 mb-4">
                                <h3 class="text-lg font-semibold mb-3"><?php echo __('Store Branding'); ?></h3>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nome da loja</label>
                                    <input type="text" name="store_name" value="<?php echo htmlspecialchars(getSetting('store_name', 'R2 Research Labs')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2"><?php echo __('Store Currency (Code)'); ?></label>
                                        <input type="text" name="store_currency" value="<?php echo htmlspecialchars(getSetting('store_currency', 'BRL')); ?>" placeholder="e.g. BRL, USD" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2"><?php echo __('Store Currency Symbol'); ?></label>
                                        <input type="text" name="store_currency_symbol" value="<?php echo htmlspecialchars(getSetting('store_currency_symbol', 'R$')); ?>" placeholder="e.g. R$, $" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <?php $brandMode = getSetting('brand_mode', 'text'); ?>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Exibição do logo</label>
                                    <select name="brand_mode" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        <option value="text" <?php echo $brandMode === 'text' ? 'selected' : ''; ?>>Texto</option>
                                        <option value="image" <?php echo $brandMode === 'image' ? 'selected' : ''; ?>>Imagem</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">URL do logo</label>
                                    <input type="text" name="brand_logo_url" value="<?php echo htmlspecialchars(getSetting('brand_logo_url', '')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Ou envie uma imagem</label>
                                    <input type="file" name="brand_logo_file" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" data-filepond="image-single">
                                    <?php if($logo = getSetting('brand_logo_url', '')): ?>
                                        <div class="mt-2 border rounded p-2 bg-gray-50">
                                            <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo atual" class="max-h-20 object-contain">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Largura do logo (px)</label>
                                        <input type="number" min="20" max="1200" name="brand_logo_width" value="<?php echo htmlspecialchars(getSetting('brand_logo_width', '160')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Altura do logo (px)</label>
                                        <input type="number" min="20" max="600" name="brand_logo_height" value="<?php echo htmlspecialchars(getSetting('brand_logo_height', '48')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                </div>
                            </div>

                            <div class="border-b pb-4 mb-4">
                                <h3 class="text-lg font-semibold mb-3"><?php echo __('Store Theme'); ?></h3>
                                <div class="grid grid-cols-1 gap-3">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2"><?php echo __('Header Color'); ?></label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="theme_header_bg" value="<?php echo htmlspecialchars(getSetting('theme_header_bg', '#0f1115')); ?>" class="h-10 w-10 p-0 border rounded">
                                            <input type="text" name="theme_header_bg_text" value="<?php echo htmlspecialchars(getSetting('theme_header_bg', '#0f1115')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" oninput="this.previousElementSibling.value = this.value">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2"><?php echo __('Background Color'); ?></label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="theme_page_bg" value="<?php echo htmlspecialchars(getSetting('theme_page_bg', '#f3f4f6')); ?>" class="h-10 w-10 p-0 border rounded">
                                            <input type="text" name="theme_page_bg_text" value="<?php echo htmlspecialchars(getSetting('theme_page_bg', '#f3f4f6')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" oninput="this.previousElementSibling.value = this.value">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2"><?php echo __('Font Color'); ?></label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="theme_text_color" value="<?php echo htmlspecialchars(getSetting('theme_text_color', '#1f2937')); ?>" class="h-10 w-10 p-0 border rounded">
                                            <input type="text" name="theme_text_color_text" value="<?php echo htmlspecialchars(getSetting('theme_text_color', '#1f2937')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" oninput="this.previousElementSibling.value = this.value">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-b pb-4 mb-4">
                                <h3 class="text-lg font-semibold mb-3">Layout do Produto</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Proporção da Imagem - Largura (Aspect Ratio)</label>
                                        <input type="number" min="1" name="product_card_aspect_width" value="<?php echo htmlspecialchars(getSetting('product_card_aspect_width', '1')); ?>" placeholder="Ex: 363" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Proporção da Imagem - Altura (Aspect Ratio)</label>
                                        <input type="number" min="1" name="product_card_aspect_height" value="<?php echo htmlspecialchars(getSetting('product_card_aspect_height', '1')); ?>" placeholder="Ex: 493" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    </div>
                                </div>
                            </div>

                            <div class="border-b pb-4 mb-4">
                                <h3 class="text-lg font-semibold mb-3"><?php echo __('Store and Admin Language'); ?></h3>
                                <div class="mb-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="i18n_multilang_enabled" value="1" <?php echo getSetting('i18n_multilang_enabled', '1') === '1' ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600" @change="i18nMulti = $event.target.checked">
                                        <span class="ml-2 text-gray-700 text-sm font-bold"><?php echo __('Enable multi-language (EN/PT)'); ?></span>
                                    </label>
                                </div>
                                <?php $singleLang = getSetting('i18n_single_lang', 'en'); ?>
                                <div x-show="!i18nMulti">
                                    <label class="block text-gray-700 text-sm font-bold mb-2"><?php echo __('Single language when multi-language is off'); ?></label>
                                    <select name="i18n_single_lang" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        <option value="en" <?php echo $singleLang === 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="pt" <?php echo $singleLang === 'pt' ? 'selected' : ''; ?>>Português</option>
                                    </select>
                                </div>
                            </div>

                            <div class="border-b pb-4 mb-4">
                                <h3 class="text-lg font-semibold mb-3">WhatsApp</h3>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">
                                        Store WhatsApp Number
                                    </label>
                                    <p class="text-gray-500 text-xs mb-2">Number that will receive order summaries (include country code, e.g., 5511999999999)</p>
                                    <input type="text" name="store_whatsapp" value="<?php echo htmlspecialchars(getSetting('store_whatsapp', '')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" oninput="maskPhone(event)">
                                </div>
                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="enable_whatsapp_button" value="1" <?php echo getSetting('enable_whatsapp_button', '1') === '1' ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600">
                                        <span class="ml-2 text-gray-700 text-sm font-bold"><?php echo __('Enable WhatsApp checkout button'); ?></span>
                                    </label>
                                    <p class="text-gray-500 text-xs mt-1 ml-7">Se desativado, o botão do WhatsApp não aparecerá no carrinho nem na página de sucesso.</p>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition-colors text-lg">
                                    Save General Settings
                                </button>
                            </div>
                            
                            <div class="border-t pt-8 mt-8">
                                <h3 class="text-lg font-semibold mb-3">Email (SMTP)</h3>
                                <div class="mb-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="smtp_enabled" value="1" <?php echo getSetting('smtp_enabled', '0') === '1' ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600">
                                        <span class="ml-2 text-gray-700 text-sm font-bold"><?php echo __('Enable email sending (Resend SMTP)'); ?></span>
                                    </label>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Host</label>
                                        <input type="text" name="smtp_host" value="<?php echo htmlspecialchars(getSetting('smtp_host', 'smtp.resend.com')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Porta</label>
                                        <input type="text" name="smtp_port" value="<?php echo htmlspecialchars(getSetting('smtp_port', '587')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Usuário</label>
                                        <input type="text" name="smtp_username" value="<?php echo htmlspecialchars(getSetting('smtp_username', 'resend')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                        <p class="text-xs text-gray-500 mt-1">Para Resend, use “resend”.</p>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Senha / API Key</label>
                                        <?php $hasPass = (bool) getSetting('smtp_password', ''); ?>
                                        <input type="password" name="smtp_password" value="<?php echo $hasPass ? '********' : ''; ?>" placeholder="<?php echo $hasPass ? '********' : 're_xxxxxxxxxxxxxxxxx'; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                        <p class="text-xs text-gray-500 mt-1">Para Resend, use a API Key como senha.</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Criptografia</label>
                                        <?php $enc = getSetting('smtp_encryption', 'tls'); ?>
                                        <select name="smtp_encryption" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                            <option value="tls" <?php echo $enc === 'tls' ? 'selected' : ''; ?>>STARTTLS (587)</option>
                                            <option value="ssl" <?php echo $enc === 'ssl' ? 'selected' : ''; ?>>SMTPS (465)</option>
                                        </select>
                                    </div>
                                    <div></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">From Email</label>
                                        <input type="email" name="smtp_from_email" value="<?php echo htmlspecialchars(getSetting('smtp_from_email', '')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-2">From Name</label>
                                        <input type="text" name="smtp_from_name" value="<?php echo htmlspecialchars(getSetting('smtp_from_name', 'R2 Store')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Reply-To (opcional)</label>
                                    <input type="email" name="smtp_reply_to" value="<?php echo htmlspecialchars(getSetting('smtp_reply_to', '')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                </div>
                                <div class="mt-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">E-mail para Receber Contatos</label>
                                    <p class="text-xs text-gray-500 mb-1">E-mail que receberá as mensagens enviadas pelo formulário de contato. Se vazio, usará o Reply-To ou From Email.</p>
                                    <input type="email" name="contact_receive_email" value="<?php echo htmlspecialchars(getSetting('contact_receive_email', '')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition-colors text-lg">
                                    Save SMTP Settings
                                </button>
                            </div>
                        </form>
                        <div class="border-t mt-8 pt-6">
                            <h3 class="text-lg font-semibold mb-3"><?php echo __('Send Test'); ?></h3>
                            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                                <div class="flex-1">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">E-mail de teste</label>
                                    <input type="email" x-model="testEmail" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none" placeholder="ex.: e@crisweiser.com">
                                </div>
                                <button type="button" @click="testSMTP" :disabled="smtpTesting" class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-300 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    <span x-show="!smtpTesting"><?php echo __('Send test email'); ?></span>
                                    <span x-show="smtpTesting"><?php echo __('Sending...'); ?></span>
                                </button>
                            </div>
                            <template x-if="smtpTestMessage">
                                <p class="mt-3 text-sm" :class="smtpTestSuccess ? 'text-green-700' : 'text-red-700'" x-text="smtpTestMessage"></p>
                            </template>
                            <p class="mt-2 text-xs text-gray-500">Certifique-se de ativar o SMTP e preencher as credenciais acima antes de testar.</p>
                        </div>
                    </div>

                    <div class="bg-white rounded shadow p-6">
                        <h2 class="text-xl font-semibold mb-4 border-b pb-2">Payment Integrations</h2>
                        <?php
                            $activeProvider = getSetting('payment_provider_active', 'mercadopago');
                            $providerModulesRaw = getSetting('payment_provider_modules', 'mercadopago,manual_pix');
                            $providerModules = array_filter(array_map('trim', explode(',', $providerModulesRaw)));
                            $hasMpSecret = (bool)getSetting('payment_mercadopago_webhook_secret', '');
                            $mpEnv = getSetting('payment_mercadopago_environment', 'sandbox');
                        ?>
                        <form action="/admin/save-payment-settings" method="POST" class="space-y-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Provider ativo</label>
                                <select name="payment_provider_active" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                    <option value="mercadopago" <?php echo $activeProvider === 'mercadopago' ? 'selected' : ''; ?>>Mercado Pago</option>
                                    <option value="manual_pix" <?php echo $activeProvider === 'manual_pix' ? 'selected' : ''; ?>>Pix manual</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Módulos habilitados</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="payment_provider_modules[]" value="mercadopago" <?php echo in_array('mercadopago', $providerModules, true) ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600">
                                        <span class="ml-2 text-gray-700 text-sm font-bold">Mercado Pago</span>
                                    </label>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 border rounded p-4 bg-gray-50">
                                <h4 class="font-semibold text-gray-800">Mercado Pago</h4>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Access Token (Chave de Produção)</label>
                                    <input type="text" name="payment_mercadopago_access_token" value="<?php echo htmlspecialchars(getSetting('payment_mercadopago_access_token', '')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                    <p class="text-xs text-gray-500 mt-1">Insira a chave que inicia com APP_USR- ou TEST-.</p>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Assinatura Secreta do Webhook (Webhook Secret)</label>
                                    <input type="text" name="payment_mercadopago_webhook_secret" value="<?php echo htmlspecialchars(getSetting('payment_mercadopago_webhook_secret', '')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                    <p class="text-xs text-gray-500 mt-1">Chave secreta gerada no painel do Mercado Pago para validar a origem das notificações. Deixe em branco para desativar a verificação.</p>
                                </div>
                                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded">
                                    <h5 class="font-semibold text-blue-800 mb-2">Instruções para o Webhook (Retorno de Pagamento)</h5>
                                    <p class="text-sm text-blue-900 mb-2">Para que o sistema seja notificado automaticamente quando um PIX for pago, configure o Webhook no Mercado Pago:</p>
                                    <ol class="list-decimal list-inside text-sm text-blue-900 space-y-1 mb-3">
                                        <li>Acesse o painel <strong>Suas Integrações > Webhooks</strong> no Mercado Pago.</li>
                                        <li>No campo URL de produção, cole exatamente este endereço:</li>
                                    </ol>
                                    <div class="bg-white border border-blue-300 p-2 rounded flex items-center justify-between mb-3">
                                        <code class="text-sm text-gray-800 select-all" id="webhook-url">https://<?php echo $_SERVER['HTTP_HOST'] ?? 'seudominio.com'; ?>/webhooks/payment/mercadopago</code>
                                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('webhook-url').innerText)" class="text-blue-600 hover:text-blue-800 text-xs font-semibold px-2 py-1 bg-blue-100 rounded">Copiar</button>
                                    </div>
                                    <p class="text-sm text-blue-900">3. Em <strong>Eventos</strong>, selecione a opção <strong>"Pagamentos"</strong> e salve.</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 border rounded p-4 bg-gray-50">
                                <h4 class="font-semibold text-gray-800">Pix manual</h4>
                                <div>
                                    <label class="flex items-center mb-2">
                                        <input type="checkbox" name="payment_provider_modules[]" value="manual_pix" <?php echo in_array('manual_pix', $providerModules, true) ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600">
                                        <span class="ml-2 text-gray-700 text-sm font-bold">Habilitar módulo Pix manual</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Chave PIX</label>
                                    <input type="text" name="payment_manual_pix_key" value="<?php echo htmlspecialchars(getSetting('payment_manual_pix_key', '')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nome do recebedor (Titular da Conta)</label>
                                    <input type="text" name="payment_manual_pix_recipient_name" value="<?php echo htmlspecialchars(getSetting('payment_manual_pix_recipient_name', '')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 gap-4 border rounded p-4 bg-gray-50">
                                <h4 class="font-semibold text-gray-800">Instruções de Pagamento</h4>
                                <label class="flex items-center mb-2">
                                    <input type="checkbox" name="payment_instructions_enabled" value="1" <?php echo getSetting('payment_instructions_enabled', '0') === '1' ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600">
                                    <span class="ml-2 text-gray-700 text-sm font-bold">Exibir Instruções de Pagamento no Pedido Concluído</span>
                                </label>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Texto das Instruções</label>
                                    <div class="bg-white mb-12">
                                        <div id="payment_instructions_editor" style="min-height: 200px; height: auto;"><?php echo getSetting('payment_instructions_text', ''); ?></div>
                                    </div>
                                    <input type="hidden" name="payment_instructions_text" id="payment_instructions_text_input" value="<?php echo htmlspecialchars(getSetting('payment_instructions_text', '')); ?>">
                                </div>
                            </div>

                            <div class="mt-12 pt-4 border-t">
                                <button type="submit" onclick="document.getElementById('payment_instructions_text_input').value = window.paymentInstructionsQuill ? window.paymentInstructionsQuill.root.innerHTML : document.getElementById('payment_instructions_text_input').value;" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition-colors text-lg">
                                    Save Payment Settings
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Banner Settings -->
                    <div class="bg-white rounded shadow p-6">
                        <h2 class="text-xl font-semibold mb-4 border-b pb-2">Homepage Banner</h2>
                        <form action="/admin/save-banner-settings" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Banner Image URL</label>
                                <input type="text" name="banner_image_url" value="<?php echo htmlspecialchars(getSetting('banner_image_url', 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Or Upload New Image</label>
                                <input type="file" name="banner_image_file" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" data-filepond="image-single">
                                <?php if($img = getSetting('banner_image_url')): ?>
                                    <div class="mt-2 h-20 w-full rounded bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars($img); ?>')"></div>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Banner Title</label>
                                <input type="text" name="banner_title" value="<?php echo htmlspecialchars(getSetting('banner_title', 'Lab-Grade Peptides & Peptide Blends')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Banner Subtitle</label>
                                <textarea name="banner_subtitle" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars(getSetting('banner_subtitle', 'High purity research peptides for laboratory use only.')); ?></textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Primary Button Text</label>
                                    <input type="text" name="banner_button_text" value="<?php echo htmlspecialchars(getSetting('banner_button_text', '')); ?>" placeholder="e.g. Shop Now" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <p class="text-gray-500 text-xs mt-1">Leave empty to hide button.</p>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Primary Button Link</label>
                                    <input type="text" name="banner_button_link" value="<?php echo htmlspecialchars(getSetting('banner_button_link', '')); ?>" placeholder="e.g. /products" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Secondary Button Text</label>
                                    <input type="text" name="banner_button2_text" value="<?php echo htmlspecialchars(getSetting('banner_button2_text', '')); ?>" placeholder="e.g. View Plans" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <p class="text-gray-500 text-xs mt-1">Leave empty to hide button.</p>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Secondary Button Link</label>
                                    <input type="text" name="banner_button2_link" value="<?php echo htmlspecialchars(getSetting('banner_button2_link', '')); ?>" placeholder="e.g. /plans" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Right Image URL (Optional)</label>
                                <p class="text-gray-500 text-xs mb-2">Leave empty to hide the right image.</p>
                                <input type="text" name="banner_right_image_url" value="<?php echo htmlspecialchars(getSetting('banner_right_image_url', 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Or Upload New Image</label>
                                <input type="file" name="banner_right_image_file" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" data-filepond="image-single">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Overlay Color 1 (Left)</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="banner_overlay_color1" value="<?php echo htmlspecialchars(getSetting('banner_overlay_color1', '#111827')); ?>" class="h-10 w-10 p-0 border rounded">
                                            <input type="text" name="banner_overlay_color1_text" value="<?php echo htmlspecialchars(getSetting('banner_overlay_color1', '#111827')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" oninput="this.previousElementSibling.value = this.value">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Overlay Color 2 (Right)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="banner_overlay_color2" value="<?php echo htmlspecialchars(getSetting('banner_overlay_color2', '#1f2937')); ?>" class="h-10 w-10 p-0 border rounded">
                                        <input type="text" name="banner_overlay_color2_text" value="<?php echo htmlspecialchars(getSetting('banner_overlay_color2', '#1f2937')); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" oninput="this.previousElementSibling.value = this.value">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="banner_overlay_enabled" value="1" <?php echo getSetting('banner_overlay_enabled', '1') ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600">
                                    <span class="ml-2 text-gray-700 font-bold">Enable Overlay Gradient</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Overlay Opacity (0 to 100)</label>
                                <p class="text-gray-500 text-xs mb-2">Controls the darkness of the layer over the background image (default is 30).</p>
                                <input type="range" name="banner_overlay_opacity" min="0" max="100" value="<?php echo htmlspecialchars(getSetting('banner_overlay_opacity', '30')); ?>" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" oninput="this.nextElementSibling.value = this.value + '%'">
                                <output class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars(getSetting('banner_overlay_opacity', '30')); ?>%</output>
                            </div>

                            <div class="mt-8 pt-4 border-t">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition-colors text-lg">
                                    Save Banner Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Admins Tab -->
            <div x-show="tab === 'admins'" style="display: none;">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold"><?php echo __('Admins'); ?></h1>
                </div>

                <!-- Add New Admin -->
                <div class="bg-white rounded shadow p-6 mb-8" x-data="{ newAdminName: '', newAdminEmail: '', newAdminToken: '', loading: false, msg: '', success: false }">
                    <h2 class="text-lg font-semibold mb-4"><?php echo __('Add New Admin'); ?></h2>
                    <form @submit.prevent="
                        if(!newAdminEmail) return;
                        loading = true;
                        msg = '';
                        fetch('/admin/users/add', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ name: newAdminName, email: newAdminEmail, token: newAdminToken })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if(data.success) {
                                success = true;
                                msg = '<?php echo __('Admin added successfully. Refreshing...'); ?>';
                                setTimeout(() => window.location.reload(), 1500);
                            } else {
                                success = false;
                                msg = data.message || 'Error';
                            }
                        })
                        .catch(err => {
                            success = false;
                            msg = 'Error: ' + err;
                        })
                        .finally(() => loading = false);
                    " class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Name'); ?></label>
                            <input @keydown.enter="$event.preventDefault(); document.getElementById('add-admin-btn').click();" type="text" x-model="newAdminName" class="w-full border-gray-300 rounded-md shadow-sm p-2 border" placeholder="<?php echo __('Name'); ?>">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('User Email'); ?></label>
                            <input @keydown.enter="$event.preventDefault(); document.getElementById('add-admin-btn').click();" type="email" x-model="newAdminEmail" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border" placeholder="admin@example.com">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Bypass Token'); ?></label>
                            <input @keydown.enter="$event.preventDefault(); document.getElementById('add-admin-btn').click();" type="text" x-model="newAdminToken" class="w-full border-gray-300 rounded-md shadow-sm p-2 border font-mono" placeholder="000000">
                        </div>
                        <button id="add-admin-btn" type="submit" :disabled="loading" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 disabled:opacity-50 h-[42px]">
                            <span x-show="!loading"><?php echo __('Add Admin'); ?></span>
                            <span x-show="loading"><?php echo __('Adding...'); ?></span>
                        </button>
                    </form>
                    <p x-show="msg" class="mt-2 text-sm" :class="success ? 'text-green-600' : 'text-red-600'" x-text="msg"></p>
                </div>

                <!-- Admins List -->
                <div class="bg-white rounded shadow overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Name'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Email'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Added'); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Bypass Token'); ?></th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($adminUsers as $admin): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#<?php echo $admin['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($admin['name'] ?? '—'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($admin['email'] ?? ''); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('Y-m-d', strtotime($admin['created_at'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-data="{ editing: false, token: '<?php echo htmlspecialchars($admin['admin_bypass_token'] ?? ''); ?>' }">
                                        <div x-show="!editing" class="flex items-center gap-2">
                                            <span class="font-mono bg-gray-100 px-2 py-1 rounded" x-text="token || '<?php echo __('None'); ?>'"></span>
                                            <button @click="editing = true" class="text-blue-600 hover:text-blue-800 text-xs"><?php echo __('Edit'); ?></button>
                                        </div>
                                        <div x-show="editing" class="flex items-center gap-2">
                                            <input @keydown.enter="$event.preventDefault(); document.getElementById('save-token-btn-<?php echo $admin['id']; ?>').click();" type="text" x-model="token" class="border rounded px-2 py-1 text-sm w-24 font-mono" placeholder="000000">
                                            <button id="save-token-btn-<?php echo $admin['id']; ?>" @click="
                                                fetch('/admin/users/update-key', {
                                                    method: 'POST',
                                                    headers: { 'Content-Type': 'application/json' },
                                                    body: JSON.stringify({ id: <?php echo $admin['id']; ?>, token: token })
                                                }).then(r => r.json()).then(d => { 
                                                    if(d.success) { editing = false; } 
                                                    else { alert(d.message); } 
                                                });
                                            " class="text-green-600 hover:text-green-800 text-xs"><?php echo __('Save'); ?></button>
                                            <button @click="editing = false; token = '<?php echo htmlspecialchars($admin['admin_bypass_token'] ?? ''); ?>'" class="text-gray-500 hover:text-gray-700 text-xs"><?php echo __('Cancel'); ?></button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <?php if ($admin['id'] != 1 && $admin['id'] != $_SESSION['user_id']): ?>
                                            <button onclick="if(confirm('<?php echo __('Are you sure you want to revoke admin access for this user?'); ?>')) {
                                                fetch('/admin/users/remove', {
                                                    method: 'POST',
                                                    headers: { 'Content-Type': 'application/json' },
                                                    body: JSON.stringify({ id: <?php echo $admin['id']; ?> })
                                                }).then(r => r.json()).then(d => { if(d.success) window.location.reload(); else alert(d.message); });
                                            }" class="text-red-600 hover:text-red-900">
                                                <?php echo __('Revoke Access'); ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs"><?php echo __('Cannot revoke'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    </div>

    <!-- Category Modal -->
    <div x-show="categoryModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75" @click="categoryModalOpen = false"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="/admin/save-category" method="POST" class="p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" x-text="editCategory.id ? 'Edit Category' : 'Add New Category'"></h3>
                    
                    <input type="hidden" name="id" :value="editCategory.id">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" x-model="editCategory.name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 flex gap-3 justify-end">
                        <button type="button" @click="categoryModalOpen = false" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
                            Cancel
                        </button>
                        <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none">
                            Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Page Modal -->
    <div x-show="pageModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75" @click="pageModalOpen = false"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full">
                <form action="/admin/save-page" method="POST" class="p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" x-text="editPage.id ? 'Edit Page' : 'Add New Page'"></h3>
                    
                    <input type="hidden" name="id" :value="editPage.id">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" x-model="editPage.title" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Content</label>
                            <input type="hidden" name="content" id="pageContent" x-model="editPage.content">
                            <div id="editor" class="h-64 bg-white"></div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 flex gap-3 justify-end">
                        <button type="button" @click="pageModalOpen = false" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
                            Cancel
                        </button>
                        <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none">
                            Save Page
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('payment_instructions_editor')) {
                window.paymentInstructionsQuill = new Quill('#payment_instructions_editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            ['blockquote', 'code-block'],
                            [{ 'header': 1 }, { 'header': 2 }],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'script': 'sub'}, { 'script': 'super' }],
                            [{ 'indent': '-1'}, { 'indent': '+1' }],
                            [{ 'direction': 'rtl' }],
                            [{ 'size': ['small', false, 'large', 'huge'] }],
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'font': [] }],
                            [{ 'align': [] }],
                            ['clean'],
                            ['link', 'image']
                        ]
                    }
                });
            }
        });
    </script>    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof FilePond === 'undefined') {
                return;
            }

            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginFileValidateSize,
                FilePondPluginImagePreview
            );

            document.querySelectorAll('input[type="file"][data-filepond]').forEach(function(input) {
                var mode = input.getAttribute('data-filepond');
                var options = {
                    storeAsFile: true,
                    credits: false,
                    allowReorder: false,
                    labelIdle: 'Arraste e solte ou <span class="filepond--label-action">selecione um arquivo</span>'
                };

                if (mode === 'image-single') {
                    options.allowMultiple = false;
                    options.acceptedFileTypes = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
                    options.maxFileSize = '5MB';
                    options.labelFileTypeNotAllowed = 'Tipo de arquivo inválido';
                    options.fileValidateTypeLabelExpectedTypes = 'Use PNG, JPG, WEBP ou GIF';
                    options.labelMaxFileSizeExceeded = 'Arquivo muito grande';
                    options.labelMaxFileSize = 'Tamanho máximo: {filesize}';
                } else if (mode === 'csv-single') {
                    options.allowMultiple = false;
                    options.acceptedFileTypes = ['text/csv', 'application/csv', 'text/plain'];
                    options.maxFileSize = '10MB';
                    options.allowImagePreview = false;
                    options.labelFileTypeNotAllowed = 'Tipo de arquivo inválido';
                    options.fileValidateTypeLabelExpectedTypes = 'Use arquivo CSV';
                }

                FilePond.create(input, options);
            });
        });
    </script>

    <!-- Powered By -->
    <div class="w-full bg-black h-[35px] flex items-center justify-center shrink-0">
        <span class="text-white text-xs">Powered by LojaSimples</span>
    </div>
</body>
</html>
