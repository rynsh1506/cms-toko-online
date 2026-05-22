<?php

/**
 * Mencegah serangan XSS dengan sanitasi string
 */
function sanitize_input($string)
{
    if (empty($string)) return '';
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

/**
 * Escapes HTML for output encoding.
 */
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect ke halaman tertentu
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * Mendapatkan base URL (opsional, untuk assets)
 * Bisa disesuaikan sesuai environment server Anda
 */
function base_url($path = '')
{
    // Misalnya localhost/cms-toko-online
    // Sesuaikan ini dengan nama folder saat deploy
    $base = 'http://' . $_SERVER['HTTP_HOST'];
    $dirname = dirname($_SERVER['SCRIPT_NAME']);
    if ($dirname !== '/' && $dirname !== '\\') {
        $base .= $dirname;
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Cek apakah user sudah login
 */
function isAuth()
{
    return isset($_SESSION['user_id']);
}

/**
 * Cek apakah user adalah admin
 * Jika bukan, redirect ke halaman login
 */
function checkAdmin()
{
    if (!isAuth() || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        redirect('index.php?page=login');
    }
}

/**
 * Generate CSRF Token secara otomatis ke dalam Session
 */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Buat token acak yang kuat
    }
    return $_SESSION['csrf_token'];
}

/**
 * Mencetak input hidden CSRF untuk digunakan di dalam Form HTML
 */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}


function csrf_meta()
{
    return '<meta name="csrf-token" content="' . csrf_token() . '">';
}
