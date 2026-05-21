<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/ProductService.php';

// Proteksi: Hanya Admin
checkAdmin();

$productService = new ProductService($pdo);

$action = $_GET['action'] ?? '';
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

if ($action === 'add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $image_url = '';

        // Handle Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowed_mimes = ['image/jpeg', 'image/png'];
            
            $mime = mime_content_type($file['tmp_name']);
            if (in_array($mime, $allowed_mimes)) {
                $ext = ($mime === 'image/png') ? 'png' : 'jpg';
                $new_filename = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $upload_dir = __DIR__ . '/../uploads/';

                // Ensure uploads folder exists
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                    $image_url = 'uploads/' . $new_filename;
                } else {
                    $err = "Gagal memindahkan file upload.";
                }
            } else {
                $err = "Format file tidak didukung. Harap gunakan JPG atau PNG.";
            }
        } else {
            $err = "Gambar produk wajib diupload.";
        }

        if (!isset($err)) {
            if ($productService->addProduct($category_id, $name, $description, $price, $stock, $image_url)) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Produk berhasil ditambahkan!']);
                    exit;
                }
                $_SESSION['success'] = "Produk berhasil ditambahkan!";
            } else {
                $err = "Gagal menambahkan produk ke database.";
            }
        }

        if (isset($err)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $err]);
                exit;
            }
            $_SESSION['error'] = $err;
        }
    }
    redirect('index.php?page=admin_products');

} elseif ($action === 'edit') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id']);
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);

        // Fetch current product
        $product = $productService->getProductById($id);

        if (!$product) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
                exit;
            }
            $_SESSION['error'] = "Produk tidak ditemukan.";
            redirect('index.php?page=admin_products');
        }

        $image_url = $product['image_url'];
        $err = null;

        // Handle Image Upload if new image is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowed_mimes = ['image/jpeg', 'image/png'];
            
            $mime = mime_content_type($file['tmp_name']);
            if (in_array($mime, $allowed_mimes)) {
                $ext = ($mime === 'image/png') ? 'png' : 'jpg';
                $new_filename = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $upload_dir = __DIR__ . '/../uploads/';

                // Ensure uploads folder exists
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                    // Delete old image if it is stored locally
                    if ($image_url && strpos($image_url, 'uploads/') === 0) {
                        $old_path = __DIR__ . '/../' . $image_url;
                        if (file_exists($old_path)) {
                            unlink($old_path);
                        }
                    }
                    $image_url = 'uploads/' . $new_filename;
                } else {
                    $err = "Gagal memindahkan file upload.";
                }
            } else {
                $err = "Format file tidak didukung. Harap gunakan JPG atau PNG.";
            }
        }

        if (!$err) {
            if ($productService->updateProduct($id, $category_id, $name, $description, $price, $stock, $image_url)) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Produk berhasil diperbarui!']);
                    exit;
                }
                $_SESSION['success'] = "Produk berhasil diperbarui!";
            } else {
                $err = "Gagal memperbarui produk.";
            }
        }

        if ($err) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $err]);
                exit;
            }
            $_SESSION['error'] = $err;
        }
    }
    redirect('index.php?page=admin_products');

} elseif ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    $product = $productService->getProductById($id);

    if ($product) {
        $image_url = $product['image_url'];
        // Delete image file from server
        if ($image_url && strpos($image_url, 'uploads/') === 0) {
            $path = __DIR__ . '/../' . $image_url;
            if (file_exists($path)) {
                unlink($path);
            }
        }

        if ($productService->deleteProduct($id)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Produk berhasil dihapus!']);
                exit;
            }
            $_SESSION['success'] = "Produk berhasil dihapus!";
        } else {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus produk dari database.']);
                exit;
            }
            $_SESSION['error'] = "Gagal menghapus produk dari database.";
        }
    } else {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
            exit;
        }
        $_SESSION['error'] = "Produk tidak ditemukan.";
    }
    redirect('index.php?page=admin_products');

} else {
    redirect('index.php?page=admin_products');
}
