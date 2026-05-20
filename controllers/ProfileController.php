<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

if (!isAuth() || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Akses ditolak.";
    redirect('index.php?page=login');
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update_profile') {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $bio = sanitize_input($_POST['bio'] ?? '');

        if (empty($name) || empty($email)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Nama dan Email wajib diisi.']);
                exit;
            }
            $_SESSION['error'] = "Nama dan Email wajib diisi.";
            redirect('index.php?page=admin_profile');
        }

        // Cek email duplikat selain dirinya sendiri
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmtCheck->execute([$email, $user_id]);
        if ($stmtCheck->fetch()) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Email sudah digunakan oleh akun lain.']);
                exit;
            }
            $_SESSION['error'] = "Email sudah digunakan oleh akun lain.";
            redirect('index.php?page=admin_profile');
        }

        // Handle Avatar Upload
        $avatar_url = null;
        $err = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['avatar'];
            
            // Validasi error upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $err = "Gagal mengunggah foto profil.";
            } else {
                // Validasi tipe file
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($file_ext, $allowed_exts)) {
                    $err = "Format file tidak valid. Harap gunakan format JPG, JPEG, PNG, atau GIF.";
                } elseif ($file['size'] > 2 * 1024 * 1024) {
                    // Validasi ukuran file (Max 2MB)
                    $err = "Ukuran file terlalu besar. Maksimal 2MB.";
                } else {
                    // Path upload
                    $upload_dir = __DIR__ . '/../uploads/avatars';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    // Generate nama file unik
                    $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_ext;
                    $upload_path = $upload_dir . '/' . $new_filename;

                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $avatar_url = 'uploads/avatars/' . $new_filename;

                        // Hapus avatar lama jika ada
                        $stmtOld = $pdo->prepare("SELECT avatar_url FROM users WHERE id = ?");
                        $stmtOld->execute([$user_id]);
                        $old_avatar = $stmtOld->fetchColumn();
                        if ($old_avatar && file_exists(__DIR__ . '/../' . $old_avatar)) {
                            @unlink(__DIR__ . '/../' . $old_avatar);
                        }
                    } else {
                        $err = "Gagal memindahkan file ke direktori tujuan.";
                    }
                }
            }
        }

        if ($err) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $err]);
                exit;
            }
            $_SESSION['error'] = $err;
            redirect('index.php?page=admin_profile');
        }

        try {
            if ($avatar_url) {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, phone = ?, bio = ?, avatar_url = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $phone, $bio, $avatar_url, $user_id]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, phone = ?, bio = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $phone, $bio, $user_id]);
            }

            // Update session data
            $_SESSION['name'] = $name;

            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui.']);
                exit;
            }
            $_SESSION['success'] = "Profil berhasil diperbarui.";
            redirect('index.php?page=admin_profile');

        } catch (\PDOException $e) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui profil: ' . $e->getMessage()]);
                exit;
            }
            $_SESSION['error'] = "Gagal memperbarui profil: " . $e->getMessage();
            redirect('index.php?page=admin_profile');
        }
    } 
    
    elseif ($action === 'change_password') {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Semua kolom password wajib diisi.']);
                exit;
            }
            $_SESSION['error'] = "Semua kolom password wajib diisi.";
            redirect('index.php?page=admin_profile');
        }

        if ($new_password !== $confirm_password) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Konfirmasi password baru tidak cocok.']);
                exit;
            }
            $_SESSION['error'] = "Konfirmasi password baru tidak cocok.";
            redirect('index.php?page=admin_profile');
        }

        // Ambil password lama dari DB
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $current_hash = $stmt->fetchColumn();

        if (!password_verify($old_password, $current_hash)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Password lama yang Anda masukkan salah.']);
                exit;
            }
            $_SESSION['error'] = "Password lama yang Anda masukkan salah.";
            redirect('index.php?page=admin_profile');
        }

        // Update password baru
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        try {
            $stmtUpdate = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmtUpdate->execute([$new_hash, $user_id]);
            
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Password berhasil diubah.']);
                exit;
            }
            $_SESSION['success'] = "Password berhasil diubah.";
            redirect('index.php?page=admin_profile');
        } catch (\PDOException $e) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gagal mengubah password: ' . $e->getMessage()]);
                exit;
            }
            $_SESSION['error'] = "Gagal mengubah password: " . $e->getMessage();
            redirect('index.php?page=admin_profile');
        }
    }
} else {
    redirect('index.php?page=admin_profile');
}
