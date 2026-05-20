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
        
    // Nanti tambahkan case lain seperti 'product', 'cart', 'checkout', dsb.
    
    case 'admin':
        // Cek auth sederhana untuk admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            // Kalau belum login, nanti arahkan ke halaman login (akan dibuat di Phase 2)
            // Untuk sementara kita biarkan sederhana atau die()
            die("Akses Ditolak. Anda bukan Admin.");
        }
        require __DIR__ . '/views/admin/dashboard.php';
        break;

    default:
        // Halaman 404 sederhana
        http_response_code(404);
        echo "404 - Halaman tidak ditemukan.";
        break;
}
