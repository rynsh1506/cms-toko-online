<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';

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
        $_SESSION['error'] = "Produk tidak ditemukan.";
        redirect('index.php?page=home');
    }
    
    $qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] + 1 : 1;
    
    if ($qty > $product['stock']) {
        $_SESSION['error'] = "Gagal menambah produk. Stok tidak mencukupi (Tersedia: " . $product['stock'] . ").";
        redirect('index.php?page=home');
    }
    
    $_SESSION['cart'][$product_id] = $qty;
    $_SESSION['success'] = "Berhasil menambahkan " . htmlspecialchars($product['name']) . " ke keranjang.";
    redirect('index.php?page=home');
} 

elseif ($action === 'remove') {
    $product_id = intval($_GET['id'] ?? 0);
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    redirect('index.php?page=cart');
}

elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);
    
    if ($qty <= 0) {
        unset($_SESSION['cart'][$product_id]);
        redirect('index.php?page=cart');
    }
    
    // Check stock
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $stock = $stmt->fetchColumn();
    
    if ($qty > $stock) {
        $_SESSION['error'] = "Jumlah melebihi stok yang tersedia (Tersedia: $stock).";
        $_SESSION['cart'][$product_id] = $stock; // Set ke batas maksimal
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }
    redirect('index.php?page=cart');
}

elseif ($action === 'clear') {
    $_SESSION['cart'] = [];
    redirect('index.php?page=cart');
}

else {
    redirect('index.php?page=home');
}
