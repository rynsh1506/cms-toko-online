<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    
    // Check product in DB
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan.']);
            exit;
        }
        $_SESSION['error'] = "Produk tidak ditemukan.";
        redirect('index.php?page=home');
    }
    
    $qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] + 1 : 1;
    
    if ($qty > $product['stock']) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => "Gagal menambah produk. Stok tidak mencukupi (Tersedia: " . $product['stock'] . ")."]);
            exit;
        }
        $_SESSION['error'] = "Gagal menambah produk. Stok tidak mencukupi (Tersedia: " . $product['stock'] . ").";
        redirect('index.php?page=home');
    }
    
    $_SESSION['cart'][$product_id] = $qty;
    
    // Optimasi: Lepaskan lock session agar tidak memblokir request lain
    session_write_close();
    
    if ($is_ajax) {
        header('Content-Type: application/json');
        $cart_count = 0;
        foreach ($_SESSION['cart'] as $q) {
            $cart_count += $q;
        }
        echo json_encode([
            'status' => 'success',
            'message' => "Berhasil menambahkan " . htmlspecialchars($product['name']) . " ke keranjang.",
            'cart_count' => $cart_count
        ]);
        exit;
    }
    $_SESSION['success'] = "Berhasil menambahkan " . htmlspecialchars($product['name']) . " ke keranjang.";
    redirect('index.php?page=home');
} 

elseif ($action === 'remove') {
    $product_id = intval($_GET['id'] ?? 0);
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    
    // Optimasi: Lepaskan lock session agar tidak memblokir request lain
    session_write_close();
    
    if ($is_ajax) {
        header('Content-Type: application/json');
        // Recalculate total price
        $total_price = 0;
        $cart_count = 0;
        if (!empty($_SESSION['cart'])) {
            $ids = array_keys($_SESSION['cart']);
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $products = $stmt->fetchAll();
            foreach ($products as $p) {
                $total_price += ($p['price'] * $_SESSION['cart'][$p['id']]);
                $cart_count += $_SESSION['cart'][$p['id']];
            }
        }
        echo json_encode([
            'status' => 'success',
            'message' => 'Produk dihapus dari keranjang.',
            'total_price' => $total_price,
            'cart_count' => $cart_count
        ]);
        exit;
    }
    redirect('index.php?page=cart');
}

elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);
    
    if ($qty <= 0) {
        unset($_SESSION['cart'][$product_id]);
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'removed', 'message' => 'Produk dihapus karena kuantiti 0.']);
            exit;
        }
        redirect('index.php?page=cart');
    }
    
    // Check stock
    $stmt = $pdo->prepare("SELECT price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    $error_msg = '';
    if ($qty > $product['stock']) {
        $qty = $product['stock'];
        $error_msg = "Jumlah disesuaikan ke batas maksimal stok (Tersedia: {$product['stock']}).";
    }
    
    $_SESSION['cart'][$product_id] = $qty;
    $subtotal = $product['price'] * $qty;
    
    // Optimasi: Lepaskan lock session agar tidak memblokir request lain
    session_write_close();
    
    if ($is_ajax) {
        header('Content-Type: application/json');
        
        // Recalculate total price
        $total_price = 0;
        $cart_count = 0;
        $ids = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmtTotal = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
        $stmtTotal->execute($ids);
        $products = $stmtTotal->fetchAll();
        foreach ($products as $p) {
            $total_price += ($p['price'] * $_SESSION['cart'][$p['id']]);
            $cart_count += $_SESSION['cart'][$p['id']];
        }

        echo json_encode([
            'status' => 'success',
            'qty' => $qty,
            'subtotal' => $subtotal,
            'total_price' => $total_price,
            'cart_count' => $cart_count,
            'error_message' => $error_msg
        ]);
        exit;
    }
    
    if (!empty($error_msg)) {
        $_SESSION['error'] = $error_msg;
    }
    redirect('index.php?page=cart');
}

elseif ($action === 'clear') {
    $_SESSION['cart'] = [];
    
    // Optimasi: Lepaskan lock session agar tidak memblokir request lain
    session_write_close();
    
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Keranjang dikosongkan.']);
        exit;
    }
    redirect('index.php?page=cart');
}

else {
    redirect('index.php?page=home');
}
