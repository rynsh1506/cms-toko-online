<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ProductService.php';
require_once __DIR__ . '/../services/CategoryService.php';

class CategoryController extends BaseController
{
    private const REDIRECT_URL = 'index.php?page=admin_categories';

    public function handle(): void
    {
        checkAdmin();

        $service = new CategoryService(new ProductService($this->pdo));
        $action = $_GET['action'] ?? '';

        $result = match ($action) {
            'add' => $this->requirePost(fn () => $service->create($_POST)),
            'edit' => $this->requirePost(fn () => $service->update($_POST)),
            'delete' => $service->delete(intval($_GET['id'] ?? 0)),
            default => null,
        };

        if ($result === null) {
            redirect(self::REDIRECT_URL);
        }

        $this->respondWithResult($result, self::REDIRECT_URL);
    }

    private function requirePost(callable $handler): ?array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        return $handler();
    }

    private function respondWithResult(array $result, string $redirectUrl): void
    {
        if ($this->isAjax()) {
            $this->json($result);
        }

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        redirect($redirectUrl);
    }
}
