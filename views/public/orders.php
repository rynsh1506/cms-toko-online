<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

// Pastikan user sudah login
if (!isAuth()) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu.";
    redirect('index.php?page=login');
}

$user_id = $_SESSION['user_id'];

// Ambil konfigurasi landing page untuk styles
$stmt = $pdo->query("SELECT section_key, content_value FROM landing_configs");
$configs_raw = $stmt->fetchAll();
$configs = [];
foreach ($configs_raw as $c) {
    $configs[$c['section_key']] = $c['content_value'];
}
$primary_color = $configs['primary_color'] ?? '#2563eb';

// Ambil riwayat order milik user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Pro-Store CMS</title>
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
                    <a href="index.php?page=home" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition">Beranda</a>
                    <a href="index.php?page=cart" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition">Keranjang</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-4xl mx-auto px-4 py-12 flex-1 w-full">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Riwayat Pesanan Saya</h1>

        <?php if (empty($orders)): ?>
            <div class="bg-white p-8 rounded-lg shadow text-center border">
                <p class="text-gray-500 mb-6 font-medium">Anda belum pernah melakukan pemesanan.</p>
                <a href="index.php?page=home" class="inline-block bg-[<?= $primary_color ?>] text-white font-bold py-2 px-6 rounded hover:opacity-90 transition">
                    Belanja Sekarang
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-200 text-gray-700 text-sm font-semibold">
                                <th class="p-4">Order ID</th>
                                <th class="p-4">Tanggal</th>
                                <th class="p-4">Total Pembayaran</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="p-4 font-bold text-gray-900">#<?= $order['id'] ?></td>
                                    <td class="p-4 text-gray-600 text-sm"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                                    <td class="p-4 text-gray-900 font-semibold">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                                    <td class="p-4">
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu Pembayaran</span>
                                        <?php elseif ($order['status'] === 'paid'): ?>
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Sudah Dibayar</span>
                                        <?php elseif ($order['status'] === 'shipped'): ?>
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Dikirim</span>
                                        <?php elseif ($order['status'] === 'done'): ?>
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <a href="index.php?page=invoice&id=<?= $order['id'] ?>" class="text-[<?= $primary_color ?>] hover:underline font-bold text-sm">Lihat Detail / Invoice</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
