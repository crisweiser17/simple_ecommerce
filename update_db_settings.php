<?php
$dbFile = __DIR__ . '/data/database.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create Settings Table
$pdo->exec("CREATE TABLE IF NOT EXISTS settings (
    key TEXT PRIMARY KEY,
    value TEXT
)");

// Default values
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
    'i18n_single_lang' => 'en'
];

$stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)");

foreach ($defaults as $key => $value) {
    $stmt->execute([$key, $value]);
    echo "Ensured setting: $key\n";
}

echo "Settings table updated!\n";
