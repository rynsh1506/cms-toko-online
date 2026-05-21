<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

$stmt = $pdo->query("SELECT section_key, content_value FROM landing_configs");
$configs_raw = $stmt->fetchAll();
$configs = [];
foreach ($configs_raw as $c) {
    $configs[$c['section_key']] = $c['content_value'];
}
$primary_color = $configs['primary_color'] ?? '#6366f1';

$cart_items = [];
$total_price = 0;
$cart_count = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $cart_key => $qty) {
        $parts = explode('-', $cart_key);
        $product_id = intval($parts[0] ?? 0);
        $variant_id = intval($parts[1] ?? 0);

        if (isset($_SESSION['cart_meta'][$cart_key])) {
            $meta = $_SESSION['cart_meta'][$cart_key];
            $stock = $meta['stock'];
            $price = $meta['price'];
            $name = $meta['name'];
            $img = $meta['image_url'];
            $v_info = $meta['variant_info'] ?? '';
        } else {
            $stmtP = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmtP->execute([$product_id]);
            $p = $stmtP->fetch();
            if (!$p) continue;

            $stock = $p['stock'];
            $price = floatval($p['price']);
            $name = $p['name'];
            $img = $p['image_url'];
            $v_info = '';

            if ($variant_id > 0) {
                $stmtV = $pdo->prepare("SELECT * FROM product_variants WHERE id = ?");
                $stmtV->execute([$variant_id]);
                $v = $stmtV->fetch();
                if ($v) {
                    $stock = $v['stock'];
                    $price += floatval($v['additional_price']);
                    $v_info = $v['variant_name'] . ': ' . $v['variant_value'];
                }
            }

            $_SESSION['cart_meta'][$cart_key] = [
                'product_id'   => $product_id,
                'variant_id'   => $variant_id,
                'name'         => $name,
                'price'        => $price,
                'image_url'    => $img,
                'stock'        => $stock,
                'variant_info' => $v_info
            ];
        }

        if ($qty > $stock) {
            $qty = ($stock > 0) ? $stock : 1;
            $_SESSION['cart'][$cart_key] = $qty;
        }

        $subtotal = $price * $qty;
        $total_price += $subtotal;
        $cart_count += $qty;

        $cart_items[] = [
            'cart_key'     => $cart_key,
            'product_id'   => $product_id,
            'variant_id'   => $variant_id,
            'name'         => $name,
            'price'        => $price,
            'image_url'    => $img,
            'stock'        => $stock,
            'variant_info' => $v_info,
            'qty'          => $qty,
            'subtotal'     => $subtotal,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - NusaBay</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
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

    <nav class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 sticky top-0 z-50 transition-colors duration-300">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex justify-between items-center h-20">
                <a href="index.php?page=home" class="text-2xl font-black tracking-tight text-slate-900 dark:text-white hover:opacity-85 transition font-display flex items-center space-x-2">
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
                    <a href="index.php?page=home" class="text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition flex items-center space-x-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="hidden sm:inline">Kembali Belanja</span>
                    </a>

                    <a href="index.php?page=cart" id="cart-link" class="relative p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span id="cart-badge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-md shadow-primary/20 <?= $cart_count > 0 ? '' : 'hidden' ?>">
                            <?= $cart_count ?>
                        </span>
                    </a>

                    <?php if (isAuth()): ?>
                        <a href="index.php?page=orders" class="hidden sm:inline text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition">Pesanan Saya</a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="index.php?page=admin" class="hidden md:inline text-sm font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-3.5 py-1.5 rounded-xl transition">Admin Panel</a>
                        <?php endif; ?>
                        <a href="index.php?page=auth_process&action=logout" class="text-sm font-bold text-red-500 hover:text-red-700 transition">Logout</a>
                    <?php endif; ?>

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

    <main class="max-w-6xl mx-auto px-6 py-10 flex-1 w-full">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-8 font-display">Keranjang Belanja</h1>

        <div id="cart-empty-placeholder" class="<?= empty($cart_items) ? '' : 'hidden' ?> bg-white dark:bg-slate-900 rounded-3xl p-16 text-center border border-slate-100 dark:border-slate-800 shadow-sm max-w-lg mx-auto mt-10">
            <svg class="h-16 w-16 text-slate-200 dark:text-slate-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <p class="text-slate-500 dark:text-slate-400 font-semibold mb-6">Keranjang belanjamu masih kosong nih.</p>
            <a href="index.php?page=home" class="inline-flex items-center space-x-2 bg-primary text-white font-bold py-3.5 px-6 rounded-2xl hover:opacity-90 transition text-sm shadow-lg shadow-primary/25">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <span>Cari Produk Sekarang</span>
            </a>
        </div>

        <?php if (!empty($cart_items)): ?>
            <div id="cart-container" class="flex flex-col lg:flex-row gap-8 items-start relative">

                <div class="w-full lg:w-2/3 space-y-4">

                    <div class="flex justify-between items-center bg-white dark:bg-slate-900 rounded-2xl p-4 px-5 border border-slate-100 dark:border-slate-800 shadow-sm">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" id="select-all-checkbox" class="rounded border-slate-300 text-primary focus:ring-primary h-5 w-5 accent-indigo-600 cursor-pointer" checked>
                            <label for="select-all-checkbox" class="text-sm font-bold text-slate-600 dark:text-slate-400 cursor-pointer select-none">Pilih Semua</label>
                        </div>
                        <button type="button" id="btn-clear-cart" class="text-xs font-bold text-rose-500 hover:text-rose-700 transition flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Hapus Semua
                        </button>
                    </div>

                    <div id="cart-items-wrapper" class="space-y-4">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item-row bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4 transition" data-cart-key="<?= htmlspecialchars($item['cart_key']) ?>">

                                <input type="checkbox" class="item-checkbox rounded border-slate-300 text-primary focus:ring-primary h-5 w-5 accent-indigo-600 cursor-pointer" data-cart-key="<?= htmlspecialchars($item['cart_key']) ?>" data-price="<?= $item['price'] ?>" checked>

                                <a href="index.php?page=product_detail&id=<?= $item['product_id'] ?>" class="shrink-0 block ml-1">
                                    <img src="<?= htmlspecialchars($item['image_url'] ?? 'https://placehold.co/100') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="h-20 w-20 object-cover rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
                                </a>

                                <div class="flex-1 min-w-0">
                                    <a href="index.php?page=product_detail&id=<?= $item['product_id'] ?>" class="text-base font-bold text-slate-800 dark:text-white hover:text-primary transition line-clamp-1"><?= htmlspecialchars($item['name']) ?></a>
                                    <?php if (!empty($item['variant_info'])): ?>
                                        <p class="text-xs text-primary font-semibold mt-0.5"><?= htmlspecialchars($item['variant_info']) ?></p>
                                    <?php endif; ?>
                                    <div class="mt-1 text-sm font-bold text-slate-900 dark:text-slate-100 font-mono">
                                        Rp <?= number_format($item['price'], 0, ',', '.') ?>
                                    </div>
                                </div>

                                <div class="shrink-0 flex flex-col items-end justify-between h-20">
                                    <button type="button" class="btn-remove-item text-slate-400 hover:text-rose-500 transition p-1" data-cart-key="<?= htmlspecialchars($item['cart_key']) ?>">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>

                                    <div class="flex flex-col items-center">
                                        <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-950 overflow-hidden shadow-sm scale-90 origin-right">
                                            <button type="button" class="btn-qty-minus h-8 w-8 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" /></svg></button>
                                            <input type="text" value="<?= $item['qty'] ?>" class="input-qty h-8 w-10 bg-transparent text-center font-bold text-xs text-slate-800 dark:text-white focus:outline-none" data-cart-key="<?= htmlspecialchars($item['cart_key']) ?>" data-max="<?= $item['stock'] ?>" readonly>
                                            <button type="button" class="btn-qty-plus h-8 w-8 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg></button>
                                        </div>
                                        <span class="text-[10px] text-slate-400 mt-0.5">Stok: <?= $item['stock'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="w-full lg:w-1/3 lg:sticky lg:top-28">
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-md">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 font-display uppercase tracking-wider">Ringkasan Belanja</h2>

                        <div class="space-y-3 text-sm border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                            <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                <span>Total Harga (<span id="summary-count">0</span> barang)</span>
                                <span id="summary-total" class="font-bold text-slate-800 dark:text-slate-200 font-mono">Rp 0</span>
                            </div>
                        </div>

                        <div class="mb-6 flex justify-between items-center">
                            <span class="text-sm font-bold text-slate-800 dark:text-white">Total Tagihan</span>
                            <span id="grand-total" class="text-xl font-black text-primary font-display">Rp 0</span>
                        </div>

                        <button type="button" id="btn-checkout-action" class="flex items-center justify-center space-x-2 w-full bg-primary text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-primary/25 hover:opacity-90 active:scale-[0.98] transition text-sm">
                            <span>Beli (<span id="btn-checkout-count">0</span>)</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </button>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </main>

    <?php require __DIR__ . '/components/footer.php'; ?>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script>
        window.NusaBayCart = {
            primaryColor: <?= json_encode($primary_color) ?>
        };
    </script>
    <script src="assets/js/pages/cart.js"></script>
</body>
</html>
