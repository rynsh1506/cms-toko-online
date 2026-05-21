<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/DashboardService.php';

$dashboardService = new DashboardService($pdo);

// Verify admin status
if (!isAuth() || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. Daily Earnings Trend (Past 7 Days)
    $earnings_trend = $dashboardService->getEarningsTrend(7);

    // 2. Order Status Breakdown
    $order_status = $dashboardService->getOrderStatusCounts();

    // 3. Category Sales (Quantity sold per category)
    $category_sales = $dashboardService->getCategorySales();

    // 4. User Registration Trend (Past 7 Days)
    $registration_trend = $dashboardService->getRegistrationTrend(7);

    echo json_encode([
        'success' => true,
        'data' => [
            'earnings_trend' => $earnings_trend,
            'order_status' => $order_status,
            'category_sales' => $category_sales,
            'registration_trend' => $registration_trend
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
    ]);
}
