<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

// Fetch Configurations
$stmt = $pdo->query("SELECT section_key, content_value FROM landing_configs");
$configs_raw = $stmt->fetchAll();
$configs = [];
foreach ($configs_raw as $c) {
    $configs[$c['section_key']] = $c['content_value'];
}

$hero_title = $configs['hero_title'] ?? 'Selamat Datang di Pro-Store';
$hero_subtitle = $configs['hero_subtitle'] ?? 'Temukan barang impianmu dengan harga terbaik di sini.';
$primary_color = $configs['primary_color'] ?? '#6366f1';
$hero_image = $configs['hero_image'] ?? '';

// Fetch Products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

// Count Cart Items
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storefront - Pro-Store CMS</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4, .font-display {
            font-family: 'Outfit', sans-serif;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(1deg); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-300">

    <!-- Navbar -->
    <nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-slate-100 dark:border-slate-800 transition-colors duration-300">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="index.php?page=home" class="text-2xl font-black tracking-tight text-slate-900 dark:text-white hover:opacity-85 transition font-display flex items-center space-x-2">
                    <span class="h-9 w-9 rounded-xl bg-primary flex items-center justify-center font-bold text-white text-lg shadow-lg shadow-primary/20 font-display">P</span>
                    <span>Pro-Store <span class="text-primary">Toko</span></span>
                </a>
                
                <!-- Nav Links -->
                <div class="flex items-center space-x-6">
                    <a href="index.php?page=cart" id="cart-link" class="relative p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition flex items-center justify-center">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span id="cart-badge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-md shadow-primary/20 <?= $cart_count > 0 ? '' : 'hidden' ?>">
                            <?= $cart_count ?>
                        </span>
                    </a>

                    <?php if (isAuth()): ?>
                        <a href="index.php?page=orders" class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">Pesanan Saya</a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="index.php?page=admin" class="text-sm font-bold text-slate-700 dark:text-slate-200 hover:text-slate-900 bg-slate-100 dark:bg-slate-800 px-3.5 py-1.5 rounded-xl transition">Admin Panel</a>
                        <?php endif; ?>
                        <a href="index.php?page=auth_process&action=logout" class="text-sm font-bold text-red-500 hover:text-red-700 transition">Logout</a>
                    <?php else: ?>
                        <a href="index.php?page=login" class="text-sm font-bold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">Login</a>
                        <a href="index.php?page=register" class="text-sm font-bold text-white bg-primary px-4 py-2 rounded-xl hover:opacity-90 transition shadow-md shadow-primary/25">Daftar</a>
                    <?php endif; ?>

                    <!-- Dark mode toggle -->
                    <button id="theme-toggle" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        <!-- Sun Icon -->
                        <svg id="theme-toggle-sun" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-3.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                        <!-- Moon Icon -->
                        <svg id="theme-toggle-moon" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="bg-gradient-to-b from-white to-slate-50/50 dark:from-slate-900 dark:to-slate-950 py-24 border-b border-slate-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
        <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <!-- Text Info -->
            <div class="space-y-6 text-center md:text-left">
                <h1 class="text-4xl md:text-6xl font-black text-slate-900 dark:text-white leading-tight tracking-tight">
                    <?= htmlspecialchars($hero_title) ?>
                </h1>
                <p class="text-base md:text-lg text-slate-500 dark:text-slate-400 leading-relaxed font-light">
                    <?= htmlspecialchars($hero_subtitle) ?>
                </p>
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center md:justify-start gap-4">
                    <a href="#products" class="w-full sm:w-auto text-center bg-primary text-white font-bold py-4 px-8 rounded-2xl shadow-xl shadow-primary/25 hover:opacity-90 active:scale-95 transition duration-150 text-sm">
                        Mulai Belanja
                    </a>
                </div>
            </div>
            
            <!-- Graphic/Image -->
            <div class="flex justify-center relative">
                <!-- Background Blob effect -->
                <div class="absolute w-72 h-72 bg-primary/10 rounded-full blur-3xl -z-10 animate-pulse"></div>
                
                <div class="animate-float">
                    <?php if (!empty($hero_image)): ?>
                        <img src="uploads/<?= htmlspecialchars($hero_image) ?>" alt="Hero Banner" class="max-w-sm sm:max-w-md w-full object-cover rounded-3xl shadow-2xl border-4 border-white dark:border-slate-800">
                    <?php else: ?>
                        <img src="https://images.unsplash.com/photo-1472851294608-062f824d29cc?w=600&auto=format&fit=crop&q=60" alt="Default Banner" class="max-w-sm sm:max-w-md w-full object-cover rounded-3xl shadow-2xl border-4 border-white dark:border-slate-800">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Catalog Section -->
    <main id="products" class="max-w-6xl mx-auto px-6 py-20 flex-1 w-full">
        
        <!-- Live Search Bar and Headline -->
        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-16 gap-6 border-b border-slate-100 dark:border-slate-800 pb-10">
            <div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Katalog Produk</h2>
                <p class="text-sm text-slate-400 mt-2">Daftar produk eksklusif dengan penawaran dan harga terbaik saat ini.</p>
            </div>
            
            <!-- Search bar -->
            <div class="relative w-full md:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" id="product-search" placeholder="Cari produk impianmu..." class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-2xl pl-12 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-sm">
            </div>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-16 text-center border border-slate-100 dark:border-slate-800 shadow-sm max-w-md mx-auto">
                <svg class="h-12 w-12 text-slate-300 dark:text-slate-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <p class="text-slate-400 font-semibold">Belum ada produk</p>
                <p class="text-xs text-slate-400 mt-1">Nantikan pembaruan katalog produk kami segera.</p>
            </div>
        <?php else: ?>
            <div id="product-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <?php foreach ($products as $product): ?>
                    <div class="product-card bg-white dark:bg-slate-900 rounded-3xl shadow-sm overflow-hidden flex flex-col border border-slate-100 dark:border-slate-800/80 hover:shadow-xl hover:-translate-y-1.5 transition duration-300 group" data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>" data-desc="<?= strtolower(htmlspecialchars($product['description'] ?? '')) ?>">
                        <!-- Image Container -->
                        <div class="relative overflow-hidden aspect-[4/3] bg-slate-50 dark:bg-slate-850">
                            <img src="<?= htmlspecialchars($product['image_url'] ?? 'https://placehold.co/400x300') ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="h-full w-full object-cover group-hover:scale-105 transition duration-500">
                            <?php if ($product['stock'] <= 0): ?>
                                <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-[2px] flex items-center justify-center">
                                    <span class="px-3 py-1.5 bg-rose-600 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg">Habis</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card Info -->
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div class="space-y-2 mb-6">
                                <h3 class="font-bold text-slate-850 dark:text-white text-lg leading-tight group-hover:text-primary transition product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="text-slate-400 dark:text-slate-450 text-xs line-clamp-2 leading-relaxed"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-primary font-extrabold text-xl font-display">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-2 py-0.5 rounded-lg">Stok: <?= $product['stock'] ?></span>
                                </div>

                                <?php if ($product['stock'] > 0): ?>
                                    <form action="index.php?page=cart_process&action=add" method="POST" class="add-to-cart-form">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="w-full bg-primary text-white font-bold py-3 px-4 rounded-2xl hover:opacity-90 active:scale-95 transition duration-150 flex items-center justify-center space-x-2 text-sm shadow-md shadow-primary/10">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>Keranjang</span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button disabled class="w-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-650 font-bold py-3 px-4 rounded-2xl cursor-not-allowed text-sm">
                                        Habis
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- No results message -->
            <div id="no-search-results" class="hidden bg-white dark:bg-slate-900 rounded-3xl p-16 text-center border border-slate-100 dark:border-slate-800 shadow-sm max-w-md mx-auto">
                <svg class="h-12 w-12 text-slate-300 dark:text-slate-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <p class="text-slate-400 font-semibold">Produk tidak ditemukan</p>
                <p class="text-xs text-slate-400 mt-1">Coba masukkan kata kunci pencarian yang lain.</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 mt-auto border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-6 text-center text-xs space-y-2">
            <p>&copy; <?= date('Y') ?> Pro-Store CMS. Powered by Mini-Framework.</p>
            <p class="text-slate-600 font-light">Desain antarmuka premium & dinamis untuk kemudahan berbelanja Anda.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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

            // Live jQuery Search Filter
            $('#product-search').on('input', function() {
                const query = $(this).val().toLowerCase().trim();
                let matchCount = 0;
                
                $('.product-card').each(function() {
                    const name = $(this).data('name');
                    const desc = $(this).data('desc');
                    
                    if (name.includes(query) || desc.includes(query)) {
                        $(this).fadeIn(200);
                        matchCount++;
                    } else {
                        $(this).fadeOut(200);
                    }
                });

                // Display empty message if no products found
                if (matchCount === 0) {
                    $('#no-search-results').removeClass('hidden').fadeIn(300);
                } else {
                    $('#no-search-results').addClass('hidden');
                }
            });

            // Toast helper
            function showToast(message, type = 'success') {
                const toast = $('<div class="fixed bottom-6 right-6 px-5 py-3 rounded-2xl text-white font-bold text-xs shadow-2xl flex items-center space-x-2.5 transition-all duration-300 transform translate-y-10 opacity-0 z-50"></div>');
                
                if (type === 'success') {
                    toast.addClass('bg-emerald-600');
                    toast.html(`
                        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>${message}</span>
                    `);
                } else {
                    toast.addClass('bg-rose-600');
                    toast.html(`
                        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>${message}</span>
                    `);
                }
                
                $('body').append(toast);
                
                // Animate entrance
                setTimeout(() => {
                    toast.removeClass('translate-y-10 opacity-0');
                }, 10);
                
                // Animate exit and remove
                setTimeout(() => {
                    toast.addClass('translate-y-10 opacity-0');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, 3000);
            }

            // AJAX add to cart
            $('.add-to-cart-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const formData = form.serialize() + '&ajax=1';
                
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(data) {
                        if (data.status === 'success') {
                            showToast(data.message, 'success');
                            
                            // Update navbar count
                            const badge = $('#cart-badge');
                            if (badge.length) {
                                badge.text(data.cart_count).removeClass('hidden');
                            } else {
                                const newBadge = $('<span id="cart-badge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-md shadow-primary/20"></span>');
                                newBadge.text(data.cart_count);
                                $('#cart-link').append(newBadge);
                            }
                        } else {
                            showToast(data.message, 'error');
                        }
                    },
                    error: function() {
                        showToast('Terjadi kesalahan saat menambahkan produk.', 'error');
                    }
                });
            });
        });
    </script>
</body>
</html>
