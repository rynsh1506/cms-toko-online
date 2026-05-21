<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/ProductService.php';

$productService = new ProductService($pdo);

// Pastikan semua output dari file ini berformat JSON
header('Content-Type: application/json');

// Mencegah error jika parameter aksi tidak dikirim
$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : (isset($_POST['action']) ? sanitize_input($_POST['action']) : '');

// --- LIST: GET ?action=list&product_id=X ---
if ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $product_id = intval($_GET['product_id'] ?? 0);
    if ($product_id <= 0) { 
        echo json_encode(['status' => 'error', 'message' => 'ID Produk tidak valid.']); 
        exit; 
    }

    try {
        $variants = $productService->getVariantsByProductId($product_id);
        echo json_encode(['status' => 'success', 'variants' => $variants]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memuat varian: Terjadi kesalahan database.']);
    }
    exit;
}

// --- ADD: POST ?action=add ---
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id       = intval($_POST['product_id'] ?? 0);
    $variant_name     = trim(sanitize_input($_POST['variant_name'] ?? ''));
    $variant_value    = trim(sanitize_input($_POST['variant_value'] ?? ''));
    $additional_price = floatval($_POST['additional_price'] ?? 0);
    $stock            = intval($_POST['stock'] ?? 0);

    if ($product_id <= 0 || $variant_name === '' || $variant_value === '') {
        echo json_encode(['status' => 'error', 'message' => 'Nama varian, nilai, dan produk wajib diisi.']); 
        exit;
    }

    try {
        // Verifikasi apakah produk terkait benar-benar ada
        $product = $productService->getProductById($product_id);
        if (!$product) { 
            echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan di database.']); 
            exit; 
        }

        // Insert varian baru
        $new_id = $productService->addVariant($product_id, $variant_name, $variant_value, $additional_price, $stock);

        if ($new_id) {
            $newVariant = [
                'id'               => $new_id, 
                'product_id'       => $product_id, 
                'variant_name'     => $variant_name, 
                'variant_value'    => $variant_value, 
                'additional_price' => $additional_price, 
                'stock'            => $stock
            ];

            echo json_encode([
                'status'  => 'success', 
                'message' => 'Varian produk berhasil ditambahkan.', 
                'variant' => $newVariant
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan varian.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan varian. Silakan coba lagi.']);
    }
    exit;
}

// --- EDIT: POST ?action=edit ---
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id               = intval($_POST['id'] ?? 0);
    $variant_name     = trim(sanitize_input($_POST['variant_name'] ?? ''));
    $variant_value    = trim(sanitize_input($_POST['variant_value'] ?? ''));
    $additional_price = floatval($_POST['additional_price'] ?? 0);
    $stock            = intval($_POST['stock'] ?? 0);

    if ($id <= 0 || $variant_name === '' || $variant_value === '') {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap (Nama Varian dan Nilai wajib diisi).']); 
        exit;
    }

    try {
        $productService->updateVariant($id, $variant_name, $variant_value, $additional_price, $stock);
        echo json_encode(['status' => 'success', 'message' => 'Data varian berhasil diperbarui.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data varian.']);
    }
    exit;
}

// --- DELETE: POST ?action=delete ---
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) { 
        echo json_encode(['status' => 'error', 'message' => 'ID Varian tidak valid.']); 
        exit; 
    }

    try {
        $productService->deleteVariant($id);
        echo json_encode(['status' => 'success', 'message' => 'Varian berhasil dihapus.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus varian.']);
    }
    exit;
}

// Jika action tidak ada yang cocok
echo json_encode(['status' => 'error', 'message' => 'Aksi sistem tidak dikenali.']);