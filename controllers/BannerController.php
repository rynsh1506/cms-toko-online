<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/LandingService.php';

class BannerController extends BaseController
{
    public function handle(): void
    {
        checkAdmin();
        $this->verifyCsrfToken();
        $landingService = new LandingService($this->pdo);
        $action = sanitize_input($_GET['action'] ?? '');
        $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($action === 'add') {
                    $landingService->addBannerWithImage($_POST, $_FILES['image'] ?? null);
                    $this->sendResponse($is_ajax, true, 'Banner promosi berhasil ditambahkan!', 'index.php?page=admin_banners');
                } elseif ($action === 'edit') {
                    $landingService->updateBannerWithImage($_POST, $_FILES['image'] ?? null);
                    $this->sendResponse($is_ajax, true, 'Banner promosi berhasil diperbarui!', 'index.php?page=admin_banners');
                }
            } elseif ($action === 'delete') {
                $id = intval($_GET['id'] ?? 0);
                $landingService->deleteBannerWithImage($id);
                $this->sendResponse($is_ajax, true, 'Banner berhasil dihapus!', 'index.php?page=admin_banners');
            }
        } catch (\Exception $e) {
            $this->sendResponse($is_ajax, false, $e->getMessage(), 'index.php?page=admin_banners');
        }

        // Jika tidak masuk kondisi aksi di atas
        redirect('index.php?page=admin_banners');
    }

    private function sendResponse(bool $isAjax, bool $success, string $message, string $redirect): void
    {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $message]);
            exit;
        }
        $_SESSION[$success ? 'success' : 'error'] = $message;
        redirect($redirect);
    }
}
