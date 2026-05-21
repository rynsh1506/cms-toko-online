<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $code = sanitize_input($_POST['code'] ?? '');

    if (empty($email) || empty($code)) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Email dan kode verifikasi harus diisi!']);
            exit;
        }
        $_SESSION['error'] = "Email dan kode verifikasi harus diisi!";
        redirect('index.php?page=verify');
    }

    // Cari user berdasarkan email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Email tidak ditemukan!']);
            exit;
        }
        $_SESSION['error'] = "Email tidak ditemukan!";
        redirect('index.php?page=verify');
    }

    if ($user['email_verified_at'] !== null) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Email sudah terverifikasi! Silakan login.']);
            exit;
        }
        $_SESSION['success'] = "Email sudah terverifikasi!";
        redirect('index.php?page=login');
    }

    if ($user['verification_token'] !== $code) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Kode verifikasi salah atau kedaluwarsa!']);
            exit;
        }
        $_SESSION['error'] = "Kode verifikasi salah!";
        redirect('index.php?page=verify&email=' . urlencode($email));
    }

    // Update status verifikasi
    $stmtUpdate = $pdo->prepare("UPDATE users SET email_verified_at = NOW(), verification_token = NULL WHERE id = ?");
    $stmtUpdate->execute([$user['id']]);

    // Auto-login setelah verifikasi berhasil
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    // Hapus session email verifikasi
    unset($_SESSION['verify_email']);

    $redirect_url = ($user['role'] === 'admin') ? 'index.php?page=admin' : 'index.php?page=home';

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Verifikasi berhasil! Mengalihkan...',
            'redirect_url' => $redirect_url
        ]);
        exit;
    }
    $_SESSION['success'] = "Email Anda berhasil diverifikasi! Selamat datang kembali.";
    redirect($redirect_url);
} else {
    // GET request (Support link verifikasi lama jika ada)
    $token = sanitize_input($_GET['token'] ?? '');

    if (empty($token)) {
        $_SESSION['error'] = "Token verifikasi tidak valid.";
        redirect('index.php?page=home');
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "Token verifikasi tidak ditemukan atau kedaluwarsa.";
        redirect('index.php?page=home');
    }

    if ($user['email_verified_at'] !== null) {
        $_SESSION['success'] = "Email Anda sudah pernah diverifikasi.";
        redirect('index.php?page=home');
    }

    // Update status verifikasi
    $stmtUpdate = $pdo->prepare("UPDATE users SET email_verified_at = NOW(), verification_token = NULL WHERE id = ?");
    $stmtUpdate->execute([$user['id']]);

    // Auto-login
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    unset($_SESSION['verify_email']);

    $_SESSION['success'] = "Selamat! Email Anda berhasil diverifikasi secara instan.";
    redirect('index.php?page=home');
}
