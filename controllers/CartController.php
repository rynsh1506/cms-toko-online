<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax']);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['cart_meta'])) { 
    $_SESSION['cart_meta'] = []; 
}

// --- ADD TO CART ---
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $variant_id  = intval($_POST['variant_id'] ?? 0);
    $variant_info_text = trim(sanitize_input($_POST['variant_info'] ?? ''));
    
    // FIX BUG: Ambil kuantitas asli yang diinput user, default 1 jika tidak ada
    $quantity_to_add = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    if ($quantity_to_add <= 0) $quantity_to_add = 1;

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        if ($is_ajax) { header('Content-Type: application/json'); echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan.']); exit; }
        $_SESSION['error'] = "Produk tidak ditemukan.";
        redirect('index.php?page=home');
    }

    $variant = null;
    $effective_stock = $product['stock'];
    if ($variant_id > 0) {
        $vStmt = $pdo->prepare("SELECT * FROM product_variants WHERE id = ? AND product_id = ?");
        $vStmt->execute([$variant_id, $product_id]);
        $variant = $vStmt->fetch();
        if (!$variant) {
            if ($is_ajax) { header('Content-Type: application/json'); echo json_encode(['status' => 'error', 'message' => 'Varian tidak valid.']); exit; }
            redirect('index.php?page=home');
        }
        $effective_stock = $variant['stock'];
    }

    $cart_key = $product_id . '-' . $variant_id;

    // FIX BUG: Akumulasikan berdasarkan quantity_to_add, bukan + 1
    $qty = isset($_SESSION['cart'][$cart_key]) ? $_SESSION['cart'][$cart_key] + $quantity_to_add : $quantity_to_add;

    if ($qty > $effective_stock) {
        $msg = "Stok tidak mencukupi (Tersedia: $effective_stock).";
        if ($is_ajax) { header('Content-Type: application/json'); echo json_encode(['status' => 'error', 'message' => $msg]); exit; }
        $_SESSION['error'] = $msg;
        redirect('index.php?page=home');
    }

    $_SESSION['cart'][$cart_key] = $qty;
    
    $additional_price = $variant ? floatval($variant['additional_price']) : 0;
    $_SESSION['cart_meta'][$cart_key] = [
        'product_id'   => $product_id,
        'variant_id'   => $variant_id,
        'name'         => $product['name'],
        'base_price'   => floatval($product['price']),
        'price'        => floatval($product['price']) + $additional_price,
        'image_url'    => $product['image_url'],
        'stock'        => $effective_stock,
        'variant_info' => $variant_info_text,
    ];

    session_write_close();

    if ($is_ajax) {
        header('Content-Type: application/json');
        $cart_count = array_sum($_SESSION['cart']);
        echo json_encode([
            'status'     => 'success',
            'message'    => "Berhasil menambahkan " . htmlspecialchars($product['name']) . " ke keranjang.",
            'cart_count' => $cart_count
        ]);
        exit;
    }
    $_SESSION['success'] = "Berhasil menambahkan " . htmlspecialchars($product['name']) . " ke keranjang.";
    redirect('index.php?page=cart');
}

// --- DIRECT CHECKOUT (BELI LANGSUNG TANPA ANTRI DI CART) ---
elseif ($action === 'direct_checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $variant_id  = intval($_POST['variant_id'] ?? 0);
    $variant_info_text = trim(sanitize_input($_POST['variant_info'] ?? ''));
    $qty = intval($_POST['quantity'] ?? 1);
    if ($qty <= 0) $qty = 1;

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Content-Type: application/json'); echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan.']); exit;
    }

    $variant = null;
    $effective_stock = $product['stock'];
    if ($variant_id > 0) {
        $vStmt = $pdo->prepare("SELECT * FROM product_variants WHERE id = ? AND product_id = ?");
        $vStmt->execute([$variant_id, $product_id]);
        $variant = $vStmt->fetch();
        if (!$variant) {
            header('Content-Type: application/json'); echo json_encode(['status' => 'error', 'message' => 'Varian tidak valid.']); exit;
        }
        $effective_stock = $variant['stock'];
    }

    if ($qty > $effective_stock) {
        header('Content-Type: application/json'); echo json_encode(['status' => 'error', 'message' => "Stok tidak mencukupi (Tersedia: $effective_stock)."]); exit;
    }

    $cart_key = $product_id . '-' . $variant_id;
    $_SESSION['cart'][$cart_key] = $qty;
    
    $additional_price = $variant ? floatval($variant['additional_price']) : 0;
    $_SESSION['cart_meta'][$cart_key] = [
        'product_id'   => $product_id,
        'variant_id'   => $variant_id,
        'name'         => $product['name'],
        'base_price'   => floatval($product['price']),
        'price'        => floatval($product['price']) + $additional_price,
        'image_url'    => $product['image_url'],
        'stock'        => $effective_stock,
        'variant_info' => $variant_info_text,
    ];

    // Otomatis set hanya item ini yang masuk sesi checkout terpilih
    $_SESSION['selected_cart_keys'] = [$cart_key];

    session_write_close();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'redirect' => 'index.php?page=checkout']);
    exit;
}

