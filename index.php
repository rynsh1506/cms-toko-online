<?php
session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

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
    require_once __DIR__ . '/controllers/' . $className . '.php';
    $controller = new $className($pdo);
    $controller->handle();
    exit;
}

if (isset($publicPageRoutes[$page])) {
    require_once __DIR__ . '/controllers/PublicPageController.php';
    $controller = new PublicPageController($pdo, $publicPageRoutes[$page]);
    $controller->handle();
    exit;
}

if (isset($adminPageRoutes[$page])) {
    require_once __DIR__ . '/controllers/AdminPageController.php';
    $controller = new AdminPageController($pdo, $adminPageRoutes[$page]);
    $controller->handle();
    exit;
}

http_response_code(404);
echo "404 - Halaman tidak ditemukan.";
