<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

// Wajib Login
if (!isAuth()) {
    $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
        exit;
    }
    redirect('index.php?page=login');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    if (empty($_SESSION['cart'])) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Keranjang Anda kosong.']);
            exit;
        }
        $_SESSION['error'] = "Keranjang Anda kosong.";
        redirect('index.php?page=home');
    }

    $user_id = $_SESSION['user_id'];
    $customer_name = sanitize_input($_POST['customer_name'] ?? '');
    $customer_phone = sanitize_input($_POST['customer_phone'] ?? '');
    $customer_address = sanitize_input($_POST['customer_address'] ?? '');
    $bank_account_id = intval($_POST['bank_account_id'] ?? 0);

    if (empty($customer_name) || empty($customer_phone) || empty($customer_address) || $bank_account_id <= 0) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Harap lengkapi semua data pengiriman dan pilih metode pembayaran.']);
            exit;
        }
        $_SESSION['error'] = "Harap lengkapi semua data pengiriman dan pilih metode pembayaran.";
        redirect('index.php?page=checkout');
    }

    try {
        // Mulai transaksi SEBELUM melakukan query produk dengan FOR UPDATE
        $pdo->beginTransaction();

        // 1. Validasi Rekening Bank dengan Row Lock
        $stmtBank = $pdo->prepare("SELECT id FROM bank_accounts WHERE id = ? AND is_active = 1 FOR UPDATE");
        $stmtBank->execute([$bank_account_id]);
        if (!$stmtBank->fetch()) {
            throw new Exception("Metode pembayaran yang dipilih tidak valid atau dinonaktifkan.");
        }

        // 2. Query data produk dengan FOR UPDATE untuk mencegah race condition stok
        $ids = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT id, price, stock, name FROM products WHERE id IN ($placeholders) FOR UPDATE");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $product_data = [];
        foreach ($products as $p) {
            $product_data[$p['id']] = $p;
        }

        // 3. Validasi stok & kalkulasi total
        $total_pure = 0;
        foreach ($_SESSION['cart'] as $p_id => $qty) {
            if (!isset($product_data[$p_id])) {
                throw new Exception("Ada produk yang tidak valid di keranjang Anda.");
            }
            
            $p_info = $product_data[$p_id];
            
            if ($qty > $p_info['stock']) {
                throw new Exception("Stok untuk produk {$p_info['name']} tidak mencukupi (Sisa: {$p_info['stock']}).");
            }
            
            $total_pure += ($p_info['price'] * $qty);
        }

        // Generate Kode Unik & Total Akhir
        $unique_code = rand(100, 999);
        $final_total = $total_pure + $unique_code;

        // 4. Insert ke tabel orders
        $stmtOrder = $pdo->prepare("
            INSERT INTO orders (user_id, customer_name, customer_phone, customer_address, total_price, unique_code, bank_account_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmtOrder->execute([
            $user_id, 
            $customer_name, 
            $customer_phone, 
            $customer_address, 
            $final_total, 
            $unique_code,
            $bank_account_id
        ]);
        
        $order_id = $pdo->lastInsertId();

        // 5. Insert ke tabel order_items & kurangi stok
        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        
        foreach ($_SESSION['cart'] as $p_id => $qty) {
            $price = $product_data[$p_id]['price'];
            
            // Insert item
            $stmtItem->execute([$order_id, $p_id, $qty, $price]);
            
            // Kurangi stok
            $stmtUpdateStock->execute([$qty, $p_id]);
        }

        // Commit transaksi jika semua berhasil
        $pdo->commit();

        // Bersihkan keranjang belanja
        unset($_SESSION['cart']);

        // Set pesan sukses dengan instruksi pembayaran
        $_SESSION['success'] = "
            <strong>Pesanan Berhasil Dibuat! (Order ID: #$order_id)</strong><br>
            Silakan transfer tepat sebesar <strong>Rp " . number_format($final_total, 0, ',', '.') . "</strong><br>
            (Termasuk kode unik $unique_code untuk verifikasi instan).
        ";

        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Pesanan berhasil dibuat.', 
                'redirect_url' => 'index.php?page=invoice&id=' . $order_id
            ]);
            exit;
        }

        redirect('index.php?page=invoice&id=' . $order_id);

    } catch (\Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }

        $_SESSION['error'] = "Gagal memproses pesanan: " . $e->getMessage();
        redirect('index.php?page=checkout');
    }

} else {
    redirect('index.php?page=checkout');
}
