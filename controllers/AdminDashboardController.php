<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/DashboardService.php';

// Pastikan admin sudah login
if (!isAuth() || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Akses ditolak. Anda bukan admin.";
    redirect('index.php?page=login');
}

$dashboardService = new DashboardService($pdo);

// Fetch advanced KPI stats
$kpi_stats = $dashboardService->getKPIStats();
$total_products = $kpi_stats['total_products'];
$total_orders = $kpi_stats['total_orders'];
$total_income = $kpi_stats['total_income'];
$pending_orders = $kpi_stats['pending_orders'];
$out_of_stock = $kpi_stats['out_of_stock'];

// Recent orders
$recent_orders = $dashboardService->getRecentOrders(5);

// Render the view through layout
$admin_page = 'dashboard.php';
require __DIR__ . '/../views/admin/layout.php';
