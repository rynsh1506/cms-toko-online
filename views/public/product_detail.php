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

// Fetch variants grouped by variant_name
$stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_name ASC, id ASC");
$stmt->execute([$product_id]);
$variants_raw = $stmt->fetchAll();
$variants_grouped = [];
foreach ($variants_raw as $v) {
    $variants_grouped[$v['variant_name']][] = $v;
}

// Cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $q) { $cart_count += $q; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
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

    <!-- Navbar -->
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

    <!-- Breadcrumb -->
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

                <!-- Product Image -->
                <div class="space-y-4">
                    <div class="relative rounded-3xl overflow-hidden bg-slate-100 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm aspect-square">
                        <img id="main-product-img"
                             src="<?= htmlspecialchars($product['image_url'] ?? 'https://placehold.co/600x600') ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             class="h-full w-full object-cover">
                        <?php if ($product['stock'] <= 0): ?>
                            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-[2px] flex items-center justify-center">
                                <span class="px-4 py-2 bg-rose-600 text-white font-bold text-sm uppercase tracking-wider rounded-xl shadow-lg">Stok Habis</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="flex flex-col space-y-6">
                    <!-- Category & Name -->
                    <div>
                        <?php if ($product['category_name']): ?>
                            <span class="inline-block px-3 py-1 bg-primary/10 text-primary text-xs font-bold rounded-full mb-3"><?= htmlspecialchars($product['category_name']) ?></span>
                        <?php endif; ?>
                        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white font-display leading-tight"><?= htmlspecialchars($product['name']) ?></h1>
                    </div>

                    <!-- Price -->
                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-5 border border-slate-100 dark:border-slate-700">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Harga</span>
                        <div class="flex items-baseline space-x-2 mt-1">
                            <span id="display-price" class="text-3xl font-black text-slate-900 dark:text-white">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                            <span id="price-note" class="text-xs text-slate-400 hidden">(termasuk biaya varian)</span>
                        </div>
                        <!-- Stock -->
                        <div class="flex items-center space-x-2 mt-3">
                            <?php if ($product['stock'] > 5): ?>
                                <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Stok: <?= $product['stock'] ?> pcs</span>
                            <?php elseif ($product['stock'] > 0): ?>
                                <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                                <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold">Stok menipis: <?= $product['stock'] ?> pcs</span>
                            <?php else: ?>
                                <span class="w-2 h-2 bg-rose-500 rounded-full"></span>
                                <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold">Stok Habis</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <?php if (!empty($product['description'])): ?>
                    <div>
                        <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Deskripsi Produk</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Variants -->
                    <?php if (!empty($variants_grouped)): ?>
                    <div class="space-y-4">
                        <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pilih Varian</h2>
                        <?php foreach ($variants_grouped as $group_name => $group_variants): ?>
                        <div class="space-y-2" data-variant-group="<?= htmlspecialchars($group_name) ?>">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                <?= htmlspecialchars($group_name) ?>:
                                <span class="selected-label ml-1 text-primary font-bold"></span>
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($group_variants as $v): ?>
                                <button type="button"
                                    class="variant-btn px-4 py-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:border-primary hover:text-primary transition duration-150 active:scale-95"
                                    data-variant-id="<?= $v['id'] ?>"
                                    data-variant-name="<?= htmlspecialchars($group_name) ?>"
                                    data-variant-value="<?= htmlspecialchars($v['variant_value']) ?>"
                                    data-additional-price="<?= $v['additional_price'] ?>"
                                    data-stock="<?= $v['stock'] ?>"
                                    data-group="<?= htmlspecialchars($group_name) ?>">
                                    <?= htmlspecialchars($v['variant_value']) ?>
                                    <?php if ($v['additional_price'] > 0): ?>
                                        <span class="text-xs font-normal text-slate-400 ml-0.5">+Rp <?= number_format($v['additional_price'], 0, ',', '.') ?></span>
                                    <?php endif; ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Add to Cart Form -->
                    <div class="pt-2">
                        <?php if ($product['stock'] > 0): ?>
                        <form id="add-to-cart-form" action="index.php?page=cart_process&action=add" method="POST">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="variant_id" id="selected-variant-id" value="0">
                            <input type="hidden" name="variant_info" id="selected-variant-info" value="">

                            <!-- Qty Selector -->
                            <div class="flex items-center space-x-3 mb-5">
                                <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Jumlah:</span>
                                <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                    <button type="button" id="qty-minus" class="px-4 py-2.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition font-bold text-lg">−</button>
                                    <input type="number" id="qty-input" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>"
                                           class="w-14 text-center py-2.5 bg-transparent text-sm font-bold text-slate-800 dark:text-white border-x border-slate-200 dark:border-slate-700 focus:outline-none">
                                    <button type="button" id="qty-plus" class="px-4 py-2.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition font-bold text-lg">+</button>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <button type="submit"
                                    class="flex-1 flex items-center justify-center space-x-2 bg-primary hover:bg-primary/90 text-white font-bold py-4 px-6 rounded-2xl transition duration-200 active:scale-[0.98] shadow-lg shadow-primary/25">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span id="cart-btn-text">Tambah ke Keranjang</span>
                                </button>
                                <a href="index.php?page=home" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 transition">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                </a>
                            </div>
                        </form>
                        <?php else: ?>
                        <button disabled class="w-full py-4 px-6 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold rounded-2xl cursor-not-allowed">
                            Produk Tidak Tersedia
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 mt-auto border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-6 text-center text-xs space-y-2">
            <p>&copy; <?= date('Y') ?> NusaBay. All rights reserved.</p>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

    <script src="assets/js/jquery.min.js"></script>
    <script>
    $(document).ready(function () {
        // Theme Toggle
        const themeBtn = document.getElementById('theme-toggle');
        const sun = document.getElementById('theme-toggle-sun');
        const moon = document.getElementById('theme-toggle-moon');
        if (document.documentElement.classList.contains('dark')) { sun.classList.remove('hidden'); } else { moon.classList.remove('hidden'); }
        themeBtn.addEventListener('click', function () {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light');
                sun.classList.add('hidden'); moon.classList.remove('hidden');
            } else {
                document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark');
                moon.classList.add('hidden'); sun.classList.remove('hidden');
            }
        });

        // Base price
        const basePrice = <?= floatval($product['price']) ?>;

        // Track selected variants per group
        const selectedVariants = {};

        // Variant Selection
        $(document).on('click', '.variant-btn', function () {
            const group = $(this).data('group');
            const variantId = $(this).data('variant-id');
            const variantName = $(this).data('variant-name');
            const variantValue = $(this).data('variant-value');
            const addPrice = parseFloat($(this).data('additional-price')) || 0;

            // Deselect others in same group
            $(`.variant-btn[data-group="${group}"]`).removeClass('selected').css({'background-color': '', 'color': '', 'border-color': ''});

            // Select this
            $(this).addClass('selected').css({'background-color': '<?= $primary_color ?>', 'color': 'white', 'border-color': 'transparent'});

            // Track selection
            selectedVariants[group] = { id: variantId, name: variantName, value: variantValue, addPrice: addPrice };

            // Update label
            $(this).closest('[data-variant-group]').find('.selected-label').text(variantValue);

            // Update hidden inputs (use last selected variant id for single-variant products, or combine)
            updateVariantInputs();
            updatePrice();
        });

        function updateVariantInputs() {
            const groups = Object.keys(selectedVariants);
            if (groups.length === 0) {
                $('#selected-variant-id').val('0');
                $('#selected-variant-info').val('');
                return;
            }
            // If only one group, use that variant id. If multiple, use 0 and encode info as JSON.
            if (groups.length === 1) {
                const v = selectedVariants[groups[0]];
                $('#selected-variant-id').val(v.id);
            } else {
                $('#selected-variant-id').val('0');
            }
            const infoArr = groups.map(g => selectedVariants[g].name + ': ' + selectedVariants[g].value);
            $('#selected-variant-info').val(infoArr.join(', '));
        }

        function updatePrice() {
            let totalAdd = 0;
            Object.values(selectedVariants).forEach(v => totalAdd += v.addPrice);
            const finalPrice = basePrice + totalAdd;
            const formatted = 'Rp ' + finalPrice.toLocaleString('id-ID');
            $('#display-price').text(formatted);
            if (totalAdd > 0) { $('#price-note').removeClass('hidden'); } else { $('#price-note').addClass('hidden'); }
        }

        // Qty controls
        $('#qty-minus').on('click', function () {
            let val = parseInt($('#qty-input').val()) || 1;
            if (val > 1) { $('#qty-input').val(val - 1); }
        });
        $('#qty-plus').on('click', function () {
            let val = parseInt($('#qty-input').val()) || 1;
            const max = parseInt($('#qty-input').attr('max')) || 999;
            if (val < max) { $('#qty-input').val(val + 1); }
        });

        // Toast helper
        function showToast(message, type) {
            const bg = type === 'success' ? 'bg-emerald-600' : 'bg-rose-600';
            const toast = $(`<div class="flex items-center space-x-2.5 px-5 py-3 rounded-2xl text-white font-bold text-xs shadow-2xl transition-all duration-300 transform translate-y-4 opacity-0 ${bg}"><span>${message}</span></div>`);
            $('#toast-container').append(toast);
            setTimeout(() => toast.removeClass('translate-y-4 opacity-0'), 10);
            setTimeout(() => { toast.addClass('translate-y-4 opacity-0'); setTimeout(() => toast.remove(), 300); }, 3000);
        }

        // AJAX Add to Cart
        $('#add-to-cart-form').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).addClass('opacity-70');
            $('#cart-btn-text').text('Memproses...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize() + '&ajax=1',
                dataType: 'json',
                success: function (data) {
                    if (data.status === 'success') {
                        showToast(data.message, 'success');
                        const badge = $('#cart-badge');
                        badge.text(data.cart_count).removeClass('hidden');
                    } else {
                        showToast(data.message, 'error');
                    }
                },
                error: function () { showToast('Terjadi kesalahan. Coba lagi.', 'error'); },
                complete: function () {
                    btn.prop('disabled', false).removeClass('opacity-70');
                    $('#cart-btn-text').text('Tambah ke Keranjang');
                }
            });
        });
    });
    </script>
</body>
</html>
