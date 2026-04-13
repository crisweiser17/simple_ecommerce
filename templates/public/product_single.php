<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - <?php echo getSetting('store_name', 'R2 Research Labs'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php
    $primaryImage = getProductPrimaryImageUrl($product);
    if ($primaryImage === '') {
        $primaryImage = 'https://placehold.co/700x700?text=No+Image';
    }
    ?>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="md:flex">
                <!-- Product Info -->
                <div class="md:w-1/2 p-4 md:p-8 bg-gray-50" x-data="productInfo()">
                    <img src="<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-64 object-contain rounded-lg mb-6 bg-white p-4 border">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="text-2xl font-bold text-orange-600 mb-4" x-text="formatMoney(currentPrice)"><?php echo formatMoney($product['price']); ?></div>
                    <div class="prose text-gray-600 mb-6">
                        <?php echo $product['short_desc']; ?>
                    </div>
                    
                    <template x-if="variations.length > 0">
                        <div class="mb-6 space-y-4">
                            <template x-for="v in variations" :key="v.name">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" x-text="v.name"></label>
                                    <select x-model="$store.checkoutVariations[v.name]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md border bg-white">
                                        <template x-for="opt in v.options" :key="opt.name">
                                            <option :value="opt.name" x-text="opt.name + (parseFloat(opt.price) > 0 ? ' (' + formatMoney(parseFloat(opt.price)) + ')' : '')"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Checkout Form -->
                <div class="md:w-1/2 p-4 md:p-8" x-data="singleCheckout()">
                    
                    <!-- Step 1: Customer Info -->
                    <div x-show="step === 1">
                        <h2 class="text-2xl font-bold mb-6 text-gray-800">Checkout Expresso</h2>
                        <form @submit.prevent="submitCheckout" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                                <input type="text" x-model="customer.name" required class="w-full border border-gray-300 rounded-md p-3 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                                <input type="email" x-model="customer.email" required class="w-full border border-gray-300 rounded-md p-3 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                                <input type="text" x-model="customer.whatsapp" x-mask="(99) 99999-9999" required class="w-full border border-gray-300 rounded-md p-3 focus:ring-orange-500 focus:border-orange-500" placeholder="(11) 99999-9999">
                            </div>
                            
                            <button type="submit" :disabled="loading" class="w-full bg-orange-600 text-white py-4 rounded-md font-bold text-lg hover:bg-orange-700 transition-colors disabled:opacity-50 mt-6">
                                <span x-show="!loading">Gerar PIX - <span x-text="finalPriceFormatted"></span></span>
                                <span x-show="loading">Processando...</span>
                            </button>

                            <?php if (!empty($product['type']) && $product['type'] === 'digital'): ?>
                                <div class="mt-4 p-4 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    <p class="text-sm text-blue-800 leading-relaxed">
                                        <strong class="font-semibold block mb-0.5">Entrega Imediata</strong>
                                        Logo após a confirmação do pagamento, você receberá o link de acesso diretamente no seu e-mail e o download será liberado automaticamente nesta mesma tela.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Step 2: Payment PIX -->
                    <div x-show="step === 2" class="text-center" style="display: none;">
                        <h2 class="text-2xl font-bold mb-2 text-gray-800">Pagamento via PIX</h2>
                        <p class="text-gray-600 mb-6">Escaneie o QR Code ou copie o código abaixo para pagar.</p>
                        
                        <div x-show="!expired" class="mb-4 bg-red-50 border border-red-200 rounded p-3 text-center">
                            <p class="text-sm text-red-800 font-semibold mb-1">Tempo restante para pagamento:</p>
                            <div class="text-3xl font-bold text-red-600 tabular-nums" x-text="timeDisplay">10:00</div>
                        </div>

                        <div x-show="expired" class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg font-bold border border-red-200">
                            O tempo para pagamento expirou. Por favor, atualize a página e faça um novo pedido.
                        </div>

                        <div x-show="!expired">
                            <div class="flex justify-center mb-6">
                                <div class="p-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm inline-block min-w-[200px] min-h-[200px] flex items-center justify-center">
                                    <template x-if="payment.qr_code && (payment.qr_code.startsWith('data:image') || payment.qr_code.startsWith('http'))">
                                        <img :src="payment.qr_code" alt="QR Code PIX" class="w-48 h-48">
                                    </template>
                                    <template x-if="payment.qr_code && !payment.qr_code.startsWith('data:image') && !payment.qr_code.startsWith('http')">
                                        <img :src="'data:image/png;base64,' + payment.qr_code" alt="QR Code PIX" class="w-48 h-48">
                                    </template>
                                    <template x-if="!payment.qr_code">
                                        <span class="text-gray-400 text-sm">Gerando QR Code...</span>
                                    </template>
                                </div>
                            </div>

                            <div class="mb-6 text-left">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Código PIX (Copia e Cola)</label>
                                <div class="flex gap-2">
                                    <input type="text" readonly class="w-full border border-gray-300 rounded-md p-2 text-sm bg-gray-50 text-gray-500" :value="payment.copy_paste || 'Gerando código...'">
                                    <button @click="copyPix()" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-900 transition-colors text-sm whitespace-nowrap" :class="{'bg-green-600 hover:bg-green-700': copied}" :disabled="!payment.copy_paste">
                                        <span x-show="!copied">Copiar</span>
                                        <span x-show="copied">Copiado!</span>
                                    </button>
                                </div>
                                <p x-show="copied" class="text-green-600 text-sm mt-2">Código copiado com sucesso!</p>
                            </div>

                            <div class="bg-blue-50 text-blue-800 p-4 rounded-md flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Aguardando pagamento...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Success -->
                    <div x-show="step === 3" class="text-center py-8" style="display: none;">
                        <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h2 class="text-3xl font-bold mb-4 text-gray-800">Pagamento Confirmado!</h2>
                        <p class="text-gray-600 mb-8">Seu pedido foi processado com sucesso.</p>

                        <?php if (!empty($product['pdf_url']) || !empty($product['file_url'])): ?>
                            <div class="bg-gray-50 border rounded-lg p-6 mb-6">
                                <h3 class="font-bold text-lg mb-4">Seu Produto Digital</h3>
                                <a href="<?php echo htmlspecialchars($product['file_url'] ?? $product['pdf_url']); ?>" target="_blank" class="inline-block bg-green-600 text-white px-6 py-3 rounded-md font-bold hover:bg-green-700 transition-colors">
                                    Baixar Arquivo
                                </a>
                            </div>
                        <?php endif; ?>

                        <p class="text-sm text-gray-500">Um e-mail de confirmação foi enviado para <span x-text="customer.email" class="font-semibold"></span>.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('checkoutVariations', {});
        });

        function productInfo() {
            return {
                basePrice: <?php echo (float)($product['price'] ?? 0); ?>,
                variations: <?php echo empty($product['variations_json']) ? '[]' : $product['variations_json']; ?>,
                get currentPrice() {
                    let total = this.basePrice;
                    for (const v of this.variations) {
                        const selected = this.$store.checkoutVariations[v.name];
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
                        const selected = this.$store.checkoutVariations[v.name];
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
                init() {
                    for (const v of this.variations) {
                        if (v.options && v.options.length > 0) {
                            this.$store.checkoutVariations[v.name] = v.options[0].name;
                        }
                    }
                }
            }
        }

        function singleCheckout() {
            return {
                step: 1,
                loading: false,
                copied: false,
                expired: false,
                timeDisplay: '10:00',
                timerInterval: null,
                productId: <?php echo json_encode($product['id']); ?>,
                customer: {
                    name: '',
                    email: '',
                    whatsapp: ''
                },
                payment: {
                    order_id: null,
                    qr_code: '',
                    copy_paste: ''
                },
                pollInterval: null,
                get finalPriceFormatted() {
                    let basePrice = <?php echo (float)($product['price'] ?? 0); ?>;
                    let total = basePrice;
                    const variations = <?php echo empty($product['variations_json']) ? '[]' : $product['variations_json']; ?>;
                    for (const v of variations) {
                        const selected = this.$store.checkoutVariations[v.name];
                        if (selected) {
                            const opt = v.options.find(o => o.name === selected);
                            if (opt && opt.price && parseFloat(opt.price) > 0) {
                                total = parseFloat(opt.price);
                            }
                        }
                    }
                    return new Intl.NumberFormat('<?php echo htmlspecialchars($_SESSION['lang'] ?? 'pt'); ?>-BR', { style: 'currency', currency: '<?php echo htmlspecialchars(getSetting('store_currency', 'BRL')); ?>' }).format(total);
                },

                submitCheckout() {
                    if (!this.customer.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.customer.email)) {
                        alert('<?php echo __('Invalid email format'); ?>');
                        return;
                    }
                    
                    const variations = <?php echo empty($product['variations_json']) ? '[]' : $product['variations_json']; ?>;
                    for (const v of variations) {
                        if (!this.$store.checkoutVariations[v.name]) {
                            alert('<?php echo __('Please select all options before checkout'); ?>: ' + v.name);
                            return;
                        }
                    }
                    
                    this.loading = true;
                    this.payment.qr_code = '';
                    this.payment.copy_paste = '';
                    
                    fetch('/api/express-checkout', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            product_id: this.productId,
                            name: this.customer.name,
                            email: this.customer.email,
                            whatsapp: this.customer.whatsapp,
                            variations: this.$store.checkoutVariations
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.loading = false;
                        if (data.success) {
                            this.payment.order_id = data.order_id;
                            this.payment.qr_code = data.pix_qr_code;
                            this.payment.copy_paste = data.pix_copy_paste;
                            this.step = 2;
                            this.startTimer();
                            this.startPolling();
                        } else {
                            alert('Erro ao gerar PIX: ' + (data.message || 'Tente novamente.'));
                        }
                    })
                    .catch(err => {
                        this.loading = false;
                        alert('Erro de conexão. Tente novamente.');
                        console.error(err);
                    });
                },

                copyPix() {
                    navigator.clipboard.writeText(this.payment.copy_paste).then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 3000);
                    });
                },

                startTimer() {
                    const expiresAt = new Date().getTime() + (10 * 60 * 1000); // 10 minutos
                    
                    const updateTimer = () => {
                        const now = new Date().getTime();
                        const distance = expiresAt - now;

                        if (distance <= 0) {
                            clearInterval(this.timerInterval);
                            clearInterval(this.pollInterval);
                            this.timeDisplay = '00:00';
                            this.expired = true;
                            return;
                        }

                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                        this.timeDisplay = minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
                    };

                    updateTimer();
                    this.timerInterval = setInterval(updateTimer, 1000);
                },

                startPolling() {
                    this.pollInterval = setInterval(() => {
                        fetch('/api/orders/payment-status?id=' + this.payment.order_id)
                            .then(res => res.json())
                            .then(data => {
                                if (data.success && data.payment_status === 'paid') {
                                    clearInterval(this.pollInterval);
                                    clearInterval(this.timerInterval);
                                    this.step = 3;
                                }
                            })
                            .catch(err => console.error('Poll error:', err));
                    }, 3000);
                }
            }
        }
    </script>
</body>
</html>