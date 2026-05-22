<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthFlowService.php';

class AuthController extends BaseController
{
    public function handle(): void
    {
        $this->verifyCsrfToken();
        $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';

        if ($action === 'logout') {
            session_destroy();
            redirect('index.php?page=login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=home');
        }

        $service = new AuthFlowService(new AuthService($this->pdo));

        $result = match ($action) {
            'register' => $service->register($_POST),
            'login' => $service->login($_POST),
            'resend_code' => $service->resendCode($_POST),
            default => ['success' => false, 'message' => 'Aksi auth tidak dikenali.', 'redirect_url' => 'index.php?page=home'],
        };

        $this->applySessionChanges($result);

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

    private function applySessionChanges(array $result): void
    {
        foreach (($result['session'] ?? []) as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }
}
