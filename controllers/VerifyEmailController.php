<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$token = sanitize_input($_GET['token'] ?? '');

if (empty($token)) {
    $_SESSION['error'] = "Token verifikasi tidak valid.";
    redirect('index.php?page=home');
}

$stmt = $pdo->prepare("SELECT id, email_verified_at FROM users WHERE verification_token = ?");
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

$_SESSION['success'] = "Selamat! Email Anda berhasil diverifikasi secara instan.";
redirect('index.php?page=home');
