<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/LandingService.php';

$landingService = new LandingService($pdo);

$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $title = sanitize_input($_POST['title'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $link_url = sanitize_input($_POST['link_url'] ?? '');
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $is_active = intval($_POST['is_active'] ?? 1);
        $image_url = '';

        // Handle Banner Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowed_mimes = ['image/jpeg', 'image/png'];
            
            $mime = mime_content_type($file['tmp_name']);
            if (in_array($mime, $allowed_mimes)) {
                $ext = ($mime === 'image/png') ? 'png' : 'jpg';
                $new_filename = 'banner_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $upload_dir = __DIR__ . '/../uploads/';

                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                    $image_url = 'uploads/' . $new_filename;
                } else {
                    $err = "Gagal memindahkan file upload.";
                }
            } else {
                $err = "Format gambar banner tidak didukung. Gunakan JPG atau PNG.";
            }
        } else {
            $err = "Gambar banner wajib diupload.";
        }

        if (!isset($err)) {
            if ($landingService->addBanner($title, $description, $image_url, $link_url, $is_active, $sort_order)) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Banner promosi berhasil ditambahkan!']);
                    exit;
                }
                $_SESSION['success'] = "Banner promosi berhasil ditambahkan!";
            } else {
                $err = "Gagal menyimpan banner ke database.";
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

    } elseif ($action === 'edit') {
        $id = intval($_POST['id']);
        $title = sanitize_input($_POST['title'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $link_url = sanitize_input($_POST['link_url'] ?? '');
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $is_active = intval($_POST['is_active'] ?? 1);

        // Fetch current banner
        $banner = $landingService->getBannerById($id);

        if (!$banner) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Banner tidak ditemukan.']);
                exit;
            }
            $_SESSION['error'] = "Banner tidak ditemukan.";
            redirect('index.php?page=admin_banners');
        }

        $image_url = $banner['image_url'];
        $err = null;

        // Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowed_mimes = ['image/jpeg', 'image/png'];
            
            $mime = mime_content_type($file['tmp_name']);
            if (in_array($mime, $allowed_mimes)) {
                $ext = ($mime === 'image/png') ? 'png' : 'jpg';
                $new_filename = 'banner_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $upload_dir = __DIR__ . '/../uploads/';

                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                    // Delete old banner image file
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
                $err = "Format gambar banner tidak didukung. Gunakan JPG/PNG.";
            }
        }

        if (!$err) {
            if ($landingService->updateBanner($id, $title, $description, $image_url, $link_url, $is_active, $sort_order)) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Banner promosi berhasil diperbarui!']);
                    exit;
                }
                $_SESSION['success'] = "Banner promosi berhasil diperbarui!";
            } else {
                $err = "Gagal memperbarui database.";
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
} elseif ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    $banner = $landingService->getBannerById($id);

    if ($banner) {
        $image_url = $banner['image_url'];
        // Delete image file
        if ($image_url && strpos($image_url, 'uploads/') === 0) {
            $old_path = __DIR__ . '/../' . $image_url;
            if (file_exists($old_path)) {
                unlink($old_path);
            }
        }

        if ($landingService->deleteBanner($id)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Banner berhasil dihapus!']);
                exit;
            }
            $_SESSION['success'] = "Banner berhasil dihapus!";
        }
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus banner.']);
        exit;
    }
    $_SESSION['error'] = "Gagal menghapus banner.";
}

redirect('index.php?page=admin_banners');
