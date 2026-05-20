<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

// Wajib Login
if (!isAuth()) {
    redirect('index.php?page=login');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['cart'])) {
        $_SESSION['error'] = "Keranjang Anda kosong.";
        redirect('index.php?page=home');
    }

    $user_id = $_SESSION['user_id'];
    $customer_name = sanitize_input($_POST['customer_name'] ?? '');
    $customer_phone = sanitize_input($_POST['customer_phone'] ?? '');
    $customer_address = sanitize_input($_POST['customer_address'] ?? '');

    if (empty($customer_name) || empty($customer_phone) || empty($customer_address)) {
        $_SESSION['error'] = "Harap lengkapi semua data pengiriman.";
        redirect('index.php?page=checkout');
    }

    // Hitung total murni dari database (menghindari manipulasi form HTML)
    $total_pure = 0;
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    // Kita butuh prepare data untuk insert order_items nantinya
    $stmt = $pdo->prepare("SELECT id, price, stock, name FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $product_data = [];
    foreach ($products as $p) {
        $product_data[$p['id']] = $p;
    }

    // Validasi stok & kalkulasi total
    foreach ($_SESSION['cart'] as $p_id => $qty) {
        if (!isset($product_data[$p_id])) {
            $_SESSION['error'] = "Ada produk yang tidak valid di keranjang Anda.";
            redirect('index.php?page=cart');
        }
        
        $p_info = $product_data[$p_id];
        
        if ($qty > $p_info['stock']) {
            $_SESSION['error'] = "Stok untuk produk {$p_info['name']} tidak mencukupi (Sisa: {$p_info['stock']}).";
            redirect('index.php?page=cart');
        }
        
        $total_pure += ($p_info['price'] * $qty);
    }

    // Generate Kode Unik
    $unique_code = rand(100, 999);
    $final_total = $total_pure + $unique_code;

    try {
        // Gunakan Transaction agar jika salah satu gagal, semua di-rollback
        $pdo->beginTransaction();

        // 1. Insert ke tabel orders
        $stmtOrder = $pdo->prepare("
            INSERT INTO orders (user_id, customer_name, customer_phone, customer_address, total_price, unique_code) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtOrder->execute([
            $user_id, 
            $customer_name, 
            $customer_phone, 
            $customer_address, 
            $final_total, 
            $unique_code
        ]);
        
        $order_id = $pdo->lastInsertId();

        // 2. Insert ke tabel order_items & kurangi stok
        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        
        foreach ($_SESSION['cart'] as $p_id => $qty) {
            $price = $product_data[$p_id]['price'];
            
            // Insert item
            $stmtItem->execute([$order_id, $p_id, $qty, $price]);
            
            // Kurangi stok
            $stmtUpdateStock->execute([$qty, $p_id]);
        }

        // Commit jika semua sukses
        $pdo->commit();

        // Bersihkan keranjang
        unset($_SESSION['cart']);

        // Set pesan sukses dengan instruksi pembayaran
        $_SESSION['success'] = "
            <strong>Pesanan Berhasil Dibuat! (Order ID: #$order_id)</strong><br>
            Silakan transfer tepat sebesar <strong>Rp " . number_format($final_total, 0, ',', '.') . "</strong><br>
            (Termasuk kode unik $unique_code untuk verifikasi instan).
        ";

        redirect('index.php?page=home');

    } catch (\PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Gagal memproses pesanan: " . $e->getMessage();
        redirect('index.php?page=checkout');
    }

} else {
    redirect('index.php?page=checkout');
}
