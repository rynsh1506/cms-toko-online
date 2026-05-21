<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ProductService.php';
require_once __DIR__ . '/../services/VariantService.php';

class VariantController extends BaseController
{
    public function handle(): void
    {
        checkAdmin();

        $service = new VariantService(new ProductService($this->pdo));
        $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : sanitize_input($_POST['action'] ?? '');

        $result = match ($action) {
            'list' => $this->requireMethod('GET', fn () => $service->listByProduct(intval($_GET['product_id'] ?? 0))),
            'add' => $this->requireMethod('POST', fn () => $service->create($_POST)),
            'edit' => $this->requireMethod('POST', fn () => $service->update($_POST)),
            'delete' => $this->requireMethod('POST', fn () => $service->delete(intval($_POST['id'] ?? 0))),
            default => ['status' => 'error', 'message' => 'Aksi sistem tidak dikenali.'],
        };

        $this->json($result);
    }

    private function requireMethod(string $method, callable $handler): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            return ['status' => 'error', 'message' => 'Metode request tidak valid.'];
        }

        return $handler();
    }
}
