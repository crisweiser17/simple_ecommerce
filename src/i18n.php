<?php

require_once __DIR__ . '/db.php';

function getI18nSettingValue($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    } catch (Throwable $e) {
        return $default;
    }
}

$supportedLanguages = ['en', 'pt'];
$isMultilangEnabled = getI18nSettingValue('i18n_multilang_enabled', '1') === '1';
$singleLang = getI18nSettingValue('i18n_single_lang', 'en');
if (!in_array($singleLang, $supportedLanguages, true)) {
    $singleLang = 'en';
}

if ($isMultilangEnabled) {
    if (isset($_GET['lang']) && in_array($_GET['lang'], $supportedLanguages, true)) {
        $selectedLang = $_GET['lang'];
        $_SESSION['lang'] = $selectedLang;
        setcookie('lang', $selectedLang, time() + (86400 * 365), '/');
    } elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $supportedLanguages, true)) {
        $selectedLang = $_SESSION['lang'];
        if (!isset($_COOKIE['lang']) || $_COOKIE['lang'] !== $selectedLang) {
            setcookie('lang', $selectedLang, time() + (86400 * 365), '/');
        }
    } elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $supportedLanguages, true)) {
        $selectedLang = $_COOKIE['lang'];
        $_SESSION['lang'] = $selectedLang;
    } else {
        $defaultLang = getI18nSettingValue('i18n_default_lang', 'en');
        if (!in_array($defaultLang, $supportedLanguages, true)) {
            $defaultLang = 'en';
        }
        $selectedLang = $defaultLang;
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (in_array($browserLang, $supportedLanguages, true)) {
                $selectedLang = $browserLang;
            }
        }
        $_SESSION['lang'] = $selectedLang;
        setcookie('lang', $selectedLang, time() + (86400 * 365), '/');
    }
} else {
    $_SESSION['lang'] = $singleLang;
}

$lang = $_SESSION['lang'] ?? 'en';
if (!in_array($lang, $supportedLanguages, true)) {
    $lang = 'en';
}

$translations = [];
$langFile = __DIR__ . "/../lang/{$lang}.php";
if (file_exists($langFile)) {
    $translations = require $langFile;
} else {
    $fallbackLangFile = __DIR__ . "/../lang/en.php";
    if (file_exists($fallbackLangFile)) {
        $translations = require $fallbackLangFile;
    }
}

function __($key) {
    global $translations;
    if (!is_string($key) && !is_int($key) && !is_float($key)) {
        $key = '';
    }
    $key = (string) $key;
    return isset($translations[$key]) ? (string) $translations[$key] : $key;
}
