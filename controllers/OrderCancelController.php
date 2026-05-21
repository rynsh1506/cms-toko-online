<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

header('Content-Type: application/json');

if (!isAuth()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if ($order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID pesanan tidak valid.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Ambil data order untuk divalidasi dengan FOR UPDATE
        $stmtOrder = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ? FOR UPDATE");
        $stmtOrder->execute([$order_id, $user_id]);
        $order = $stmtOrder->fetch();

        if (!$order) {
            throw new Exception("Pesanan tidak ditemukan.");
        }

        if ($order['status'] !== 'pending') {
            throw new Exception("Hanya pesanan berstatus 'Pending' yang dapat dibatalkan.");
        }

        // 1. Update status pesanan ke cancelled
        $stmtCancel = $pdo->prepare("
            UPDATE orders 
            SET status = 'cancelled', 
                cancel_reason = 'Dibatalkan oleh pembeli', 
                cancelled_at = NOW() 
            WHERE id = ?
        ");
        $stmtCancel->execute([$order_id]);

        // 2. Kembalikan stok produk & variannya
        $stmtItems = $pdo->prepare("SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$order_id]);
        $items = $stmtItems->fetchAll();

        $stmtRestoreProductStock = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmtRestoreVariantStock = $pdo->prepare("UPDATE product_variants SET stock = stock + ? WHERE id = ?");

        foreach ($items as $item) {
            $qty = intval($item['quantity']);
            $pId = intval($item['product_id']);
            $vId = $item['variant_id'] ? intval($item['variant_id']) : null;

            if ($vId) {
                // Kembalikan stok varian
                $stmtRestoreVariantStock->execute([$qty, $vId]);
            } else {
                // Produk normal tanpa varian
                $stmtRestoreProductStock->execute([$qty, $pId]);
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pesanan Anda berhasil dibatalkan dan stok dikembalikan.'
        ]);
        exit;

    } catch (\Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
    exit;
}
