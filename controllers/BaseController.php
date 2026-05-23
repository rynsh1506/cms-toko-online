<?php

/**
 * Abstract Base Controller class.
 * Provides helper functions for AJAX detection, JSON response, view rendering, and CSRF verification.
 */
abstract class BaseController
{
    /**
     * @var PDO Database connection instance.
     */
    protected PDO $pdo;

    /**
     * BaseController Constructor.
     * 
     * @param PDO $pdo Database connection.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Handle incoming request.
     * 
     * @return void
     */
    abstract public function handle(): void;

    /**
     * Check if current request is an AJAX request.
     * 
     * @return bool
     */
    protected function isAjax(): bool
    {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || isset($_POST['ajax']) || isset($_GET['ajax']);
    }

    /**
     * Output response payload as JSON and exit.
     * 
     * @param array $payload Response data.
     * @param int $statusCode HTTP Status Code.
     * @return void
     */
    protected function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    /**
     * Redirect to a specific URL.
     * 
     * @param string $url Target URL.
     * @return void
     */
    protected function redirectTo(string $url): void
    {
        redirect($url);
    }

    /**
     * Render a PHP view template file with data.
     * 
     * @param string $path Absolute path to the view file.
     * @param array $data Variables to extract and expose to the view template.
     * @return void
     */
    protected function view(string $path, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require $path;
    }

    /**
     * Helper to respond with either AJAX JSON payload or flash message and redirect.
     * 
     * @param array $result Success/error status and message array.
     * @param string $redirectUrl Redirect URL target.
     * @return void
     */
    protected function respond(array $result, string $redirectUrl): void
    {
        if ($this->isAjax()) {
            $this->json($result);
        }

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirectTo($redirectUrl);
    }

    /**
     * Verify the CSRF token for mutating request methods.
     * 
     * @return void
     */
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
