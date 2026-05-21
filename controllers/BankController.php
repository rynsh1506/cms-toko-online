<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/OrderService.php';

class BankController extends BaseController
{
    public function handle(): void
    {
        $pdo = $this->pdo;
        $orderService = new OrderService($pdo);

        // Proteksi: Hanya Admin
        checkAdmin();

        $action = $_GET['action'] ?? '';
        $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

        if ($action === 'add') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $bank_name = sanitize_input($_POST['bank_name']);
                $account_number = sanitize_input($_POST['account_number']);
                $account_name = sanitize_input($_POST['account_name']);

                if ($orderService->addBankAccount($bank_name, $account_number, $account_name)) {
                    if ($is_ajax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Rekening Bank berhasil ditambahkan!']);
                        exit;
                    }
                    $_SESSION['success'] = "Rekening Bank berhasil ditambahkan!";
                } else {
                    if ($is_ajax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan rekening bank.']);
                        exit;
                    }
                    $_SESSION['error'] = "Gagal menyimpan rekening bank.";
                }
            }
            redirect('index.php?page=admin_banks');

        } elseif ($action === 'toggle') {
            $id = intval($_GET['id'] ?? 0);
            $bank = $orderService->getBankAccountById($id);

            if ($bank) {
                $new_status = $bank['is_active'] ? 0 : 1;
                if ($orderService->toggleBankAccountStatus($id, $new_status)) {
                    if ($is_ajax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Status rekening bank berhasil diubah!', 'is_active' => $new_status]);
                        exit;
                    }
                    $_SESSION['success'] = "Status rekening bank berhasil diubah!";
                } else {
                    if ($is_ajax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status rekening.']);
                        exit;
                    }
                    $_SESSION['error'] = "Gagal memperbarui status rekening.";
                }
            } else {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Rekening bank tidak ditemukan.']);
                    exit;
                }
                $_SESSION['error'] = "Rekening bank tidak ditemukan.";
            }
            redirect('index.php?page=admin_banks');

        } elseif ($action === 'delete') {
            $id = intval($_GET['id'] ?? 0);
            if ($orderService->deleteBankAccount($id)) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Rekening bank berhasil dihapus!']);
                    exit;
                }
                $_SESSION['success'] = "Rekening bank berhasil dihapus!";
            } else {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Gagal menghapus rekening bank.']);
                    exit;
                }
                $_SESSION['error'] = "Gagal menghapus rekening bank.";
            }
            redirect('index.php?page=admin_banks');

        } else {
            redirect('index.php?page=admin_banks');
        }
    }
}
