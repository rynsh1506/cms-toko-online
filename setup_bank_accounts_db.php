<?php
require_once __DIR__ . '/config/db.php';

try {
    // 1. Buat tabel bank_accounts
    $sql_bank = "
    CREATE TABLE IF NOT EXISTS bank_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bank_name VARCHAR(100) NOT NULL,
        account_number VARCHAR(100) NOT NULL,
        account_name VARCHAR(255) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql_bank);
    echo "<h3 style='color:green;'>Tabel 'bank_accounts' berhasil dibuat!</h3>";

    // 2. Seeding default bank accounts (jika kosong)
    $stmt = $pdo->query("SELECT COUNT(*) FROM bank_accounts");
    $count = $stmt->fetchColumn();
    if ($count == 0) {
        $sql_seed = "
        INSERT INTO bank_accounts (bank_name, account_number, account_name, is_active) VALUES
        ('BCA', '123-4567-890', 'PT Pro Store International', 1),
        ('Mandiri', '987-6543-210', 'PT Pro Store International', 1)
        ";
        $pdo->exec($sql_seed);
        echo "<p style='color:green;'>Data dummy bank_accounts berhasil ditambahkan!</p>";
    }

    // 3. Tambahkan kolom bank_account_id di tabel orders jika belum ada
    $check_column = $pdo->query("SHOW COLUMNS FROM orders LIKE 'bank_account_id'")->fetch();
    if (!$check_column) {
        $sql_alter = "
        ALTER TABLE orders 
        ADD COLUMN bank_account_id INT NULL AFTER unique_code,
        ADD FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL
        ";
        $pdo->exec($sql_alter);
        echo "<p style='color:green;'>Kolom 'bank_account_id' berhasil ditambahkan ke tabel 'orders'!</p>";
    } else {
        echo "<p style='color:blue;'>Kolom 'bank_account_id' sudah ada di tabel 'orders'.</p>";
    }

    echo "<br><a href='index.php?page=home'>Ke Halaman Utama</a>";

} catch (\PDOException $e) {
    echo "<h3 style='color:red;'>Gagal setup database bank_accounts: " . $e->getMessage() . "</h3>";
}
