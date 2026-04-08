<?php
$productImages = isset($product['images']) && is_array($product['images']) ? $product['images'] : [];
$currentPrimaryImage = trim((string)($product['primary_image_url'] ?? $product['image_url'] ?? ''));
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo isset($product['id']) ? __('Edit Product') : __('Add New Product'); ?> - <?php echo htmlspecialchars(getSetting('store_name', 'R2 Research Labs')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
    <style>
        .ql-editor {
            min-height: 300px;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans flex flex-col h-screen" x-data="{ sidebarOpen: false }">

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
                <a href="/admin" onclick="localStorage.setItem('admin_tab','products')" class="block w-full text-left px-4 py-2 rounded bg-gray-800 text-white">
                    <?php echo __('Products'); ?>
                </a>
                <a href="/admin" onclick="localStorage.setItem('admin_tab','categories')" class="block w-full text-left px-4 py-2 text-gray-400 hover:text-white rounded">
                    <?php echo __('Categories'); ?>
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
                <span class="font-bold text-lg text-gray-800 text-truncate overflow-hidden whitespace-nowrap"><?php echo isset($product['id']) ? __('Edit Product') : __('Add New Product'); ?></span>
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 hover:text-gray-900 focus:outline-none p-1 ml-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-auto p-4 md:p-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold"><?php echo isset($product['id']) ? __('Edit Product') : __('Add New Product'); ?></h1>
                    <a href="/admin" class="text-gray-600 hover:text-gray-900"><?php echo __('Back to Dashboard'); ?></a>
                </div>

                <div class="bg-white rounded shadow overflow-x-auto p-4 sm:p-6">
                    <form action="/admin/save-product" method="POST" id="productForm" enctype="multipart/form-data">
                        <?php if (isset($product['id'])): ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
                        <?php endif; ?>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700"><?php echo __('Name'); ?></label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700"><?php echo __('Slug'); ?></label>
                                <input type="text" id="product_slug" name="slug" value="<?php echo htmlspecialchars($product['slug'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="ex: bpc-157-10mg">
                                <p class="mt-1 text-xs text-gray-500"><?php echo __('Used in product URL. If duplicated, a suffix is added automatically.'); ?></p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('SKU'); ?></label>
                                    <input type="text" name="sku" value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Price'); ?></label>
                                    <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700"><?php echo __('Category'); ?></label>
                                <select name="category_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    <option value=""><?php echo __('Select Category'); ?></option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($product['category_id']) && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700"><?php echo __('Upload Product Images'); ?></label>
                                <input type="file" name="product_images[]" accept="image/*" multiple class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white" data-filepond="image-multi">
                                <p class="mt-2 text-xs text-gray-500"><?php echo __('You can upload one or multiple images.'); ?></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3"><?php echo __('Product Images'); ?></label>
                                <div id="image-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                    <?php
                                    $combinedImages = [];
                                    if (!empty($product['image_url'])) {
                                        $combinedImages[] = $product['image_url'];
                                    }
                                    if (!empty($productImages)) {
                                        foreach ($productImages as $img) {
                                            if (!empty($img['image_url']) && !in_array($img['image_url'], $combinedImages)) {
                                                $combinedImages[] = $img['image_url'];
                                            }
                                        }
                                    }
                                    foreach ($combinedImages as $index => $imgUrl):
                                        $imgUrl = trim((string)$imgUrl);
                                        if ($imgUrl === '') continue;
                                    ?>
                                        <div class="relative border rounded-md p-2 bg-white shadow-sm cursor-move group">
                                            <img src="<?php echo htmlspecialchars($imgUrl); ?>" class="h-32 w-full object-contain rounded">
                                            <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($imgUrl); ?>" class="existing-image-input">
                                            <!-- Primary Badge -->
                                            <div class="absolute top-2 left-2 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded hidden group-first:block uppercase">Primary</div>
                                            <!-- Remove Button -->
                                            <button type="button" onclick="this.closest('.relative').remove();" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 shadow transition-transform hover:scale-110" title="Remove image">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Arraste e solte para reordenar. A primeira imagem sempre será a principal (capa). Clique no X vermelho para excluir imagens (inclusive placeholders indesejados).</p>
                                
                                <div class="mt-4 flex gap-2 max-w-lg">
                                    <input type="text" id="new_image_url" placeholder="Adicionar imagem via URL..." class="flex-1 border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                    <button type="button" onclick="addImageFromUrl()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-bold hover:bg-gray-300 transition-colors">Adicionar URL</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('PDF Report URL or File'); ?></label>
                                    <input type="text" name="pdf_url" value="<?php echo htmlspecialchars($product['pdf_url'] ?? ''); ?>" placeholder="<?php echo __('External URL (or use upload below)'); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 mb-3">
                                    
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Upload PDF File'); ?></label>
                                    <input type="file" name="pdf_file" accept="application/pdf" class="mt-1 block w-full bg-white" data-filepond="pdf-single">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('PDF Button Text'); ?></label>
                                    <input type="text" name="pdf_label" value="<?php echo htmlspecialchars($product['pdf_label'] ?? ''); ?>" placeholder="<?php echo __('Ex: Download Analysis Report'); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                </div>
                            </div>

                            <!-- Digital Product Settings -->
                            <div class="border border-gray-200 rounded-md p-4 bg-gray-50 mt-4 mb-4" x-data="{ isDigital: <?php echo isset($product['digital_delivery']) && $product['digital_delivery'] ? 'true' : 'false'; ?> }">
                                <div class="flex items-center justify-between mb-4 border-b border-gray-200 pb-3">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900"><?php echo __('Digital Delivery'); ?></h3>
                                        <p class="text-sm text-gray-500"><?php echo __('Enable if this product includes a downloadable file after purchase.'); ?></p>
                                    </div>
                                    <label class="flex items-center cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" name="digital_delivery" value="1" class="sr-only" x-model="isDigital">
                                            <div class="block bg-gray-300 w-10 h-6 rounded-full transition" :class="{'bg-indigo-600': isDigital}"></div>
                                            <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform" :class="{'translate-x-4': isDigital}"></div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div x-show="isDigital" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2" style="display: none;">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><?php echo __('File URL'); ?></label>
                                        <input type="text" name="file_url" value="<?php echo htmlspecialchars($product['file_url'] ?? ''); ?>" placeholder="<?php echo __('Remote URL (or upload below)'); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 mb-3">
                                        
                                        <label class="block text-sm font-medium text-gray-700"><?php echo __('Upload Digital File (Max 25MB)'); ?></label>
                                        <input type="file" name="digital_file" accept=".pdf,.zip,.mp4,.docx" class="mt-1 block w-full bg-white" data-filepond="digital-single">
                                        <p class="mt-1 text-xs text-gray-500">Allowed: PDF, ZIP, MP4, DOCX.</p>
                                    </div>
                                    <div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700"><?php echo __('Download Limit'); ?></label>
                                            <input type="number" min="0" name="download_limit" value="<?php echo htmlspecialchars($product['download_limit'] ?? ''); ?>" placeholder="<?php echo __('Leave empty for unlimited'); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700"><?php echo __('Expiry Days'); ?></label>
                                            <input type="number" min="0" name="download_expiry_days" value="<?php echo htmlspecialchars($product['download_expiry_days'] ?? ''); ?>" placeholder="<?php echo __('Leave empty for no expiry'); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        </div>
                                        
                                        <!-- Widget Embed Code Box -->
                                        <?php if(isset($product['slug']) && $product['slug']): ?>
                                        <div class="mt-4 p-3 bg-gray-100 rounded border border-gray-300 text-xs">
                                            <p class="font-bold mb-1 text-gray-800">Widget de Venda Externa (Embed)</p>
                                            <p class="text-gray-500 mb-2">Copie e cole este código em qualquer site para exibir um botão de compra deste produto.</p>
                                            <textarea readonly class="w-full bg-gray-800 text-green-400 p-2 rounded font-mono h-24" onclick="this.select()">&lt;!-- Botão de Compra --&gt;
&lt;div data-checkout="<?php echo htmlspecialchars($product['slug']); ?>" data-color="#017737" data-text="Comprar agora"&gt;&lt;/div&gt;
&lt;script src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/public/embed.js"&gt;&lt;/script&gt;</textarea>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <input type="hidden" name="type" x-bind:value="isDigital ? 'digital' : 'physical'">
                            </div>

                            <div class="mb-12">
                                <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Short Description'); ?></label>
                                <input type="hidden" name="short_desc" id="short_desc_input">
                                <div id="short_desc_editor" class="bg-white">
                                    <?php echo $product['short_desc'] ?? ''; ?>
                                </div>
                            </div>

                            <div class="mb-12">
                                <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Long Description'); ?></label>
                                <input type="hidden" name="long_desc" id="long_desc_input">
                                <div id="long_desc_editor" class="bg-white">
                                    <?php echo $product['long_desc'] ?? ''; ?>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <a href="/admin" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none mr-3">
                                    <?php echo __('Cancel'); ?>
                                </a>
                                <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none">
                                    <?php echo __('Save Product'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Short Description Editor
            var shortQuill = new Quill('#short_desc_editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ]
                }
            });

            // Initialize Long Description Editor
            var longQuill = new Quill('#long_desc_editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });

            // Form submission handler
            var form = document.getElementById('productForm');
            var nameInput = form ? form.querySelector('input[name="name"]') : null;
            var slugInput = document.getElementById('product_slug');
            var slugTouched = slugInput ? slugInput.value.trim() !== '' : false;

            var slugify = function(value) {
                return String(value || '')
                    .toLowerCase()
                    .replace(/[^a-z0-9-]+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '');
            };

            if (nameInput && slugInput) {
                if (!slugTouched) {
                    slugInput.value = slugify(nameInput.value);
                }
                nameInput.addEventListener('input', function() {
                    if (slugTouched) return;
                    slugInput.value = slugify(nameInput.value);
                });
                slugInput.addEventListener('input', function() {
                    slugTouched = true;
                });
            }

            form.addEventListener('submit', function(e) {
                document.getElementById('short_desc_input').value = shortQuill.root.innerHTML;
                document.getElementById('long_desc_input').value = longQuill.root.innerHTML;
            });

            // Initialize Sortable for image grid
            var grid = document.getElementById('image-grid');
            if (grid) {
                new Sortable(grid, {
                    animation: 150,
                    ghostClass: 'opacity-50',
                    cursor: 'move'
                });
            }

            window.addImageFromUrl = function() {
                const input = document.getElementById('new_image_url');
                const url = input.value.trim();
                if (!url) return;
                
                const div = document.createElement('div');
                div.className = 'relative border rounded-md p-2 bg-white shadow-sm cursor-move group';
                div.innerHTML = `
                    <img src="${url}" class="h-32 w-full object-contain rounded">
                    <input type="hidden" name="existing_images[]" value="${url}">
                    <div class="absolute top-2 left-2 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded hidden group-first:block uppercase">Primary</div>
                    <button type="button" onclick="this.closest('.relative').remove();" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 shadow transition-transform hover:scale-110" title="Remove image">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                `;
                document.getElementById('image-grid').appendChild(div);
                input.value = '';
            };

            if (typeof FilePond !== 'undefined') {
                FilePond.registerPlugin(
                    FilePondPluginFileValidateType,
                    FilePondPluginFileValidateSize,
                    FilePondPluginImagePreview
                );

                var productImagesInput = document.querySelector('input[type="file"][data-filepond="image-multi"]');
                if (productImagesInput) {
                    FilePond.create(productImagesInput, {
                        storeAsFile: true,
                        credits: false,
                        allowReorder: true,
                        allowMultiple: true,
                        maxFiles: 12,
                        acceptedFileTypes: ['image/png', 'image/jpeg', 'image/webp', 'image/gif'],
                        maxFileSize: '5MB',
                        labelIdle: 'Arraste e solte ou <span class="filepond--label-action">selecione imagens</span>',
                        labelFileTypeNotAllowed: 'Tipo de arquivo inválido',
                        fileValidateTypeLabelExpectedTypes: 'Use PNG, JPG, WEBP ou GIF',
                        labelMaxFileSizeExceeded: 'Arquivo muito grande',
                        labelMaxFileSize: 'Tamanho máximo: {filesize}'
                    });
                }

                var pdfInput = document.querySelector('input[type="file"][data-filepond="pdf-single"]');
                if (pdfInput) {
                    FilePond.create(pdfInput, {
                        storeAsFile: true,
                        credits: false,
                        allowMultiple: false,
                        acceptedFileTypes: ['application/pdf'],
                        maxFileSize: '10MB',
                        labelIdle: 'Arraste e solte o PDF ou <span class="filepond--label-action">selecione o arquivo</span>',
                        labelFileTypeNotAllowed: 'Tipo de arquivo inválido',
                        fileValidateTypeLabelExpectedTypes: 'Use PDF',
                        labelMaxFileSizeExceeded: 'Arquivo muito grande',
                        labelMaxFileSize: 'Tamanho máximo: {filesize}'
                    });
                }

                var digitalInput = document.querySelector('input[type="file"][data-filepond="digital-single"]');
                if (digitalInput) {
                    FilePond.create(digitalInput, {
                        storeAsFile: true,
                        credits: false,
                        allowMultiple: false,
                        acceptedFileTypes: ['application/pdf', 'application/zip', 'video/mp4', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                        maxFileSize: '25MB',
                        labelIdle: 'Arraste e solte o arquivo digital ou <span class="filepond--label-action">selecione o arquivo</span>',
                        labelFileTypeNotAllowed: 'Tipo de arquivo inválido',
                        fileValidateTypeLabelExpectedTypes: 'Use PDF, ZIP, MP4 ou DOCX',
                        labelMaxFileSizeExceeded: 'Arquivo muito grande',
                        labelMaxFileSize: 'Tamanho máximo: {filesize}'
                    });
                }
            }
        });
    </script>

    <!-- Powered By -->
    <div class="w-full bg-black h-[35px] flex items-center justify-center shrink-0">
        <span class="text-white text-xs">Powered by LojaSimples</span>
    </div>
</body>
</html>
