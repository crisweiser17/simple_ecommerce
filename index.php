<?php

// Configure session directory and lifetime to 24 hours (86400 seconds)
$sessionPath = __DIR__ . '/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

ini_set('session.gc_maxlifetime', 86400);
$isSecureCookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'secure' => $isSecureCookie,
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Serve static files directly if they exist
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $decodedPath = urldecode((string)$path);
    $file = __DIR__ . $decodedPath;
    if (file_exists($file) && is_file($file)) {
        return false;
    }

    if (strpos($decodedPath, '/uploads/') === 0) {
        $uploadsBaseDir = realpath(__DIR__ . '/public/uploads');
        $publicFilePath = realpath(__DIR__ . '/public' . $decodedPath);
        if ($uploadsBaseDir && $publicFilePath && strpos($publicFilePath, $uploadsBaseDir) === 0 && is_file($publicFilePath)) {
            $mimeType = function_exists('mime_content_type') ? (string)mime_content_type($publicFilePath) : 'application/octet-stream';
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . (string)filesize($publicFilePath));
            readfile($publicFilePath);
            exit;
        }
    }
}

require_once __DIR__ . '/src/i18n.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/products.php';
require_once __DIR__ . '/src/orders.php';
require_once __DIR__ . '/src/functions.php';
require_once __DIR__ . '/src/user.php';
require_once __DIR__ . '/src/categories.php';
require_once __DIR__ . '/src/pages.php';
require_once __DIR__ . '/src/contact.php';
require_once __DIR__ . '/src/products_csv.php';
require_once __DIR__ . '/src/payments.php';
require_once __DIR__ . '/src/payment_engine.php';
require_once __DIR__ . '/src/FileUploader.php';

tryAutoLogin();
ensureSettingsSchema();
ensureUsersSchema();
ensureCategoriesSchema();
ensureProductsSchema();
ensurePagesSchema();
ensureOrdersSchema();
ensurePaymentsSchema();

function normalizeImageUrlsFromForm($urls) {
    $normalized = [];
    if (!is_array($urls)) {
        return $normalized;
    }

    foreach ($urls as $url) {
        $value = trim((string)$url);
        if ($value !== '' && !in_array($value, $normalized, true)) {
            $normalized[] = $value;
        }
    }

    return $normalized;
}

function uploadProductImages($files) {
    $uploadedUrls = [];
    if (!is_array($files) || !isset($files['name']) || !is_array($files['name'])) {
        return $uploadedUrls;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSizeBytes = 5 * 1024 * 1024;
    $uploadDir = __DIR__ . '/public/uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;

    foreach ($files['name'] as $index => $name) {
        $error = (int)($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($error !== UPLOAD_ERR_OK) {
            continue;
        }

        $tmpName = (string)($files['tmp_name'][$index] ?? '');
        $size = (int)($files['size'][$index] ?? 0);
        $ext = strtolower(pathinfo((string)$name, PATHINFO_EXTENSION));
        if ($tmpName === '' || !is_uploaded_file($tmpName) || $size <= 0 || $size > $maxSizeBytes) {
            continue;
        }
        if (!in_array($ext, $allowedExtensions, true)) {
            continue;
        }

        if ($finfo) {
            $mimeType = finfo_file($finfo, $tmpName) ?: '';
            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                continue;
            }
        }

        $fileName = uniqid('product_', true) . '.' . $ext;
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($tmpName, $targetFile)) {
            $uploadedUrls[] = '/uploads/products/' . $fileName;
        }
    }

    if ($finfo) {
        finfo_close($finfo);
    }

    return $uploadedUrls;
}

