<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <?php
    $storeName = getSetting('store_name', 'R2 Research Labs');
    $brandMode = getSetting('brand_mode', 'text') === 'image' ? 'image' : 'text';
    $brandLogoUrl = getSetting('brand_logo_url', '');
    $brandLogoWidth = max(20, min(1200, (int)getSetting('brand_logo_width', '160')));
    $brandLogoHeight = max(20, min(600, (int)getSetting('brand_logo_height', '48')));
    $themeHeaderBg = getSetting('theme_header_bg', '#0f1115');
    $themePageBg = getSetting('theme_page_bg', '#f3f4f6');
    $themeTextColor = getSetting('theme_text_color', '#1f2937');
    $isMultilangEnabled = getSetting('i18n_multilang_enabled', '1') === '1';
    ?>
    <title><?php echo htmlspecialchars($storeName); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        [x-cloak] { display: none !important; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="font-sans" x-data="{ cartOpen: false, mobileSearchOpen: false }" style="background-color: <?php echo htmlspecialchars($themePageBg); ?>; color: <?php echo htmlspecialchars($themeTextColor); ?>;">

    <!-- Top Bar -->
    <div class="text-white border-b border-gray-800" style="background-color: <?php echo htmlspecialchars($themeHeaderBg); ?>;">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2">
                    <?php if ($brandMode === 'image' && !empty($brandLogoUrl)): ?>
                        <img src="<?php echo htmlspecialchars($brandLogoUrl); ?>" alt="<?php echo htmlspecialchars($storeName); ?>" style="width: <?php echo $brandLogoWidth; ?>px; height: <?php echo $brandLogoHeight; ?>px;" class="object-contain">
                    <?php else: ?>
                        <span class="text-xl md:text-2xl font-bold tracking-tight text-white"><?php echo htmlspecialchars($storeName); ?></span>
                    <?php endif; ?>
                </a>

                <!-- Search (Desktop) -->
                <div class="hidden md:flex flex-1 max-w-xl mx-8">
                    <form action="/" method="GET" class="relative w-full" id="top-search-box">
                        <?php if (!empty($_GET['category'])): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars((string)$_GET['category']); ?>">
                        <?php endif; ?>
                        <input id="top-search-input" name="q" type="text" value="<?php echo htmlspecialchars((string)($_GET['q'] ?? '')); ?>" placeholder="<?php echo __('Search item...'); ?>" autocomplete="off" class="w-full bg-[#1a1d21] border border-gray-700 text-gray-300 rounded-sm px-4 py-2 pr-10 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 outline-none placeholder-gray-500">
                        <button type="submit" class="absolute right-3 top-2.5 text-gray-500 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 4 4 0 0114 0z"></path></svg>
                        </button>
                        <div id="top-search-suggestions" class="hidden absolute z-50 mt-1 w-full bg-[#1a1d21] border border-gray-700 rounded-sm shadow-xl overflow-hidden"></div>
                    </form>
                </div>

                <!-- Language Switcher (Desktop) -->
                <?php if ($isMultilangEnabled): ?>
                    <div class="hidden md:flex items-center gap-4 text-sm text-gray-400 mr-6">
                        <a href="?lang=en" class="<?php echo $_SESSION['lang'] === 'en' ? 'text-white font-bold' : 'hover:text-white'; ?>">EN</a>
                        <span class="text-gray-600">|</span>
                        <a href="?lang=pt" class="<?php echo $_SESSION['lang'] === 'pt' ? 'text-white font-bold' : 'hover:text-white'; ?>">PT</a>
                    </div>
                <?php endif; ?>

                <!-- User & Cart -->
                <div class="flex items-center gap-4 md:gap-6 text-sm">
                    <!-- Mobile Search Toggle -->
                    <button @click="mobileSearchOpen = !mobileSearchOpen" class="md:hidden text-gray-300 hover:text-white p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 4 4 0 0114 0z"></path></svg>
                    </button>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-1 md:gap-2 hover:text-orange-500 transition-colors p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span class="hidden sm:inline"><?php echo __('Account'); ?></span>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded shadow-lg py-1 z-50" x-cloak>
                                <a href="/account" class="block px-4 py-2 hover:bg-gray-100"><?php echo __('My Profile'); ?></a>
                                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                    <a href="/admin" class="block px-4 py-2 hover:bg-gray-100"><?php echo __('Admin Dashboard'); ?></a>
                                <?php endif; ?>
                                <a href="/logout" class="block px-4 py-2 text-red-600 hover:bg-gray-100"><?php echo __('Sign out'); ?></a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/login" class="flex items-center gap-1 md:gap-2 hover:text-orange-500 transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="hidden sm:inline"><?php echo __('Sign in'); ?></span>
                        </a>
                    <?php endif; ?>

                    <a href="/cart" class="relative hover:text-orange-500 transition-colors flex items-center gap-1 md:gap-2 p-1">
                        <div class="relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="absolute -top-2 -right-2 bg-orange-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center" x-text="$store.cart.count" x-show="$store.cart.count > 0">0</span>
                        </div>
                        <span class="hidden sm:inline"><?php echo __('Cart'); ?></span>
                    </a>
                </div>
            </div>

            <!-- Mobile Search & Language Dropdown -->
            <div x-show="mobileSearchOpen" class="md:hidden pt-4 pb-2" x-cloak x-transition>
                <form action="/" method="GET" class="relative w-full mb-4">
                    <?php if (!empty($_GET['category'])): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars((string)$_GET['category']); ?>">
                    <?php endif; ?>
                    <input name="q" type="text" value="<?php echo htmlspecialchars((string)($_GET['q'] ?? '')); ?>" placeholder="<?php echo __('Search item...'); ?>" class="w-full bg-[#1a1d21] border border-gray-700 text-gray-300 rounded-md px-4 py-3 pr-10 focus:ring-1 focus:ring-orange-500 outline-none">
                    <button type="submit" class="absolute right-3 top-3 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 4 4 0 0114 0z"></path></svg>
                    </button>
                </form>
                
                <?php if ($isMultilangEnabled): ?>
                    <div class="flex items-center justify-center gap-6 text-sm text-gray-400 border-t border-gray-800 pt-3">
                        <a href="?lang=en" class="px-4 py-1 rounded-full <?php echo $_SESSION['lang'] === 'en' ? 'bg-gray-800 text-white font-medium' : 'hover:text-white'; ?>">EN</a>
                        <a href="?lang=pt" class="px-4 py-1 rounded-full <?php echo $_SESSION['lang'] === 'pt' ? 'bg-gray-800 text-white font-medium' : 'hover:text-white'; ?>">PT</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="bg-white border-b shadow-sm sticky top-0 z-40">
        <div class="container mx-auto px-4">
            <nav class="flex items-center space-x-6 md:space-x-8 py-3 text-sm font-semibold text-gray-600 overflow-x-auto hide-scrollbar">
                <?php
                // Get all dynamic pages
                $navPages = getAllPages();
                
                // Active class logic
                $activeClass = 'text-orange-600 border-b-2 border-orange-600 pb-3 -mb-3.5';
                $inactiveClass = 'hover:text-orange-600 transition-colors whitespace-nowrap';
                
                // Home Link (All Products)
                // $path is available from index.php
                $isHome = (isset($path) && ($path === '/' || $path === '/home'));
                $isContact = (isset($path) && $path === '/contact');
                ?>
                <a href="/" class="<?php echo $isHome ? $activeClass : $inactiveClass; ?>"><?php echo __('All Products'); ?></a>
                
                <?php foreach ($navPages as $page): 
                    $pageSlug = '/' . $page['slug'];
                    $isActive = (isset($path) && $path === $pageSlug);
                ?>
                    <a href="<?php echo htmlspecialchars($pageSlug); ?>" class="<?php echo $isActive ? $activeClass : $inactiveClass; ?>">
                        <?php echo htmlspecialchars(__($page['title'])); ?>
                    </a>
                <?php endforeach; ?>
                <a href="/contact" class="<?php echo $isContact ? $activeClass : $inactiveClass; ?>"><?php echo __('Contact Us'); ?></a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <main class="min-h-screen pb-12" style="background-color: <?php echo htmlspecialchars($themePageBg); ?>;">
        <?php include $template; ?>
    </main>

    <div x-cloak x-show="$store.cartFeedbackModal.open" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center p-4" @keydown.escape.window="$store.cartFeedbackModal.close()">
        <div class="absolute inset-0 bg-black/50" @click="$store.cartFeedbackModal.close()"></div>
        <div class="relative w-full max-w-md rounded-lg bg-white shadow-xl p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Produto adicionado ao carrinho</h3>
            <p class="text-gray-700 mb-6" x-text="`O produto ${$store.cartFeedbackModal.productName} foi adicionado ao carrinho.`"></p>
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors" @click="$store.cartFeedbackModal.close()">Continuar comprando</button>
                <button type="button" class="w-full sm:w-auto px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition-colors" @click="$store.cartFeedbackModal.goToCheckout()">Finalizar compra</button>
            </div>
        </div>
    </div>

    <!-- Powered By -->
    <div class="w-full bg-black h-[35px] flex items-center justify-center shrink-0">
        <span class="text-white text-xs">Powered by LojaSimples</span>
    </div>

    <!-- Alpine Cart Store -->
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

        document.addEventListener('DOMContentLoaded', () => {
            const searchBox = document.getElementById('top-search-box');
            const searchInput = document.getElementById('top-search-input');
            const suggestionsBox = document.getElementById('top-search-suggestions');

            if (!searchBox || !searchInput || !suggestionsBox) {
                return;
            }

            let debounceTimer = null;
            let activeRequest = null;

            const hideSuggestions = () => {
                suggestionsBox.classList.add('hidden');
                suggestionsBox.innerHTML = '';
            };

            const renderSuggestions = (items) => {
                if (!Array.isArray(items) || items.length === 0) {
                    hideSuggestions();
                    return;
                }

                suggestionsBox.innerHTML = items.map((item) => {
                    const safeName = String(item.name || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                    const safeSlug = encodeURIComponent(String(item.slug || '').trim());
                    const url = safeSlug ? `/product/${safeSlug}` : `/product?id=${encodeURIComponent(String(item.id || ''))}`;
                    return `<a href="${url}" class="block px-4 py-2 text-sm text-gray-200 hover:bg-[#2c3036]">${safeName}</a>`;
                }).join('');

                suggestionsBox.classList.remove('hidden');
            };

            searchInput.addEventListener('input', () => {
                const term = searchInput.value.trim();

                if (term.length < 3) {
                    hideSuggestions();
                    if (activeRequest) {
                        activeRequest.abort();
                    }
                    return;
                }

                if (debounceTimer) {
                    clearTimeout(debounceTimer);
                }

                debounceTimer = setTimeout(async () => {
                    if (activeRequest) {
                        activeRequest.abort();
                    }

                    activeRequest = new AbortController();
                    const requestTerm = term;

                    try {
                        const response = await fetch(`/api/products/autocomplete?q=${encodeURIComponent(requestTerm)}`, {
                            signal: activeRequest.signal
                        });

                        if (!response.ok) {
                            hideSuggestions();
                            return;
                        }

                        const data = await response.json();

                        if (searchInput.value.trim() !== requestTerm) {
                            return;
                        }

                        renderSuggestions(data.items || []);
                    } catch (error) {
                        hideSuggestions();
                    }
                }, 220);
            });

            document.addEventListener('click', (event) => {
                if (!searchBox.contains(event.target)) {
                    hideSuggestions();
                }
            });
        });

        document.addEventListener('alpine:init', () => {
            Alpine.store('cartFeedbackModal', {
                open: false,
                productName: '',

                show(name) {
                    this.productName = name || 'produto';
                    this.open = true;
                },

                close() {
                    this.open = false;
                },

                goToCheckout() {
                    this.close();
                    window.location.href = '/cart';
                }
            });

            Alpine.store('cart', {
                items: JSON.parse(localStorage.getItem('cart')) || [],
                
                get count() {
                    return this.items.reduce((acc, item) => acc + item.quantity, 0);
                },

                get total() {
                    return this.items.reduce((acc, item) => acc + (item.price * item.quantity), 0);
                },

                add(product) {
                    const quantityToAdd = Number.isFinite(parseInt(product.quantity)) && parseInt(product.quantity) > 0 ? parseInt(product.quantity) : 1;
                    const existing = this.items.find(i => i.id === product.id);
                    if (existing) {
                        existing.quantity += quantityToAdd;
                    } else {
                        this.items.push({ ...product, quantity: quantityToAdd });
                    }
                    this.save();
                    Alpine.store('cartFeedbackModal').show(product.name || 'produto');
                },

                remove(id) {
                    this.items = this.items.filter(i => i.id !== id);
                    this.save();
                },

                updateQuantity(id, qty) {
                    const item = this.items.find(i => i.id === id);
                    if (item) {
                        item.quantity = parseInt(qty);
                        if (item.quantity <= 0) this.remove(id);
                        this.save();
                    }
                },

                clear() {
                    this.items = [];
                    this.save();
                },

                save() {
                    localStorage.setItem('cart', JSON.stringify(this.items));
                }
            });
        });
    </script>
</body>
</html>
