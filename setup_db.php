<?php

$dbFile = __DIR__ . '/data/database.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create Products Table
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

$pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    image_url TEXT NOT NULL,
    is_primary INTEGER DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_product_images_product_id ON product_images(product_id)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_product_images_primary ON product_images(product_id, is_primary, sort_order)");

// Create Orders Table
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

// Create Digital Products Tables
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

// Create Users Table (for Admin & Login Tokens)
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT UNIQUE NOT NULL,
    login_token TEXT,
    token_expiry DATETIME,
    remember_selector TEXT UNIQUE,
    remember_token_hash TEXT,
    remember_expires_at DATETIME,
    is_admin INTEGER DEFAULT 0,
    admin_bypass_token TEXT,
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

// Seed Admin User
$stmt = $pdo->prepare("INSERT OR IGNORE INTO users (email, is_admin) VALUES (?, ?)");
$stmt->execute(['admin@r2.com', 1]);

// Seed Initial Products
$products = [
    [
        'name' => 'AOD9604',
        'slug' => 'aod9604',
        'sku' => '184-172-PS',
        'price' => 65.00,
        'image_url' => 'https://placehold.co/400x400/orange/white?text=AOD9604',
        'category' => 'Peptides',
        'short_desc' => 'Anti-Obesity Drug 9604 is a modified form of amino acids 176-191 of the GH polypeptide.',
        'long_desc' => 'AOD9604 is a modified form of amino acids 176-191 of the GH polypeptide. Investigators at Monash University discovered that the fat-reducing effects of GH appear to be controlled by a small region near one end of the GH molecule. This region, which consists of amino acids 176-191, is less than 10% of the total size of the GH molecule and appears to have no effect on growth or insulin resistance.',
        'pdf_url' => ''
    ],
    [
        'name' => 'GHK-Cu 50mg Copper Peptide',
        'slug' => 'ghk-cu-50mg-copper-peptide',
        'sku' => '107-052-PS',
        'price' => 70.00,
        'image_url' => 'https://placehold.co/400x400/blue/white?text=GHK-Cu',
        'category' => 'Peptides',
        'short_desc' => 'GHK-Cu is a naturally occurring copper complex that was first identified in human plasma.',
        'long_desc' => 'GHK-Cu is a naturally occurring copper complex that was first identified in human plasma. It has been found to have a variety of roles in the human body including promoting activation of wound healing, attraction of immune cells, antioxidant and anti-inflammatory effects, stimulation of collagen and glycosaminoglycan synthesis in skin fibroblasts and promotion of blood vessel growth.',
        'pdf_url' => ''
    ],
    [
        'name' => 'Ipamorelin',
        'slug' => 'ipamorelin',
        'sku' => '16-593-PS',
        'price' => 30.00,
        'image_url' => 'https://placehold.co/400x400/gray/white?text=Ipamorelin',
        'category' => 'Peptides',
        'short_desc' => 'Ipamorelin is a pentapeptide and the first selective GH-Secretagogue.',
        'long_desc' => 'Ipamorelin is a pentapeptide and the first selective GH-Secretagogue. It displays high GH releasing potency and efficacy in vitro and in vivo. The specificity of Ipamorelin for the GH secretagogue receptor makes it a very interesting candidate for future clinical development.',
        'pdf_url' => ''
    ],
    [
        'name' => 'Melanotan 1 (MT1) 10mg',
        'slug' => 'melanotan-1-mt1-10mg',
        'sku' => '14-095-PS',
        'price' => 55.00,
        'image_url' => 'https://placehold.co/400x400/black/white?text=MT1',
        'category' => 'Peptides',
        'short_desc' => 'Melanotan 1 is a synthetic analogue of the naturally occurring melanocortin peptide hormone alpha-MSH.',
        'long_desc' => 'Melanotan 1 is a synthetic analogue of the naturally occurring melanocortin peptide hormone alpha-melanocyte stimulating hormone (alpha-MSH). It has been shown to induce skin tanning in humans.',
        'pdf_url' => ''
    ]
];

$stmt = $pdo->prepare("INSERT INTO products (name, sku, slug, price, image_url, category, short_desc, long_desc, pdf_url, pdf_label, type, digital_delivery, download_limit, download_expiry_days, file_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($products as $product) {
    // Check if exists to avoid duplicates on re-run
    $check = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
    $check->execute([$product['sku']]);
    if (!$check->fetch()) {
        $stmt->execute([
            $product['name'],
            $product['sku'],
            $product['slug'],
            $product['price'],
            $product['image_url'],
            $product['category'],
            $product['short_desc'],
            $product['long_desc'],
            $product['pdf_url'],
            '', // pdf_label
            'physical', // type
            0, // digital_delivery
            0, // download_limit
            0, // download_expiry_days
            '' // file_url
        ]);
        echo "Inserted: " . $product['name'] . "\n";
    }
}

echo "Database setup complete!\n";
