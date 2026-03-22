<div class="container mx-auto px-4 py-8" x-data="{ 
    step: 1, 
    customer: { name: '', email: '', whatsapp: '', address: '' },
    loginEmail: '',
    loginToken: '',
    isLoggedIn: <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>,
    tokenSent: false,
    modalOpen: false,
    modalMessage: '',
    resendTimer: 0,
    startTimer() {
        this.resendTimer = 30;
        let interval = setInterval(() => {
            if(this.resendTimer > 0) this.resendTimer--;
            else clearInterval(interval);
        }, 1000);
    },
    requestToken() {
        if(this.resendTimer > 0) return;
        fetch('/api/login-request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: this.loginEmail })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) { 
                this.tokenSent = true; 
                this.modalMessage = data.message;
                this.modalOpen = true;
                this.startTimer();
            } else {
                this.modalMessage = data.message || 'Error sending token';
                this.modalOpen = true;
            }
        });
    },
    verifyToken() {
        fetch('/api/login-verify.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: this.loginEmail, token: this.loginToken })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) { 
                this.isLoggedIn = true; 
                this.step = 2; 
                this.customer.email = this.loginEmail;
                window.location.reload(); // Refresh to update session state PHP side
            } else {
                this.modalMessage = '<?php echo __('Invalid Token'); ?>';
                this.modalOpen = true;
            }
        });
    }
}">
    
    <h1 class="text-3xl font-bold mb-8"><?php echo __('Shopping Cart'); ?></h1>

    <!-- Empty Cart State -->
    <div x-show="$store.cart.count === 0" class="text-center py-12 bg-white rounded shadow-sm border">
        <p class="text-gray-500 mb-4"><?php echo __('Your cart is currently empty.'); ?></p>
        <a href="/" class="bg-orange-500 text-white px-6 py-2 rounded hover:bg-orange-600 shadow-md transition-colors"><?php echo __('Return to Shop'); ?></a>
    </div>

    <div x-show="$store.cart.count > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Cart Items -->
        <div class="lg:col-span-2 space-y-4">
            <template x-for="item in $store.cart.items" :key="item.id">
                <div class="flex items-center gap-4 bg-white p-4 rounded border shadow-sm">
                    <img :src="item.image_url" class="w-20 h-20 object-contain rounded border">
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900" x-text="item.name"></h3>
                        <p class="text-sm text-gray-500" x-text="'<?php echo getSetting('store_currency_symbol', 'R$'); ?> ' + item.price.toFixed(2)"></p>
                    </div>
                    <div class="flex items-center border rounded">
                        <button @click="$store.cart.updateQuantity(item.id, item.quantity - 1)" class="px-2 py-1 hover:bg-gray-100">-</button>
                        <span class="px-2" x-text="item.quantity"></span>
                        <button @click="$store.cart.updateQuantity(item.id, item.quantity + 1)" class="px-2 py-1 hover:bg-gray-100">+</button>
                    </div>
                    <button @click="$store.cart.remove(item.id)" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16z"></path></svg>
                    </button>
                </div>
            </template>
        </div>

        <!-- Checkout Sidebar -->
        <div class="bg-white p-6 rounded border shadow-sm h-fit sticky top-24">
            <h2 class="text-xl font-bold mb-4 text-gray-800"><?php echo __('Order Summary'); ?></h2>
            <div class="flex justify-between mb-2 text-gray-700">
                <span><?php echo __('Subtotal'); ?></span>
                <span class="font-bold" x-text="'<?php echo getSetting('store_currency_symbol', 'R$'); ?> ' + $store.cart.total.toFixed(2)"></span>
            </div>
            <div class="border-t border-gray-100 my-4"></div>
            
            <!-- Auth Step -->
            <div x-show="!isLoggedIn && step === 1">
                <p class="text-sm text-gray-600 mb-4"><?php echo __('Please login with your email to continue.'); ?></p>
                <div x-show="!tokenSent">
                    <input @keydown.enter="requestToken()" type="email" x-model="loginEmail" placeholder="<?php echo __('Enter your email'); ?>" class="w-full border border-gray-300 rounded p-2 mb-2 focus:ring-1 focus:ring-orange-500 outline-none">
                    <button @click="requestToken()" class="w-full bg-gray-900 text-white py-2 rounded hover:bg-black transition-colors"><?php echo __('Send Login Code'); ?></button>
                </div>

                <div x-show="tokenSent">
                    <input @keydown.enter="verifyToken()" type="text" x-model="loginToken" placeholder="<?php echo __('Enter 6-digit code'); ?>" class="w-full border border-gray-300 rounded p-2 mb-2 focus:ring-1 focus:ring-orange-500 outline-none">
                    <button @click="verifyToken()" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition-colors mb-2"><?php echo __('Verify & Login'); ?></button>
                    
                    <div class="mt-2 text-center">
                        <button @click="resendTimer === 0 ? requestToken() : null" 
                                :class="resendTimer > 0 ? 'text-gray-400 cursor-not-allowed' : 'text-orange-500 hover:text-orange-600 hover:underline'"
                                class="text-xs font-medium transition-colors">
                            <span x-show="resendTimer > 0"><?php echo __('Resend code in'); ?> <span x-text="resendTimer"></span>s</span>
                            <span x-show="resendTimer === 0"><?php echo __('Resend code'); ?></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Customer Details Step -->
            <div x-show="isLoggedIn || step === 2">
                <h3 class="font-bold mb-2 text-gray-800"><?php echo __('Customer Details'); ?></h3>
                <form action="/checkout" method="POST" class="space-y-3">
                    <input type="hidden" name="items" :value="JSON.stringify($store.cart.items)">
                    <input type="hidden" name="total" :value="$store.cart.total">
                    
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase"><?php echo __('Full Name'); ?></label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required class="w-full border border-gray-300 rounded p-2 focus:ring-1 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase"><?php echo __('Email'); ?></label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? $_SESSION['user_email'] ?? ''); ?>" required class="w-full border border-gray-300 rounded p-2 focus:ring-1 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase"><?php echo __('WhatsApp'); ?></label>
                        <input type="text" name="whatsapp" value="<?php echo htmlspecialchars($user['whatsapp'] ?? ''); ?>" required class="w-full border border-gray-300 rounded p-2 focus:ring-1 focus:ring-orange-500 outline-none" oninput="maskPhone(event)">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase"><?php echo __('Delivery Address'); ?></label>
                        <div class="space-y-3 mt-1">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="text" name="cep" id="cep" placeholder="<?php echo __('CEP'); ?>" 
                                        value="<?php echo htmlspecialchars($user['cep'] ?? ''); ?>" 
                                        class="w-full border border-gray-300 rounded p-2 focus:ring-1 focus:ring-orange-500 outline-none"
                                        onblur="fetchAddress(this.value)">
                                </div>
                                <div>
                                    <input type="text" name="state" id="state" placeholder="<?php echo __('State'); ?>" 
                                        value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" 
                                        class="w-full border border-gray-300 rounded p-2 focus:ring-1 focus:ring-orange-500 outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="col-span-2">
                                    <input type="text" name="street" id="street" placeholder="<?php echo __('Street'); ?>" 
                                        value="<?php echo htmlspecialchars($user['street'] ?? ''); ?>" 
                                        class="w-full border border-gray-300 rounded p-2 focus:ring-1 focus:ring-orange-500 outline-none">
                                </div>
                                <div>
                                    <input type="text" name="number" id="number" placeholder="<?php echo __('Number'); ?>" 
                                        value="<?php echo htmlspecialchars($user['number'] ?? ''); ?>" 
                                        class="w-full border border-gray-300 rounded p-2 focus:ring-1 focus:ring-orange-500 outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="text" name="neighborhood" id="neighborhood" placeholder="<?php echo __('Neighborhood'); ?>" 
                                        value="<?php echo htmlspecialchars($user['neighborhood'] ?? ''); ?>" 
                                        class="w-full border border-gray-300 rounded p-2 focus:ring-1 focus:ring-orange-500 outline-none">
                                </div>
                                <div>
                                    <input type="text" name="city" id="city" placeholder="<?php echo __('City'); ?>" 
                                        value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" 
                                        class="w-full border border-gray-300 rounded p-2 focus:ring-1 focus:ring-orange-500 outline-none">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-orange-500 text-white font-bold py-3 rounded mt-4 hover:bg-orange-600 transition-colors uppercase shadow-lg">
                        <?php echo __('Finalize Order'); ?>
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- Modal -->
    <div x-show="modalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded p-6 max-w-sm w-full shadow-lg text-center" @click.outside="modalOpen = false">
            <p x-text="modalMessage" class="text-gray-800 mb-6"></p>
            <button @click="modalOpen = false" class="w-full bg-orange-500 text-white font-bold py-2 rounded hover:bg-orange-600 transition-colors shadow-sm">OK</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const whatsappInput = document.querySelector('input[name="whatsapp"]');
        if (whatsappInput && whatsappInput.value && typeof maskPhone === 'function') {
            maskPhone({ target: whatsappInput });
        }
    });

    function fetchAddress(cep) {
        // Remove non-digits
        cep = cep.replace(/\D/g, '');
        
        if (cep.length === 8) {
            // Show loading state if desired
            document.getElementById('street').placeholder = "<?php echo __('Loading...'); ?>";
            
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('street').value = data.logradouro;
                        document.getElementById('neighborhood').value = data.bairro;
                        document.getElementById('city').value = data.localidade;
                        document.getElementById('state').value = data.uf;
                        document.getElementById('number').focus();
                    } else {
                        alert("<?php echo __('CEP not found.'); ?>");
                    }
                })
                .catch(error => {
                    console.error('Error fetching CEP:', error);
                    alert("<?php echo __('Error fetching address details.'); ?>");
                })
                .finally(() => {
                    document.getElementById('street').placeholder = "<?php echo __('Street'); ?>";
                });
        }
    }
</script>
