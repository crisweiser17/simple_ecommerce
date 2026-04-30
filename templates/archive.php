<?php $searchQuery = trim((string)($_GET['q'] ?? '')); ?>

<!-- Hero Banner (Full Width) -->
<?php
$overlayColor1 = getSetting('banner_overlay_color1', '#111827');
$overlayColor2 = getSetting('banner_overlay_color2', '#1f2937');
$overlayEnabled = getSetting('banner_overlay_enabled', '1');
?>
<div class="relative w-full h-[350px] overflow-hidden shadow-lg bg-gray-900">
    <!-- Fallback Background Color -->
    <div class="absolute inset-0" style="background: linear-gradient(to right, <?php echo $overlayColor1; ?>, <?php echo $overlayColor2; ?>);"></div>

    <!-- Background Image -->
    <div class="absolute inset-0 bg-[url('<?php echo htmlspecialchars(getSetting('banner_image_url', 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80')); ?>')] bg-cover bg-center"></div>
    
    <!-- Overlay Gradient -->
    <?php if ($overlayEnabled): ?>
    <?php 
    $opacityValue = (int)getSetting('banner_overlay_opacity', '30');
    $opacityDecimal = $opacityValue / 100;
    ?>
    <div class="absolute inset-0" style="background: linear-gradient(to right, <?php echo $overlayColor1; ?>, <?php echo $overlayColor2; ?>); opacity: <?php echo $opacityDecimal; ?>"></div>
    <?php endif; ?>
    
    <div class="absolute inset-0 flex items-center">
        <div class="container mx-auto px-6 md:px-12 flex justify-between items-center">
            <div class="max-w-lg z-10">
                <?php 
                $bannerTitle = getSetting('banner_title', 'Lab-Grade Peptides & Peptide Blends');
                if (!empty($bannerTitle)): 
                ?>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white leading-tight mb-4">
                    <?php echo __($bannerTitle); ?>
                </h1>
                <?php endif; ?>

                <?php 
                $bannerSubtitle = getSetting('banner_subtitle', 'Strict internal assessments ensure our peptides meet the highest standards of purity and potency.');
                if (!empty($bannerSubtitle)):
                ?>
                <p class="text-gray-300 mb-8 text-sm md:text-base max-w-md">
                    <?php echo __($bannerSubtitle); ?>
                </p>
                <?php endif; ?>

                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                    <?php 
                    $btn1Text = getSetting('banner_button_text');
                    $btn1Link = getSetting('banner_button_link');
                    if (!empty($btn1Text)):
                    ?>
                    <a href="<?php echo htmlspecialchars($btn1Link); ?>" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 sm:px-8 rounded shadow-lg transition-transform transform hover:scale-105 uppercase text-sm text-center">
                        <?php echo __($btn1Text); ?>
                    </a>
                    <?php endif; ?>

                    <?php 
                    $btn2Text = getSetting('banner_button2_text');
                    $btn2Link = getSetting('banner_button2_link');
                    if (!empty($btn2Text)):
                    ?>
                    <a href="<?php echo htmlspecialchars($btn2Link); ?>" class="bg-transparent border border-gray-500 hover:border-white text-white font-bold py-3 px-6 sm:px-8 rounded transition-colors uppercase text-sm text-center">
                        <?php echo __($btn2Text); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Hero Image (Right) -->
            <?php 
            $rightImage = getSetting('banner_right_image_url', 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80');
            if (!empty($rightImage)): 
            ?>
            <div class="hidden lg:block relative z-10">
                 <img src="<?php echo htmlspecialchars($rightImage); ?>" alt="Banner Image" class="w-80 h-auto object-contain drop-shadow-2xl rounded-lg opacity-90 transform rotate-3 hover:rotate-0 transition-transform duration-500">
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- About / Mission Section (Full Width) -->
<section class="py-8 px-4 md:px-0 bg-white border-y border-gray-200">
    <div class="container mx-auto px-4">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-3">
            <?php echo __('Lab-Grade Peptides for Scientific Research'); ?>
        </h2>
        <div class="text-gray-600 text-sm leading-relaxed space-y-3">
            <p class="text-justify">
                <?php echo __('R2 Research Labs™ delivers high-quality, laboratory-certified peptides, produced under strict standards for purity, stability, and consistency. Our products are manufactured in GMP-certified laboratories, ensuring reliability and reproducibility for scientific applications.'); ?>
            </p>
            <p class="text-justify">
                <?php echo __('We provide research-grade peptides suitable for a wide range of fields, including biotechnology, cell biology, and oncology research. Each product undergoes rigorous testing to guarantee accuracy, safety, and performance.'); ?>
            </p>
            <p class="text-justify">
                <?php echo __('Our mission is to support scientific advancement by offering dependable materials for experimentation, innovation, and development. With a strong commitment to quality control and precision, R2 Research Labs™ helps researchers achieve consistent and reliable results.'); ?>
            </p>
        </div>
    </div>
</section>

<!-- Main Content: Categories + Product Grid -->
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row gap-6">
        
        <!-- Sidebar (Categories) -->
        <aside class="w-full md:w-64 flex-shrink-0 space-y-8">
            <!-- Categories -->
            <div class="bg-[#1a1d21] rounded-lg overflow-hidden text-gray-300">
                <div class="p-4 bg-[#23272b] border-b border-gray-700">
                    <h2 class="font-bold text-white uppercase text-sm tracking-wider"><?php echo __('Categories'); ?></h2>
                </div>
                <ul class="text-sm">
                    <?php 
                    if (function_exists('getAllCategories')) {
                        $sidebarCategories = getAllCategories();
                        foreach ($sidebarCategories as $cat) {
                            $isActive = isset($_GET['category']) && $_GET['category'] === $cat['slug'];
                            $activeClass = $isActive ? 'bg-[#2c3036] text-white border-orange-500' : 'border-transparent';
                    ?>
                    <li>
                        <a href="/?category=<?php echo htmlspecialchars($cat['slug']); ?><?php echo $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : ''; ?>" class="block px-4 py-3 hover:bg-[#2c3036] hover:text-white transition-colors border-l-2 <?php echo $activeClass; ?> hover:border-orange-500 flex justify-between items-center group">
                            <span><?php echo htmlspecialchars(__($cat['name'])); ?></span>
                            <svg class="w-3 h-3 text-gray-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </li>
                    <?php 
                        }
                    }
                    ?>
                </ul>
            </div>
        </aside>

        <!-- Main Column (Title + Products) -->
        <div class="flex-1">
            
            <!-- Product Grid -->
            <?php if (empty($products)): ?>
                <div class="text-center py-12 text-gray-500">
                    <p><?php echo $searchQuery !== '' ? __('No products found for your search.') : __('No products found in this category.'); ?></p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $product): ?>
                    <?php $cardImageUrl = getProductPrimaryImageUrl($product); ?>
                    <?php $productUrl = getProductUrl($product); ?>
                    <?php if ($cardImageUrl === '') $cardImageUrl = 'https://placehold.co/400x400?text=No+Image'; ?>
                    <?php 
                        $arWidth = getSetting('product_card_aspect_width', '1');
                        $arHeight = getSetting('product_card_aspect_height', '1');
                    ?>
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-xl border border-gray-200 p-4 flex flex-col items-center text-center group transition-all duration-300">
                        
                        <!-- Labels -->
                        <div class="w-full flex justify-between items-start mb-2">
                            <span class="bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase"><?php echo __('New'); ?></span>
                        </div>

                        <!-- Image -->
                        <a href="<?php echo htmlspecialchars($productUrl); ?>" 
                           class="block mb-4 w-full flex items-center justify-center bg-gray-50 rounded-md p-2 group-hover:bg-white transition-colors"
                           style="aspect-ratio: <?php echo htmlspecialchars($arWidth); ?> / <?php echo htmlspecialchars($arHeight); ?>;">
                            <img src="<?php echo htmlspecialchars($cardImageUrl); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="max-h-full max-w-full object-contain mix-blend-multiply group-hover:scale-110 transition-transform duration-300">
                        </a>

                        <!-- Content -->
                        <div class="w-full text-left">
                            <h3 class="font-bold text-gray-900 text-lg leading-tight mb-1 font-product-title">
                                <a href="<?php echo htmlspecialchars($productUrl); ?>" class="hover:text-orange-600 transition-colors"><?php echo htmlspecialchars($product['name']); ?></a>
                            </h3>
                            <p class="text-xs text-gray-500 mb-3"><?php echo __('Best-in-class Bioavail.'); ?></p>
                            
                            <!-- Price -->
                            <?php if ($storeMode === 'ecommerce'): ?>
                            <div class="font-bold text-xl text-gray-900 mb-4 border-t border-gray-100 pt-2 font-prices">
                                <?php echo formatMoney($product['price']); ?>
                            </div>
                            <?php else: ?>
                            <div class="mb-4 border-t border-gray-100 pt-2"></div>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="flex flex-col gap-2" x-data="{ qty: 1 }">
                                <!-- Add to Cart -->
                                <?php if ($storeMode !== 'informational'): ?>
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click="$store.cart.add({ ...<?php echo htmlspecialchars(json_encode($product)); ?>, quantity: qty })"
                                        class="flex-1 bg-orange-500 text-white py-2.5 rounded text-sm font-bold hover:bg-orange-600 transition-colors shadow-md hover:shadow-lg flex justify-center items-center gap-2 font-buttons">
                                        <span><?php echo $storeMode === 'catalog' ? __('Adicionar à lista') : __('Add to Cart'); ?></span>
                                        <?php if ($storeMode === 'catalog'): ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        <?php else: ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <?php endif; ?>
                                    </button>
                                    <input 
                                        type="number"
                                        min="1"
                                        step="1"
                                        x-model.number="qty"
                                        class="w-16 border border-gray-300 rounded py-2.5 px-2 text-sm text-center font-semibold focus:outline-none focus:ring-2 focus:ring-orange-300"
                                    >
                                </div>
                                <?php endif; ?>

                                <!-- Learn More -->
                                <a 
                                    href="<?php echo htmlspecialchars($productUrl); ?>"
                                    class="w-full bg-white text-gray-700 border border-gray-300 py-2.5 rounded text-sm font-bold hover:bg-gray-50 transition-colors flex justify-center items-center gap-2">
                                    <span><?php echo __('Learn More'); ?></span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="mt-12 flex justify-center">
                <nav class="flex items-center gap-2">
                    <?php 
                    $baseUrl = '/?';
                    if (isset($categorySlug) && $categorySlug) {
                        $baseUrl .= 'category=' . urlencode($categorySlug) . '&';
                    }
                    if ($searchQuery !== '') {
                        $baseUrl .= 'q=' . urlencode($searchQuery) . '&';
                    }
                    ?>

                    <!-- Previous Page -->
                    <?php if ($currentPage > 1): ?>
                        <a href="<?php echo $baseUrl . 'page=' . ($currentPage - 1); ?>" class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-500 hover:bg-gray-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </a>
                    <?php else: ?>
                        <span class="w-8 h-8 flex items-center justify-center rounded border border-gray-200 text-gray-300 cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </span>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="w-8 h-8 flex items-center justify-center rounded bg-orange-500 text-white font-bold"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo $baseUrl . 'page=' . $i; ?>" class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?php echo $baseUrl . 'page=' . ($currentPage + 1); ?>" class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-500 hover:bg-gray-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    <?php else: ?>
                        <span class="w-8 h-8 flex items-center justify-center rounded border border-gray-200 text-gray-300 cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
