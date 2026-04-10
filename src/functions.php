<?php
require_once __DIR__ . '/db.php';

function formatMoney($amount) {
    static $currencyCode = null;
    static $currencySymbol = null;
    
    if ($currencyCode === null) {
        $currencyCode = getSetting('store_currency', 'BRL');
        $currencySymbol = getSetting('store_currency_symbol', 'R$');
    }
    
    if ($currencyCode === 'BRL') {
        return $currencySymbol . ' ' . number_format((float)$amount, 2, ',', '.');
    } else {
        // Default format (USD, EUR, etc)
        return $currencySymbol . ' ' . number_format((float)$amount, 2, '.', ',');
    }
}

function ensureSettingsSchema() {
    global $pdo;
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT
    )");
}

function getSetting($key, $default = '') {
    ensureSettingsSchema();
    global $pdo;
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($result && $result['value'] !== null) ? $result['value'] : $default;
}

function updateSetting($key, $value) {
    ensureSettingsSchema();
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = ?");
    return $stmt->execute([$key, $value, $value]);
}
