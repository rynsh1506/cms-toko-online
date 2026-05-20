<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

// Wajib Login
if (!isAuth()) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu.";
    redirect('index.php?page=login');
}

$order_id = intval($_GET['id'] ?? 0);
if ($order_id <= 0) {
    redirect('index.php?page=home');
}

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Pesanan tidak ditemukan.";
    redirect('index.php?page=home');
}

// Robustness: pastikan hanya pemilik pesanan atau admin yang dapat melihat invoice ini
if ($order['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk melihat pesanan ini.";
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

// Fetch order items joined with product info
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_url 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Persiapan Link WhatsApp
$admin_phone = '6281234567890'; // Nomor HP Admin default (Ganti sesuai kebutuhan)
$wa_message = "Halo Admin,\nSaya ingin melakukan konfirmasi pembayaran untuk pesanan berikut:\n\n"
            . "• Order ID: #" . $order['id'] . "\n"
            . "• Nama Penerima: " . $order['customer_name'] . "\n"
            . "• Total Pembayaran: Rp " . number_format($order['total_price'], 0, ',', '.') . "\n"
            . "• Status: " . ucfirst($order['status']) . "\n\n"
            . "Mohon untuk segera dikonfirmasi dan diproses. Terima kasih!";
$wa_link = "https://wa.me/" . $admin_phone . "?text=" . urlencode($wa_message);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $order['id'] ?> - Pro-Store CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-white shadow-md print:hidden">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="index.php?page=home" class="text-xl font-bold text-gray-800 hover:text-gray-600 transition">
                    Pro-Store <span class="text-[<?= $primary_color ?>]">Toko</span>
                </a>
                <div class="flex items-center space-x-4">
                    <a href="index.php?page=orders" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition flex items-center space-x-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Daftar Pesanan</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-3xl mx-auto px-4 py-12 flex-1 w-full">
        
        <!-- Cetak Invoice & Notifikasi Sukses -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6 shadow-sm border border-green-200 print:hidden">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Panel Invoice -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
            
            <!-- Header Invoice -->
            <div class="p-6 md:p-8 bg-gray-50 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900">INVOICE</h1>
                    <p class="text-sm text-gray-500">Order ID: <span class="font-bold text-gray-800">#<?= $order['id'] ?></span></p>
                </div>
                <div class="text-left md:text-right">
                    <span class="text-sm text-gray-500 block">Tanggal Pesanan:</span>
                    <span class="font-semibold text-gray-900"><?= date('d F Y, H:i', strtotime($order['created_at'])) ?></span>
                    
                    <div class="mt-2">
                        <?php if ($order['status'] === 'pending'): ?>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu Pembayaran</span>
                        <?php elseif ($order['status'] === 'paid'): ?>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Sudah Dibayar</span>
                        <?php elseif ($order['status'] === 'shipped'): ?>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Dikirim</span>
                        <?php elseif ($order['status'] === 'done'): ?>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Detail Alamat Pengiriman -->
            <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-8 border-b border-gray-100">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Tujuan Pengiriman</h3>
                    <p class="font-bold text-gray-900 text-base"><?= htmlspecialchars($order['customer_name']) ?></p>
                    <p class="text-gray-600 mt-1"><?= htmlspecialchars($order['customer_phone']) ?></p>
                    <p class="text-gray-600 mt-1 whitespace-pre-line text-sm"><?= htmlspecialchars($order['customer_address']) ?></p>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Metode Pembayaran</h3>
                    <p class="font-semibold text-gray-900">Transfer Bank Manual</p>
                    <p class="text-sm text-gray-600 mt-1">Bank BCA: <strong>123-4567-890</strong><br>a/n PT Pro Store International</p>
                </div>
            </div>

            <!-- Daftar Item Produk -->
            <div class="p-6 md:p-8">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Rincian Belanja</h3>
                <div class="space-y-4">
                    <?php foreach ($items as $item): ?>
                        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                            <div class="flex items-center space-x-4">
                                <img src="<?= htmlspecialchars($item['image_url'] ?? 'https://placehold.co/100') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="h-12 w-12 object-cover rounded border">
                                <div>
                                    <h4 class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($item['name']) ?></h4>
                                    <p class="text-xs text-gray-500">Rp <?= number_format($item['price'], 0, ',', '.') ?> x <?= $item['quantity'] ?></p>
                                </div>
                            </div>
                            <span class="font-bold text-gray-900 text-sm">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Perhitungan Akhir -->
                <div class="mt-8 border-t border-gray-200 pt-6 flex flex-col items-end">
                    <div class="w-full md:w-80 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal:</span>
                            <span>Rp <?= number_format($order['total_price'] - $order['unique_code'], 0, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Kode Unik (+):</span>
                            <span><?= $order['unique_code'] ?></span>
                        </div>
                        <div class="flex justify-between border-t pt-2 text-lg font-bold text-gray-900">
                            <span>Total Transfer:</span>
                            <span class="text-[<?= $primary_color ?>]">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instruksi Transfer & Tombol Konfirmasi WA -->
            <div class="p-6 md:p-8 bg-blue-50 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6 print:hidden">
                <div class="text-center md:text-left">
                    <p class="text-sm font-bold text-blue-900 mb-1">Penting untuk Verifikasi!</p>
                    <p class="text-xs text-blue-700 leading-relaxed">
                        Harap transfer tepat hingga 3 digit terakhir. Setelah melakukan pembayaran, segera konfirmasi untuk mempercepat pengiriman barang Anda.
                    </p>
                </div>
                <div class="flex gap-3 w-full md:w-auto">
                    <button onclick="window.print()" class="w-full md:w-auto border border-gray-300 text-gray-700 bg-white font-semibold py-2 px-4 rounded hover:bg-gray-100 transition text-sm">
                        Cetak Nota
                    </button>
                    <a href="<?= $wa_link ?>" target="_blank" class="w-full md:w-auto bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 px-6 rounded-lg shadow transition flex items-center justify-center space-x-2 text-sm">
                        <!-- WhatsApp Icon -->
                        <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                            <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.333 4.993L2 22l5.233-1.371a9.936 9.936 0 004.777 1.218h.005c5.505 0 9.987-4.479 9.988-9.986A9.972 9.972 0 0012.012 2zm5.73 14.184c-.313.88-1.56 1.621-2.148 1.68-.482.05-1.107.078-2.61-.54-2.023-.831-3.328-2.887-3.428-3.021-.1-.133-.805-.968-.805-1.847 0-.878.46-1.31.625-1.488.164-.179.359-.224.479-.224h.343c.108 0 .252-.041.396.302.144.343.493 1.205.536 1.293.043.088.072.19.014.302-.057.113-.086.183-.172.283-.086.102-.18.228-.258.309-.086.088-.176.183-.076.353.1.171.444.733.953 1.187.658.583 1.21.764 1.38.849.171.085.271.071.371-.044.1-.115.43-.5.545-.672.115-.172.23-.143.389-.085.158.058 1.005.474 1.178.56.172.087.288.13.33.202.043.072.043.415-.27 1.294z"/>
                        </svg>
                        <span>Konfirmasi via WhatsApp</span>
                    </a>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-6 mt-auto print:hidden">
        <div class="max-w-6xl mx-auto px-4 text-center text-sm text-gray-400">
            <p>&copy; <?= date('Y') ?> Pro-Store CMS. Powered by Mini-Framework.</p>
        </div>
    </footer>

</body>
</html>
