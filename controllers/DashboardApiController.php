<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

// Verify admin status
if (!isAuth() || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. Daily Earnings Trend (Past 7 Days)
    $earnings_trend = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $pdo->prepare("
            SELECT SUM(total_price) as total 
            FROM orders 
            WHERE DATE(created_at) = ? AND status NOT IN ('pending', 'cancelled')
        ");
        $stmt->execute([$date]);
        $total = $stmt->fetchColumn() ?? 0;
        
        $earnings_trend[] = [
            'date' => date('d M', strtotime($date)),
            'amount' => floatval($total)
        ];
    }

    // 2. Order Status Breakdown
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM orders 
        GROUP BY status
    ");
    $order_status_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $statuses = ['pending', 'paid', 'shipped', 'done', 'cancelled'];
    $order_status = [];
    foreach ($statuses as $status) {
        $order_status[$status] = intval($order_status_counts[$status] ?? 0);
    }

    // 3. Category Sales (Quantity sold per category)
    $stmt = $pdo->query("
        SELECT c.name as category_name, SUM(oi.quantity) as total_qty
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status NOT IN ('pending', 'cancelled')
        GROUP BY c.id
        ORDER BY total_qty DESC
    ");
    $category_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. User Registration Trend (Past 7 Days)
    $registration_trend = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE DATE(created_at) = ?
        ");
        $stmt->execute([$date]);
        $count = $stmt->fetchColumn() ?? 0;
        
        $registration_trend[] = [
            'date' => date('d M', strtotime($date)),
            'count' => intval($count)
        ];
    }

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
