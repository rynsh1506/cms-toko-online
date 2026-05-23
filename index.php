<?php
session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/Logger.php';

spl_autoload_register(function ($className) {
    // Check in controllers
    $controllerPath = __DIR__ . '/controllers/' . $className . '.php';
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
        return;
    }

    // Check in services
    $servicePath = __DIR__ . '/services/' . $className . '.php';
    if (file_exists($servicePath)) {
        require_once $servicePath;
        return;
    }

    // Check in config
    $configPath = __DIR__ . '/config/' . $className . '.php';
    if (file_exists($configPath)) {
        require_once $configPath;
        return;
    }
});

set_exception_handler(function (Throwable $exception) {
    Logger::error($exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
    ]);

    http_response_code(500);
    if (
        (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        isset($_POST['ajax']) || isset($_GET['ajax'])
    ) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem internal. Silakan coba lagi nanti.']);
    } else {
        echo '<h1>500 - Kesalahan Sistem Internal</h1><p>Terjadi kesalahan yang tidak terduga pada server. Harap hubungi Administrator atau coba lagi beberapa saat lagi.</p>';
    }
    exit;
});

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $message = "PHP Error [{$errno}]: {$errstr}";
    $context = [
        'file' => $errfile,
        'line' => $errline,
    ];

    if ($errno === E_USER_ERROR || $errno === E_RECOVERABLE_ERROR || $errno === E_COMPILE_ERROR || $errno === E_CORE_ERROR || $errno === E_ERROR) {
        Logger::error($message, $context);
        http_response_code(500);
        echo '<h1>500 - Kesalahan Sistem Internal</h1><p>Terjadi kesalahan sistem fatal.</p>';
        exit;
    } else {
        Logger::warning($message, $context);
    }

    return true;
});

$page = isset($_GET['page']) ? sanitize_input($_GET['page']) : 'home';

$controllerRoutes = [
    'home' => ['HomeController'],
    'auth_process' => ['AuthController'],
    'cart_process' => ['CartController'],
    'config_process' => ['ConfigController'],
    'checkout' => ['CheckoutViewController'],
    'checkout_process' => ['CheckoutController'],
    'validate_promo' => ['PromoValidateController'],
    'invoice' => ['InvoiceViewController'],
    'verify_email' => ['VerifyEmailController'],
    'order_cancel' => ['OrderCancelController'],
    'admin' => ['AdminDashboardController'],
    'admin_product_process' => ['ProductController'],
    'admin_profile_process' => ['ProfileController'],
    'admin_bank_process' => ['BankController'],
    'admin_order_process' => ['OrderAdminController'],
    'admin_promo_process' => ['PromoController'],
    'admin_banner_process' => ['BannerController'],
    'admin_category_process' => ['CategoryController'],
    'admin_variant_process' => ['VariantController'],
    'dashboard_api' => ['DashboardApiController'],
];

$publicPageRoutes = [
    'login' => 'login.php',
    'register' => 'register.php',
    'verify' => 'verify.php',
    'product_detail' => 'product_detail.php',
    'cart' => 'cart.php',
    'orders' => 'orders.php',
    'tos' => 'tos.php',
    'privacy' => 'privacy.php',
    'faq' => 'faq.php',
];

$adminPageRoutes = [
    'admin_settings' => 'settings.php',
    'admin_products' => 'products.php',
    'admin_banks' => 'bank_accounts.php',
    'admin_orders' => 'orders.php',
    'admin_promos' => 'promo_codes.php',
    'admin_banners' => 'banners.php',
    'admin_categories' => 'categories.php',
    'admin_profile' => 'profile.php',
];

$adminControllerPages = [
    'admin_product_process',
    'admin_profile_process',
    'admin_bank_process',
    'admin_order_process',
    'admin_promo_process',
    'admin_banner_process',
    'admin_category_process',
    'admin_variant_process',
    'dashboard_api',
];

if (isset($controllerRoutes[$page])) {
    if (in_array($page, $adminControllerPages, true)) {
        checkAdmin();
    }

    [$className] = $controllerRoutes[$page];
    $controller = new $className($pdo);
    $controller->handle();
    exit;
}

if (isset($publicPageRoutes[$page])) {
    $controller = new PublicPageController($pdo, $publicPageRoutes[$page]);
    $controller->handle();
    exit;
}

if (isset($adminPageRoutes[$page])) {
    $controller = new AdminPageController($pdo, $adminPageRoutes[$page]);
    $controller->handle();
    exit;
}

http_response_code(404);
echo "404 - Halaman tidak ditemukan.";
