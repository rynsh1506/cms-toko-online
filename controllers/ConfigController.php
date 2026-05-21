<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/LandingService.php';

$landingService = new LandingService($pdo);

// Proteksi: Hanya Admin
checkAdmin();

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Looping melalui inputan teks & color
    if (isset($_POST['config'])) {
        foreach ($_POST['config'] as $key => $value) {
            $safe_key = sanitize_input($key);
            $safe_value = sanitize_input($value); // Boleh XSS sanitize di sini atau saat render
            
            $landingService->updateConfigValue($safe_key, $safe_value);
        }
    }

    // Handle Upload Gambar (contoh untuk hero_image)
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['hero_image'];
        $allowed_mimes = ['image/jpeg', 'image/png'];
        
        // Cek MIME type
        $mime = mime_content_type($file['tmp_name']);
        if (in_array($mime, $allowed_mimes)) {
            // Dapatkan ekstensi
            $ext = ($mime === 'image/png') ? 'png' : 'jpg';
            
            // Nama acak dengan timestamp
            $new_filename = 'hero_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/';

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                // Hapus gambar lama jika ada (Optional, untuk kebersihan)
                $old_img = $landingService->getConfigValueByKey('hero_image');
                if ($old_img && file_exists($upload_dir . $old_img)) {
                    unlink($upload_dir . $old_img);
                }
                
                // Update database
                $landingService->updateHeroImage($new_filename);
            } else {
                $err = "Gagal memindahkan file yang diupload.";
            }
        } else {
            $err = "Format file tidak didukung. Harap gunakan JPG atau PNG.";
        }
    }

    if (isset($err)) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $err]);
            exit;
        }
        $_SESSION['error'] = $err;
    } else {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil diperbarui!']);
            exit;
        }
        $_SESSION['success'] = "Pengaturan berhasil diperbarui!";
    }
    
    redirect('index.php?page=admin_banners');
} else {
    redirect('index.php?page=admin_banners');
}
