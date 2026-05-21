<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/LandingService.php';
require_once __DIR__ . '/../services/OrderService.php';

// Wajib Login
if (!isAuth()) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu.";
    redirect('index.php?page=login');
}

$order_id = intval($_GET['id'] ?? 0);
if ($order_id <= 0) {
    redirect('index.php?page=home');
}

$orderService = new OrderService($pdo);
$landingService = new LandingService($pdo);

// Fetch order with bank details
$order = $orderService->getOrderWithBankDetails($order_id);

if (!$order) {
    $_SESSION['error'] = "Pesanan tidak ditemukan.";
    redirect('index.php?page=home');
}

// Robustness: pastikan hanya pemilik pesanan atau admin yang dapat melihat invoice ini
if ($order['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk melihat pesanan ini.";
    redirect('index.php?page=home');
}

// Fetch Configurations for Dynamic Styles
$configs = $landingService->getAllConfigs();
$primary_color = $configs['primary_color'] ?? '#6366f1';

// Fetch order items joined with product info
$items = $orderService->getOrderItemsWithProductInfo($order_id);

// Persiapan Link WhatsApp
$admin_phone = $orderService->getAdminPhone();
$wa_message = "Halo Admin,\nSaya ingin melakukan konfirmasi pembayaran untuk pesanan berikut:\n\n"
            . "• Order ID: #" . $order['id'] . "\n"
            . "• Nama Penerima: " . $order['customer_name'] . "\n"
            . "• Total Pembayaran: Rp " . number_format($order['total_price'], 0, ',', '.') . "\n"
            . "• Status: " . ucfirst($order['status']) . "\n\n"
            . "Mohon untuk segera dikonfirmasi dan diproses. Terima kasih!";
$wa_link = "https://wa.me/" . $admin_phone . "?text=" . urlencode($wa_message);

require __DIR__ . '/../views/public/invoice.php';
