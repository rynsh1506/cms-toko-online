<?php
require_once __DIR__ . '/config/db.php';

try {
    // Disable foreign key checks to safely drop tables
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // Drop tables if they exist
    $tables = ['order_items', 'orders', 'bank_accounts', 'product_variants', 'products', 'landing_configs', 'users', 'categories', 'promo_codes', 'banners'];
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
        address TEXT DEFAULT NULL,
        bio TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'users' berhasil dibuat.<br>";

    // 2. Categories Table
    $pdo->exec("
    CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        icon VARCHAR(50) DEFAULT NULL,
        color VARCHAR(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'categories' berhasil dibuat.<br>";

    // 3. Products Table
    $pdo->exec("
    CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        image_url VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'products' berhasil dibuat.<br>";

    // 3b. Product Variants Table
    $pdo->exec("
    CREATE TABLE product_variants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        variant_name VARCHAR(100) NOT NULL,
        variant_value VARCHAR(100) NOT NULL,
        additional_price DECIMAL(10, 2) DEFAULT 0.00,
        stock INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'product_variants' berhasil dibuat.<br>";

    // 4. Bank Accounts Table
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

    // 5. Promo Codes Table
    $pdo->exec("
    CREATE TABLE promo_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        discount_type ENUM('percentage', 'fixed') NOT NULL,
        discount_value DECIMAL(10, 2) NOT NULL,
        min_order DECIMAL(10, 2) DEFAULT 0.00,
        max_uses INT NOT NULL DEFAULT 100,
        used_count INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'promo_codes' berhasil dibuat.<br>";

    // 6. Orders Table
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
        promo_code_id INT NULL,
        discount_amount DECIMAL(10, 2) DEFAULT 0.00,
        status ENUM('pending', 'paid', 'shipped', 'done', 'cancelled') DEFAULT 'pending',
        cancel_reason TEXT DEFAULT NULL,
        cancelled_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL,
        FOREIGN KEY (promo_code_id) REFERENCES promo_codes(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'orders' berhasil dibuat.<br>";

    // 7. Order Items Table
    $pdo->exec("
    CREATE TABLE order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        variant_id INT DEFAULT NULL,
        variant_info TEXT DEFAULT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'order_items' berhasil dibuat.<br>";

    // 8. Landing Configs Table
    $pdo->exec("
    CREATE TABLE landing_configs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_key VARCHAR(100) NOT NULL UNIQUE,
        content_value TEXT,
        type ENUM('text', 'image', 'color') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'landing_configs' berhasil dibuat.<br>";

    // 9. Banners Table
    $pdo->exec("
    CREATE TABLE banners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) DEFAULT NULL,
        description VARCHAR(255) DEFAULT NULL,
        image_url VARCHAR(255) NOT NULL,
        link_url VARCHAR(255) DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel 'banners' berhasil dibuat.<br>";


    // --- SEEDING DATA ---
    echo "Memulai seeding data...<br>";

    // Seed Admin Account (Email: admin@prostore.com, Pass: Admin@12345)
    $hashed_admin_pass = password_hash('Admin@12345', PASSWORD_BCRYPT);
    $stmtUser = $pdo->prepare("
        INSERT INTO users (name, email, password, role, email_verified_at, phone, bio) 
        VALUES (?, ?, ?, 'admin', NOW(), ?, ?)
    ");
    $stmtUser->execute([
        'Administrator NusaBay', 
        'admin@prostore.com', 
        $hashed_admin_pass, 
        '+6281234567890', 
        'Gubernur Toko Online NusaBay. Siap melayani pelanggan 24/7.'
    ]);
    echo "- Seed admin@prostore.com berhasil.<br>";

    // Seed Categories
    $pdo->exec("
        INSERT INTO categories (name, slug, icon, color) VALUES
        ('Elektronik', 'elektronik', 'cpu', '#3b82f6'),
        ('Fashion', 'fashion', 'shirt', '#ec4899'),
        ('Buku & Alat Tulis', 'buku-alat-tulis', 'book', '#f59e0b'),
        ('Makanan & Minuman', 'makanan-minuman', 'coffee', '#10b981'),
        ('Rumah Tangga', 'rumah-tangga', 'home', '#8b5cf6'),
        ('Olahraga', 'olahraga', 'activity', '#ef4444')
    ");
    echo "- Seed categories berhasil.<br>";

    // Fetch Category IDs
    $cat_ids = [];
    $stmtCat = $pdo->query("SELECT id, slug FROM categories");
    while ($row = $stmtCat->fetch()) {
        $cat_ids[$row['slug']] = $row['id'];
    }

    // Seed Products with category IDs
    $stmtProd = $pdo->prepare("
        INSERT INTO products (category_id, name, description, price, stock, image_url) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmtProd->execute([$cat_ids['fashion'], 'Kemeja Flannel Klasik Indigo', 'Kemeja flannel lengan panjang dengan bahan katun premium lembut dan nyaman dipakai sehari-hari.', 189000.00, 15, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['fashion'], 'Sepatu Sneakers Casual Leather', 'Sneakers kasual unisex dengan kulit sintetis premium. Ringan, empuk, dan tangguh.', 349000.00, 8, 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['fashion'], 'Tas Ransel Rolltop Minimalist', 'Tas ransel tahan air berkapasitas besar dengan slot laptop 15.6 inch. Ergonomis dan modern.', 275000.00, 5, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['fashion'], 'Kaos Polos Cotton Combed 30s Heavy Black', 'Kaos polos adem menyerap keringat dengan pilihan jahitan rapi standard distro modern.', 59000.00, 50, 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['fashion'], 'Jaket Bomber Premium Windbreaker', 'Jaket bomber kasual dengan bahan parasut berkualitas tinggi, tahan angin dan cipratan air.', 299000.00, 10, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['fashion'], 'Celana Chino Slim Fit Khaki', 'Celana chino katun strech yang elastis dan pas di kaki. Cocok untuk acara formal maupun santai.', 175000.00, 20, 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['fashion'], 'Topi Baseball Corduroy Retro', 'Topi baseball retro berbahan korduroi premium dengan ukuran yang mudah disesuaikan.', 65000.00, 30, 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['fashion'], 'Kacamata Hitam Polarized Classic', 'Kacamata hitam polarized klasik dengan pelindung UV400, nyaman untuk aktivitas luar ruangan.', 125000.00, 15, 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=500&auto=format&fit=crop&q=60']);
    
    $stmtProd->execute([$cat_ids['elektronik'], 'Smartwatch Active Pro NFC', 'Smartwatch sport premium dengan ketahanan baterai 14 hari, sensor detak jantung, dan layar AMOLED.', 899000.00, 12, 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['elektronik'], 'TWS Earphones ANC Hi-Res Audio', 'Earphone TWS dengan active noise cancelling canggih dan suara bass yang jernih memukau.', 45000.00, 18, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['elektronik'], 'Charger Baseus GaN5 Pro 65W', 'Charger GaN adapter berkecepatan tinggi dengan 3 port output untuk laptop, tablet, dan smartphone.', 350000.00, 25, 'https://images.unsplash.com/photo-1622445262465-2481c4574875?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['elektronik'], 'Mouse Wireless Silent Click', 'Mouse nirkabel ergonomis dengan teknologi tombol senyap dan sensitivitas tinggi.', 120000.00, 40, 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['elektronik'], 'Keyboard Mechanical RGB 60%', 'Keyboard mekanikal kompak 60 persen dengan lampu latar RGB dinamis dan switch yang responsif.', 580000.00, 14, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['elektronik'], 'Powerbank 20000mAh Power Delivery', 'Pengisi daya portabel kapasitas besar 20000mAh yang mendukung pengisian cepat dua arah.', 299000.00, 22, 'https://images.unsplash.com/photo-1609592424089-98642bbec402?w=500&auto=format&fit=crop&q=60']);
    
    $stmtProd->execute([$cat_ids['buku-alat-tulis'], 'Agenda Kerja Leather Executive A5', 'Buku agenda kerja hardcover premium A5 dengan kertas 100gsm ramah mata.', 95000.00, 25, 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['buku-alat-tulis'], 'Pen Pulpen Gel Parker Jotter Classic', 'Pulpen gel mewah Parker dengan goresan tinta hitam pekat yang lancar dan desain elegan.', 150000.00, 10, 'https://images.unsplash.com/photo-1583485088034-697b5bc54ccd?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['buku-alat-tulis'], 'Notebook Grid Journal Dotted', 'Buku catatan harian bergaris titik/grid berukuran portabel, ideal untuk membuat jurnal pelacak.', 45000.00, 35, 'https://images.unsplash.com/photo-1531346878377-a5be20888e57?w=500&auto=format&fit=crop&q=60']);
    
    $stmtProd->execute([$cat_ids['makanan-minuman'], 'Kopi Arabika Gayo Single Origin 250g', 'Biji kopi arabika Gayo pilihan yang disangrai dengan profil medium, memberikan rasa asam buah yang seimbang.', 85000.00, 15, 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['makanan-minuman'], 'Cokelat Hitam Premium Dark Chocolate 85%', 'Batangan cokelat hitam premium dengan kandungan kakao 85% dari perkebunan lokal pilihan.', 42000.00, 28, 'https://images.unsplash.com/photo-1548907040-4d42b52145ca?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['makanan-minuman'], 'Teh Hijau Matcha Jepang Organik 100g', 'Bubuk matcha teh hijau organik asli dari Kyoto, Jepang. Sangat cocok untuk minuman hangat maupun kue.', 110000.00, 12, 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=500&auto=format&fit=crop&q=60']);
    
    $stmtProd->execute([$cat_ids['rumah-tangga'], 'Botol Minum Tumbler Termos Stainless 500ml', 'Termos tumbler minimalis dengan isolasi vakum ganda, menjaga suhu minuman panas atau dingin hingga 12 jam.', 135000.00, 30, 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['rumah-tangga'], 'Lampu Meja LED Minimalis Dimmable', 'Lampu meja baca dengan pengaturan tingkat terang cahaya sentuh, hemat daya dan pelindung mata.', 199000.00, 16, 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500&auto=format&fit=crop&q=60']);
    
    $stmtProd->execute([$cat_ids['olahraga'], 'Matras Yoga Anti-Slip NBR 10mm', 'Matras yoga tebal 10mm dengan bahan NBR anti-slip, memberikan kenyamanan sendi saat berolahraga.', 125000.00, 25, 'https://images.unsplash.com/photo-1592432678016-e910b452f9a2?w=500&auto=format&fit=crop&q=60']);
    $stmtProd->execute([$cat_ids['olahraga'], 'Tali Skipping Digital dengan Counter', 'Lompat tali pintar dengan penghitung lompatan digital otomatis dan kalori, pegangan anti-selip.', 75000.00, 50, 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=500&auto=format&fit=crop&q=60']);
    
    echo "- Seed products berhasil.<br>";

    // Seed Product Variants (untuk 2 produk pertama sebagai contoh)
    $stmtVar = $pdo->prepare("
        INSERT INTO product_variants (product_id, variant_name, variant_value, additional_price, stock)
        VALUES (?, ?, ?, ?, ?)
    ");
    $firstProducts = $pdo->query("SELECT id FROM products ORDER BY id ASC LIMIT 2")->fetchAll();
    if (count($firstProducts) >= 1) {
        $pid1 = $firstProducts[0]['id']; // Kemeja Flannel
        $stmtVar->execute([$pid1, 'Ukuran', 'S', 0.00, 10]);
        $stmtVar->execute([$pid1, 'Ukuran', 'M', 0.00, 15]);
        $stmtVar->execute([$pid1, 'Ukuran', 'L', 10000.00, 12]);
        $stmtVar->execute([$pid1, 'Ukuran', 'XL', 20000.00, 8]);
        $stmtVar->execute([$pid1, 'Warna', 'Indigo', 0.00, 20]);
        $stmtVar->execute([$pid1, 'Warna', 'Merah', 0.00, 15]);
        $stmtVar->execute([$pid1, 'Warna', 'Hitam', 15000.00, 10]);
    }
    if (count($firstProducts) >= 2) {
        $pid2 = $firstProducts[1]['id']; // Sepatu Sneakers
        $stmtVar->execute([$pid2, 'Ukuran', '38', 0.00, 5]);
        $stmtVar->execute([$pid2, 'Ukuran', '39', 0.00, 8]);
        $stmtVar->execute([$pid2, 'Ukuran', '40', 0.00, 8]);
        $stmtVar->execute([$pid2, 'Ukuran', '41', 5000.00, 6]);
        $stmtVar->execute([$pid2, 'Ukuran', '42', 5000.00, 4]);
        $stmtVar->execute([$pid2, 'Warna', 'Putih', 0.00, 15]);
        $stmtVar->execute([$pid2, 'Warna', 'Hitam', 0.00, 16]);
    }
    echo "- Seed product variants berhasil.<br>";

    // Seed Bank Accounts
    $pdo->exec("
        INSERT INTO bank_accounts (bank_name, account_number, account_name, is_active) VALUES
        ('BCA', '123-4567-890', 'PT Nusa Bay Nusantara', 1),
        ('Mandiri', '987-6543-210', 'PT Nusa Bay Nusantara', 1),
        ('BNI', '111-2222-333', 'PT Nusa Bay Nusantara', 1)
    ");
    echo "- Seed bank accounts berhasil.<br>";

    // Seed Promo Codes
    $stmtPromo = $pdo->prepare("
        INSERT INTO promo_codes (code, discount_type, discount_value, min_order, max_uses, used_count, is_active, expires_at) 
        VALUES (?, ?, ?, ?, ?, 0, 1, ?)
    ");
    $stmtPromo->execute(['NUSA10', 'percentage', 10.00, 50000.00, 200, date('Y-m-d H:i:s', strtotime('+30 days'))]);
    $stmtPromo->execute(['BAY50K', 'fixed', 50000.00, 250000.00, 100, date('Y-m-d H:i:s', strtotime('+30 days'))]);
    echo "- Seed promo codes berhasil.<br>";

    // Seed Landing Configs
    $pdo->exec("
        INSERT INTO landing_configs (section_key, content_value, type) VALUES
        ('hero_title', 'Marketplace Serba Ada Berkualitas', 'text'),
        ('hero_subtitle', 'Toko online terlengkap untuk kebutuhan gaya hidup, gadget, hobi, dan keperluan harian Anda.', 'text'),
        ('primary_color', '#6366f1', 'color'),
        ('hero_image', '', 'image'),
        ('footer_description', 'NusaBay adalah toko online tepercaya untuk segala kebutuhan Anda. Belanja aman, cepat, dan mudah.', 'text'),
        ('footer_address', 'Jl. Merdeka No. 45, Jakarta Pusat, DKI Jakarta 10110', 'text'),
        ('footer_email', 'support@nusabay.com', 'text'),
        ('social_facebook', 'https://facebook.com/', 'text'),
        ('social_instagram', 'https://instagram.com/', 'text'),
        ('social_twitter', 'https://twitter.com/', 'text'),
        ('social_whatsapp', 'https://wa.me/6281234567890', 'text')
    ");
    echo "- Seed landing configs berhasil.<br>";

    // Seed Banners
    $pdo->exec("
        INSERT INTO banners (title, description, image_url, link_url, is_active, sort_order) VALUES
        ('Diskon Spesial Gajian', 'Gunakan kode promo NUSA10 dan dapatkan diskon belanja ekstra 10%.', 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200&auto=format&fit=crop&q=80', '#products', 1, 1),
        ('Koleksi Fashion Outfit Baru', 'Outfit kekinian stylish trend 2026 kini telah hadir.', 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200&auto=format&fit=crop&q=80', '#products', 1, 2)
    ");
    echo "- Seed banners berhasil.<br>";

    echo "<h3 style='color:green;'>Setup Database Fresh NusaBay berhasil sepenuhnya!</h3>";
    echo "<a href='index.php?page=home'>Kembali ke Toko</a>";

} catch (\PDOException $e) {
    echo "<h3 style='color:red;'>Gagal setup database: " . $e->getMessage() . "</h3>";
}
