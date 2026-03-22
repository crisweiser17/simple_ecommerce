<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo htmlspecialchars($product['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Hide scrollbar for a cleaner checkout experience */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }
        ::-webkit-scrollbar-thumb {
            background: #888; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555; 
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <?php
    $primaryImage = getProductPrimaryImageUrl($product);
    if ($primaryImage === '') {
        $primaryImage = 'https://placehold.co/700x700?text=No+Image';
    }
    ?>
    <div class="max-w-3xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="md:flex">
            <!-- Product Summary (Leaner) -->
            <div class="md:w-5/12 p-8 bg-gray-900 text-white flex flex-col justify-between">
                <div>
                    <h2 class="text-sm uppercase tracking-wider text-gray-400 mb-2">Você está comprando</h2>
                    <h1 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="bg-white/10 rounded-lg p-4 mb-6 flex items-center justify-center">
                        <img src="<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="max-h-48 object-contain rounded">
                    </div>
                </div>
                
                <div class="mt-8 border-t border-gray-700 pt-6">
                    <div class="flex justify-between items-center text-xl">
                        <span class="text-gray-300">Total:</span>
                        <span class="font-bold text-green-400"><?php echo formatMoney($product['price']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <div class="md:w-7/12 p-8" x-data="expressCheckout()">
                
                <!-- Step 1: Customer Info -->
                <div x-show="step === 1" x-transition.opacity>
                    <h2 class="text-2xl font-bold mb-2 text-gray-800">Finalizar Compra</h2>
                    <p class="text-gray-500 mb-6 text-sm">Preencha seus dados para receber o produto.</p>
                    
                    <form @submit.prevent="submitCheckout" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                            <input type="text" x-model="customer.name" required placeholder="João da Silva" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                            <input type="email" x-model="customer.email" required placeholder="joao@exemplo.com" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            <p class="text-xs text-gray-400 mt-1">É por aqui que você receberá o acesso.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                            <input type="text" x-model="customer.whatsapp" x-mask="(99) 99999-9999" required placeholder="(11) 99999-9999" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                        
                        <button type="submit" :disabled="loading" class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold text-lg hover:bg-blue-700 transition-all transform hover:-translate-y-1 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none mt-8 flex justify-center items-center gap-2">
                            <span x-show="!loading">Pagar com PIX</span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processando...
                            </span>
                        </button>
                        
                        <div class="text-center mt-4 text-xs text-gray-400 flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Pagamento 100% seguro
                        </div>
                    </form>
                </div>

                <!-- Step 2: Payment PIX -->
                <div x-show="step === 2" class="text-center" style="display: none;" x-transition.opacity>
                    <div class="bg-green-50 text-green-800 p-3 rounded-lg text-sm mb-6 font-medium">
                        Pedido gerado! Efetue o pagamento para liberar.
                    </div>
                    
                    <h2 class="text-xl font-bold mb-2 text-gray-800">Pagamento via PIX</h2>
                    <p class="text-gray-600 mb-6 text-sm">Abra o app do seu banco e escaneie o QR Code.</p>
                    
                    <div x-show="!expired" class="mb-4 bg-red-50 border border-red-200 rounded p-3 text-center">
                        <p class="text-sm text-red-800 font-semibold mb-1">Tempo restante para pagamento:</p>
                        <div class="text-3xl font-bold text-red-600 tabular-nums" x-text="timeDisplay">10:00</div>
                    </div>

                    <div x-show="expired" class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg font-bold border border-red-200 text-sm">
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
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Ou use o PIX Copia e Cola</label>
                            <div class="flex shadow-sm rounded-lg overflow-hidden">
                                <input type="text" readonly :value="payment.copy_paste || 'Gerando código...'" class="w-full border-y border-l border-gray-300 p-3 bg-gray-50 text-sm outline-none text-gray-600 font-mono">
                                <button @click="copyPix()" class="bg-gray-800 text-white px-5 hover:bg-gray-900 transition-colors font-medium text-sm flex items-center gap-2" :disabled="!payment.copy_paste">
                                    <span x-show="!copied">Copiar</span>
                                    <span x-show="copied" class="text-green-400">Copiado!</span>
                                </button>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-100 text-blue-800 p-4 rounded-lg flex items-center justify-center gap-3 text-sm">
                            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Aguardando confirmação do pagamento...</span>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Success -->
                <div x-show="step === 3" class="text-center py-8" style="display: none;" x-transition.opacity>
                    <div class="w-24 h-24 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h2 class="text-3xl font-bold mb-3 text-gray-800">Tudo certo!</h2>
                    <p class="text-gray-600 mb-8">Seu pagamento foi confirmado e o pedido está liberado.</p>

                    <?php if (!empty($product['pdf_url']) || !empty($product['file_url'])): ?>
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-100 rounded-xl p-8 mb-8 shadow-sm">
                            <h3 class="font-bold text-gray-800 text-lg mb-2">Acesse seu produto</h3>
                            <p class="text-sm text-gray-600 mb-6">Clique no botão abaixo para fazer o download do seu arquivo.</p>
                            <a href="<?php echo htmlspecialchars($product['file_url'] ?? $product['pdf_url']); ?>" target="_blank" class="inline-block bg-green-600 text-white px-8 py-4 rounded-lg font-bold hover:bg-green-700 transition-all transform hover:-translate-y-1 shadow-md">
                                Fazer Download Agora
                            </a>
                        </div>
                    <?php endif; ?>

                    <p class="text-sm text-gray-500 bg-gray-50 p-4 rounded-lg">
                        Enviamos também um e-mail com os detalhes para <br>
                        <strong x-text="customer.email" class="text-gray-800 mt-1 inline-block"></strong>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <script>
        function expressCheckout() {
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

                submitCheckout() {
                    this.loading = true;
                    // Limpar os dados antigos do QR code para forçar a re-renderização
                    this.payment.qr_code = '';
                    this.payment.copy_paste = '';
                    
                    fetch('/api/express-checkout', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            product_id: this.productId,
                            name: this.customer.name,
                            email: this.customer.email,
                            whatsapp: this.customer.whatsapp
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
