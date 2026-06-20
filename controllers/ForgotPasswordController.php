<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthFlowService.php';

class ForgotPasswordController extends BaseController
{
    public function handle(): void
    {
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=forgot_password');
        }

        $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';
        $service = new AuthFlowService(new AuthService($this->pdo));

        $result = match ($action) {
            'send_code' => $service->forgotPassword($_POST),
            'verify_code' => $service->verifyResetCode($_POST),
            'reset_password' => $service->resetPassword($_POST),
            'resend_code' => $service->resendResetCode($_POST),
            default => ['success' => false, 'message' => 'Aksi tidak dikenali.', 'redirect_url' => 'index.php?page=forgot_password'],
        };

        // Apply session changes
        foreach (($result['session'] ?? []) as $key => $value) {
            $_SESSION[$key] = $value;
        }

        if ($this->isAjax()) {
            $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'redirect_url' => $result['redirect_url'],
            ]);
        }

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['flash'] ?? $result['message'];
        redirect($result['redirect_url']);
    }
}
