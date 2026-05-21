<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/OrderService.php';

class OrderCancelController extends BaseController
{
    public function handle(): void
    {
        $pdo = $this->pdo;
        $orderService = new OrderService($pdo);

        header('Content-Type: application/json');

        if (!isAuth()) {
            echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $order_id = intval($_POST['order_id'] ?? 0);
            $user_id = $_SESSION['user_id'];

            if ($order_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID pesanan tidak valid.']);
                exit;
            }

            try {
                $pdo->beginTransaction();

                // Ambil data order untuk divalidasi dengan FOR UPDATE
                $order = $orderService->getOrderForUpdate($order_id, $user_id);

                if (!$order) {
                    throw new Exception("Pesanan tidak ditemukan.");
                }

                if ($order['status'] !== 'pending') {
                    throw new Exception("Hanya pesanan berstatus 'Pending' yang dapat dibatalkan.");
                }

                // 1. Update status pesanan ke cancelled
                $orderService->cancelOrder($order_id);

                // 2. Kembalikan stok produk & variannya
                $items = $orderService->getOrderItems($order_id);

                foreach ($items as $item) {
                    $qty = intval($item['quantity']);
                    $pId = intval($item['product_id']);
                    $vId = $item['variant_id'] ? intval($item['variant_id']) : null;

                    if ($vId) {
                        // Kembalikan stok varian
                        $orderService->restoreVariantStock($vId, $qty);
                    } else {
                        // Produk normal tanpa varian
                        $orderService->restoreProductStock($pId, $qty);
                    }
                }

                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Pesanan Anda berhasil dibatalkan dan stok dikembalikan.'
                ]);
                exit;

            } catch (\Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }
    }
}
