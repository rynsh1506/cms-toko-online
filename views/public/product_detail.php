<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

// Fetch Config
$stmt = $pdo->query("SELECT section_key, content_value FROM landing_configs");
$configs = [];
foreach ($stmt->fetchAll() as $c) { $configs[$c['section_key']] = $c['content_value']; }
$primary_color = $configs['primary_color'] ?? '#6366f1';

// Get product ID
$product_id = intval($_GET['id'] ?? 0);
if ($product_id <= 0) { redirect('index.php?page=home'); }

// Fetch product with category
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) { redirect('index.php?page=home'); }

// Fetch variants
$stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_name ASC, id ASC");
$stmt->execute([$product_id]);
$variants_raw = $stmt->fetchAll();

// Group variants and calculate total variant stock
$variants_grouped = [];
$total_variant_stock = 0;
$has_variants = count($variants_raw) > 0;

if ($has_variants) {
    foreach ($variants_raw as $v) {
        $variants_grouped[$v['variant_name']][] = $v;
        $total_variant_stock += $v['stock'];
    }
}

// Tentukan stok awal yang ditampilkan (jika punya varian, tampilkan total stok normal + varian)
$initial_stock = $has_variants ? ($total_variant_stock + intval($product['stock'])) : intval($product['stock']);

// Cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $q) { $cart_count += $q; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - NusaBay</title>
    <meta name="description" content="<?= htmlspecialchars(substr($product['description'] ?? '', 0, 160)) ?>">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <script src="assets/js/tailwind.js"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { primary: '<?= $primary_color ?>' } } } }
    </script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else { document.documentElement.classList.remove('dark'); }
    </script>
    <link href="assets/css/fonts.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, .font-display { font-family: 'Outfit', sans-serif; }
        .variant-btn.selected { background-color: var(--primary, #6366f1); color: white; border-color: transparent; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-300">

    <nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-slate-100 dark:border-slate-800 transition-colors duration-300">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex justify-between items-center h-20">
                <a href="index.php?page=home" class="text-2xl font-black tracking-tight text-slate-900 dark:text-white hover:opacity-85 transition font-display flex items-center space-x-2">
                    <svg class="h-9 w-9 rounded-xl shadow-lg shadow-indigo-500/20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="12" fill="url(#logo-grad-detail)"/>
                        <rect x="10" y="8" width="8" height="32" rx="2" fill="#ffffff"/>
                        <rect x="30" y="8" width="8" height="32" rx="2" fill="#ffffff"/>
                        <rect x="20" y="6" width="8" height="36" rx="2" fill="#ffffff" transform="rotate(-32 24 24)"/>
                        <defs><linearGradient id="logo-grad-detail" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse"><stop stop-color="#6366f1"/><stop offset="1" stop-color="#a855f7"/></linearGradient></defs>
                    </svg>
                    <span>Nusa<span class="text-primary">Bay</span></span>
                </a>
                <div class="flex items-center space-x-6">
                    <a href="index.php?page=cart" id="cart-link" class="relative p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition flex items-center justify-center">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span id="cart-badge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-md shadow-primary/20 <?= $cart_count > 0 ? '' : 'hidden' ?>"><?= $cart_count ?></span>
                    </a>
                    <?php if (isAuth()): ?>
                        <a href="index.php?page=orders" class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">Pesanan Saya</a>
                        <a href="index.php?page=profile" class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">Profil Saya</a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="index.php?page=admin" class="text-sm font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-3.5 py-1.5 rounded-xl transition">Admin Panel</a>
                        <?php endif; ?>
                        <a href="index.php?page=auth_process&action=logout" class="text-sm font-bold text-red-500 hover:text-red-700 transition">Logout</a>
                    <?php else: ?>
                        <a href="index.php?page=login" class="text-sm font-bold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">Login</a>
                        <a href="index.php?page=register" class="text-sm font-bold text-white bg-indigo-600 px-4 py-2 rounded-xl hover:bg-indigo-700 transition shadow-md shadow-indigo-600/25">Daftar</a>
                    <?php endif; ?>
                    <button id="theme-toggle" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        <svg id="theme-toggle-sun" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-3.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                        <svg id="theme-toggle-moon" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800">
        <div class="max-w-6xl mx-auto px-6 py-3">
            <nav class="flex items-center space-x-2 text-xs text-slate-400 dark:text-slate-500">
                <a href="index.php?page=home" class="hover:text-primary transition">Beranda</a>
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                <?php if ($product['category_name']): ?>
                    <span><?= htmlspecialchars($product['category_name']) ?></span>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                <?php endif; ?>
                <span class="text-slate-600 dark:text-slate-300 font-medium line-clamp-1"><?= htmlspecialchars($product['name']) ?></span>
            </nav>
        </div>
    </div>

    <main class="flex-1 py-10">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-16">

                <div class="space-y-4">
                    <div class="relative rounded-3xl overflow-hidden bg-slate-100 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm aspect-square">
                        <img id="main-product-img"
                             src="<?= htmlspecialchars($product['image_url'] ?? 'https://placehold.co/600x600') ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             class="h-full w-full object-cover">
                        <?php if ($initial_stock <= 0): ?>
                            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-[2px] flex items-center justify-center">
                                <span class="px-4 py-2 bg-rose-600 text-white font-bold text-sm uppercase tracking-wider rounded-xl shadow-lg">Stok Habis</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col space-y-6">
                    <div>
                        <?php if ($product['category_name']): ?>
                            <span class="inline-block px-3 py-1 bg-primary/10 text-primary text-xs font-bold rounded-full mb-3"><?= htmlspecialchars($product['category_name']) ?></span>
                        <?php endif; ?>
                        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white font-display leading-tight"><?= htmlspecialchars($product['name']) ?></h1>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-5 border border-slate-100 dark:border-slate-700">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Harga</span>
                        <div class="flex items-baseline space-x-2 mt-1">
                            <span id="display-price" class="text-3xl font-black text-slate-900 dark:text-white">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                            <span id="price-note" class="text-xs text-slate-400 hidden">(termasuk biaya varian)</span>
                        </div>

                        <div class="flex items-center space-x-2 mt-3" id="stock-indicator-container">
                            <span id="stock-dot" class="w-2 h-2 <?= $initial_stock > 5 ? 'bg-emerald-500' : ($initial_stock > 0 ? 'bg-amber-500' : 'bg-rose-500') ?> rounded-full"></span>
                            <span id="stock-text" class="text-xs font-semibold <?= $initial_stock > 5 ? 'text-emerald-600 dark:text-emerald-400' : ($initial_stock > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') ?>">
                                <?= $initial_stock > 0 ? 'Tersedia: ' . $initial_stock . ' pcs' : 'Stok Habis' ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($product['description'])): ?>
                    <div>
                        <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Deskripsi Produk</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($has_variants): ?>
                    <div class="space-y-3">
                        <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pilih Varian</h2>
                        <div class="flex flex-wrap gap-2 items-center">
                            <!-- Normal Variant Button (Default Selected) -->
                            <button type="button" id="btn-variant-normal"
                                class="variant-btn px-3 py-1.5 rounded-xl border-2 text-xs font-semibold transition duration-150 active:scale-95 selected"
                                style="background-color: <?= $primary_color ?>; color: white; border-color: transparent;"
                                data-variant-id="0"
                                data-variant-name="Varian"
                                data-variant-value="Normal"
                                data-additional-price="0"
                                data-stock="<?= $product['stock'] ?>">
                                Normal
                            </button>

                            <?php foreach ($variants_grouped as $group_name => $group_variants): ?>
                                <?php foreach ($group_variants as $v): ?>
                                <button type="button"
                                    class="variant-btn px-3 py-1.5 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-primary hover:text-primary transition duration-150 active:scale-95 <?= $v['stock'] <= 0 ? 'opacity-40 cursor-not-allowed' : '' ?>"
                                    <?= $v['stock'] <= 0 ? 'disabled' : '' ?>
                                    data-variant-id="<?= $v['id'] ?>"
                                    data-variant-name="<?= htmlspecialchars($group_name) ?>"
                                    data-variant-value="<?= htmlspecialchars($v['variant_value']) ?>"
                                    data-additional-price="<?= $v['additional_price'] ?>"
                                    data-stock="<?= $v['stock'] ?>">
                                    <?= htmlspecialchars($group_name) ?>: <?= htmlspecialchars($v['variant_value']) ?>
                                </button>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="pt-2">
                        <form id="add-to-cart-form" action="index.php?page=cart_process&action=add" method="POST">

                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="variant_id" id="selected-variant-id" value="0">
                            <input type="hidden" name="variant_info" id="selected-variant-info" value="">

                            <div class="flex items-center space-x-3 mb-5">
                                <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Jumlah:</span>
                                <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                    <button type="button" id="qty-minus" class="px-3.5 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition font-bold text-lg">−</button>

                                    <input type="text" id="qty-input" name="quantity" value="1" readonly max="<?= $initial_stock ?>"
                                           class="w-12 text-center py-2 bg-transparent text-sm font-bold text-slate-800 dark:text-white border-x border-slate-200 dark:border-slate-700 focus:outline-none">

                                    <button type="button" id="qty-plus" class="px-3.5 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition font-bold text-lg">+</button>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <button type="submit" id="btn-add-cart"
                                    class="flex-1 flex items-center justify-center space-x-1.5 bg-primary hover:bg-primary/90 text-white font-bold py-3 px-4 rounded-xl transition duration-200 active:scale-[0.98] shadow-lg shadow-primary/25 disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm whitespace-nowrap"
                                    <?= ($initial_stock <= 0) ? 'disabled' : '' ?>>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span id="cart-btn-text"><?= $initial_stock <= 0 ? 'Stok Habis' : '+ Keranjang' ?></span>
                                </button>

                                <button type="button" id="btn-buy-now" onclick="buyNowBtnAction()"
                                    class="flex-1 flex items-center justify-center space-x-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition duration-200 active:scale-[0.98] shadow-lg shadow-indigo-600/25 disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm whitespace-nowrap"
                                    <?= ($initial_stock <= 0) ? 'disabled' : '' ?>>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span id="buy-now-btn-text">Beli</span>
                                </button>

                                <a href="index.php?page=home" class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/components/footer.php'; ?>

    <div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

    <script src="assets/js/jquery.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script>
        window.NusaBayProductDetail = {
            basePrice: <?= json_encode((float) $product['price']) ?>,
            hasVariants: <?= $has_variants ? 'true' : 'false' ?>,
            productId: <?= json_encode((int) $product['id']) ?>,
            primaryColor: <?= json_encode($primary_color) ?>
        };
    </script>
    <script src="assets/js/pages/product-detail.js"></script>
</body>
</html>
