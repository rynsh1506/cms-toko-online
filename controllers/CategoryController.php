<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/ProductService.php';

$productService = new ProductService($pdo);

// Proteksi: Hanya Admin
checkAdmin();

$action = $_GET['action'] ?? '';
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

if ($action === 'add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = sanitize_input($_POST['name'] ?? '');
        $icon = sanitize_input($_POST['icon'] ?? '');
        $color = sanitize_input($_POST['color'] ?? '');

        if (empty($name)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Nama kategori wajib diisi.']);
                exit;
            }
            $_SESSION['error'] = "Nama kategori wajib diisi.";
            redirect('index.php?page=admin_categories');
        }

        // Slugify name
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        // Check if slug exists
        if ($productService->checkCategorySlugExists($slug)) {
            $slug .= '-' . time();
        }

        if ($productService->addCategory($name, $slug, $icon, $color)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Kategori berhasil ditambahkan!']);
                exit;
            }
            $_SESSION['success'] = "Kategori berhasil ditambahkan!";
        } else {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan kategori.']);
                exit;
            }
            $_SESSION['error'] = "Gagal menyimpan kategori.";
        }
    }
    redirect('index.php?page=admin_categories');

} elseif ($action === 'edit') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $name = sanitize_input($_POST['name'] ?? '');
        $icon = sanitize_input($_POST['icon'] ?? '');
        $color = sanitize_input($_POST['color'] ?? '');

        if (empty($name)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Nama kategori wajib diisi.']);
                exit;
            }
            $_SESSION['error'] = "Nama kategori wajib diisi.";
            redirect('index.php?page=admin_categories');
        }

        // Slugify name
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        // Check duplicate slug (excluding self)
        if ($productService->checkCategorySlugExistsExcludingSelf($slug, $id)) {
            $slug .= '-' . time();
        }

        if ($productService->updateCategory($id, $name, $slug, $icon, $color)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Kategori berhasil diperbarui!']);
                exit;
            }
            $_SESSION['success'] = "Kategori berhasil diperbarui!";
        } else {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui kategori.']);
                exit;
            }
            $_SESSION['error'] = "Gagal memperbarui kategori.";
        }
    }
    redirect('index.php?page=admin_categories');

} elseif ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    
    // Set associated products' category_id to NULL first to prevent constraint issues
    $productService->nullifyProductsCategory($id);

    if ($productService->deleteCategory($id)) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Kategori berhasil dihapus!']);
            exit;
        }
        $_SESSION['success'] = "Kategori berhasil dihapus!";
    } else {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus kategori.']);
            exit;
        }
        $_SESSION['error'] = "Gagal menghapus kategori.";
    }
    redirect('index.php?page=admin_categories');

} else {
    redirect('index.php?page=admin_categories');
}
