<?php

/**
 * Mencegah serangan XSS dengan sanitasi string
 */
function sanitize_input($string) {
    if (empty($string)) return '';
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect ke halaman tertentu
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Mendapatkan base URL (opsional, untuk assets)
 * Bisa disesuaikan sesuai environment server Anda
 */
function base_url($path = '') {
    // Misalnya localhost/cms-toko-online
    // Sesuaikan ini dengan nama folder saat deploy
    $base = 'http://' . $_SERVER['HTTP_HOST'];
    $dirname = dirname($_SERVER['SCRIPT_NAME']);
    if ($dirname !== '/' && $dirname !== '\\') {
        $base .= $dirname;
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}
