<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/OrderService.php';

class OrderAdminController extends BaseController
{
    public function handle(): void
    {
        $pdo = $this->pdo;
        $orderService = new OrderService($pdo);

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
                        $order = $orderService->lockOrderForUpdate($order_id);

                        if (!$order) {
                            throw new Exception("Pesanan tidak ditemukan.");
                        }

                        $old_status = $order['status'];

                        if ($old_status !== $status) {
                            // Update status pesanan
                            $orderService->updateOrderStatus($order_id, $status);

                            // LOGIKA SINKRONISASI STOK BERDASARKAN STATUS TRANSISI

                            // 1. Dari status aktif (pending/paid/shipped/done) ke 'cancelled'
                            // -> Stok dikembalikan (ditambah)
                            if ($status === 'cancelled' && $old_status !== 'cancelled') {
                                $items = $orderService->getOrderItems($order_id);

                                foreach ($items as $item) {
                                    $qty = intval($item['quantity']);
                                    $pId = intval($item['product_id']);
                                    $vId = $item['variant_id'] ? intval($item['variant_id']) : null;

                                    if ($vId) {
                                        $orderService->restoreVariantStock($vId, $qty);
                                    } else {
                                        $orderService->restoreProductStock($pId, $qty);
                                    }
                                }
                            }

                            // 2. Dari status 'cancelled' kembali ke status aktif (pending/paid/shipped/done)
                            // -> Stok dipotong ulang (dikurang)
                            if ($old_status === 'cancelled' && $status !== 'cancelled') {
                                $items = $orderService->getOrderItems($order_id);

                                foreach ($items as $item) {
                                    $qty = intval($item['quantity']);
                                    $pId = intval($item['product_id']);
                                    $vId = $item['variant_id'] ? intval($item['variant_id']) : null;

                                    if ($vId) {
                                        $orderService->deductVariantStock($vId, $qty);
                                    } else {
                                        $orderService->deductProductStock($pId, $qty);
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
    }
}
