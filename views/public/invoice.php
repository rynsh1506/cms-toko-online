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

// Fetch order with bank details
$stmt = $pdo->prepare("
    SELECT o.*, b.bank_name, b.account_number, b.account_name 
    FROM orders o
    LEFT JOIN bank_accounts b ON o.bank_account_id = b.id
    WHERE o.id = ?
");
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
$primary_color = $configs['primary_color'] ?? '#6366f1';

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
$stmtAdmin = $pdo->query("SELECT phone FROM users WHERE role = 'admin' LIMIT 1");
$admin_user = $stmtAdmin->fetch();
$admin_phone = '6281234567890'; // Default jika tidak ada
if ($admin_user && !empty($admin_user['phone'])) {
    // Hilangkan karakter non-angka seperti + atau spasi agar format wa.me valid
    $admin_phone = preg_replace('/[^0-9]/', '', $admin_user['phone']);
}
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
    <title>Invoice #<?= $order['id'] ?> - NusaBay</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <!-- Tailwind CSS -->
    <script src="assets/js/tailwind.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '<?= $primary_color ?>',
                    }
                }
            }
        }
    </script>
    <script>
        // Init theme
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <!-- Google Fonts Outfit & Inter -->
    <link href="assets/css/fonts.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4, .font-display {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-300">

    <!-- Navbar -->
    <nav class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 sticky top-0 z-50 print:hidden transition-colors duration-300">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex justify-between items-center h-20">
                <a href="index.php?page=home" class="text-2xl font-black tracking-tight text-slate-900 dark:text-white hover:opacity-85 transition font-display flex items-center space-x-2">
                    <!-- Geometric NusaBay Logo -->
                                        <svg class="h-9 w-9 rounded-xl shadow-lg shadow-indigo-500/20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="12" fill="url(#logo-grad-nav-global)" />
                        <rect x="10" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                        <rect x="30" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                        <rect x="20" y="6" width="8" height="36" rx="2" fill="#ffffff" transform="rotate(-32 24 24)" />
                        <defs>
                            <linearGradient id="logo-grad-nav-global" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#6366f1"/>
                                <stop offset="1" stop-color="#a855f7"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span>Nusa<span class="text-primary">Bay</span></span>
                </a>
                <div class="flex items-center space-x-6">
                    <a href="index.php?page=orders" class="text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition flex items-center space-x-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Daftar Pesanan</span>
                    </a>

                    <!-- Dark mode toggle -->
                    <button id="theme-toggle" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        <svg id="theme-toggle-sun" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-3.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                        <svg id="theme-toggle-moon" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-3xl mx-auto px-6 py-12 flex-1 w-full">
        
        <!-- Cetak Invoice & Notifikasi Sukses -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-400 p-4 rounded-r-xl mb-6 shadow-sm print:hidden text-xs font-semibold">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Notifikasi Pembatalan -->
        <?php if ($order['status'] === 'cancelled'): ?>
            <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-4 rounded-r-xl mb-6 shadow-sm text-xs flex items-start space-x-3">
                <svg class="h-5 w-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="font-bold">Pesanan Ini Telah Dibatalkan</p>
                    <p class="mt-1 text-slate-600 dark:text-slate-400 font-light">Waktu Pembatalan: <span class="font-mono font-medium"><?= date('d F Y, H:i', strtotime($order['cancelled_at'])) ?></span></p>
                    <p class="mt-0.5 text-slate-600 dark:text-slate-400 font-light">Alasan Pembatalan: <span class="italic">"<?= htmlspecialchars($order['cancel_reason']) ?>"</span></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Panel Invoice -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            
            <!-- Header Invoice -->
            <div class="p-8 bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white font-display tracking-tight">INVOICE</h1>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Order ID: <span class="font-bold text-slate-800 dark:text-slate-200">#<?= $order['id'] ?></span></p>
                </div>
                <div class="text-left md:text-right">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 block uppercase tracking-wider">Tanggal Pesanan</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm font-mono mt-1 block"><?= date('d F Y, H:i', strtotime($order['created_at'])) ?></span>
                    
                    <div class="mt-2.5">
                        <?php if ($order['status'] === 'pending'): ?>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400">Menunggu Pembayaran</span>
                        <?php elseif ($order['status'] === 'paid'): ?>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400">Sudah Dibayar</span>
                        <?php elseif ($order['status'] === 'shipped'): ?>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400">Dikirim</span>
                        <?php elseif ($order['status'] === 'done'): ?>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400">Selesai</span>
                        <?php elseif ($order['status'] === 'cancelled'): ?>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400">Dibatalkan</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Detail Alamat Pengiriman -->
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Tujuan Pengiriman</h3>
                    <p class="font-bold text-slate-800 dark:text-slate-200 text-sm"><?= htmlspecialchars($order['customer_name']) ?></p>
                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-xs font-semibold"><?= htmlspecialchars($order['customer_phone']) ?></p>
                    <p class="text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed text-xs"><?= htmlspecialchars($order['customer_address']) ?></p>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Metode Pembayaran</h3>
                    <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">Transfer Bank Manual</p>
                    <?php if ($order['bank_name']): ?>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mt-1.5">
                            Bank <?= htmlspecialchars($order['bank_name']) ?>: <strong class="text-slate-800 dark:text-slate-200 font-mono"><?= htmlspecialchars($order['account_number']) ?></strong><br>
                            a/n <?= htmlspecialchars($order['account_name']) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 mt-1">Metode pembayaran tidak valid.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Daftar Item Produk -->
            <div class="p-8">
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4">Rincian Belanja</h3>
                <div class="space-y-4">
                    <?php foreach ($items as $item): ?>
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div class="flex items-center space-x-4">
                                <img src="<?= htmlspecialchars($item['image_url'] ?? 'https://placehold.co/100') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="h-12 w-12 object-cover rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                                <div>
                                    <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs"><?= htmlspecialchars($item['name']) ?></h4>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">Rp <?= number_format($item['price'], 0, ',', '.') ?> x <?= $item['quantity'] ?></p>
                                </div>
                            </div>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-xs font-mono">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Perhitungan Akhir -->
                <div class="mt-8 border-t border-slate-100 dark:border-slate-800 pt-6 flex flex-col items-end">
                    <div class="w-full md:w-80 space-y-2 text-xs">
                        <div class="flex justify-between text-slate-500 dark:text-slate-400">
                            <span>Subtotal:</span>
                            <span class="font-semibold font-mono">Rp <?= number_format($order['total_price'] - $order['unique_code'] + $order['discount_amount'], 0, ',', '.') ?></span>
                        </div>
                        <?php if ($order['discount_amount'] > 0): ?>
                            <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                                <span>Diskon Promo (-):</span>
                                <span class="font-mono">-Rp <?= number_format($order['discount_amount'], 0, ',', '.') ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between text-slate-500 dark:text-slate-400">
                            <span>Kode Unik (+):</span>
                            <span class="font-bold text-amber-600 dark:text-amber-500 font-mono">+Rp <?= number_format($order['unique_code'], 0, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 dark:border-slate-800 pt-3 text-sm font-extrabold text-slate-900 dark:text-white">
                            <span>Total Transfer:</span>
                            <span class="text-primary font-mono">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instruksi Transfer & Tombol Konfirmasi WA -->
            <?php if ($order['status'] !== 'cancelled'): ?>
                <div class="p-8 bg-indigo-50/50 dark:bg-indigo-950/20 border-t border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center gap-6 print:hidden">
                    <div class="text-center md:text-left">
                        <p class="text-xs font-bold text-indigo-900 dark:text-indigo-300 mb-1">Penting untuk Verifikasi!</p>
                        <p class="text-[10px] text-indigo-700 dark:text-indigo-400 leading-relaxed font-light font-semibold">
                            Harap transfer tepat hingga 3 digit terakhir. Setelah melakukan pembayaran, segera konfirmasi untuk mempercepat pengiriman barang Anda.
                        </p>
                    </div>
                    <div class="flex gap-3 w-full md:w-auto">
                        <button onclick="window.print()" class="w-full md:w-auto border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 font-bold py-2 px-4 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition text-xs shadow-sm">
                            Cetak Nota
                        </button>
                        <a href="<?= $wa_link ?>" target="_blank" class="w-full md:w-auto bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-green-500/10 transition flex items-center justify-center space-x-2 text-xs">
                            <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                                <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.333 4.993L2 22l5.233-1.371a9.936 9.936 0 004.777 1.218h.005c5.505 0 9.987-4.479 9.988-9.986A9.972 9.972 0 0012.012 2zm5.73 14.184c-.313.88-1.56 1.621-2.148 1.68-.482.05-1.107.078-2.61-.54-2.023-.831-3.328-2.887-3.428-3.021-.1-.133-.805-.968-.805-1.847 0-.878.46-1.31.625-1.488.164-.179.359-.224.479-.224h.343c.108 0 .252-.041.396.302.144.343.493 1.205.536 1.293.043.088.072.19.014.302-.057.113-.086.183-.172.283-.086.102-.18.228-.258.309-.086.088-.176.183-.076.353.1.171.444.733.953 1.187.658.583 1.21.764 1.38.849.171.085.271.071.371-.044.1-.115.43-.5.545-.672.115-.172.23-.143.389-.085.158.058 1.005.474 1.178.56.172.087.288.13.33.202.043.072.043.415-.27 1.294z"/>
                            </svg>
                            <span>Konfirmasi WhatsApp</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-6 mt-auto print:hidden">
        <div class="max-w-6xl mx-auto px-6 text-center text-xs">
            <p>&copy; <?= date('Y') ?> NusaBay. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Theme toggle logic
            const themeToggleBtn = document.getElementById('theme-toggle');
            const themeToggleSun = document.getElementById('theme-toggle-sun');
            const themeToggleMoon = document.getElementById('theme-toggle-moon');

            if (document.documentElement.classList.contains('dark')) {
                themeToggleSun.classList.remove('hidden');
            } else {
                themeToggleMoon.classList.remove('hidden');
            }

            themeToggleBtn.addEventListener('click', function() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                    themeToggleSun.classList.add('hidden');
                    themeToggleMoon.classList.remove('hidden');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                    themeToggleMoon.classList.add('hidden');
                    themeToggleSun.classList.remove('hidden');
                }
            });
        });
    </script>
</body>
</html>
