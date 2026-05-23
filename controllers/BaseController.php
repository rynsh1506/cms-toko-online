<?php

abstract class BaseController
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    abstract public function handle(): void;

    protected function isAjax(): bool
    {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || isset($_POST['ajax']) || isset($_GET['ajax']);
    }

    protected function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    protected function redirectTo(string $url): void
    {
        redirect($url);
    }

    protected function view(string $path, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require $path;
    }

    // --- FITUR BARU: Menghilangkan Redundansi ---
    protected function respond(array $result, string $redirectUrl): void
    {
        if ($this->isAjax()) {
            $this->json($result);
        }

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirectTo($redirectUrl);
    }

    protected function verifyCsrfToken(): void
    {
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

            if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                $this->respond([
                    'success' => false,
                    'message' => 'Validasi keamanan (CSRF Token) gagal. Silakan muat ulang halaman dan coba lagi.'
                ], $_SERVER['HTTP_REFERER'] ?? 'index.php?page=home');
            }
        }
    }
}
