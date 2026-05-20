<?php
require_once __DIR__ . '/config/db.php';

try {
    // 1. Buat tabel
    $sql_create = "
    CREATE TABLE IF NOT EXISTS landing_configs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_key VARCHAR(100) NOT NULL UNIQUE,
        content_value TEXT,
        type ENUM('text', 'image', 'color') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql_create);

    // 2. Seeding default configs (menggunakan IGNORE agar tidak duplikat jika tabel sudah ada)
    $sql_seed = "
    INSERT IGNORE INTO landing_configs (section_key, content_value, type) VALUES
    ('hero_title', 'Selamat Datang di Pro-Store', 'text'),
    ('hero_subtitle', 'Temukan barang impianmu dengan harga terbaik di sini.', 'text'),
    ('primary_color', '#2563eb', 'color'),
    ('hero_image', '', 'image')
    ";
    $pdo->exec($sql_seed);

    echo "<h3 style='color:green;'>Tabel 'landing_configs' berhasil dibuat dan di-seed!</h3>";
    echo "<a href='index.php?page=admin'>Ke Halaman Admin</a>";

} catch (\PDOException $e) {
    echo "<h3 style='color:red;'>Gagal setup konfigurasi: " . $e->getMessage() . "</h3>";
}
