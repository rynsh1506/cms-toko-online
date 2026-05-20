<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

// Proteksi: Hanya Admin
checkAdmin();

$action = $_GET['action'] ?? '';
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

if ($action === 'update_status') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $order_id = intval($_POST['order_id']);
        $status = sanitize_input($_POST['status']);

        // Check if valid status
        $allowed_statuses = ['pending', 'paid', 'shipped', 'done', 'cancelled'];
        if (in_array($status, $allowed_statuses)) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $order_id])) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true, 
                        'message' => "Status order #{$order_id} berhasil diperbarui menjadi " . ucfirst($status) . "!",
                        'status' => $status
                    ]);
                    exit;
                }
                $_SESSION['success'] = "Status order #{$order_id} berhasil diperbarui menjadi " . ucfirst($status) . "!";
            } else {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status pesanan.']);
                    exit;
                }
                $_SESSION['error'] = "Gagal memperbarui status pesanan.";
            }
        } else {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Status tidak valid.']);
                exit;
            }
            $_SESSION['error'] = "Status tidak valid.";
        }
    }
    redirect('index.php?page=admin_orders');
} else {
    redirect('index.php?page=admin_orders');
}
