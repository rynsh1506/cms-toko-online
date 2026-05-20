<?php
require_once __DIR__ . '/config/db.php';

try {
    // Tabel Orders
    $sql_orders = "
    CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(50) NOT NULL,
        customer_address TEXT NOT NULL,
        total_price DECIMAL(10, 2) NOT NULL,
        unique_code INT NOT NULL,
        status ENUM('pending', 'paid', 'shipped', 'done') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql_orders);

    // Tabel Order Items
    $sql_order_items = "
    CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql_order_items);

    echo "<h3 style='color:green;'>Tabel 'orders' dan 'order_items' berhasil dibuat!</h3>";
    echo "<a href='index.php?page=home'>Ke Halaman Utama</a>";

} catch (\PDOException $e) {
    echo "<h3 style='color:red;'>Gagal setup tabel transaksi: " . $e->getMessage() . "</h3>";
}
