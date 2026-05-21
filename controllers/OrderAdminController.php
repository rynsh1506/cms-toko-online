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
            try {
                $pdo->beginTransaction();

                // Ambil data order untuk divalidasi dengan FOR UPDATE
                $stmtOrder = $pdo->prepare("SELECT status FROM orders WHERE id = ? FOR UPDATE");
                $stmtOrder->execute([$order_id]);
                $order = $stmtOrder->fetch();

                if (!$order) {
                    throw new Exception("Pesanan tidak ditemukan.");
                }

                $old_status = $order['status'];
                
                if ($old_status !== $status) {
                    // Update status pesanan
                    $stmtUpdate = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                    $stmtUpdate->execute([$status, $order_id]);

                    // LOGIKA SINKRONISASI STOK BERDASARKAN STATUS TRANSISI
                    
                    // 1. Dari status aktif (pending/paid/shipped/done) ke 'cancelled'
                    // -> Stok dikembalikan (ditambah)
                    if ($status === 'cancelled' && $old_status !== 'cancelled') {
                        $stmtItems = $pdo->prepare("SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?");
                        $stmtItems->execute([$order_id]);
                        $items = $stmtItems->fetchAll();

                        $stmtRestoreProductStock = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                        $stmtRestoreVariantStock = $pdo->prepare("UPDATE product_variants SET stock = stock + ? WHERE id = ?");

                        foreach ($items as $item) {
                            $qty = intval($item['quantity']);
                            $pId = intval($item['product_id']);
                            $vId = $item['variant_id'] ? intval($item['variant_id']) : null;

                            if ($vId) {
                                $stmtRestoreVariantStock->execute([$qty, $vId]);
                            } else {
                                $stmtRestoreProductStock->execute([$qty, $pId]);
                            }
                        }
                    }

                    // 2. Dari status 'cancelled' kembali ke status aktif (pending/paid/shipped/done)
                    // -> Stok dipotong ulang (dikurang)
                    if ($old_status === 'cancelled' && $status !== 'cancelled') {
                        $stmtItems = $pdo->prepare("SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?");
                        $stmtItems->execute([$order_id]);
                        $items = $stmtItems->fetchAll();

                        $stmtDeductProductStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                        $stmtDeductVariantStock = $pdo->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ?");

                        foreach ($items as $item) {
                            $qty = intval($item['quantity']);
                            $pId = intval($item['product_id']);
                            $vId = $item['variant_id'] ? intval($item['variant_id']) : null;

                            if ($vId) {
                                $stmtDeductVariantStock->execute([$qty, $vId]);
                            } else {
                                $stmtDeductProductStock->execute([$qty, $pId]);
                            }
                        }
                    }
                }

                $pdo->commit();

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

            } catch (\Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()]);
                    exit;
                }
                $_SESSION['error'] = "Gagal memperbarui status: " . $e->getMessage();
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
