<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ProductService.php';
require_once __DIR__ . '/../services/ProductManagementService.php';

class ProductController extends BaseController
{
    private const REDIRECT_URL = 'index.php?page=admin_products';

    public function handle(): void
    {
        checkAdmin();

        $service = new ProductManagementService(
            new ProductService($this->pdo),
            __DIR__ . '/../uploads'
        );

        $action = $_GET['action'] ?? '';
        $result = match ($action) {
            'add' => $this->requirePost(fn () => $service->create($_POST, $_FILES)),
            'edit' => $this->requirePost(fn () => $service->update($_POST, $_FILES)),
            'delete' => $service->delete(intval($_GET['id'] ?? 0)),
            default => null,
        };

        if ($result === null) {
            redirect(self::REDIRECT_URL);
        }

        $this->respondWithResult($result);
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
        if ($this->isAjax()) {
            $this->json($result);
        }

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        redirect(self::REDIRECT_URL);
    }
}
