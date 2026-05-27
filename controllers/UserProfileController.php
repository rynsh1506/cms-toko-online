<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ProfileService.php';

class UserProfileController extends BaseController
{
    public function handle(): void
    {
        $this->verifyCsrfToken();

        // Pastikan pengguna sudah login
        if (!isAuth()) {
            $_SESSION['error'] = "Akses ditolak. Silakan login kembali.";
            redirect('index.php?page=login');
        }

        $action = sanitize_input($_GET['action'] ?? '');
        $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profileService = new ProfileService($this->pdo);

            try {
                if ($action === 'update_profile') {
                    $profileService->updateProfile($_SESSION['user_id'], $_POST, $_FILES['avatar'] ?? null);
                    
                    // Update session name if changed
                    if (isset($_POST['name'])) {
                        $_SESSION['name'] = sanitize_input($_POST['name']);
                    }

                    $this->sendResponse($is_ajax, true, 'Profil berhasil diperbarui.', 'index.php?page=profile');
                } elseif ($action === 'change_password') {
                    $profileService->changePassword($_SESSION['user_id'], $_POST);

                    $this->sendResponse($is_ajax, true, 'Password berhasil diubah.', 'index.php?page=profile');
                } else {
                    $this->sendResponse($is_ajax, false, 'Aksi tidak valid.', 'index.php?page=profile');
                }
            } catch (\Exception $e) {
                $this->sendResponse($is_ajax, false, $e->getMessage(), 'index.php?page=profile');
            }
        } else {
            redirect('index.php?page=profile');
        }
    }

    /**
     * Helper to send response in HTML or AJAX json formats
     */
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
