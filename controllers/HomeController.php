<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/LandingService.php';
require_once __DIR__ . '/../services/ProductService.php';

class HomeController extends BaseController
{
    public function handle(): void
    {
        $pdo = $this->pdo;
        $landingService = new LandingService($pdo);
        $productService = new ProductService($pdo);

        // Fetch Configurations
        $configs = $landingService->getAllConfigs();
        $hero_title = $configs['hero_title'] ?? 'Selamat Datang di NusaBay';
        $hero_subtitle = $configs['hero_subtitle'] ?? 'Temukan barang impianmu dengan harga terbaik dan terjangkau di sini.';
        $primary_color = $configs['primary_color'] ?? '#6366f1';
        $hero_image = $configs['hero_image'] ?? '';

        // Fetch all active categories
        $categories = $productService->getCategories();

        // Fetch all active promotional banners
        $banners = $landingService->getActiveBanners();

        // Pagination, Category, and Search Filters Setup
        $search_query = isset($_GET['q']) ? trim(sanitize_input($_GET['q'])) : '';
        $active_category = isset($_GET['cat']) ? trim(sanitize_input($_GET['cat'])) : 'all';
        $page_num = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
        $items_per_page = 12;

        // Count total products matching filters
        $total_products = $productService->countProductsFiltered($active_category, $search_query);

        $total_pages = ceil($total_products / $items_per_page);
        $page_num = min($page_num, max(1, $total_pages));
        $offset = ($page_num - 1) * $items_per_page;

        // Fetch filtered products
        $products = $productService->getProductsFilteredPaginated($active_category, $search_query, $offset, $items_per_page);

        // Load the view
        require __DIR__ . '/../views/public/home.php';
    }
}
