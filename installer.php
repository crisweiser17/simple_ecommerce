<?php
// installer.php - R2 Site Installer

$message = '';
$step = 1;
$error = false;

// Security check: if database already exists, we should probably warn or block, 
// but since it's an installer, we'll allow running it (it uses IF NOT EXISTS).
// It's highly recommended to delete or rename this file after successful installation!

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    try {
        // 1. Check and Create Directories
        $dirs = [
            __DIR__ . '/data',
            __DIR__ . '/public/uploads',
            __DIR__ . '/public/uploads/banners',
            __DIR__ . '/public/uploads/products'
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new Exception("Failed to create directory: $dir. Check permissions.");
                }
            }
            // Ensure writable
            if (!is_writable($dir)) {
                chmod($dir, 0775);
            }
        }

        // 2. Setup Database
        $dbFile = __DIR__ . '/data/database.sqlite';
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Products Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            sku TEXT,
            slug TEXT UNIQUE,
            price REAL,
            image_url TEXT,
            category TEXT,
            category_id INTEGER,
            short_desc TEXT,
            long_desc TEXT,
            pdf_url TEXT,
            pdf_label TEXT,
            type TEXT DEFAULT 'physical',
            digital_delivery INTEGER DEFAULT 0,
            download_limit INTEGER DEFAULT 0,
            download_expiry_days INTEGER DEFAULT 0,
            file_url TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Orders Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_name TEXT,
            customer_whatsapp TEXT,
            customer_email TEXT,
            customer_address TEXT,
            customer_cep TEXT,
            customer_street TEXT,
            customer_number TEXT,
            customer_neighborhood TEXT,
            customer_city TEXT,
            customer_state TEXT,
            items_json TEXT,
            total_amount REAL,
            status TEXT DEFAULT 'pending',
            payment_status TEXT DEFAULT 'pending',
            payment_provider TEXT,
            paid_at DATETIME,
            tracking_number TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            provider TEXT NOT NULL,
            provider_payment_id TEXT,
            provider_reference TEXT,
            amount REAL NOT NULL,
            currency TEXT DEFAULT 'BRL',
            status TEXT DEFAULT 'pending',
            pix_qr_code TEXT,
            pix_copy_paste TEXT,
            pix_expires_at DATETIME,
            gateway_payload TEXT,
            gateway_last_event TEXT,
            paid_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE
        )");
        $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_payments_provider_payment_id ON payments(provider, provider_payment_id)");
        $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_payments_provider_reference ON payments(provider, provider_reference)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_payments_order_id ON payments(order_id)");

        $pdo->exec("CREATE TABLE IF NOT EXISTS payment_events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            provider TEXT NOT NULL,
            event_id TEXT NOT NULL,
            payment_id TEXT,
            payload TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_payment_events_provider_event_id ON payment_events(provider, event_id)");

        // Digital Products Tables
        $pdo->exec("CREATE TABLE IF NOT EXISTS order_digital_deliveries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            token TEXT NOT NULL UNIQUE,
            download_count INTEGER DEFAULT 0,
            max_downloads INTEGER DEFAULT 0,
            expires_at DATETIME,
            delivered_at DATETIME,
            downloaded_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
        )");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_order_digital_deliveries_token ON order_digital_deliveries(token)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_order_digital_deliveries_order_id ON order_digital_deliveries(order_id)");

        $pdo->exec("CREATE TABLE IF NOT EXISTS embed_sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_token TEXT NOT NULL UNIQUE,
            product_id INTEGER NOT NULL,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
        )");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_embed_sessions_token ON embed_sessions(session_token)");

        // Users Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            login_token TEXT,
            token_expiry DATETIME,
            is_admin INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            name TEXT,
            whatsapp TEXT,
            cep TEXT,
            street TEXT,
            number TEXT,
            neighborhood TEXT,
            city TEXT,
            state TEXT
        )");

        // Categories Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Pages Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            content TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Settings Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT
        )");

        // 3. Seed Admin User
        $adminEmail = trim($_POST['admin_email']);
        if (empty($adminEmail)) {
            $adminEmail = 'admin@r2.com';
        }
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (email, is_admin) VALUES (?, ?)");
        $stmt->execute([$adminEmail, 1]);

        // 4. Default Settings
        $defaults = [
            'banner_overlay_color1' => '#111827',
            'banner_overlay_color2' => '#1f2937',
            'banner_overlay_opacity' => '80',
            'banner_overlay_enabled' => '1',
            'store_whatsapp' => '',
            'store_name' => 'R2 Research Labs',
            'brand_mode' => 'text',
            'brand_logo_url' => '',
            'brand_logo_width' => '160',
            'brand_logo_height' => '48',
            'theme_header_bg' => '#0f1115',
            'theme_page_bg' => '#f3f4f6',
            'theme_text_color' => '#1f2937',
            'font_body' => 'Inter',
            'font_headings' => 'Inter',
            'font_product_title' => 'Inter',
            'font_menu' => 'Inter',
            'font_buttons' => 'Inter',
            'font_prices' => 'Inter',
            'i18n_multilang_enabled' => '1',
            'i18n_single_lang' => 'en',
            'payment_provider_active' => 'mercadopago',
            'payment_provider_modules' => 'mercadopago,manual_pix',
            'payment_mercadopago_access_token' => '',
            'payment_mercadopago_webhook_secret' => '',
            'payment_mercadopago_environment' => 'sandbox',
            'payment_manual_pix_key' => '',
            'payment_manual_pix_recipient_name' => '',
            'payment_manual_pix_city' => ''
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)");
        foreach ($defaults as $key => $value) {
            $stmt->execute([$key, $value]);
        }

        // 5. Seed Initial Products & Categories
        if (isset($_POST['seed_data'])) {
            // Seed Categories
            $defaultCategories = [
                ['name' => 'Peptides', 'slug' => 'peptides'],
                ['name' => 'Accessories', 'slug' => 'accessories']
            ];
            $stmtCat = $pdo->prepare("INSERT OR IGNORE INTO categories (name, slug) VALUES (?, ?)");
            foreach ($defaultCategories as $cat) {
                $stmtCat->execute([$cat['name'], $cat['slug']]);
            }

            // Get Peptides Category ID
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
            $stmt->execute(['peptides']);
            $peptidesCatId = $stmt->fetchColumn() ?: null;

            // Seed Products
            $products = [
                [
                    'name' => 'AOD9604',
                    'sku' => '184-172-PS',
                    'slug' => 'aod9604',
                    'price' => 65.00,
                    'image_url' => 'https://placehold.co/400x400/orange/white?text=AOD9604',
                    'category' => 'Peptides',
                    'short_desc' => 'Anti-Obesity Drug 9604 is a modified form of amino acids 176-191 of the GH polypeptide.',
                    'long_desc' => 'AOD9604 is a modified form of amino acids 176-191 of the GH polypeptide. Investigators at Monash University discovered that the fat-reducing effects of GH appear to be controlled by a small region near one end of the GH molecule. This region, which consists of amino acids 176-191, is less than 10% of the total size of the GH molecule and appears to have no effect on growth or insulin resistance.',
                    'pdf_url' => ''
                ],
                [
                    'name' => 'GHK-Cu 50mg Copper Peptide',
                    'sku' => '107-052-PS',
                    'slug' => 'ghk-cu-50mg-copper-peptide',
                    'price' => 70.00,
                    'image_url' => 'https://placehold.co/400x400/blue/white?text=GHK-Cu',
                    'category' => 'Peptides',
                    'short_desc' => 'GHK-Cu is a naturally occurring copper complex that was first identified in human plasma.',
                    'long_desc' => 'GHK-Cu is a naturally occurring copper complex that was first identified in human plasma. It has been found to have a variety of roles in the human body including promoting activation of wound healing, attraction of immune cells, antioxidant and anti-inflammatory effects, stimulation of collagen and glycosaminoglycan synthesis in skin fibroblasts and promotion of blood vessel growth.',
                    'pdf_url' => ''
                ]
            ];

            $stmtProd = $pdo->prepare("INSERT INTO products (name, sku, slug, price, image_url, category, category_id, short_desc, long_desc, pdf_url, type, digital_delivery, download_limit, download_expiry_days, file_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($products as $product) {
                $check = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
                $check->execute([$product['sku']]);
                if (!$check->fetch()) {
                    $stmtProd->execute([
                        $product['name'],
                        $product['sku'],
                        $product['slug'],
                        $product['price'],
                        $product['image_url'],
                        $product['category'],
                        $peptidesCatId,
                        $product['short_desc'],
                        $product['long_desc'],
                        $product['pdf_url'],
                        'physical',
                        0,
                        0,
                        0,
                        ''
                    ]);
                }
            }
        }

        $message = "Installation completed successfully!";
        $step = 2;

    } catch (Exception $e) {
        $error = true;
        $message = "Installation Failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R2 Site Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-xl w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">R2 E-commerce Installer</h1>
            <p class="text-gray-500 mt-2">Setup your database and initial configurations</p>
        </div>

        <?php if ($message): ?>
            <div class="p-4 mb-6 rounded-md <?php echo $error ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <form method="POST" action="installer.php" class="space-y-6">
                
                <div class="bg-gray-50 p-4 rounded border">
                    <h3 class="font-semibold mb-2">Admin Account Setup</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Admin Email</label>
                        <input type="email" name="admin_email" value="admin@r2.com" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-orange-500 focus:border-orange-500">
                        <p class="text-xs text-gray-500 mt-1">This email will be granted admin access to the dashboard.</p>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded border">
                    <h3 class="font-semibold mb-2">Initial Data</h3>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="seed_data" value="1" checked class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500">
                        <span class="text-sm text-gray-700">Install sample categories and products</span>
                    </label>
                </div>

                <div class="bg-yellow-50 p-4 rounded border border-yellow-200">
                    <h3 class="font-semibold text-yellow-800 mb-1">System Requirements Check</h3>
                    <ul class="text-sm text-yellow-700 list-disc list-inside ml-4 space-y-1">
                        <li>PHP Version: <?php echo PHP_VERSION; ?> <?php echo version_compare(PHP_VERSION, '8.0.0', '>=') ? '✅' : '❌ (Requires 8.0+)'; ?></li>
                        <li>PDO SQLite Extension: <?php echo extension_loaded('pdo_sqlite') ? '✅' : '❌'; ?></li>
                        <li>Root Directory Writable: <?php echo is_writable(__DIR__) ? '✅' : '❌'; ?></li>
                    </ul>
                </div>

                <button type="submit" name="install" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Run Installation
                </button>
            </form>
        <?php else: ?>
            <div class="text-center space-y-6">
                <div class="text-green-500">
                    <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <div class="bg-red-50 text-red-700 p-4 rounded border border-red-200">
                    <p class="font-bold">⚠️ Security Warning</p>
                    <p class="text-sm mt-1">Please delete this <code>installer.php</code> file from your server immediately to prevent unauthorized access!</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/" class="inline-flex justify-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none">
                        Go to Store
                    </a>
                    <a href="/login" class="inline-flex justify-center px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                        Login as Admin
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
