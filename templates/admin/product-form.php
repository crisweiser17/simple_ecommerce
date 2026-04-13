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
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold"><?php echo isset($product['id']) ? __('Edit Product') : __('Add New Product'); ?></h1>
                        <?php if (isset($_GET['saved']) && $_GET['saved'] == '1'): ?>
                            <span class="ml-4 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded border border-green-400">
                                <?php echo __('Saved successfully!'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-4">
                        <?php if (isset($product['slug'])): ?>
                            <a href="/product/<?php echo htmlspecialchars($product['slug']); ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800" title="<?php echo __('View Product Page'); ?>">
                                <i class="fa-solid fa-external-link-alt text-lg"></i>
                            </a>
                        <?php endif; ?>
                        <a href="/admin" class="text-gray-600 hover:text-gray-900" title="<?php echo __('Back to Dashboard'); ?>">
                            <i class="fa-solid fa-arrow-left text-lg"></i>
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded shadow overflow-x-auto p-4 sm:p-6">
                    <form action="/admin/save-product" method="POST" id="productForm" enctype="multipart/form-data">
                        <?php if (isset($product['id'])): ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
                        <?php endif; ?>

                        <!-- Seção 1: Informações Básicas -->
                        <div class="border border-gray-200 rounded-md p-5 bg-white shadow-sm mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2"><?php echo __('Basic Information'); ?></h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Name'); ?></label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Slug'); ?></label>
                                    <input type="text" id="product_slug" name="slug" value="<?php echo htmlspecialchars($product['slug'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="<?php echo __('e.g. bpc-157-10mg'); ?>">
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
                            </div>
                        </div>

                        <!-- Seção 2: Mídia e Uploads -->
                        <div class="border border-gray-200 rounded-md p-5 bg-white shadow-sm mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2"><?php echo __('Images & Files (Uploads)'); ?></h3>
                            <div class="grid grid-cols-1 gap-6">
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
                                                <?php
                                                $filename = basename($imgUrl);
                                                $displayFilename = mb_strlen($filename) > 22 ? mb_substr($filename, 0, 10) . '...' . mb_substr($filename, -9) : $filename;
                                                ?>
                                                <div class="relative group/tooltip mt-2 w-full flex justify-center">
                                                    <p class="text-[10px] text-gray-500 text-center cursor-help"><?php echo htmlspecialchars($displayFilename); ?></p>
                                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover/tooltip:block bg-gray-900 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-50 pointer-events-none shadow-lg">
                                                        <?php echo htmlspecialchars($filename); ?>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($imgUrl); ?>" class="existing-image-input">
                                                <!-- Primary Badge -->
                                                <div class="absolute top-2 left-2 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded hidden group-first:block uppercase">Primary</div>
                                                <!-- Remove Button -->
                                                <button type="button" onclick="this.closest('.relative').remove(); document.dispatchEvent(new CustomEvent('gallery-updated'));" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 shadow transition-transform hover:scale-110" title="Remove image">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500"><?php echo __('Drag and drop to reorder. The first image will be the primary one. Click the red X to remove images.'); ?></p>
                                    
                                    <div class="mt-4 flex gap-2 max-w-lg">
                                        <input type="text" id="new_image_url" placeholder="<?php echo __('Add URL'); ?>..." class="flex-1 border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                        <button type="button" onclick="addImageFromUrl()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-bold hover:bg-gray-300 transition-colors"><?php echo __('Add URL'); ?></button>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Analysis Report (PDF) -->
                        <div class="border border-gray-200 rounded-md p-5 bg-gray-50 mb-6" x-data="{ pdfActive: <?php echo isset($product['pdf_active']) && $product['pdf_active'] ? 'true' : 'false'; ?> }">
                            <div class="flex items-center justify-between mb-4 border-b border-gray-200 pb-3">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo __('Analysis Report (PDF)'); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo __('Habilite para mostrar a aba de PDF/Laudo na página do produto.'); ?></p>
                                </div>
                                <label class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" name="pdf_active" value="1" class="sr-only" x-model="pdfActive">
                                        <div class="block bg-gray-300 w-10 h-6 rounded-full transition" :class="{'bg-indigo-600': pdfActive}"></div>
                                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform" :class="{'translate-x-4': pdfActive}"></div>
                                    </div>
                                </label>
                            </div>

                            <div x-show="pdfActive" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2" style="display: none;">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('URL do PDF'); ?></label>
                                    <input type="text" name="pdf_url" value="<?php echo htmlspecialchars($product['pdf_url'] ?? ''); ?>" placeholder="<?php echo __('External URL (or use upload below)'); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 mb-3">
                                    
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Upload de Arquivo (Máx 10MB)'); ?></label>
                                    <input type="file" name="pdf_file" accept="application/pdf" class="mt-1 block w-full bg-white" data-filepond="pdf-single">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Nome da Aba / Rótulo'); ?></label>
                                    <input type="text" name="pdf_label" value="<?php echo htmlspecialchars($product['pdf_label'] ?? ''); ?>" placeholder="<?php echo __('Ex: Download Analysis Report'); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                </div>
                            </div>
                        </div>

                        <!-- Seção: Variações -->
                        <div class="border border-purple-200 rounded-md p-5 bg-purple-50 mb-6" x-data="productVariations()">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b border-purple-200 pb-2"><?php echo __('Product Variations'); ?></h3>
                            <input type="hidden" name="variations_json" :value="JSON.stringify(variations)">
                            
                            <div class="space-y-4">
                                <template x-for="(variation, vIndex) in variations" :key="vIndex">
                                    <div class="bg-white p-4 rounded border border-gray-200 shadow-sm relative">
                                        <button type="button" @click="variations.splice(vIndex, 1)" class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700"><?php echo __('Variation Name'); ?></label>
                                                <input type="text" x-model="variation.name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="ex: Tamanho">
                                            </div>
                                            <div class="flex items-center pt-6">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="checkbox" x-model="variation.save_global" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm text-gray-600"><?php echo __('Save variation for future use'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Options and Prices'); ?></label>
                                            <template x-for="(opt, oIndex) in variation.options" :key="oIndex">
                                                <div class="flex flex-col md:flex-row md:items-center gap-2 mb-2 p-2 border border-gray-200 bg-white rounded-md">
                                                    <input type="text" x-model="opt.name" class="flex-1 border border-gray-300 rounded-md shadow-sm p-2 text-sm" placeholder="Option (ex: Pequeno)">
                                                    <input type="text" x-model="opt.sku" class="w-full md:w-32 border border-gray-300 rounded-md shadow-sm p-2 text-sm" placeholder="SKU">
                                                    <div class="flex items-center gap-1 w-full md:w-auto">
                                                        <span class="text-sm text-gray-500"><?php echo htmlspecialchars(getSetting('store_currency_symbol', 'R$')); ?></span>
                                                        <input type="number" step="0.01" x-model="opt.price" class="w-full md:w-24 border border-gray-300 rounded-md shadow-sm p-2 text-sm" placeholder="Preço">
                                                    </div>
                                                    <div class="w-full md:w-48 relative" x-data="{ open: false }">
                                                        <div class="flex items-center border border-gray-300 rounded-md shadow-sm p-1 text-sm bg-white cursor-pointer h-full" @click="updateAvailableImages(); open = !open" :title="opt.image_url ? opt.image_url.split('/').pop() : ''">
                                                            <template x-if="opt.image_url">
                                                                <div class="flex items-center w-full">
                                                                    <img :src="opt.image_url" class="w-6 h-6 object-cover rounded mr-2">
                                                                    <span class="flex-1 text-xs text-gray-700" x-text="formatFilename(opt.image_url, 20)"></span>
                                                                </div>
                                                            </template>
                                                            <template x-if="!opt.image_url">
                                                                <span class="text-gray-400 pl-1"><?php echo __('Select Image...'); ?></span>
                                                            </template>
                                                            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                        </div>
                                                        <div x-show="open" @click.away="open = false" class="absolute z-50 w-64 bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto right-0">
                                                            <div class="p-2 border-b border-gray-100 flex items-center hover:bg-gray-50 cursor-pointer" @click="opt.image_url = ''; open = false">
                                                                <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-400 mr-2 text-xs">None</div>
                                                                <span class="text-sm text-gray-500 italic"><?php echo __('No Image'); ?></span>
                                                            </div>
                                                            <template x-for="imgUrl in availableImages" :key="imgUrl">
                                                                <div class="p-2 border-b border-gray-100 flex items-center hover:bg-gray-50 cursor-pointer" @click="opt.image_url = imgUrl; open = false" :title="imgUrl.split('/').pop()">
                                                                    <img :src="imgUrl" class="w-8 h-8 object-cover rounded border border-gray-200 mr-2">
                                                                    <span class="text-xs text-gray-700" x-text="formatFilename(imgUrl, 25)"></span>
                                                                </div>
                                                            </template>
                                                            <div x-show="availableImages.length === 0" class="p-3 text-center text-xs text-gray-500">
                                                                <?php echo __('No images in gallery yet. Upload above first.'); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" @click="variation.options.splice(oIndex, 1)" class="text-red-500 hover:text-red-700 p-2 ml-auto self-end md:self-auto">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <button type="button" @click="variation.options.push({name: '', sku: '', price: '', image_url: ''})" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                                + <?php echo __('Add Option'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                
                                <div class="flex flex-wrap gap-2 items-center">
                                    <button type="button" @click="variations.push({name: '', options: [], save_global: false})" class="bg-purple-600 text-white px-4 py-2 rounded text-sm hover:bg-purple-700">
                                        <?php echo __('Add Custom Variation'); ?>
                                    </button>
                                    
                                    <?php if (!empty($global_variations)): ?>
                                    <select @change="if($event.target.value !== '') { addGlobalVariation($event.target.value); $event.target.value = ''; }" class="border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                        <option value=""><?php echo __('Or select an existing one...'); ?></option>
                                        <?php foreach ($global_variations as $gv): ?>
                                            <option value="<?php echo htmlspecialchars(json_encode($gv)); ?>"><?php echo htmlspecialchars($gv['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Digital Product Settings -->
                        <div class="border border-blue-200 rounded-md p-5 bg-blue-50 mb-6" x-data="{ isDigital: <?php echo isset($product['digital_delivery']) && $product['digital_delivery'] ? 'true' : 'false'; ?> }">
                            <div class="flex items-center justify-between mb-4 border-b border-blue-200 pb-3">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo __('Digital Delivery'); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo __('Ative se este produto incluir um arquivo para download após a compra.'); ?></p>
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
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('URL do Arquivo'); ?></label>
                                    <input type="text" name="file_url" value="<?php echo htmlspecialchars($product['file_url'] ?? ''); ?>" placeholder="<?php echo __('URL Remota (ou faça o upload abaixo)'); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 mb-3">
                                    
                                    <label class="block text-sm font-medium text-gray-700"><?php echo __('Upload do Arquivo Digital (Máx 25MB)'); ?></label>
                                    <input type="file" name="digital_file" accept=".pdf,.zip,.mp4,.docx" class="mt-1 block w-full bg-white" data-filepond="digital-single">
                                    <p class="mt-1 text-xs text-gray-500"><?php echo __('Permitido: PDF, ZIP, MP4, DOCX.'); ?></p>
                                </div>
                                <div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700"><?php echo __('Limite de Downloads'); ?></label>
                                        <input type="number" min="0" name="download_limit" value="<?php echo htmlspecialchars($product['download_limit'] ?? ''); ?>" placeholder="<?php echo __('Deixe em branco para ilimitado'); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><?php echo __('Dias para Expirar'); ?></label>
                                        <input type="number" min="0" name="download_expiry_days" value="<?php echo htmlspecialchars($product['download_expiry_days'] ?? ''); ?>" placeholder="<?php echo __('Leave blank to not expire'); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    </div>
                                    
                                    <!-- Widget Embed Code Box -->
                                    <?php if(isset($product['slug']) && $product['slug']): ?>
                                    <div class="mt-4 p-3 bg-gray-100 rounded border border-gray-300 text-xs">
                                        <p class="font-bold mb-1 text-gray-800"><?php echo __('External Sale Widget (Embed)'); ?></p>
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

                        <!-- Seção 3: Descrições -->
                        <div class="border border-gray-200 rounded-md p-5 bg-white shadow-sm mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2"><?php echo __('Descriptions'); ?></h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Short Description'); ?></label>
                                    <input type="hidden" name="short_desc" id="short_desc_input">
                                    <div class="mb-6">
                                        <div id="short_desc_editor" class="bg-white">
                                            <?php echo $product['short_desc'] ?? ''; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8 pt-4 border-t border-gray-100">
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Long Description'); ?></label>
                                    <input type="hidden" name="long_desc" id="long_desc_input">
                                    <div class="mb-6">
                                        <div id="long_desc_editor" class="bg-white">
                                            <?php echo $product['long_desc'] ?? ''; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 mb-8">
                            <a href="/admin" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none mr-3">
                                <?php echo __('Cancel'); ?>
                            </a>
                            <button type="submit" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none">
                                <?php echo __('Save Product'); ?>
                            </button>
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
            // Register Code View Module
            Quill.register('modules/codeView', function(quill, options) {
                const toolbar = quill.getModule('toolbar');
                toolbar.addHandler('code-view', function() {
                    const container = quill.container;
                    const editor = container.querySelector('.ql-editor');
                    let textarea = container.querySelector('.ql-html-editor');
                    
                    if (!textarea) {
                        textarea = document.createElement('textarea');
                        textarea.className = 'ql-html-editor w-full p-4 font-mono text-sm bg-gray-900 text-green-400 border-none focus:ring-0';
                        textarea.style.minHeight = '300px';
                        textarea.style.display = 'none';
                        container.appendChild(textarea);
                        
                        // Sync textarea back to Quill on change
                        textarea.addEventListener('input', function() {
                            quill.root.innerHTML = textarea.value;
                        });
                    }

                    if (textarea.style.display === 'none') {
                        textarea.value = quill.root.innerHTML;
                        textarea.style.display = 'block';
                        editor.style.display = 'none';
                    } else {
                        quill.root.innerHTML = textarea.value;
                        textarea.style.display = 'none';
                        editor.style.display = 'block';
                    }
                });
            });

            // Initialize Short Description Editor
            var shortQuill = new Quill('#short_desc_editor', {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['clean'],
                            ['code-view']
                        ]
                    },
                    codeView: true
                }
            });

            // Initialize Long Description Editor
            var longQuill = new Quill('#long_desc_editor', {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link', 'image'],
                            ['clean'],
                            ['code-view']
                        ]
                    },
                    codeView: true
                }
            });

            // Add SVG icons to code-view buttons
            document.querySelectorAll('.ql-code-view').forEach(function(btn) {
                btn.innerHTML = '<svg viewBox="0 0 18 18"><polyline class="ql-even ql-stroke" points="5 7 3 9 5 11"></polyline><polyline class="ql-even ql-stroke" points="13 7 15 9 13 11"></polyline><line class="ql-stroke" x1="10" x2="8" y1="5" y2="13"></line></svg>';
                btn.title = 'View HTML Source';
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
                
                const filename = url.split('/').pop() || url;
                const displayFilename = filename.length > 22 ? filename.substring(0, 10) + '...' + filename.substring(filename.length - 9) : filename;
                const div = document.createElement('div');
                div.className = 'relative border rounded-md p-2 bg-white shadow-sm cursor-move group';
                div.innerHTML = `
                    <img src="${url}" class="h-32 w-full object-contain rounded">
                    <div class="relative group/tooltip mt-2 w-full flex justify-center">
                        <p class="text-[10px] text-gray-500 text-center cursor-help">${displayFilename}</p>
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover/tooltip:block bg-gray-900 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-50 pointer-events-none shadow-lg">
                            ${filename}
                        </div>
                    </div>
                    <input type="hidden" name="existing_images[]" value="${url}">
                    <div class="absolute top-2 left-2 bg-indigo-600 text-white text-[10px] font-bold px-2 py-1 rounded hidden group-first:block uppercase">Primary</div>
                    <button type="button" onclick="this.closest('.relative').remove(); document.dispatchEvent(new CustomEvent('gallery-updated'));" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 shadow transition-transform hover:scale-110" title="Remove image">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                `;
                document.getElementById('image-grid').appendChild(div);
                input.value = '';
                // Dispatch event for variations component to update its image list
                document.dispatchEvent(new CustomEvent('gallery-updated'));
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
                        labelFileTypeNotAllowed: '<?php echo __("Invalid file type"); ?>',
                        fileValidateTypeLabelExpectedTypes: '<?php echo __('Use PNG, JPG'); ?>, WEBP ou GIF',
                        labelMaxFileSizeExceeded: '<?php echo __('File is too large'); ?>',
                        labelMaxFileSize: '<?php echo __('Maximum file size is'); ?> {filesize}'
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
                        labelFileTypeNotAllowed: '<?php echo __("Invalid file type"); ?>',
                        fileValidateTypeLabelExpectedTypes: 'Use PDF',
                        labelMaxFileSizeExceeded: '<?php echo __('File is too large'); ?>',
                        labelMaxFileSize: '<?php echo __('Maximum file size is'); ?> {filesize}'
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
                        labelFileTypeNotAllowed: '<?php echo __("Invalid file type"); ?>',
                        fileValidateTypeLabelExpectedTypes: '<?php echo __('Use PDF, ZIP, MP4 or DOCX'); ?>',
                        labelMaxFileSizeExceeded: '<?php echo __('File is too large'); ?>',
                        labelMaxFileSize: '<?php echo __('Maximum file size is'); ?> {filesize}'
                    });
                }
            }
        });

        function productVariations() {
            // Get available images from the form inputs
            const getAvailableImages = () => {
                const images = [];
                // Add primary image if it exists
                const primaryInput = document.querySelector('input[name="existing_images[]"]');
                if (primaryInput && primaryInput.value) {
                    images.push(primaryInput.value);
                }
                
                // Add gallery images if they exist
                const galleryInputs = document.querySelectorAll('input[name="existing_images[]"]');
                galleryInputs.forEach(input => {
                    if (input.value && !images.includes(input.value)) {
                        images.push(input.value);
                    }
                });
                
                return images;
            };

            return {
                variations: <?php echo empty($product['variations_json']) ? '[]' : $product['variations_json']; ?>,
                availableImages: [],
                init() {
                    // Initialize available images
                    this.updateAvailableImages();
                    
                    // Listen for changes in the gallery to update available images
                    document.addEventListener('gallery-updated', () => {
                        this.updateAvailableImages();
                    });
                },
                updateAvailableImages() {
                    this.availableImages = getAvailableImages();
                },
                formatFilename(url, maxLength = 22) {
                    if (!url) return '';
                    const filename = url.split('/').pop();
                    if (filename.length <= maxLength) return filename;
                    const charsToShow = Math.floor((maxLength - 3) / 2);
                    return filename.substring(0, charsToShow) + '...' + filename.substring(filename.length - charsToShow);
                },
                addGlobalVariation(jsonStr) {
                    try {
                        const gv = JSON.parse(jsonStr);
                        const opts = gv.options.map(o => ({
                            name: o.name,
                            sku: o.sku || '',
                            price: o.price || '',
                            image_url: o.image_url || ''
                        }));
                        this.variations.push({
                            name: gv.name,
                            options: opts,
                            save_global: false
                        });
                    } catch(e) {}
                }
            }
        }
    </script>

    <!-- Powered By -->
    <div class="w-full bg-black h-[35px] flex items-center justify-center shrink-0">
        <span class="text-white text-xs"><?php echo htmlspecialchars(getSetting('store_footer_text', 'R2 Research Labs - All Rights Reserved.')); ?></span>
    </div>
</body>
</html>
