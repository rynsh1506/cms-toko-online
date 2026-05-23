<?php
require_once __DIR__ . '/BaseController.php';

class AdminPageController extends BaseController
{
    private string $adminPage;

    public function __construct(PDO $pdo, string $adminPage)
    {
        parent::__construct($pdo);
        $this->adminPage = $adminPage;
    }

    public function handle(): void
    {
        checkAdmin();
        $pdo = $this->pdo;
        $admin_page = $this->adminPage;

        // Fetch page-specific data using services to decouple view from database
        if ($admin_page === 'settings.php') {
            $landingService = new LandingService($pdo);
            $configs_raw = $landingService->getAllConfigsRaw();
            $configs = [];
            foreach ($configs_raw as $c) {
                $configs[$c['section_key']] = $c;
            }
        } elseif ($admin_page === 'products.php') {
            $productService = new ProductService($pdo);
            $categories = $productService->getCategories();
        } elseif ($admin_page === 'bank_accounts.php') {
            $orderService = new OrderService($pdo);
            $banks = $orderService->getAllBankAccounts();
        } elseif ($admin_page === 'orders.php') {
            $orderService = new OrderService($pdo);
            $orders = $orderService->getAllOrdersForAdmin();
            $all_items = $orderService->getAllOrderItemsForAdmin();
            $order_items = [];
            foreach ($all_items as $item) {
                $order_items[$item['order_id']][] = $item;
            }
        } elseif ($admin_page === 'promo_codes.php') {
            $orderService = new OrderService($pdo);
            $promos = $orderService->getAllPromoCodes();
        } elseif ($admin_page === 'banners.php') {
            $landingService = new LandingService($pdo);
            $banners = $landingService->getAllBanners();
        } elseif ($admin_page === 'categories.php') {
            $productService = new ProductService($pdo);
            $categories = $productService->getAllCategoriesDesc();
        } elseif ($admin_page === 'profile.php') {
            $authService = new AuthService($pdo);
            $admin = $authService->getUserById($_SESSION['user_id']);
        }

        require __DIR__ . '/../views/admin/layout.php';
    }
}
