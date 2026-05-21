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

        // 2. Ambil selected_cart_keys dari sesi
        $checkout_keys = $_SESSION['selected_cart_keys'] ?? [];
        if (empty($checkout_keys)) {
            throw new Exception("Pilih minimal satu produk untuk di-checkout.");
        }

        $items_to_process = [];
        $total_pure = 0;

        foreach ($checkout_keys as $cart_key) {
            if (!isset($_SESSION['cart'][$cart_key])) {
                continue;
            }
            
            $qty = intval($_SESSION['cart'][$cart_key]);
            if ($qty <= 0) continue;

            $parts = explode('-', $cart_key);
            $pId = intval($parts[0] ?? 0);
            $vId = intval($parts[1] ?? 0);

            if ($pId <= 0) {
                throw new Exception("Format produk di keranjang tidak valid.");
            }

            // Lock product row
            $stmtProduct = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE");
            $stmtProduct->execute([$pId]);
            $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Produk tidak ditemukan di database.");
            }

            $variant = null;
            $effective_price = floatval($product['price']);
            $effective_stock = intval($product['stock']);
            $variant_info_str = null;

            if ($vId > 0) {
                // Lock variant row
                $stmtVariant = $pdo->prepare("SELECT id, variant_name, variant_value, additional_price, stock FROM product_variants WHERE id = ? AND product_id = ? FOR UPDATE");
                $stmtVariant->execute([$vId, $pId]);
                $variant = $stmtVariant->fetch(PDO::FETCH_ASSOC);

                if (!$variant) {
                    throw new Exception("Varian untuk produk {$product['name']} tidak valid.");
                }

                $effective_price += floatval($variant['additional_price']);
                $effective_stock = intval($variant['stock']);
                $variant_info_str = $variant['variant_name'] . ': ' . $variant['variant_value'];
            }

            // Validasi stok
            if ($qty > $effective_stock) {
                $err_msg = "Stok untuk produk {$product['name']}";
                if ($variant_info_str) {
                    $err_msg .= " ($variant_info_str)";
                }
                $err_msg .= " tidak mencukupi (Sisa: $effective_stock).";
                throw new Exception($err_msg);
            }

            $total_pure += ($effective_price * $qty);

            $items_to_process[] = [
                'cart_key' => $cart_key,
                'product_id' => $pId,
                'variant_id' => $vId > 0 ? $vId : null,
                'variant_info' => $variant_info_str,
                'quantity' => $qty,
                'price' => $effective_price
            ];
        }

        if (empty($items_to_process)) {
            throw new Exception("Tidak ada item valid untuk diproses.");
        }

        // 4. Promo code validation inside transaction
        $promo_code_id = !empty($_POST['promo_code_id']) ? intval($_POST['promo_code_id']) : null;
        $discount_amount = 0;
        if ($promo_code_id !== null) {
            $stmtPromo = $pdo->prepare("SELECT * FROM promo_codes WHERE id = ? FOR UPDATE");
            $stmtPromo->execute([$promo_code_id]);
            $promo = $stmtPromo->fetch();
            if (!$promo) {
                throw new Exception("Kode promo tidak valid.");
            }
            if (!$promo['is_active']) {
                throw new Exception("Kode promo tidak aktif.");
            }
            if (strtotime($promo['expires_at']) < time()) {
                throw new Exception("Kode promo sudah kedaluwarsa.");
            }
            if ($promo['used_count'] >= $promo['max_uses']) {
                throw new Exception("Kuota kode promo sudah habis.");
            }
            if ($total_pure < $promo['min_order']) {
                throw new Exception("Total belanja kurang dari batas minimal kode promo.");
            }

            // Hitung diskon
            if ($promo['discount_type'] === 'percentage') {
                $discount_amount = ($promo['discount_value'] / 100) * $total_pure;
            } else {
                $discount_amount = $promo['discount_value'];
            }

            if ($discount_amount > $total_pure) {
                $discount_amount = $total_pure;
            }

            // Update usage count
            $stmtUpdatePromo = $pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?");
            $stmtUpdatePromo->execute([$promo_code_id]);
        }

        // Generate Kode Unik & Total Akhir
        $unique_code = rand(100, 999);
        $final_total = ($total_pure - $discount_amount) + $unique_code;

        // 5. Insert ke tabel orders
        $stmtOrder = $pdo->prepare("
            INSERT INTO orders (user_id, customer_name, customer_phone, customer_address, total_price, unique_code, bank_account_id, promo_code_id, discount_amount, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmtOrder->execute([
            $user_id, 
            $customer_name, 
            $customer_phone, 
            $customer_address, 
            $final_total, 
            $unique_code,
            $bank_account_id,
            $promo_code_id,
            $discount_amount
        ]);
        
        $order_id = $pdo->lastInsertId();

        // 6. Insert ke tabel order_items & kurangi stok
        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, variant_id, variant_info, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtUpdateProductStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmtUpdateVariantStock = $pdo->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ?");
        
        foreach ($items_to_process as $item) {
            // Insert item ke database
            $stmtItem->execute([
                $order_id,
                $item['product_id'],
                $item['variant_id'],
                $item['variant_info'],
                $item['quantity'],
                $item['price']
            ]);
            
            // Kurangi stok
            if ($item['variant_id']) {
                // Kurangi stok varian
                $stmtUpdateVariantStock->execute([$item['quantity'], $item['variant_id']]);
                // Kurangi juga stok utama produk (karena stok produk utama adalah jumlah stok variannya)
                $stmtUpdateProductStock->execute([$item['quantity'], $item['product_id']]);
            } else {
                // Kurangi stok utama produk saja
                $stmtUpdateProductStock->execute([$item['quantity'], $item['product_id']]);
            }
        }

        // Commit transaksi jika semua berhasil
        $pdo->commit();

        // Bersihkan item yang berhasil dibeli dari keranjang belanja
        foreach ($items_to_process as $item) {
            unset($_SESSION['cart'][$item['cart_key']]);
            unset($_SESSION['cart_meta'][$item['cart_key']]);
        }
        // Kosongkan list item yang dipilih untuk checkout
        unset($_SESSION['selected_cart_keys']);

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