function uploadSingleImageFile($file, $uploadDir, $publicBasePath, $filePrefix, $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'], $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], $maxSizeBytes = 5242880) {
    if (!is_array($file)) {
        return '';
    }

    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        return '';
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    $originalName = (string)($file['name'] ?? '');
    $size = (int)($file['size'] ?? 0);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($tmpName === '' || !is_uploaded_file($tmpName) || $size <= 0 || $size > $maxSizeBytes) {
        return '';
    }
    if (!in_array($ext, $allowedExtensions, true)) {
        return '';
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = finfo_file($finfo, $tmpName) ?: '';
            finfo_close($finfo);
            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                return '';
            }
        }
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = uniqid($filePrefix, true) . '.' . $ext;
    $targetFile = rtrim($uploadDir, '/') . '/' . $fileName;
    if (!move_uploaded_file($tmpName, $targetFile)) {
        return '';
    }

    return rtrim($publicBasePath, '/') . '/' . $fileName;
}

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Simple Router
switch ($path) {
    case '/':
    case '/home':
        $categorySlug = $_GET['category'] ?? null;
        $searchTerm = trim((string)($_GET['q'] ?? ''));
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($currentPage < 1) $currentPage = 1;
        $limit = (int)getSetting('products_per_page', '15');
        if ($limit < 1) $limit = 15;
        $offset = ($currentPage - 1) * $limit;
        
        $products = getAllProducts($categorySlug, $limit, $offset, $searchTerm);
        $totalProducts = countProducts($categorySlug, $searchTerm);
        $totalPages = ceil($totalProducts / $limit);
        
        $template = __DIR__ . '/templates/archive.php';
        require __DIR__ . '/templates/layout.php';
        break;

    case '/product':
        $product = false;
        $slug = trim((string)($_GET['slug'] ?? ''));
        if ($slug !== '') {
            $product = getProductBySlug($slug);
        }
        if (!$product) {
            $id = $_GET['id'] ?? null;
            if ($id) {
                $product = getProduct($id);
                if ($product && !empty($product['slug'])) {
                    header('Location: ' . getProductUrl($product), true, 302);
                    exit;
                }
            }
        }
        if ($product) {
            $template = __DIR__ . '/templates/product.php';
            require __DIR__ . '/templates/layout.php';
            break;
        }
        http_response_code(404);
        echo __('Product not found');
        break;

    case '/cart':
        $storeMode = getSetting('store_mode', 'ecommerce');
        if ($storeMode === 'informational') {
            header('Location: /', true, 302);
            exit;
        }
        $user = [];
        if (isLoggedIn()) {
             $user = getUser($_SESSION['user_id']) ?: [];
        }
        $template = __DIR__ . '/templates/cart.php';
        require __DIR__ . '/templates/layout.php';
        break;

    case '/contact':
        $contactSuccessMessage = $_SESSION['contact_success_message'] ?? null;
        $contactErrorMessage = $_SESSION['contact_error_message'] ?? null;
        $contactOld = $_SESSION['contact_old'] ?? [];
        unset($_SESSION['contact_success_message'], $_SESSION['contact_error_message'], $_SESSION['contact_old']);

        $contactUser = [];
        if (isLoggedIn() && isset($_SESSION['user_id'])) {
            $contactUser = getUser($_SESSION['user_id']) ?: [];
        }

        $contactData = [
            'name' => $contactOld['name'] ?? ($contactUser['name'] ?? ''),
            'email' => $contactOld['email'] ?? ($contactUser['email'] ?? ''),
            'phone' => $contactOld['phone'] ?? ($contactUser['whatsapp'] ?? ''),
            'subject' => $contactOld['subject'] ?? '',
            'message' => $contactOld['message'] ?? '',
        ];
        $contactSubjects = getContactSubjects();

        $template = __DIR__ . '/templates/contact.php';
        require __DIR__ . '/templates/layout.php';
        break;

    case '/contact/send':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /contact');
            exit;
        }

        $payload = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'subject' => trim($_POST['subject'] ?? ''),
            'message' => trim($_POST['message'] ?? ''),
        ];

        if (empty($payload['name']) || empty($payload['email']) || empty($payload['subject']) || empty($payload['message'])) {
            $_SESSION['contact_error_message'] = __('Please fill all required fields.');
            $_SESSION['contact_old'] = $payload;
            header('Location: /contact');
            exit;
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['contact_error_message'] = __('Invalid email format');
            $_SESSION['contact_old'] = $payload;
            header('Location: /contact');
            exit;
        }

        $sendResult = sendContactFormMessage($payload);

        if ($sendResult['success']) {
            $_SESSION['contact_success_message'] = $sendResult['message'];
            $_SESSION['contact_old'] = [];
        } else {
            $_SESSION['contact_error_message'] = $sendResult['message'];
            $_SESSION['contact_old'] = $payload;
        }

        header('Location: /contact');
        exit;
        break;

    case '/login':
        // If logged in, redirect to home
        if (isLoggedIn()) {
            header('Location: /');
            exit;
        }
        $template = __DIR__ . '/templates/login.php';
        require __DIR__ . '/templates/layout.php';
        break;

    case '/logout':
        logout();
        header('Location: /');
        exit;
        break;
        
    case '/api/login-request.php':
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['email'])) {
            $email = trim($input['email']);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => __('Invalid email format')]);
                exit;
            }
            
            $token = generateLoginToken($email);
            echo json_encode(['success' => true, 'message' => __('Login token sent to your email. Please enter the received code in the field below')]);
        } else {
            echo json_encode(['success' => false, 'message' => __('Email required')]);
        }
        exit;
        break;

    case '/api/login-verify.php':
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['email']) && isset($input['token'])) {
            if (verifyLoginToken($input['email'], $input['token'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => __('Invalid token')]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => __('Missing data')]);
        }
        exit;
        break;

    case '/api/products/autocomplete':
        header('Content-Type: application/json');
        $searchTerm = trim((string)($_GET['q'] ?? ''));
        if (strlen($searchTerm) < 3) {
            echo json_encode(['items' => []]);
            exit;
        }
        $items = getProductAutocompleteSuggestions($searchTerm, 8);
        echo json_encode(['items' => $items]);
        exit;
        break;

    case '/api/orders/payment-status':
        header('Content-Type: application/json');
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($orderId < 1) {
            echo json_encode(['success' => false, 'message' => 'Invalid order']);
            exit;
        }
        $order = getOrder($orderId);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        $payment = getPaymentByOrderId($orderId);
        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'order_status' => $order['status'] ?? 'pending',
            'payment_status' => $payment['status'] ?? ($order['payment_status'] ?? 'pending'),
            'paid_at' => $order['paid_at'] ?? null
        ]);
        exit;
        break;

    case '/api/express-checkout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) $input = $_POST;

            $productId = $input['product_id'] ?? null;
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $whatsapp = trim($input['whatsapp'] ?? '');

            if (!$productId || !$name || !$email) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => __('Invalid email format')]);
                exit;
            }

            $product = getProduct($productId);
            if (!$product) {
                echo json_encode(['success' => false, 'message' => __('Product not found')]);
                exit;
            }

            // Calculate final price based on selected variations
            $finalPrice = floatval($product['price']);
            $selectedVariations = $input['variations'] ?? [];
            $productVariations = json_decode($product['variations_json'] ?? '[]', true) ?: [];
            
            foreach ($productVariations as $v) {
                if (isset($selectedVariations[$v['name']])) {
                    $selectedOptName = $selectedVariations[$v['name']];
                    foreach ($v['options'] as $opt) {
                        if ($opt['name'] === $selectedOptName) {
                            $finalPrice += floatval($opt['price_modifier'] ?? 0);
                            break;
                        }
                    }
                }
            }

            $items = [
                [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $finalPrice,
                    'quantity' => 1,
                    'selected_variations' => $selectedVariations
                ]
            ];
            $total = $finalPrice;

            $customer = [
                'name' => $name,
                'whatsapp' => $whatsapp,
                'email' => $email,
                'address' => 'Express Checkout',
                'status' => 'pending_payment',
                'payment_status' => 'pending'
            ];

            $engine = new PaymentEngine();
            $checkoutProvider = $engine->getCheckoutProvider();
            $customer['payment_provider'] = $checkoutProvider ?? '';
            $orderId = createOrder($customer, $items, $total);
            
            if ($engine->hasEnabledProviders()) {
                $chargeResult = $engine->createPixCharge([
                    'order_id' => (int)$orderId,
                    'total' => $total,
                    'customer' => $customer,
                    'items' => $items,
                    'reference' => 'order_' . $orderId,
                    'description' => 'Pedido #' . $orderId
                ]);

                if (!empty($chargeResult['success'])) {
                    createPaymentForOrder((int)$orderId, $chargeResult['provider'] ?? ($checkoutProvider ?? ''), $total, $chargeResult);
                    updateOrder((int)$orderId, [
                        'payment_provider' => $chargeResult['provider'] ?? ($checkoutProvider ?? ''),
                        'payment_status' => $chargeResult['status'] ?? 'pending'
                    ]);
                    echo json_encode([
                        'success' => true,
                        'order_id' => $orderId,
                        'pix_qr_code' => $chargeResult['pix_qr_code'] ?? '',
                        'pix_copy_paste' => $chargeResult['pix_copy_paste'] ?? ''
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => $chargeResult['error'] ?? 'Failed to create charge']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No payment provider enabled']);
            }
            exit;
        }
        break;

    case '/checkout/quote':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim((string)($_POST['name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
            $itemsJson = (string)($_POST['items'] ?? '[]');
            $items = json_decode($itemsJson, true);

            if (!is_array($items) || empty($items)) {
                $_SESSION['checkout_error'] = __('Sua lista está vazia.');
                header('Location: /cart', true, 302);
                exit;
            }

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                // If not logged in via token, try to find or create user by email
                require_once __DIR__ . '/src/db.php';
                global $pdo;
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingUser) {
                    $userId = $existingUser['id'];
                    updateUser($userId, ['name' => $name, 'whatsapp' => $whatsapp]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (email, name, whatsapp) VALUES (?, ?, ?)");
                    $stmt->execute([$email, $name, $whatsapp]);
                    $userId = $pdo->lastInsertId();
                }
            } else {
                updateUser($userId, ['name' => $name, 'whatsapp' => $whatsapp]);
            }

            $customerData = [
                'name' => $name,
                'email' => $email,
                'whatsapp' => $whatsapp,
                'address' => '',
                'cep' => '',
                'street' => '',
                'number' => '',
                'complement' => '',
                'neighborhood' => '',
                'city' => '',
                'state' => '',
                'status' => 'quote',
                'payment_status' => 'none',
                'payment_provider' => 'none'
            ];

            // Create Order as 'quote'
            $orderId = createOrder($customerData, $items, 0);

            header("Location: /quote-success?id={$orderId}", true, 302);
            exit;
        }
        header('Location: /cart', true, 302);
        break;

    case '/quote-success':
        $orderId = (int)($_GET['id'] ?? 0);
        $order = getOrder($orderId);
        if (!$order || $order['status'] !== 'quote') {
            header('Location: /', true, 302);
            exit;
        }
        $template = __DIR__ . '/templates/quote-success.php';
        require __DIR__ . '/templates/layout.php';
        break;

    case '/checkout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $address = $_POST['address'] ?? '';
            if (isset($_POST['street'])) {
                $addressParts = [];
                if (!empty($_POST['street'])) $addressParts[] = $_POST['street'];
                if (!empty($_POST['number'])) $addressParts[] = $_POST['number'];
                if (!empty($_POST['complement'])) $addressParts[] = $_POST['complement'];
                if (!empty($_POST['neighborhood'])) $addressParts[] = $_POST['neighborhood'];
                if (!empty($_POST['city'])) $addressParts[] = $_POST['city'];
                if (!empty($_POST['state'])) $addressParts[] = $_POST['state'];
                if (!empty($_POST['cep'])) $addressParts[] = 'CEP: ' . $_POST['cep'];
                $address = implode(', ', $addressParts);
            }

            $customer = [
                'name' => trim($_POST['name'] ?? ''),
                'whatsapp' => trim($_POST['whatsapp'] ?? ''),
                'email' => !empty($_POST['email']) ? trim($_POST['email']) : ($_SESSION['user_email'] ?? 'guest@example.com'), // Should be logged in
                'address' => $address,
                'cep' => $_POST['cep'] ?? '',
                'street' => $_POST['street'] ?? '',
                'number' => $_POST['number'] ?? '',
                'complement' => $_POST['complement'] ?? '',
                'neighborhood' => $_POST['neighborhood'] ?? '',
                'city' => $_POST['city'] ?? '',
                'state' => $_POST['state'] ?? '',
                'status' => 'pending_payment',
                'payment_status' => 'pending'
            ];

            if ($customer['email'] !== 'guest@example.com' && !filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo __('Invalid email format');
                exit;
            }

            $items = json_decode($_POST['items'], true);
            if (!is_array($items)) {
                $items = [];
            }
            $total = floatval($_POST['total']);
            $engine = new PaymentEngine();
            $checkoutProvider = $engine->getCheckoutProvider();
            $customer['payment_provider'] = $checkoutProvider ?? '';
            $orderId = createOrder($customer, $items, $total);
            if ($engine->hasEnabledProviders()) {
                $chargeResult = $engine->createPixCharge([
                    'order_id' => (int)$orderId,
                    'total' => $total,
                    'customer' => $customer,
                    'items' => $items,
                    'reference' => 'order_' . $orderId,
                    'description' => 'Pedido #' . $orderId
                ]);

                if (!empty($chargeResult['success'])) {
                    createPaymentForOrder((int)$orderId, $chargeResult['provider'] ?? ($checkoutProvider ?? ''), $total, $chargeResult);
                    updateOrder((int)$orderId, [
                        'payment_provider' => $chargeResult['provider'] ?? ($checkoutProvider ?? ''),
                        'payment_status' => $chargeResult['status'] ?? 'pending'
                    ]);
                } else {
                    createPaymentForOrder((int)$orderId, $checkoutProvider ?? '', $total, [
                        'transaction_id' => null,
                        'reference' => 'order_' . $orderId,
                        'status' => 'failed',
                        'payload' => ['error' => $chargeResult['error'] ?? 'Failed to create charge']
                    ]);
                    $_SESSION['payment_error_message'] = $chargeResult['error'] ?? 'Falha ao iniciar pagamento PIX.';
                }
            }
            header("Location: /order-success?id=$orderId");
            exit;
        }
        break;

    case '/order-success':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $order = getOrder($id);
            if ($order) {
                $payment = getPaymentByOrderId((int)$id);
                $paymentEngine = new PaymentEngine();
                $enabledPaymentProviders = $paymentEngine->getEnabledProviders();
                $paymentErrorMessage = $_SESSION['payment_error_message'] ?? '';
                unset($_SESSION['payment_error_message']);
                $template = __DIR__ . '/templates/order-success.php';
                require __DIR__ . '/templates/layout.php';
                break;
            }
        }
        header('Location: /');
        exit;
        break;

    case '/download-pdf':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $order = getOrder($id);
            if ($order) {
                $customer = [
                    'name' => $order['customer_name'],
                    'whatsapp' => $order['customer_whatsapp'],
                    'email' => $order['customer_email'],
                    'address' => $order['customer_address'],
                    'cep' => $order['customer_cep'] ?? '',
                    'street' => $order['customer_street'] ?? '',
                    'number' => $order['customer_number'] ?? '',
                    'neighborhood' => $order['customer_neighborhood'] ?? '',
                    'city' => $order['customer_city'] ?? '',
                    'state' => $order['customer_state'] ?? ''
                ];
                $items = json_decode($order['items_json'], true);
                $total = $order['total_amount'];
                
                require_once __DIR__ . '/src/generate_pdf.php';
                generateOrderPDF($id, $customer, $items, $total);
                exit;
            }
        }
        http_response_code(404);
        echo __('Order not found');
        break;

    case '/admin':
        if (!isAdmin()) {
            if (!isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            echo __('Access Denied. Login as admin@r2.com');
            exit;
        }
        
        $page = max(1, isset($_GET['p']) ? (int)$_GET['p'] : 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $adminSearchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        $products = getAllProducts(null, $perPage, $offset, $adminSearchTerm);
        $totalProducts = countProducts(null, $adminSearchTerm);
        $totalPages = max(1, ceil($totalProducts / $perPage));
        
        $orders = getAllOrders();
        $customers = getAdminCustomers();
        $categories = getAllCategories();
        $pages = getAllPages();
        $categories = getAllCategories();
        $adminUsers = getAdminUsers();
        $global_variations = getGlobalVariations();
        
        $productsCsvReport = $_SESSION['products_csv_report'] ?? null;
        unset($_SESSION['products_csv_report']);
        
        require __DIR__ . '/templates/admin/dashboard.php';
        break;

    case '/admin/products/csv-template':
        if (!isAdmin()) {
            if (!isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            echo __('Access Denied');
            exit;
        }
        streamProductsCsvTemplate();
        exit;
        break;

    case '/admin/products/export-csv':
        if (!isAdmin()) {
            if (!isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            echo __('Access Denied');
            exit;
        }
        exportProductsToCsv();
        exit;
        break;

    case '/admin/products/import-csv':
        if (!isAdmin()) {
            if (!isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            echo __('Access Denied');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin');
            exit;
        }
        $report = importProductsFromCsvUpload($_FILES['products_csv'] ?? []);
        $_SESSION['products_csv_report'] = $report;
        header('Location: /admin');
        exit;
        break;

    case '/admin/product-form':
        if (!isAdmin()) {
            if (!isLoggedIn()) {
                header('Location: /login');
                exit;
            }
            echo __('Access Denied');
            exit;
        }
        $id = $_GET['id'] ?? null;
        $product = [];
        if ($id) {
            $product = getProduct($id);
            if (!$product) {
                http_response_code(404);
                echo __('Product not found');
                exit;
            }
        }
        $categories = getAllCategories();
        $global_variations = getGlobalVariations();
        require __DIR__ . '/templates/admin/product-form.php';
        break;

    case '/admin/save-settings':
        if (!isAdmin()) die(__('Access Denied'));
        $whatsapp = $_POST['store_whatsapp'] ?? '';
        updateSetting('store_whatsapp', $whatsapp);

        $storeName = isset($_POST['store_name']) ? trim($_POST['store_name']) : '';
        updateSetting('store_name', $storeName !== '' ? $storeName : 'R2 Research Labs');
        
        $storeCurrency = isset($_POST['store_currency']) ? trim(strtoupper($_POST['store_currency'])) : 'BRL';
        updateSetting('store_currency', $storeCurrency !== '' ? $storeCurrency : 'BRL');
        
        $storeCurrencySymbol = isset($_POST['store_currency_symbol']) ? trim($_POST['store_currency_symbol']) : 'R$';
        updateSetting('store_currency_symbol', $storeCurrencySymbol !== '' ? $storeCurrencySymbol : 'R$');

        $storeFooterText = isset($_POST['store_footer_text']) ? trim($_POST['store_footer_text']) : '';
        updateSetting('store_footer_text', $storeFooterText !== '' ? $storeFooterText : 'R2 Research Labs - All Rights Reserved.');

        $brandMode = ($_POST['brand_mode'] ?? 'text') === 'image' ? 'image' : 'text';
        updateSetting('brand_mode', $brandMode);

        $brandLogoUrl = isset($_POST['brand_logo_url']) ? trim($_POST['brand_logo_url']) : '';
        $uploadedBrandLogo = uploadSingleImageFile(
            $_FILES['brand_logo_file'] ?? null,
            __DIR__ . '/public/uploads/branding/',
            '/uploads/branding',
            'logo_',
            ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'],
            ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml']
        );
        if ($uploadedBrandLogo !== '') {
            $brandLogoUrl = $uploadedBrandLogo;
        }
        updateSetting('brand_logo_url', $brandLogoUrl);

        $brandLogoWidth = max(20, min(1200, (int)($_POST['brand_logo_width'] ?? 160)));
        $brandLogoHeight = max(20, min(600, (int)($_POST['brand_logo_height'] ?? 48)));
        updateSetting('brand_logo_width', (string)$brandLogoWidth);
        updateSetting('brand_logo_height', (string)$brandLogoHeight);

        $themeHeaderBg = $_POST['theme_header_bg_text'] ?? $_POST['theme_header_bg'] ?? '#0f1115';
        $themePageBg = $_POST['theme_page_bg_text'] ?? $_POST['theme_page_bg'] ?? '#f3f4f6';
        $themeTextColor = $_POST['theme_text_color_text'] ?? $_POST['theme_text_color'] ?? '#1f2937';
        $themeHeaderBg = trim($themeHeaderBg);
        $themePageBg = trim($themePageBg);
        $themeTextColor = trim($themeTextColor);
        updateSetting('theme_header_bg', $themeHeaderBg !== '' ? $themeHeaderBg : '#0f1115');
        updateSetting('theme_page_bg', $themePageBg !== '' ? $themePageBg : '#f3f4f6');
        updateSetting('theme_text_color', $themeTextColor !== '' ? $themeTextColor : '#1f2937');

        $multilangEnabled = isset($_POST['i18n_multilang_enabled']) ? '1' : '0';
        updateSetting('i18n_multilang_enabled', $multilangEnabled);
        $singleLang = $_POST['i18n_single_lang'] ?? 'en';
        if (!in_array($singleLang, ['en', 'pt'], true)) {
            $singleLang = 'en';
        }
        updateSetting('i18n_single_lang', $singleLang);
        
        $enable_whatsapp = isset($_POST['enable_whatsapp_button']) ? '1' : '0';
        updateSetting('enable_whatsapp_button', $enable_whatsapp);
        
        $storeMode = $_POST['store_mode'] ?? 'ecommerce';
        if (!in_array($storeMode, ['ecommerce', 'catalog', 'informational'])) {
            $storeMode = 'ecommerce';
        }
        updateSetting('store_mode', $storeMode);
        
        // Email/SMTP settings
        $smtp_enabled = isset($_POST['smtp_enabled']) ? '1' : '0';
        updateSetting('smtp_enabled', $smtp_enabled);
        updateSetting('smtp_host', $_POST['smtp_host'] ?? 'smtp.resend.com');
        updateSetting('smtp_port', $_POST['smtp_port'] ?? '587');
        updateSetting('smtp_username', $_POST['smtp_username'] ?? 'resend');
        if (isset($_POST['smtp_password']) && $_POST['smtp_password'] !== '********') {
            updateSetting('smtp_password', $_POST['smtp_password']);
        }
        updateSetting('smtp_encryption', $_POST['smtp_encryption'] ?? 'tls'); // tls | ssl
        updateSetting('smtp_from_email', $_POST['smtp_from_email'] ?? '');
        updateSetting('smtp_from_name', $_POST['smtp_from_name'] ?? '');
        updateSetting('smtp_reply_to', $_POST['smtp_reply_to'] ?? '');
        updateSetting('contact_receive_email', $_POST['contact_receive_email'] ?? '');
        
        updateSetting('product_card_aspect_width', $_POST['product_card_aspect_width'] ?? '1');
        updateSetting('product_card_aspect_height', $_POST['product_card_aspect_height'] ?? '1');
        
        $productsPerPage = max(1, (int)($_POST['products_per_page'] ?? 15));
        updateSetting('products_per_page', (string)$productsPerPage);

        updateSetting('store_laudos_tab_enabled', $_POST['store_laudos_tab_enabled'] ?? '1');
        updateSetting('store_laudos_tab_title', $_POST['store_laudos_tab_title'] ?? 'Laudos (PDF)');

        header('Location: /admin');
        exit;
        break;

    case '/admin/save-payment-settings':
        if (!isAdmin()) die(__('Access Denied'));
        updateSetting('payment_provider_active', $_POST['payment_provider_active'] ?? 'mercadopago');
        $paymentModules = $_POST['payment_provider_modules'] ?? [];
        if (!is_array($paymentModules)) {
            $paymentModules = [];
        }
        $paymentModules = implode(',', array_map('trim', $paymentModules));
        updateSetting('payment_provider_modules', (string)$paymentModules);
        updateSetting('payment_mercadopago_access_token', $_POST['payment_mercadopago_access_token'] ?? '');
        updateSetting('payment_mercadopago_webhook_secret', $_POST['payment_mercadopago_webhook_secret'] ?? '');
        updateSetting('payment_manual_pix_key', $_POST['payment_manual_pix_key'] ?? '');
        updateSetting('payment_manual_pix_recipient_name', $_POST['payment_manual_pix_recipient_name'] ?? '');
        
        updateSetting('payment_instructions_enabled', isset($_POST['payment_instructions_enabled']) ? '1' : '0');
        updateSetting('payment_instructions_text', $_POST['payment_instructions_text'] ?? '');
        
        header('Location: /admin');
        exit;
        break;

    case '/admin/users/add':
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access Denied']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => __('Invalid email')]);
            exit;
        }
        
        $name = $input['name'] ?? null;
        $token = isset($input['token']) ? $input['token'] : null;
        
        if (promoteUserToAdmin($input['email'], $name, $token)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to promote user']);
        }
        exit;
        break;

    case '/admin/users/remove':
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access Denied']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }
        if ($input['id'] == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => __('Cannot revoke your own access')]);
            exit;
        }
        if (revokeAdminAccess((int)$input['id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to revoke access']);
        }
        exit;
        break;

    case '/admin/users/update-key':
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access Denied']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }
        
        $token = isset($input['token']) ? trim($input['token']) : '';
        if (setAdminBypassToken((int)$input['id'], $token)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to set bypass token']);
        }
        exit;
        break;

    case '/admin/test-smtp':
        if (!isAdmin()) {
            http_response_code(403);
            echo 'Access Denied';
            exit;
        }
        require_once __DIR__ . '/src/mailer.php';
        $toParam = $_GET['to'] ?? 'e@crisweiser.com';
        $to = filter_var($toParam, FILTER_VALIDATE_EMAIL) ? $toParam : 'e@crisweiser.com';
        $subject = __('SMTP Test') . ' — ' . getSetting('store_name', 'R2 Research Labs');
        $code = '123456';
        $html = renderLoginTokenEmail($code);
        $alt = "Teste de SMTP — Código: $code";
        $result = sendMailSMTP($to, $subject, $html, $alt);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
        break;

    case '/admin/save-banner-settings':
        if (!isAdmin()) die(__('Access Denied'));
        
        // Handle Banner Image Upload
        $bannerImageUrl = $_POST['banner_image_url'] ?? '';
        $uploadedBannerImage = uploadSingleImageFile(
            $_FILES['banner_image_file'] ?? null,
            __DIR__ . '/public/uploads/banners/',
            '/uploads/banners',
            'banner_'
        );
        if ($uploadedBannerImage !== '') {
            $bannerImageUrl = $uploadedBannerImage;
        }
        updateSetting('banner_image_url', $bannerImageUrl);

        updateSetting('banner_title', $_POST['banner_title'] ?? '');
        updateSetting('banner_subtitle', $_POST['banner_subtitle'] ?? '');
        updateSetting('banner_button_text', isset($_POST['banner_button_text']) ? trim($_POST['banner_button_text']) : '');
        updateSetting('banner_button_link', isset($_POST['banner_button_link']) ? trim($_POST['banner_button_link']) : '');
        updateSetting('banner_button2_text', isset($_POST['banner_button2_text']) ? trim($_POST['banner_button2_text']) : '');
        updateSetting('banner_button2_link', isset($_POST['banner_button2_link']) ? trim($_POST['banner_button2_link']) : '');
        
        // Handle Right Image Upload
        $bannerRightImageUrl = $_POST['banner_right_image_url'] ?? '';
        $uploadedBannerRightImage = uploadSingleImageFile(
            $_FILES['banner_right_image_file'] ?? null,
            __DIR__ . '/public/uploads/banners/',
            '/uploads/banners',
            'right_'
        );
        if ($uploadedBannerRightImage !== '') {
            $bannerRightImageUrl = $uploadedBannerRightImage;
        }
        updateSetting('banner_right_image_url', $bannerRightImageUrl);

        updateSetting('banner_overlay_color1', $_POST['banner_overlay_color1_text'] ?? $_POST['banner_overlay_color1'] ?? '#111827');
        updateSetting('banner_overlay_color2', $_POST['banner_overlay_color2_text'] ?? $_POST['banner_overlay_color2'] ?? '#1f2937');
        updateSetting('banner_overlay_enabled', isset($_POST['banner_overlay_enabled']) ? '1' : '0');
        updateSetting('banner_overlay_opacity', $_POST['banner_overlay_opacity'] ?? '30');
        header('Location: /admin');
        exit;
        break;

    case '/admin/save-product':
        if (!isAdmin()) die(__('Access Denied'));
        $data = $_POST;
        
        // Handle PDF Upload
        $uploadedPdf = uploadSingleImageFile(
            $_FILES['pdf_file'] ?? null,
            __DIR__ . '/public/uploads/pdfs/',
            '/uploads/pdfs',
            'pdf_',
            ['pdf'],
            ['application/pdf'],
            10485760 // 10MB limit
        );
        if ($uploadedPdf !== '') {
            $data['pdf_url'] = $uploadedPdf;
        }

        // Handle Digital File Upload
        $fileUploader = new FileUploader();
        $uploadedDigitalFile = $fileUploader->upload($_FILES['digital_file'] ?? null);
        if ($uploadedDigitalFile !== '') {
            $data['file_url'] = $uploadedDigitalFile;
        }

        // Handle variations
        $variations = json_decode($data['variations_json'] ?? '[]', true) ?: [];
        $data['variations_json'] = json_encode($variations);

        // Save custom variations to global if requested
        foreach ($variations as $var) {
            if (!empty($var['save_global'])) {
                // Ensure options are formatted correctly for global
                $globalOpts = [];
                foreach ($var['options'] as $opt) {
                    $globalOpts[] = ['name' => $opt['name'], 'price_modifier' => 0];
                }
                saveGlobalVariation(null, $var['name'], $globalOpts);
            }
        }

        global $pdo;
        $productId = 0;
        if (empty($data['id'])) {
            createProduct($data);
            $productId = (int)$pdo->lastInsertId();
        } else {
            $productId = (int)$data['id'];
            updateProduct($productId, $data);
        }

        if ($productId > 0) {
            $existingImages = normalizeImageUrlsFromForm($_POST['existing_images'] ?? []);
            $newUploadedImages = uploadProductImages($_FILES['product_images'] ?? []);
            $manualImageUrl = trim((string)($data['image_url'] ?? ''));

            $allImages = $existingImages;
            foreach ($newUploadedImages as $uploadedImage) {
                if (!in_array($uploadedImage, $allImages, true)) {
                    $allImages[] = $uploadedImage;
                }
            }

            // O campo 'image_url' (manualImageUrl) foi descontinuado na nova interface do product-form
            // Não devemos forçar a adição dele se ele não estiver mais presente no array $allImages
            // A imagem primária agora é apenas a primeira imagem do array resultante
            $primaryImageUrl = '';
            if (!empty($allImages)) {
                $primaryImageUrl = $allImages[0];
            }

            $primaryImageUrl = saveProductImages($productId, $allImages, $primaryImageUrl);
            updateProductImageUrl($productId, $primaryImageUrl);
        }

        header('Location: /admin');
        exit;
        break;

    case '/admin/delete-product':
        if (!isAdmin()) die(__('Access Denied'));
        $id = $_GET['id'];
        deleteProduct($id);
        header('Location: /admin');
        exit;
        break;

    case '/admin/save-category':
        if (!isAdmin()) die(__('Access Denied'));
        $data = $_POST;
        if (empty($data['id'])) {
            createCategory($data);
        } else {
            updateCategory($data['id'], $data);
        }
        header('Location: /admin');
        exit;
        break;

    case '/admin/delete-category':
        if (!isAdmin()) die(__('Access Denied'));
        $id = $_GET['id'];
        deleteCategory($id);
        header('Location: /admin');
        exit;
        break;

    case '/admin/save-global-variation':
        if (!isAdmin()) die(__('Access Denied'));
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        
        $options = [];
        if (isset($_POST['options']) && is_array($_POST['options'])) {
            foreach ($_POST['options'] as $opt) {
                $optName = trim($opt['name'] ?? '');
                if ($optName !== '') {
                    $options[] = [
                        'name' => $optName,
                        'price_modifier' => (float)($opt['price_modifier'] ?? 0)
                    ];
                }
            }
        }
        
        if ($name !== '') {
            saveGlobalVariation($id, $name, $options);
        }
        
        header('Location: /admin');
        exit;
        break;

    case '/admin/delete-global-variation':
        if (!isAdmin()) die(__('Access Denied'));
        $id = $_GET['id'];
        deleteGlobalVariation($id);
        header('Location: /admin');
        exit;
        break;

    case '/admin/save-page':
        if (!isAdmin()) die(__('Access Denied'));
        $data = $_POST;
        if (empty($data['id'])) {
            createPage($data);
        } else {
            updatePage($data['id'], $data);
        }
        header('Location: /admin');
        exit;
        break;

    case '/admin/delete-page':
        if (!isAdmin()) die(__('Access Denied'));
        $id = $_GET['id'];
        deletePage($id);
        header('Location: /admin');
        exit;
        break;

    case '/admin/order/update':
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Access Denied']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['id'])) {
            if (isset($input['items']) && is_array($input['items'])) {
                $input['items_json'] = json_encode($input['items']);
                unset($input['items']);
            }
            if (updateOrder($input['id'], $input)) {
                // If status is being updated to 'paid', trigger digital delivery
                if (isset($input['status']) && $input['status'] === 'paid') {
                    require_once __DIR__ . '/src/DeliveryManager.php';
                    require_once __DIR__ . '/src/EmailDigital.php';
                    
                    $deliveryManager = new DeliveryManager();
                    $deliveries = $deliveryManager->generateDeliveriesForOrder((int)$input['id']);
                    
                    if (!empty($deliveries)) {
                        $order = getOrder((int)$input['id']);
                        $emailDigital = new EmailDigital();
                        $emailResult = $emailDigital->sendDeliveryEmail($order, $deliveries);
                        
                        if ($emailResult['success']) {
                            $deliveryIds = array_column($deliveries, 'id');
                            $deliveryManager->markAsDelivered($deliveryIds);
                        }
                    }
                }
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
        }
        exit;
        break;

    case '/admin/order/delete':
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Access Denied']);
            exit;
        }
        $input = null;
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }
        if (!empty($input['id']) && deleteOrder($input['id'])) {
            if (!empty($input['redirect_to_admin'])) {
                header('Location: /admin');
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            }
        } else {
            if (!empty($input['redirect_to_admin'])) {
                header('Location: /admin');
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Delete failed']);
            }
        }
        exit;
        break;

    case '/account':
        if (!isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        $user = getUser($_SESSION['user_id']);
        if (!$user) {
            logout();
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'whatsapp' => trim($_POST['whatsapp'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'cep' => trim($_POST['cep'] ?? ''),
                'street' => trim($_POST['street'] ?? ''),
                'number' => trim($_POST['number'] ?? ''),
                'complement' => trim($_POST['complement'] ?? ''),
                'neighborhood' => trim($_POST['neighborhood'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? '')
            ];

            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error_message = __('Invalid email format');
            } else if (updateUser($_SESSION['user_id'], $data)) {
                $user = getUser($_SESSION['user_id']); // Refresh data
                $success_message = __('Profile updated successfully!');
            } else {
                $error_message = __('Failed to update profile.');
            }
        }
        
        $orders = getOrdersByEmail($user['email']);
        
        require_once __DIR__ . '/src/DeliveryManager.php';
        $deliveryManager = new DeliveryManager();
        foreach ($orders as &$o) {
            $o['digital_deliveries'] = $deliveryManager->getDeliveriesForOrder((int)$o['id']);
        }
        unset($o);

        $template = __DIR__ . '/templates/account.php';
        require __DIR__ . '/templates/layout.php';
        break;

    default:
        if (strpos($path, '/download/') === 0) {
            $token = trim(substr($path, strlen('/download/')), '/');
            if ($token !== '') {
                require_once __DIR__ . '/src/DownloadHandler.php';
                $handler = new DownloadHandler();
                $handler->processDownload($token);
                exit;
            }
        }

        if (strpos($path, '/webhooks/payment/') === 0) {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo 'Method Not Allowed';
                exit;
            }
            $provider = trim((string)substr($path, strlen('/webhooks/payment/')));
            if ($provider === '') {
                http_response_code(400);
                echo 'Invalid provider';
                exit;
            }

            $payload = file_get_contents('php://input') ?: '';
            $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
            $engine = new PaymentEngine();
            $parsed = $engine->parseWebhook($provider, $payload, $headers);
            if (empty($parsed['success'])) {
                http_response_code(401);
                echo 'Invalid webhook';
                exit;
            }

            $eventId = (string)($parsed['event_id'] ?? '');
            if ($eventId === '') {
                http_response_code(400);
                echo 'Missing event id';
                exit;
            }
            if (hasProcessedPaymentEvent($provider, $eventId)) {
                http_response_code(200);
                echo 'ok';
                exit;
            }
            registerPaymentEvent($provider, $eventId, $parsed['transaction_id'] ?? null, $parsed['raw'] ?? []);

            $payment = null;
            $transactionId = trim((string)($parsed['transaction_id'] ?? ''));
            if ($transactionId !== '') {
                $payment = getPaymentByProviderPaymentId($provider, $transactionId);
            }
            if (!$payment) {
                $reference = trim((string)($parsed['reference'] ?? ''));
                if ($reference !== '') {
                    $payment = getPaymentByProviderReference($provider, $reference);
                }
            }

            if ($payment) {
                updatePayment((int)$payment['id'], [
                    'status' => $parsed['status'] ?? 'pending',
                    'gateway_last_event' => $eventId,
                    'gateway_payload' => $parsed['raw'] ?? []
                ]);
                if (($parsed['status'] ?? '') === 'paid') {
                    markOrderAsPaidByPayment($payment);
                }
            }

            http_response_code(200);
            echo 'ok';
            exit;
        }

        // Admin Order Detail Route
        if (strpos($path, '/admin/order/') === 0) {
             if (!isAdmin()) {
                 header('Location: /login');
                 exit;
             }
             $id = substr($path, strlen('/admin/order/'));
             if (is_numeric($id)) {
                 $order = getOrder($id);
                 if ($order) {
                     $payment = getPaymentByOrderId($id);
                     $paymentEvents = [];
                     if ($payment && !empty($payment['provider_payment_id'])) {
                         $paymentEvents = getPaymentEventsByProviderPaymentId($payment['provider'], $payment['provider_payment_id']);
                     }
                     require __DIR__ . '/templates/admin/order_detail.php';
                     exit;
                 }
             }
        }

        if ($path === '/admin/customer/edit') {
            if (!isAdmin()) {
                header('Location: /login');
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $data = [
                        'name' => trim($_POST['name'] ?? ''),
                        'email' => trim($_POST['email'] ?? ''),
                        'whatsapp' => trim($_POST['whatsapp'] ?? ''),
                        'cep' => trim($_POST['cep'] ?? ''),
                        'street' => trim($_POST['street'] ?? ''),
                        'number' => trim($_POST['number'] ?? ''),
                        'complement' => trim($_POST['complement'] ?? ''),
                        'neighborhood' => trim($_POST['neighborhood'] ?? ''),
                        'city' => trim($_POST['city'] ?? ''),
                        'state' => trim($_POST['state'] ?? '')
                    ];
                    updateUser($id, $data);
                }
                header('Location: /admin/customer/' . $id);
                exit;
            }
        }

        if (strpos($path, '/admin/customer/') === 0) {
            if (!isAdmin()) {
                header('Location: /login');
                exit;
            }
            $id = substr($path, strlen('/admin/customer/'));
            if (is_numeric($id)) {
                $customer = getAdminCustomerDetails((int)$id);
                if ($customer) {
                    require __DIR__ . '/templates/admin/customer_detail.php';
                    exit;
                }
            }
        }

        if (strpos($path, '/produto/') === 0 && substr($path, -7) === '/single') {
            $slug = trim((string)substr($path, strlen('/produto/'), -7), '/');
            if ($slug !== '') {
                $product = getProductBySlug(rawurldecode($slug));
                if ($product) {
                    require __DIR__ . '/templates/public/product_single.php';
                    break;
                }
            }
            http_response_code(404);
            echo __('Product not found');
            break;
        }

        if (strpos($path, '/checkout/express/') === 0) {
            $slug = trim((string)substr($path, strlen('/checkout/express/')), '/');
            if ($slug !== '') {
                $product = getProductBySlug(rawurldecode($slug));
                if ($product) {
                    require __DIR__ . '/templates/public/checkout_express.php';
                    break;
                }
            }
            http_response_code(404);
            echo __('Product not found');
            break;
        }

        if (strpos($path, '/product/') === 0) {
            $slug = trim((string)substr($path, strlen('/product/')), '/');
            if ($slug !== '') {
                $product = getProductBySlug(rawurldecode($slug));
                if ($product) {
                    $template = __DIR__ . '/templates/product.php';
                    require __DIR__ . '/templates/layout.php';
                    break;
                }
            }
            http_response_code(404);
            echo __('Product not found');
            break;
        }

        // Check if it's a dynamic page
        // Remove leading slash
        $slug = trim($path, '/');
        // Handle sub-pages if necessary, but here simple slugs
        $page = getPageBySlug($slug);
        if ($page) {
             $template = __DIR__ . '/templates/page.php';
             require __DIR__ . '/templates/layout.php';
             break;
        }

        http_response_code(404);
        echo __('404 Not Found');
        break;
}
