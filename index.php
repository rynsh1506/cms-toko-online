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

    case 'page_builder':
        checkAdmin();
        require __DIR__ . '/views/admin/page_builder.php';
        break;

    case 'admin':
        // Gunakan middleware yang baru kita buat
        checkAdmin();
        require __DIR__ . '/views/admin/dashboard.php';
        break;

    default:
        // Halaman 404 sederhana
        http_response_code(404);
        echo "404 - Halaman tidak ditemukan.";
        break;
}
