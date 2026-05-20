<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

// Pastikan pengguna sudah login
if (!isAuth()) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu untuk melakukan checkout.";
    redirect('index.php?page=login');
}

// Pastikan keranjang tidak kosong
if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Keranjang Anda kosong.";
    redirect('index.php?page=home');
}

// Fetch Configurations for Dynamic Styles
$stmt = $pdo->query("SELECT section_key, content_value FROM landing_configs");
$configs_raw = $stmt->fetchAll();
$configs = [];
foreach ($configs_raw as $c) {
    $configs[$c['section_key']] = $c['content_value'];
}
$primary_color = $configs['primary_color'] ?? '#2563eb';

// Hitung ringkasan
$cart_items = [];
$total_price = 0;
$ids = array_keys($_SESSION['cart']);

if (count($ids) > 0) {
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    
    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $subtotal = $p['price'] * $qty;
        $total_price += $subtotal;
        
        $cart_items[] = [
            'name' => $p['name'],
            'qty' => $qty,
            'subtotal' => $subtotal
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Pro-Store CMS</title>
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
                    <a href="index.php?page=cart" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition flex items-center space-x-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali ke Keranjang</span>
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
    <main class="max-w-6xl mx-auto px-4 py-12 flex-1 w-full grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Kolom Form Pengiriman -->
        <div class="md:col-span-2">
            <div class="bg-white p-8 rounded-lg shadow-md border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Detail Pengiriman</h2>
                
                <form action="index.php?page=checkout_process" method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Penerima</label>
                        <input type="text" name="customer_name" required value="<?= htmlspecialchars($_SESSION['name'] ?? '') ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nomor WhatsApp / HP</label>
                        <input type="text" name="customer_phone" required placeholder="081234567890" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
                    </div>
                    
                    <div class="mb-8">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Lengkap</label>
                        <textarea name="customer_address" required rows="4" placeholder="Jalan, RT/RW, Kecamatan, Kota, Kode Pos" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-[<?= $primary_color ?>] text-white font-bold py-3 px-4 rounded-lg hover:opacity-90 transition duration-300 text-lg shadow-lg">
                        Selesaikan Pesanan
                    </button>
                </form>
            </div>
        </div>

        <!-- Kolom Ringkasan Pesanan -->
        <div>
            <div class="bg-gray-100 p-6 rounded-lg shadow-inner border border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 mb-4 border-b pb-2">Ringkasan Pesanan</h3>
                
                <div class="space-y-3 mb-6 max-h-60 overflow-y-auto">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 truncate mr-2"><?= htmlspecialchars($item['name']) ?> <span class="text-gray-400">x<?= $item['qty'] ?></span></span>
                            <span class="text-gray-800 font-medium whitespace-nowrap">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="border-t border-gray-300 pt-4 flex justify-between items-center mb-2">
                    <span class="text-gray-700 font-bold">Total Sementara</span>
                    <span class="text-gray-900 font-bold">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                    <span>Kode Unik</span>
                    <span>(Akan di-generate)</span>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 p-3 rounded text-sm text-blue-800 flex items-start space-x-2">
                    <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>Kode unik transfer akan ditambahkan ke total belanja Anda setelah menekan tombol "Selesaikan Pesanan".</p>
                </div>
            </div>
        </div>
        
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-6 mt-auto">
        <div class="max-w-6xl mx-auto px-4 text-center text-sm text-gray-400">
            <p>&copy; <?= date('Y') ?> Pro-Store CMS. Powered by Mini-Framework.</p>
        </div>
    </footer>

</body>
</html>
