<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

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
        $stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_name ASC, id ASC");
        $stmt->execute([$product_id]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'variants' => $variants]);
    } catch (PDOException $e) {
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
        $chk = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $chk->execute([$product_id]);
        if (!$chk->fetch()) { 
            echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan di database.']); 
            exit; 
        }

        // Insert varian baru
        $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, variant_name, variant_value, additional_price, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $variant_name, $variant_value, $additional_price, $stock]);
        $new_id = $pdo->lastInsertId();

        // Update parent product stock
        updateProductStockFromVariants($pdo, $product_id);

        // Siapkan data varian yang baru ditambahkan untuk dikembalikan ke frontend
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
    } catch (PDOException $e) {
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
        // Ambil product_id sebelum update varian
        $vStmt = $pdo->prepare("SELECT product_id FROM product_variants WHERE id = ?");
        $vStmt->execute([$id]);
        $vData = $vStmt->fetch(PDO::FETCH_ASSOC);
        $product_id = $vData ? intval($vData['product_id']) : 0;

        $stmt = $pdo->prepare("UPDATE product_variants SET variant_name=?, variant_value=?, additional_price=?, stock=? WHERE id=?");
        $stmt->execute([$variant_name, $variant_value, $additional_price, $stock, $id]);
        
        if ($product_id > 0) {
            updateProductStockFromVariants($pdo, $product_id);
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Data varian berhasil diperbarui.']);
    } catch (PDOException $e) {
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
        // Ambil product_id sebelum menghapus varian
        $vStmt = $pdo->prepare("SELECT product_id FROM product_variants WHERE id = ?");
        $vStmt->execute([$id]);
        $vData = $vStmt->fetch(PDO::FETCH_ASSOC);
        $product_id = $vData ? intval($vData['product_id']) : 0;

        $stmt = $pdo->prepare("DELETE FROM product_variants WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($product_id > 0) {
            updateProductStockFromVariants($pdo, $product_id);
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Varian berhasil dihapus.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus varian.']);
    }
    exit;
}

// Helper untuk update total stok produk dari semua variannya
function updateProductStockFromVariants($pdo, $product_id) {
    if ($product_id <= 0) return;
    try {
        $stmt = $pdo->prepare("SELECT SUM(stock) as total_stock, COUNT(*) as variant_count FROM product_variants WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Jika ada varian, set stok produk ke total stok varian. Jika tidak ada varian tersisa, set ke 0
            $total_stock = $result['variant_count'] > 0 ? intval($result['total_stock']) : 0;
            $update = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $update->execute([$total_stock, $product_id]);
        }
    } catch (PDOException $e) {
        // Abaikan atau log error jika ada
    }
}

// Jika action tidak ada yang cocok
echo json_encode(['status' => 'error', 'message' => 'Aksi sistem tidak dikenali.']);