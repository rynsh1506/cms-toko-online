<?php
session_start();

// Include database & helpers
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

// Ambil parameter 'page', default ke 'home'
$page = isset($_GET['page']) ? sanitize_input($_GET['page']) : 'home';

// Routing sederhana
switch ($page) {
    case 'home':
        require __DIR__ . '/views/public/home.php';
        break;
        
    case 'login':
        require __DIR__ . '/views/public/login.php';
        break;

    case 'register':
        require __DIR__ . '/views/public/register.php';
        break;

    case 'auth_process':
        require __DIR__ . '/controllers/AuthController.php';
        break;

    case 'cart':
        require __DIR__ . '/views/public/cart.php';
        break;

    case 'cart_process':
        require __DIR__ . '/controllers/CartController.php';
        break;

    case 'config_process':
        require __DIR__ . '/controllers/ConfigController.php';
        break;

    case 'checkout':
        require __DIR__ . '/views/public/checkout.php';
        break;

    case 'checkout_process':
        require __DIR__ . '/controllers/CheckoutController.php';
        break;

    case 'validate_promo':
        require __DIR__ . '/controllers/PromoValidateController.php';
        break;

    case 'orders':
        require __DIR__ . '/views/public/orders.php';
        break;

    case 'invoice':
        require __DIR__ . '/views/public/invoice.php';
        break;

    case 'verify_email':
        require __DIR__ . '/controllers/VerifyEmailController.php';
        break;

    case 'order_cancel':
        require __DIR__ . '/controllers/OrderCancelController.php';
        break;

    // Admin Pages (Rendered through shared layout)
    case 'admin':
        checkAdmin();
        $admin_page = 'dashboard.php';
        require __DIR__ . '/views/admin/layout.php';
        break;


    case 'admin_products':
        checkAdmin();
        $admin_page = 'products.php';
        require __DIR__ . '/views/admin/layout.php';
        break;

    case 'admin_banks':
        checkAdmin();
        $admin_page = 'bank_accounts.php';
        require __DIR__ . '/views/admin/layout.php';
        break;

    case 'admin_orders':
        checkAdmin();
        $admin_page = 'orders.php';
        require __DIR__ . '/views/admin/layout.php';
        break;

    case 'admin_promos':
        checkAdmin();
        $admin_page = 'promo_codes.php';
        require __DIR__ . '/views/admin/layout.php';
        break;

    case 'admin_banners':
        checkAdmin();
        $admin_page = 'banners.php';
        require __DIR__ . '/views/admin/layout.php';
        break;

    case 'admin_profile':
        checkAdmin();
        $admin_page = 'profile.php';
        require __DIR__ . '/views/admin/layout.php';
        break;

    // Admin Process Handlers
    case 'admin_product_process':
        checkAdmin();
        require __DIR__ . '/controllers/ProductController.php';
        break;

    case 'admin_profile_process':
        checkAdmin();
        require __DIR__ . '/controllers/ProfileController.php';
        break;

    case 'admin_bank_process':
        checkAdmin();
        require __DIR__ . '/controllers/BankController.php';
        break;

    case 'admin_order_process':
        checkAdmin();
        require __DIR__ . '/controllers/OrderAdminController.php';
        break;

    case 'admin_promo_process':
        checkAdmin();
        require __DIR__ . '/controllers/PromoController.php';
        break;

    case 'admin_banner_process':
        checkAdmin();
        require __DIR__ . '/controllers/BannerController.php';
        break;

    case 'dashboard_api':
        checkAdmin();
        require __DIR__ . '/controllers/DashboardApiController.php';
        break;

    default:
        // Halaman 404 sederhana
        http_response_code(404);
        echo "404 - Halaman tidak ditemukan.";
        break;
}
