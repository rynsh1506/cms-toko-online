<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/OrderService.php';

class BankController extends BaseController
{
    private const REDIRECT_URL = 'index.php?page=admin_banks';

    public function handle(): void
    {
        // Proteksi: Hanya Admin
        checkAdmin();

        $action = $_GET['action'] ?? '';
        $orderService = new OrderService($this->pdo);

        // Gunakan match seperti di ProductController agar routing sangat rapi
        $result = match ($action) {
            'add' => $this->requirePost(fn() => $this->addBank($orderService)),
            'toggle' => $this->toggleBank($orderService),
            'delete' => $this->deleteBank($orderService),
            default => null,
        };

        if ($result === null) {
            redirect(self::REDIRECT_URL);
        }

        $this->respondWithResult($result);
    }

    private function addBank(OrderService $orderService): array
    {
        $bank_name = sanitize_input($_POST['bank_name'] ?? '');
        $account_number = sanitize_input($_POST['account_number'] ?? '');
        $account_name = sanitize_input($_POST['account_name'] ?? '');

        if ($orderService->addBankAccount($bank_name, $account_number, $account_name)) {
            return ['success' => true, 'message' => 'Rekening Bank berhasil ditambahkan!'];
        }
        
        return ['success' => false, 'message' => 'Gagal menyimpan rekening bank.'];
    }

    private function toggleBank(OrderService $orderService): array
    {
        $id = intval($_GET['id'] ?? 0);
        $bank = $orderService->getBankAccountById($id);

        if (!$bank) {
            return ['success' => false, 'message' => 'Rekening bank tidak ditemukan.'];
        }

        $new_status = $bank['is_active'] ? 0 : 1;
        if ($orderService->toggleBankAccountStatus($id, $new_status)) {
            return [
                'success' => true, 
                'message' => 'Status rekening bank berhasil diubah!', 
                'is_active' => $new_status
            ];
        }

        return ['success' => false, 'message' => 'Gagal memperbarui status rekening.'];
    }

    private function deleteBank(OrderService $orderService): array
    {
        $id = intval($_GET['id'] ?? 0);
        
        if ($orderService->deleteBankAccount($id)) {
            return ['success' => true, 'message' => 'Rekening bank berhasil dihapus!'];
        }
        
        return ['success' => false, 'message' => 'Gagal menghapus rekening bank.'];
    }

    private function requirePost(callable $handler): ?array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }
        return $handler();
    }

    private function respondWithResult(array $result): void
    {
        // Panggil helper bawaan BaseController (seperti di CartController/ProductController)
        if ($this->isAjax()) {
            $this->json($result);
        }

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        redirect(self::REDIRECT_URL);
    }
}