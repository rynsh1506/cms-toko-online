<?php
require_once __DIR__ . '/config/db.php';

try {
    // 1. Buat tabel products
    $sql_create = "
    CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        image_url VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql_create);

    // 2. Seeding default products (jika tabel masih kosong)
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $sql_seed = "
        INSERT INTO products (name, description, price, stock, image_url) VALUES
        ('Kemeja Flannel Klasik', 'Kemeja flannel lengan panjang dengan bahan katun premium lembut dan nyaman dipakai sehari-hari.', 189000.00, 15, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&auto=format&fit=crop&q=60'),
        ('Sepatu Sneakers Casual', 'Sneakers kasual unisex yang cocok untuk segala aktivitas santai Anda. Ringan dan kuat.', 349000.00, 8, 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=500&auto=format&fit=crop&q=60'),
        ('Tas Ransel Outdoor', 'Tas ransel tahan air berkapasitas besar dengan slot laptop 15.6 inch. Cocok untuk kuliah atau camping.', 275000.00, 5, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=60'),
        ('Kaos Polos Cotton Combed 30s', 'Kaos polos adem menyerap keringat dengan pilihan jahitan rapi standard distro.', 59000.00, 50, 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500&auto=format&fit=crop&q=60')
        ";
        $pdo->exec($sql_seed);
        echo "<h3 style='color:green;'>Tabel 'products' berhasil dibuat dan data dummy ditambahkan!</h3>";
    } else {
        echo "<h3 style='color:blue;'>Tabel 'products' sudah ada dan terisi.</h3>";
    }

    echo "<a href='index.php?page=home'>Ke Halaman Utama</a>";

} catch (\PDOException $e) {
    echo "<h3 style='color:red;'>Gagal setup produk: " . $e->getMessage() . "</h3>";
}
