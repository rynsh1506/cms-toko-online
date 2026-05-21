<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : (isset($_POST['action']) ? sanitize_input($_POST['action']) : '');

// --- LIST: GET ?action=list&product_id=X ---
if ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $product_id = intval($_GET['product_id'] ?? 0);
    if ($product_id <= 0) { echo json_encode(['status' => 'error', 'message' => 'product_id tidak valid.']); exit; }

    $stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_name ASC, id ASC");
    $stmt->execute([$product_id]);
    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'variants' => $variants]);
    exit;
}

// --- ADD: POST ?action=add ---
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id      = intval($_POST['product_id'] ?? 0);
    $variant_name    = trim(sanitize_input($_POST['variant_name'] ?? ''));
    $variant_value   = trim(sanitize_input($_POST['variant_value'] ?? ''));
    $additional_price = floatval($_POST['additional_price'] ?? 0);
    $stock           = intval($_POST['stock'] ?? 0);

    if ($product_id <= 0 || $variant_name === '' || $variant_value === '') {
        echo json_encode(['status' => 'error', 'message' => 'Nama varian, nilai, dan produk wajib diisi.']); exit;
    }

    // Verify product exists
    $chk = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $chk->execute([$product_id]);
    if (!$chk->fetch()) { echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan.']); exit; }

    $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, variant_name, variant_value, additional_price, stock) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$product_id, $variant_name, $variant_value, $additional_price, $stock]);
    $new_id = $pdo->lastInsertId();

    $newVariant = ['id' => $new_id, 'product_id' => $product_id, 'variant_name' => $variant_name, 'variant_value' => $variant_value, 'additional_price' => $additional_price, 'stock' => $stock];
    echo json_encode(['status' => 'success', 'message' => 'Varian berhasil ditambahkan.', 'variant' => $newVariant]);
    exit;
}

// --- EDIT: POST ?action=edit ---
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id              = intval($_POST['id'] ?? 0);
    $variant_name    = trim(sanitize_input($_POST['variant_name'] ?? ''));
    $variant_value   = trim(sanitize_input($_POST['variant_value'] ?? ''));
    $additional_price = floatval($_POST['additional_price'] ?? 0);
    $stock           = intval($_POST['stock'] ?? 0);

    if ($id <= 0 || $variant_name === '' || $variant_value === '') {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']); exit;
    }

    $stmt = $pdo->prepare("UPDATE product_variants SET variant_name=?, variant_value=?, additional_price=?, stock=? WHERE id=?");
    $stmt->execute([$variant_name, $variant_value, $additional_price, $stock, $id]);
    echo json_encode(['status' => 'success', 'message' => 'Varian berhasil diperbarui.']);
    exit;
}

// --- DELETE: POST ?action=delete ---
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']); exit; }

    $stmt = $pdo->prepare("DELETE FROM product_variants WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success', 'message' => 'Varian berhasil dihapus.']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
