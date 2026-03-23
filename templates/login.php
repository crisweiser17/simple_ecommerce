<div class="container mx-auto px-4 py-8 flex justify-center" x-data="{ 
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
        if (!this.loginEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.loginEmail)) {
            this.modalMessage = '<?php echo __('Invalid email format'); ?>';
            this.modalOpen = true;
            return;
        }
        fetch('/api/login-request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: this.loginEmail })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) { 
                this.loginToken = '';
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
                window.location.href = '/'; // Redirect to home after login
            } else {
                this.modalMessage = '<?php echo __('Invalid Token'); ?>';
                this.modalOpen = true;
            }
        });
    }
}">
    <div class="bg-white p-8 rounded border shadow-sm w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center"><?php echo __('Sign In'); ?></h1>

        <div x-show="!isLoggedIn">
            <p class="text-sm text-gray-600 mb-6 text-center"><?php echo __('Please login with your email to continue.'); ?></p>
            
            <div x-show="!tokenSent">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        <?php echo __('Email Address'); ?>
                    </label>
                    <input @keydown.enter="requestToken()" type="email" x-model="loginEmail" id="email" placeholder="<?php echo __('Enter your email'); ?>" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-1 focus:ring-orange-500">
                </div>
                <button @click="requestToken()" class="w-full bg-gray-900 text-white font-bold py-2 rounded hover:bg-black transition-colors"><?php echo __('Send Login Code'); ?></button>
            </div>

            <div x-show="tokenSent" style="display: none;">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="token">
                        <?php echo __('Verification Code'); ?>
                    </label>
                    <input @keydown.enter="verifyToken()" type="text" x-model="loginToken" id="token" placeholder="<?php echo __('Enter 6-digit code'); ?>" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-1 focus:ring-orange-500">
                </div>
                <button @click="verifyToken()" class="w-full bg-orange-500 text-white font-bold py-2 rounded hover:bg-orange-600 transition-colors shadow-lg"><?php echo __('Verify & Login'); ?></button>
                
                <div class="mt-4 flex flex-col gap-2 items-center">
                    <button @click="resendTimer === 0 ? requestToken() : null" 
                            :class="resendTimer > 0 ? 'text-gray-400 cursor-not-allowed' : 'text-orange-500 hover:text-orange-600 hover:underline'"
                            class="text-sm font-medium transition-colors">
                        <span x-show="resendTimer > 0"><?php echo __('Resend code in'); ?> <span x-text="resendTimer"></span>s</span>
                        <span x-show="resendTimer === 0"><?php echo __('Resend code'); ?></span>
                    </button>
                    <button @click="tokenSent = false; loginToken = '';" class="w-full text-center text-sm text-gray-500 hover:text-gray-700 underline"><?php echo __('Back to Email'); ?></button>
                </div>
            </div>
        </div>

        <div x-show="isLoggedIn" style="display: none;">
            <p class="text-green-600 text-center mb-4"><?php echo __('You are logged in!'); ?></p>
            <a href="/" class="block w-full bg-orange-500 text-white text-center font-bold py-2 rounded hover:bg-orange-600 transition-colors shadow-lg"><?php echo __('Go to Home'); ?></a>
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
