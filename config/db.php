<?php
require_once __DIR__ . '/env.php';

$db_url = $_ENV['DB_URL'] ?? getenv('DB_URL') ?? '';
if (empty($db_url)) {
    die("Kesalahan Konfigurasi: DB_URL belum diset di file .env");
}

// Parsing URL
$db = parse_url($db_url);

define('DB_HOST', $db['host']);
define('DB_USER', $db['user']);
define('DB_PASS', $db['pass']);
define('DB_NAME', ltrim($db['path'], '/'));
define('DB_PORT', $db['port']);

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => true,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    if (($_ENV['APP_ENV'] ?? '') === 'development') {
        die("Koneksi Database Gagal: " . $e->getMessage());
    } else {
        die("Koneksi Database Gagal. Silakan hubungi Administrator.");
    }
}
