<?php
/** @var array $product */
$categoryName = isset($product['category_name']) ? (string) $product['category_name'] : (isset($product['category']) ? (string)$product['category'] : '');
$categorySlug = $product['category_slug'] ?? strtolower(str_replace(' ', '-', $categoryName));
$categoryLabel = $categoryName !== '' ? __($categoryName) : __('Category');
$galleryImages = [];
if (isset($product['images']) && is_array($product['images'])) {
    foreach ($product['images'] as $img) {
        $url = trim((string)($img['image_url'] ?? ''));
        if ($url !== '' && !in_array($url, $galleryImages, true)) {
            $galleryImages[] = $url;
        }
    }
}
$primaryImage = getProductPrimaryImageUrl($product);
if ($primaryImage === '' && !empty($galleryImages)) {
    $primaryImage = $galleryImages[0];
}
if ($primaryImage !== '' && !in_array($primaryImage, $galleryImages, true)) {
    array_unshift($galleryImages, $primaryImage);
}
if ($primaryImage === '') {
    $primaryImage = 'https://placehold.co/700x700?text=No+Image';
}
?>
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 mb-6">
        <a href="/" class="hover:text-orange-600"><?php echo __('Home'); ?></a>
        <span class="mx-2">/</span>
        <a href="/?category=<?php echo urlencode($categorySlug); ?>" class="hover:text-orange-600"><?php echo htmlspecialchars($categoryLabel ?? ''); ?></a>
        <span class="mx-2">/</span>
        <span class="text-gray-900"><?php echo htmlspecialchars($product['name'] ?? ''); ?></span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
        <div x-data="{ selectedImage: <?php echo htmlspecialchars(json_encode($primaryImage), ENT_QUOTES, 'UTF-8'); ?> }">
            <div class="bg-white border rounded-lg p-8 flex items-center justify-center">
                <img :src="selectedImage" src="<?php echo htmlspecialchars($primaryImage ?? ''); ?>" alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" class="max-h-[500px] object-contain">
            </div>
            <?php if (count($galleryImages) > 1): ?>
                <div class="mt-4 grid grid-cols-4 gap-3">
                    <?php foreach ($galleryImages as $thumbUrl): ?>
                        <button type="button" @click="selectedImage = <?php echo htmlspecialchars(json_encode($thumbUrl), ENT_QUOTES, 'UTF-8'); ?>" class="border rounded-md p-2 bg-white hover:border-orange-500 transition-colors">
                            <img src="<?php echo htmlspecialchars($thumbUrl ?? ''); ?>" alt="" class="h-16 w-full object-contain">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($product['name'] ?? ''); ?></h1>
            
            <?php $variations = json_decode($product['variations_json'] ?? '[]', true) ?: []; ?>
            <div x-data="{ 
                qty: 1, 
                basePrice: <?php echo (float)($product['price'] ?? 0); ?>,
                variations: <?php echo htmlspecialchars(json_encode($variations)); ?>,
                selectedOptions: {},
                get currentPrice() {
                    let total = this.basePrice;
                    for (const v of this.variations) {
                        const selected = this.selectedOptions[v.name];
                        if (selected) {
                            const opt = v.options.find(o => o.name === selected);
                            if (opt && opt.price && parseFloat(opt.price) > 0) {
                                total = parseFloat(opt.price);
                            }
                        }
                    }
                    return total;
                },
                get currentSku() {
                    let sku = '<?php echo htmlspecialchars($product['sku'] ?? ''); ?>';
                    for (const v of this.variations) {
                        const selected = this.selectedOptions[v.name];
                        if (selected) {
                            const opt = v.options.find(o => o.name === selected);
                            if (opt && opt.sku) {
                                sku = opt.sku;
                            }
                        }
                    }
                    return sku;
                },
                formatMoney(amount) {
                    return new Intl.NumberFormat('<?php echo htmlspecialchars($_SESSION['lang'] ?? 'pt'); ?>-BR', { style: 'currency', currency: '<?php echo htmlspecialchars(getSetting('store_currency', 'BRL')); ?>' }).format(amount);
                },
                addToCart() {
                    for (const v of this.variations) {
                        if (!this.selectedOptions[v.name]) {
                            alert('<?php echo __('Please select all options before adding to cart'); ?>: ' + v.name);
                            return;
                        }
                    }
                    
                    const productData = <?php echo htmlspecialchars(json_encode($product)); ?>;
                    productData.price = this.currentPrice;
                    productData.selected_variations = this.selectedOptions;
                    
                    $store.cart.add({ ...productData, quantity: this.qty });
                },
                init() {
                    for (const v of this.variations) {
                        if (v.options && v.options.length > 0) {
                            this.selectedOptions[v.name] = v.options[0].name;
                        }
                    }
                }
            }">
                <div class="mb-4">
                    <span class="text-gray-500 text-sm">SKU:</span>
                    <span class="text-gray-900 text-sm font-medium" x-text="currentSku"></span>
                </div>

                <?php if ($storeMode === 'ecommerce'): ?>
                <div class="text-2xl font-bold text-orange-600 mb-6" x-text="formatMoney(currentPrice)"><?php echo formatMoney($product['price']); ?></div>
                <?php endif; ?>

                <div class="prose text-gray-600 mb-8">
                    <?php echo $product['short_desc']; ?>
                </div>

                <template x-if="variations.length > 0">
                    <div class="mb-6 space-y-4">
                        <template x-for="v in variations" :key="v.name">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" x-text="v.name"></label>
                                <select x-model="selectedOptions[v.name]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md border">
                                    <template x-for="opt in v.options" :key="opt.name">
                                        <option :value="opt.name" x-text="opt.name + (parseFloat(opt.price) > 0 ? ' (' + formatMoney(parseFloat(opt.price)) + ')' : '')"></option>
                                    </template>
                                </select>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Add to Cart Form -->
                <?php if ($storeMode !== 'informational'): ?>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-8">
                    <div class="flex items-center border rounded w-full sm:w-auto justify-between sm:justify-start">
                        <button @click="qty > 1 ? qty-- : null" class="px-4 py-3 hover:bg-gray-100">-</button>
                        <input type="number" x-model="qty" class="w-16 text-center border-none focus:ring-0" min="1">
                        <button @click="qty++" class="px-4 py-3 hover:bg-gray-100">+</button>
                    </div>
                    <button 
                        @click="addToCart()"
                        class="w-full sm:w-auto bg-orange-500 text-white px-8 py-3 rounded font-bold hover:bg-orange-600 transition-colors uppercase shadow-lg transform active:scale-95 text-center">
                        <?php echo $storeMode === 'catalog' ? __('Adicionar à lista') : __('Add to cart'); ?>
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-4 text-sm text-gray-500">
                <span><?php echo __('Category'); ?>: <?php echo htmlspecialchars($categoryLabel ?? ''); ?></span>
            </div>
        </div>
    </div>

    <div class="mt-16" x-data="{ activeTab: 'description' }">
        <div class="border-b flex gap-8 mb-6">
            <button 
                @click="activeTab = 'description'"
                :class="{ 'border-b-2 border-orange-600 text-orange-600 font-bold': activeTab === 'description', 'text-gray-500 hover:text-gray-700': activeTab !== 'description' }"
                class="pb-4 px-2 transition-colors">
                <?php echo __('Description'); ?>
            </button>
            <?php if (isset($product['pdf_active']) && $product['pdf_active']): ?>
            <button 
                @click="activeTab = 'laudos'"
                :class="{ 'border-b-2 border-orange-600 text-orange-600 font-bold': activeTab === 'laudos', 'text-gray-500 hover:text-gray-700': activeTab !== 'laudos' }"
                class="pb-4 px-2 transition-colors">
                <?php echo !empty($product['pdf_label']) ? htmlspecialchars($product['pdf_label'] ?? '') : __('Analysis Report (PDF)'); ?>
            </button>
            <?php endif; ?>
        </div>

        <div x-show="activeTab === 'description'" class="prose max-w-none text-gray-600">
            <?php echo $product['long_desc']; ?>
        </div>

        <?php if (isset($product['pdf_active']) && $product['pdf_active']): ?>
        <div x-show="activeTab === 'laudos'" class="text-gray-600">
            <?php if (!empty($product['pdf_url'])): ?>
                <?php $pdfUrlWithZoom = htmlspecialchars($product['pdf_url'] ?? '') . '#zoom=75'; ?>
                <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-gray-50 p-4 rounded-lg border border-gray-200 gap-4 sm:gap-0">
                    <span class="font-medium text-gray-700">
                        <?php echo !empty($product['pdf_label']) ? htmlspecialchars($product['pdf_label'] ?? '') : __('Analysis Report (PDF)'); ?>
                    </span>
                    <a href="<?php echo htmlspecialchars($product['pdf_url'] ?? ''); ?>" target="_blank" class="flex items-center gap-2 bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 transition-colors shadow-sm whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <?php echo __('Download PDF'); ?>
                    </a>
                </div>
                <div class="w-full border border-gray-200 rounded-lg overflow-hidden shadow-sm bg-gray-50" style="height: 800px;">
                    <object data="<?php echo $pdfUrlWithZoom; ?>" type="application/pdf" width="100%" height="100%">
                        <iframe src="<?php echo $pdfUrlWithZoom; ?>" width="100%" height="100%" style="border: none;">
                            <div class="flex flex-col items-center justify-center h-full p-8 text-center">
                                <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-gray-500 mb-2"><?php echo __('Your browser does not support embedded PDFs.'); ?></p>
                                <a href="<?php echo htmlspecialchars($product['pdf_url'] ?? ''); ?>" class="text-orange-600 hover:underline"><?php echo __('Click here to download the PDF'); ?></a>
                            </div>
                        </iframe>
                    </object>
                </div>
            <?php else: ?>
                <p><?php echo __('No reports available for this product.'); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
