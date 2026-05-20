<?php
require_once __DIR__ . '/config/db.php';

try {
    // Disable foreign key checks to safely drop tables
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // Drop tables if they exist
    $tables = ['order_items', 'orders', 'bank_accounts', 'products', 'landing_configs', 'users'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`;");
    }

    echo "Tabel lama berhasil dihapus.<br>";

    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 1. Users Table
    $pdo->exec("
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        avatar_url VARCHAR(255) DEFAULT NULL,
        email_verified_at DATETIME DEFAULT NULL,
        verification_token VARCHAR(100) DEFAULT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        bio TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'users' berhasil dibuat.<br>";

    // 2. Products Table
    $pdo->exec("
    CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        image_url VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'products' berhasil dibuat.<br>";

    // 3. Bank Accounts Table
    $pdo->exec("
    CREATE TABLE bank_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bank_name VARCHAR(100) NOT NULL,
        account_number VARCHAR(100) NOT NULL,
        account_name VARCHAR(255) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'bank_accounts' berhasil dibuat.<br>";

    // 4. Orders Table
    $pdo->exec("
    CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(50) NOT NULL,
        customer_address TEXT NOT NULL,
        total_price DECIMAL(10, 2) NOT NULL,
        unique_code INT NOT NULL,
        bank_account_id INT NULL,
        status ENUM('pending', 'paid', 'shipped', 'done', 'cancelled') DEFAULT 'pending',
        cancel_reason TEXT DEFAULT NULL,
        cancelled_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'orders' berhasil dibuat.<br>";

    // 5. Order Items Table
    $pdo->exec("
    CREATE TABLE order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'order_items' berhasil dibuat.<br>";

    // 6. Landing Configs Table
    $pdo->exec("
    CREATE TABLE landing_configs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_key VARCHAR(100) NOT NULL UNIQUE,
        content_value TEXT,
        type ENUM('text', 'image', 'color') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'landing_configs' berhasil dibuat.<br>";

    // --- SEEDING DATA ---
    echo "Memulai seeding data...<br>";

    // Seed Admin Account (Email: admin@prostore.com, Pass: Admin@12345)
    $hashed_admin_pass = password_hash('Admin@12345', PASSWORD_BCRYPT);
    $stmtUser = $pdo->prepare("
        INSERT INTO users (name, email, password, role, email_verified_at, phone, bio) 
        VALUES (?, ?, ?, 'admin', NOW(), ?, ?)
    ");
    $stmtUser->execute([
        'Administrator ProStore', 
        'admin@prostore.com', 
        $hashed_admin_pass, 
        '+6281234567890', 
        'Gubernur Toko Online ProStore. Siap melayani pelanggan 24/7.'
    ]);
    echo "- Seed admin@prostore.com berhasil.<br>";

    // Seed Bank Accounts
    $pdo->exec("
        INSERT INTO bank_accounts (bank_name, account_number, account_name, is_active) VALUES
        ('BCA', '123-4567-890', 'PT Pro Store International', 1),
        ('Mandiri', '987-6543-210', 'PT Pro Store International', 1),
        ('BNI', '111-2222-333', 'PT Pro Store International', 1)
    ");
    echo "- Seed bank accounts berhasil.<br>";

    // Seed Landing Configs
    $pdo->exec("
        INSERT INTO landing_configs (section_key, content_value, type) VALUES
        ('hero_title', 'Gadget & Outfit Premium Terbaik', 'text'),
        ('hero_subtitle', 'Tingkatkan gaya hidup modern Anda dengan koleksi eksklusif terkurasi.', 'text'),
        ('primary_color', '#6366f1', 'color'),
        ('hero_image', '', 'image')
    ");
    echo "- Seed landing configs berhasil.<br>";

    // Seed Products
    $pdo->exec("
        INSERT INTO products (name, description, price, stock, image_url) VALUES
        ('Kemeja Flannel Klasik Indigo', 'Kemeja flannel lengan panjang dengan bahan katun premium lembut dan nyaman dipakai sehari-hari.', 189000.00, 15, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&auto=format&fit=crop&q=60'),
        ('Sepatu Sneakers Casual Leather', 'Sneakers kasual unisex dengan kulit sintetis premium. Ringan, empuk, dan tangguh.', 349000.00, 8, 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=500&auto=format&fit=crop&q=60'),
        ('Tas Ransel Rolltop Minimalist', 'Tas ransel tahan air berkapasitas besar dengan slot laptop 15.6 inch. Ergonomis dan modern.', 275000.00, 5, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=60'),
        ('Kaos Polos Cotton Combed 30s Heavy Black', 'Kaos polos adem menyerap keringat dengan pilihan jahitan rapi standard distro modern.', 59000.00, 50, 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500&auto=format&fit=crop&q=60')
    ");
    echo "- Seed products berhasil.<br>";

    echo "<h3 style='color:green;'>Setup Database Fresh berhasil sepenuhnya!</h3>";
    echo "<a href='index.php?page=home'>Kembali ke Toko</a>";

} catch (\PDOException $e) {
    echo "<h3 style='color:red;'>Gagal setup database: " . $e->getMessage() . "</h3>";
}
