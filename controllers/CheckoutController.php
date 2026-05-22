<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/OrderService.php';

class CheckoutController extends BaseController
{
    public function handle(): void
    {
        $this->verifyCsrfToken();
        $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

        if (!isAuth()) {
            $this->sendResponse($is_ajax, false, 'Silakan login terlebih dahulu.', 'index.php?page=login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_SESSION['cart'])) {
                $this->sendResponse($is_ajax, false, 'Keranjang Anda kosong.', 'index.php?page=home');
            }

            $orderService = new OrderService($this->pdo);

            try {
                // Semua proses transaksi dioper ke service
                $result = $orderService->processCheckout(
                    $_SESSION['user_id'],
                    $_POST,
                    $_SESSION['selected_cart_keys'] ?? [],
                    $_SESSION['cart'] ?? []
                );

                // Bersihkan item yang berhasil dibeli dari session
                foreach ($result['processed_keys'] as $key) {
                    unset($_SESSION['cart'][$key]);
                    unset($_SESSION['cart_meta'][$key]);
                }
                unset($_SESSION['selected_cart_keys']);

                $formatted_total = number_format($result['final_total'], 0, ',', '.');
                $success_msg = "<strong>Pesanan Berhasil Dibuat! (Order ID: #{$result['order_id']})</strong><br>
                                Silakan transfer tepat sebesar <strong>Rp $formatted_total</strong><br>
                                (Termasuk kode unik {$result['unique_code']} untuk verifikasi instan).";

                $this->sendResponse($is_ajax, true, $success_msg, 'index.php?page=invoice&id=' . $result['order_id'], ['redirect_url' => 'index.php?page=invoice&id=' . $result['order_id']]);
            } catch (\Exception $e) {
                $this->sendResponse($is_ajax, false, $e->getMessage(), 'index.php?page=checkout');
            }
        } else {
            redirect('index.php?page=checkout');
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
