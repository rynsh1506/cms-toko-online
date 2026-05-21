<?php
require_once __DIR__ . '/BaseController.php';

class PromoValidateController extends BaseController
{
    public function handle(): void
    {
        $pdo = $this->pdo;
        header('Content-Type: application/json');
        require_once __DIR__ . '/../config/db.php';
        require_once __DIR__ . '/../config/helpers.php';
        require_once __DIR__ . '/../services/OrderService.php';

        $orderService = new OrderService($pdo);

        if (!isAuth()) {
            echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
            exit;
        }

        $code = strtoupper(sanitize_input($_POST['code'] ?? $_GET['code'] ?? ''));
        $total_price = floatval($_POST['total_price'] ?? $_GET['total_price'] ?? 0);

        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Masukkan kode promo terlebih dahulu.']);
            exit;
        }

        try {
            $promo = $orderService->getPromoCodeByCode($code);

            if (!$promo) {
                echo json_encode(['success' => false, 'message' => 'Kode promo tidak valid atau tidak terdaftar.']);
                exit;
            }

            if (!$promo['is_active']) {
                echo json_encode(['success' => false, 'message' => 'Kode promo ini sudah tidak aktif.']);
                exit;
            }

            if (strtotime($promo['expires_at']) < time()) {
                echo json_encode(['success' => false, 'message' => 'Kode promo ini sudah kedaluwarsa.']);
                exit;
            }

            if ($promo['used_count'] >= $promo['max_uses']) {
                echo json_encode(['success' => false, 'message' => 'Kuota pemakaian kode promo ini sudah habis.']);
                exit;
            }

            if ($total_price < $promo['min_order']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Minimum belanja untuk menggunakan promo ini adalah Rp ' . number_format($promo['min_order'], 0, ',', '.')
                ]);
                exit;
            }

            // Calculate discount amount
            $discount_amount = 0;
            if ($promo['discount_type'] === 'percentage') {
                $discount_amount = ($promo['discount_value'] / 100) * $total_price;
            } else {
                $discount_amount = $promo['discount_value'];
            }

            // Discount cannot exceed order total
            if ($discount_amount > $total_price) {
                $discount_amount = $total_price;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Kode promo berhasil diterapkan!',
                'promo_id' => $promo['id'],
                'code' => $promo['code'],
                'discount_type' => $promo['discount_type'],
                'discount_value' => $promo['discount_value'],
                'discount_amount' => $discount_amount
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }
}
