<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/OrderService.php';

class PromoController extends BaseController
{
    public function handle(): void
    {
        $this->verifyCsrfToken();
        $pdo = $this->pdo;
        $orderService = new OrderService($pdo);

        $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';
        $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'add' || $action === 'edit') {
                $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                $code = strtoupper(sanitize_input($_POST['code'] ?? ''));
                $discount_type = sanitize_input($_POST['discount_type'] ?? 'percentage');
                $discount_value = floatval($_POST['discount_value'] ?? 0);
                $min_order = floatval($_POST['min_order'] ?? 0);
                $max_uses = intval($_POST['max_uses'] ?? 100);
                $expires_at = sanitize_input($_POST['expires_at'] ?? '');
                $is_active = intval($_POST['is_active'] ?? 1);

                if (empty($code) || $discount_value <= 0 || empty($expires_at)) {
                    $err = "Semua field wajib diisi dengan benar.";
                }

                // Check unique code
                if (!isset($err)) {
                    if ($action === 'add') {
                        $exists = $orderService->checkPromoCodeExists($code);
                    } else {
                        $exists = $orderService->checkPromoCodeExistsExcludingSelf($code, $id);
                    }
                    if ($exists) {
                        $err = "Kode promo '$code' sudah terdaftar!";
                    }
                }

                if (!isset($err)) {
                    // Reformat expires_at
                    $expires_at_sql = date('Y-m-d H:i:s', strtotime($expires_at));

                    if ($action === 'add') {
                        $success = $orderService->addPromoCode($code, $discount_type, $discount_value, $min_order, $max_uses, $is_active, $expires_at_sql);
                        $msg = "Kode promo baru berhasil ditambahkan!";
                    } else {
                        $success = $orderService->updatePromoCode($id, $code, $discount_type, $discount_value, $min_order, $max_uses, $is_active, $expires_at_sql);
                        $msg = "Kode promo berhasil diperbarui!";
                    }

                    if ($success) {
                        if ($is_ajax) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'message' => $msg]);
                            exit;
                        }
                        $_SESSION['success'] = $msg;
                    } else {
                        $err = "Gagal memproses query database.";
                    }
                }

                if (isset($err)) {
                    if ($is_ajax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $err]);
                        exit;
                    }
                    $_SESSION['error'] = $err;
                }
            }
        } elseif ($action === 'delete') {
            $id = intval($_GET['id'] ?? 0);
            if ($orderService->deletePromoCode($id)) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Kode promo berhasil dihapus!']);
                    exit;
                }
                $_SESSION['success'] = "Kode promo berhasil dihapus!";
            } else {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Gagal menghapus kode promo.']);
                    exit;
                }
                $_SESSION['error'] = "Gagal menghapus kode promo.";
            }
        }

        redirect('index.php?page=admin_promos');
    }
}
