<?php
$isMultilangEnabled = function_exists('getSetting') ? getSetting('i18n_multilang_enabled', '0') === '1' : false;
$pageType = $page['page_type'] ?? 'internal';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo isset($page['id']) ? __('Edit Page') : __('Add New Page'); ?> - <?php echo htmlspecialchars(getSetting('store_name', 'R2 Research Labs')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .ql-editor {
            min-height: 300px;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans flex flex-col h-screen" x-data="{ sidebarOpen: false, pageType: '<?php echo htmlspecialchars($pageType); ?>' }">

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
                <a href="/admin" onclick="localStorage.setItem('admin_tab','products')" class="block w-full text-left px-4 py-2 text-gray-400 hover:text-white rounded">
                    <?php echo __('Products'); ?>
                </a>
                <a href="/admin" onclick="localStorage.setItem('admin_tab','categories')" class="block w-full text-left px-4 py-2 text-gray-400 hover:text-white rounded">
                    <?php echo __('Categories'); ?>
                </a>
                <a href="/admin" onclick="localStorage.setItem('admin_tab','pages')" class="block w-full text-left px-4 py-2 rounded bg-gray-800 text-white">
                    <?php echo __('Pages'); ?>
                </a>
                <div class="border-t border-gray-800 my-1"></div>
                <a href="/admin" onclick="localStorage.setItem('admin_tab','orders')" class="block w-full text-left px-4 py-2 text-gray-400 hover:text-white rounded">
                    <?php echo __('Orders'); ?>
                </a>
                <a href="/admin" onclick="localStorage.setItem('admin_tab','customers')" class="block w-full text-left px-4 py-2 text-gray-400 hover:text-white rounded">
                    <?php echo __('Customers'); ?>
                </a>
                <div class="border-t border-gray-800 my-1"></div>
                <a href="/admin" onclick="localStorage.setItem('admin_tab','settings')" class="block w-full text-left px-4 py-2 text-gray-400 hover:text-white rounded">
                    <?php echo __('Settings'); ?>
                </a>
                <a href="/logout" class="block w-full text-left px-4 py-2 text-red-400 hover:text-red-300 hover:bg-gray-800 rounded">
                    <?php echo __('Logout'); ?>
                </a>
            </nav>
        </div>

        <!-- Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Mobile Header -->
            <div class="md:hidden bg-white border-b border-gray-200 flex items-center justify-between p-4 flex-shrink-0 shadow-sm z-10">
                <span class="font-bold text-lg text-gray-800 overflow-hidden whitespace-nowrap"><?php echo isset($page['id']) ? __('Edit Page') : __('Add New Page'); ?></span>
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 hover:text-gray-900 focus:outline-none p-1 ml-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-auto p-4 md:p-8">
                <div class="max-w-5xl mx-auto">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center">
                            <h1 class="text-2xl font-bold"><?php echo isset($page['id']) ? __('Edit Page') : __('Add New Page'); ?></h1>
                            <?php if (isset($_GET['saved']) && $_GET['saved'] == '1'): ?>
                                <span class="ml-4 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded border border-green-400">
                                    <?php echo __('Saved successfully!'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-4">
                            <?php if (!empty($page['slug']) && ($page['page_type'] ?? 'internal') === 'internal'): ?>
                                <a href="/<?php echo htmlspecialchars($page['slug']); ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800" title="<?php echo __('View Page'); ?>">
                                    <i class="fa-solid fa-external-link-alt text-lg"></i>
                                </a>
                            <?php endif; ?>
                            <a href="/admin" onclick="localStorage.setItem('admin_tab','pages')" class="text-gray-600 hover:text-gray-900" title="<?php echo __('Back to Dashboard'); ?>">
                                <i class="fa-solid fa-arrow-left text-lg"></i>
                            </a>
                        </div>
                    </div>

                    <form action="/admin/save-page" method="POST" id="pageForm" class="bg-white rounded shadow p-4 sm:p-6 space-y-6">
                        <?php if (isset($page['id'])): ?>
                            <input type="hidden" name="id" value="<?php echo (int)$page['id']; ?>">
                        <?php endif; ?>

                        <!-- Section 1: Type -->
                        <div class="border border-gray-200 rounded-md p-5 bg-white shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2"><?php echo __('Page Type'); ?></h3>
                            <div class="flex gap-6">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" name="page_type" value="internal" x-model="pageType" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700"><?php echo __('Internal'); ?></span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" name="page_type" value="external" x-model="pageType" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700"><?php echo __('External'); ?></span>
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-gray-500"><?php echo __('Internal pages render content here. External pages redirect to a URL.'); ?></p>
                        </div>

                        <!-- Section 2: Title -->
                        <div class="border border-gray-200 rounded-md p-5 bg-white shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2"><?php echo __('Title'); ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Title (English)</label>
                                    <input type="text" name="title" required value="<?php echo htmlspecialchars($page['title'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Title (Portuguese)</label>
                                    <input type="text" name="title_pt" value="<?php echo htmlspecialchars($page['title_pt'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                            <?php if (!empty($page['slug'])): ?>
                                <p class="mt-3 text-xs text-gray-500">Slug: <code class="px-1.5 py-0.5 bg-gray-100 rounded"><?php echo htmlspecialchars($page['slug']); ?></code></p>
                            <?php endif; ?>
                        </div>

                        <!-- Section 3: External URL -->
                        <div x-show="pageType === 'external'" class="border border-gray-200 rounded-md p-5 bg-white shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2"><?php echo __('External URL'); ?></h3>
                            <input type="url" name="external_url" placeholder="https://..." value="<?php echo htmlspecialchars($page['external_url'] ?? ''); ?>" class="block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Section 4: Content (Internal only) -->
                        <div x-show="pageType === 'internal'" class="border border-gray-200 rounded-md p-5 bg-white shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2"><?php echo __('Content'); ?></h3>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Content (English)</label>
                                    <input type="hidden" name="content" id="pageContent" value="<?php echo htmlspecialchars($page['content'] ?? ''); ?>">
                                    <div id="editor_en" class="bg-white border border-gray-300 rounded-md"></div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Content (Portuguese)</label>
                                    <input type="hidden" name="content_pt" id="pageContentPt" value="<?php echo htmlspecialchars($page['content_pt'] ?? ''); ?>">
                                    <div id="editor_pt" class="bg-white border border-gray-300 rounded-md"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 justify-end sticky bottom-0 bg-gray-50 -mx-4 sm:-mx-6 -mb-4 sm:-mb-6 px-4 sm:px-6 py-4 border-t border-gray-200">
                            <a href="/admin" onclick="localStorage.setItem('admin_tab','pages')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <?php echo __('Cancel'); ?>
                            </a>
                            <button type="submit" class="bg-indigo-600 py-2 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700">
                                <?php echo __('Save Page'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        const initialContentEn = <?php echo json_encode($page['content'] ?? ''); ?>;
        const initialContentPt = <?php echo json_encode($page['content_pt'] ?? ''); ?>;

        const quillToolbar = [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': [] }],
            ['link', 'image'],
            ['clean']
        ];

        const quillEn = new Quill('#editor_en', { theme: 'snow', modules: { toolbar: quillToolbar } });
        const quillPt = new Quill('#editor_pt', { theme: 'snow', modules: { toolbar: quillToolbar } });

        if (initialContentEn) quillEn.root.innerHTML = initialContentEn;
        if (initialContentPt) quillPt.root.innerHTML = initialContentPt;

        document.getElementById('pageForm').addEventListener('submit', function() {
            document.getElementById('pageContent').value = quillEn.root.innerHTML;
            document.getElementById('pageContentPt').value = quillPt.root.innerHTML;
        });
    </script>
</body>
</html>
