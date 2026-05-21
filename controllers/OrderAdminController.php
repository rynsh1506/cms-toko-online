<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/OrderService.php';

class OrderAdminController extends BaseController
{
    public function handle(): void
    {
        checkAdmin();

        $action = $_GET['action'] ?? '';
        $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

        if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $order_id = intval($_POST['order_id']);
            $status = sanitize_input($_POST['status']);
            $allowed_statuses = ['pending', 'paid', 'shipped', 'done', 'cancelled'];

            if (!in_array($status, $allowed_statuses)) {
                $this->sendResponse($is_ajax, false, 'Status tidak valid.', 'index.php?page=admin_orders');
            }

            $orderService = new OrderService($this->pdo);

            try {
                // Semua transaksi dan sinkronisasi stok dieksekusi di service
                $orderService->updateStatusAndSyncStock($order_id, $status);

                $this->sendResponse($is_ajax, true, "Status order #{$order_id} berhasil diperbarui menjadi " . ucfirst($status) . "!", 'index.php?page=admin_orders', ['status' => $status]);
            } catch (\Exception $e) {
                $this->sendResponse($is_ajax, false, 'Gagal memperbarui status: ' . $e->getMessage(), 'index.php?page=admin_orders');
            }
        } else {
            redirect('index.php?page=admin_orders');
        }
    }

    private function sendResponse(bool $isAjax, bool $success, string $message, string $redirect, array $extra = []): void
    {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
            exit;
        }
        $_SESSION[$success ? 'success' : 'error'] = $message;
        redirect($redirect);
    }
}
