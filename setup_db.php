<?php
require_once __DIR__ . '/config/db.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "<h3 style='color:green;'>Tabel 'users' berhasil dibuat atau sudah ada!</h3>";
    echo "<a href='index.php?page=register'>Ke Halaman Register</a>";

} catch (\PDOException $e) {
    echo "<h3 style='color:red;'>Gagal membuat tabel: " . $e->getMessage() . "</h3>";
}
