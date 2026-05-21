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
}
