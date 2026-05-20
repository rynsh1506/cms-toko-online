<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

// Fetch Configurations for Dynamic Styles
$stmt = $pdo->query("SELECT section_key, content_value FROM landing_configs");
$configs_raw = $stmt->fetchAll();
$configs = [];
foreach ($configs_raw as $c) {
    $configs[$c['section_key']] = $c['content_value'];
}
$primary_color = $configs['primary_color'] ?? '#2563eb';

$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    if (count($ids) > 0) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll();
        
        foreach ($products as $p) {
            $qty = $_SESSION['cart'][$p['id']];
            $subtotal = $p['price'] * $qty;
            $total_price += $subtotal;
            
            $cart_items[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'price' => $p['price'],
                'image_url' => $p['image_url'],
                'stock' => $p['stock'],
                'qty' => $qty,
                'subtotal' => $subtotal
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Pro-Store CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-white shadow-md">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="index.php?page=home" class="text-xl font-bold text-gray-800 hover:text-gray-600 transition">
                    Pro-Store <span class="text-[<?= $primary_color ?>]">Toko</span>
                </a>
                <div class="flex items-center space-x-4">
                    <a href="index.php?page=home" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition flex items-center space-x-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali Belanja</span>
                    </a>
                    <?php if (isAuth()): ?>
                        <a href="index.php?page=orders" class="text-sm font-semibold text-gray-700 hover:text-gray-900 transition">Pesanan Saya</a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="index.php?page=admin" class="text-sm font-semibold text-gray-700 hover:text-gray-900 transition">Admin Panel</a>
                        <?php endif; ?>
                        <a href="index.php?page=auth_process&action=logout" class="text-sm font-semibold text-red-600 hover:text-red-800 transition">Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-4xl mx-auto px-4 py-12 flex-1 w-full">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Keranjang Belanja Anda</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="bg-white p-8 rounded-lg shadow text-center border">
                <p class="text-gray-500 mb-6">Keranjang belanja Anda masih kosong.</p>
                <a href="index.php?page=home" class="inline-block bg-[<?= $primary_color ?>] text-white font-bold py-2 px-6 rounded hover:opacity-90 transition">
                    Cari Produk
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200 text-gray-700 text-sm font-semibold">
                            <th class="p-4">Produk</th>
                            <th class="p-4">Harga</th>
                            <th class="p-4 text-center">Jumlah</th>
                            <th class="p-4 text-right">Subtotal</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="p-4 flex items-center space-x-4">
                                    <img src="<?= htmlspecialchars($item['image_url'] ?? 'https://placehold.co/100') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="h-16 w-16 object-cover rounded">
                                    <span class="font-semibold text-gray-900"><?= htmlspecialchars($item['name']) ?></span>
                                </td>
                                <td class="p-4 text-gray-600">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                <td class="p-4">
                                    <form action="index.php?page=cart_process&action=update" method="POST" class="flex items-center justify-center space-x-1">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" max="<?= $item['stock'] ?>" class="w-16 px-2 py-1 border rounded text-center focus:outline-none focus:ring-1 focus:ring-blue-300">
                                        <button type="submit" class="bg-gray-200 text-gray-700 p-1 px-2 rounded hover:bg-gray-300 transition text-xs font-semibold">Update</button>
                                    </form>
                                    <p class="text-center text-xs text-gray-400 mt-1">Maks: <?= $item['stock'] ?></p>
                                </td>
                                <td class="p-4 text-right text-gray-900 font-semibold">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                <td class="p-4 text-center">
                                    <a href="index.php?page=cart_process&action=remove&id=<?= $item['id'] ?>" class="text-red-500 hover:text-red-700 font-semibold text-sm transition">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Summary & Checkout Button -->
                <div class="p-6 bg-gray-50 flex flex-col md:flex-row justify-between items-center border-t border-gray-200">
                    <a href="index.php?page=cart_process&action=clear" class="text-sm text-red-600 hover:underline mb-4 md:mb-0">Kosongkan Keranjang</a>
                    
                    <div class="text-right">
                        <div class="mb-4">
                            <span class="text-gray-600 mr-2 text-lg">Total Pembayaran:</span>
                            <span class="text-2xl font-bold text-[<?= $primary_color ?>]">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                        </div>
                        <!-- Tombol Checkout akan diarahkan ke Checkout di Phase 5 -->
                        <a href="index.php?page=checkout" class="inline-block bg-[<?= $primary_color ?>] text-white font-bold py-3 px-8 rounded-lg shadow hover:opacity-90 transition">
                            Lanjut ke Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-6">
        <div class="max-w-6xl mx-auto px-4 text-center text-sm text-gray-400">
            <p>&copy; <?= date('Y') ?> Pro-Store CMS. Powered by Mini-Framework.</p>
        </div>
    </footer>

</body>
</html>