// --- SET CHECKOUT KEYS (DARI SELEKSI CHECKBOX) ---
elseif ($action === 'select_checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['selected_cart_keys'] = $_POST['keys'] ?? [];
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}

// --- REMOVE ITEM ---
elseif ($action === 'remove') {
    $cart_key = isset($_POST['cart_key']) ? $_POST['cart_key'] : (isset($_GET['id']) ? intval($_GET['id']) . '-0' : '');

    if (isset($_SESSION['cart'][$cart_key])) {
        unset($_SESSION['cart'][$cart_key]);
        if (isset($_SESSION['cart_meta'][$cart_key])) {
            unset($_SESSION['cart_meta'][$cart_key]);
        }
    }

    session_write_close();

    if ($is_ajax) {
        header('Content-Type: application/json');
        $total_price = 0;
        $cart_count = 0;
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => $qty) {
                $price = isset($_SESSION['cart_meta'][$key]) ? floatval($_SESSION['cart_meta'][$key]['price']) : 0;
                $total_price += $price * $qty;
                $cart_count += $qty;
            }
        }
        echo json_encode([
            'status'      => 'success',
            'message'     => 'Produk dihapus dari keranjang.',
            'total_price' => $total_price,
            'cart_count'  => $cart_count
        ]);
        exit;
    }
    redirect('index.php?page=cart');
}

// --- UPDATE QUANTITY VIA CONTROL IN CART ---
elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_key = $_POST['cart_key'] ?? '';
    $qty = intval($_POST['qty'] ?? 1);

    if ($qty <= 0) {
        unset($_SESSION['cart'][$cart_key]);
        if (isset($_SESSION['cart_meta'][$cart_key])) unset($_SESSION['cart_meta'][$cart_key]);
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'removed', 'message' => 'Produk dihapus karena kuantitas diatur 0.']);
            exit;
        }
        redirect('index.php?page=cart');
    }

    $parts = explode('-', $cart_key);
    $product_id = intval($parts[0] ?? 0);
    $variant_id = intval($parts[1] ?? 0);

    $effective_stock = 999;
    $price = 0;

    if (!empty($_SESSION['cart_meta'][$cart_key])) {
        $meta = $_SESSION['cart_meta'][$cart_key];
        $effective_stock = $meta['stock'];
        $price = $meta['price'];
    } else {
        $stmtP = $pdo->prepare("SELECT price, stock FROM products WHERE id = ?");
        $stmtP->execute([$product_id]);
        $p = $stmtP->fetch();
        if ($p) {
            $effective_stock = $p['stock'];
            $price = floatval($p['price']);
            
            if ($variant_id > 0) {
                $stmtV = $pdo->prepare("SELECT stock, additional_price FROM product_variants WHERE id = ?");
                $stmtV->execute([$variant_id]);
                $v = $stmtV->fetch();
                if ($v) {
                    $effective_stock = $v['stock'];
                    $price += floatval($v['additional_price']);
                }
            }
        }
    }

    $error_msg = '';
    if ($qty > $effective_stock) {
        $qty = ($effective_stock > 0) ? $effective_stock : 1;
        $error_msg = "Jumlah disesuaikan ke batas maksimal ketersediaan stok (Tersedia: {$effective_stock}).";
    }

    $_SESSION['cart'][$cart_key] = $qty;
    $subtotal = $price * $qty;

    session_write_close();

    if ($is_ajax) {
        header('Content-Type: application/json');
        $total_price = 0;
        $cart_count = 0;
        foreach ($_SESSION['cart'] as $key => $q) {
            $p = isset($_SESSION['cart_meta'][$key]) ? floatval($_SESSION['cart_meta'][$key]['price']) : 0;
            $total_price += $p * $q;
            $cart_count += $q;
        }
        
        echo json_encode([
            'status'        => 'success',
            'qty'           => $qty,
            'subtotal'      => $subtotal,
            'total_price'   => $total_price,
            'cart_count'    => $cart_count,
            'error_message' => $error_msg
        ]);
        exit;
    }

    if (!empty($error_msg)) $_SESSION['error'] = $error_msg;
    redirect('index.php?page=cart');
}

// --- CLEAR CART ---
elseif ($action === 'clear') {
    $_SESSION['cart'] = [];
    $_SESSION['cart_meta'] = [];
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