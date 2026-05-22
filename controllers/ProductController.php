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
        $this->verifyCsrfToken();

        $service = new ProductManagementService(
            new ProductService($this->pdo),
            __DIR__ . '/../uploads'
        );

        $action = $_GET['action'] ?? '';
        $result = match ($action) {
            'add' => $this->requirePost(fn() => $service->create($_POST, $_FILES)),
            'edit' => $this->requirePost(fn() => $service->update($_POST, $_FILES)),
            'delete' => $service->delete(intval($_GET['id'] ?? 0)),
            'fetch' => $this->fetchProducts(),
            default => null,
        };

        if ($result === null) {
            redirect(self::REDIRECT_URL);
        }

        $this->respondWithResult($result);
    }

    private function fetchProducts(): ?array
    {
        $productService = new ProductService($this->pdo);
        $filters = [
            'search' => $_GET['search'] ?? '',
            'category' => $_GET['category'] ?? 'all',
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
        ];
        $page = intval($_GET['p'] ?? 1);
        $perPage = intval($_GET['per_page'] ?? 10);
        
        $result = $productService->getAdminProductsPaginated($filters, $page, $perPage);
        $this->json(['success' => true, 'data' => $result['data'], 'meta' => $result]);
        return null;
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
