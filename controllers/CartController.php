<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ProductService.php';
require_once __DIR__ . '/../services/CartService.php';

class CartController extends BaseController
{
    public function handle(): void
    {
        $service = new CartService(new ProductService($this->pdo));
        $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';

        match ($action) {
            'add' => $this->handleAdd($service),
            'direct_checkout' => $this->handleDirectCheckout($service),
            'select_checkout' => $this->json($service->selectCheckout($_POST['keys'] ?? [])),
            'remove' => $this->handleRemove($service),
            'update' => $this->handleUpdate($service),
            'clear' => $this->handleClear($service),
            default => redirect('index.php?page=home'),
        };
    }

    private function handleAdd(CartService $service): void
    {
        $this->ensurePost();

        $result = $service->add($_POST);

        if ($this->isAjax()) {
            $this->json($result);
        }

        if ($result['status'] === 'success') {
            $_SESSION['success'] = $result['message'];
            redirect($result['redirect'] ?? 'index.php?page=cart');
        }

        $_SESSION['error'] = $result['message'];
        redirect('index.php?page=home');
    }

    private function handleDirectCheckout(CartService $service): void
    {
        $this->ensurePost();
        $this->json($service->directCheckout($_POST));
    }

    private function handleRemove(CartService $service): void
    {
        $cartKey = $_POST['cart_key'] ?? (isset($_GET['id']) ? intval($_GET['id']) . '-0' : '');
        $result = $service->remove($cartKey);

        if ($this->isAjax()) {
            $this->json($result);
        }

        redirect('index.php?page=cart');
    }

    private function handleUpdate(CartService $service): void
    {
        $this->ensurePost();

        $cartKey = $_POST['cart_key'] ?? '';
        $quantity = intval($_POST['qty'] ?? 1);
        $result = $service->updateQuantity($cartKey, $quantity);

        if ($this->isAjax()) {
            $this->json($result);
        }

        if (!empty($result['error_message'])) {
            $_SESSION['error'] = $result['error_message'];
        }

        redirect('index.php?page=cart');
    }

    private function handleClear(CartService $service): void
    {
        $result = $service->clear();

        if ($this->isAjax()) {
            $this->json($result);
        }

        redirect('index.php?page=cart');
    }

    private function ensurePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?page=home');
        }
    }
}
