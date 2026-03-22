<?php
// Mock
$isMultilangEnabled = true;
$supportedLanguages = ['en', 'pt'];
$singleLang = 'en';

// Test 1: Fallback (should be 'en')
$_SESSION = [];
$_COOKIE = [];
$_GET = [];
$_SERVER = [];
ob_start();
include 'src/i18n.php';
ob_end_clean();
echo "Test 1 Default: " . $_SESSION['lang'] . "\n";

// Test 2: Fallback with Accept-Language
$_SESSION = [];
$_COOKIE = [];
$_GET = [];
$_SERVER = ['HTTP_ACCEPT_LANGUAGE' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7'];
ob_start();
include 'src/i18n.php';
ob_end_clean();
echo "Test 2 Accept-Language: " . $_SESSION['lang'] . "\n";

// Test 3: Cookie overrides Accept-Language
$_SESSION = [];
$_COOKIE = ['lang' => 'en'];
$_GET = [];
$_SERVER = ['HTTP_ACCEPT_LANGUAGE' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7'];
ob_start();
include 'src/i18n.php';
ob_end_clean();
echo "Test 3 Cookie: " . $_SESSION['lang'<?php
// Mock
 = true;
// M [ = ['" = "'en'"