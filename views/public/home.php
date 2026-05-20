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

$hero_title = $configs['hero_title'] ?? 'Selamat Datang di NusaBay';
$hero_subtitle = $configs['hero_subtitle'] ?? 'Temukan barang impianmu dengan harga terbaik dan terjangkau di sini.';
$primary_color = $configs['primary_color'] ?? '#6366f1';
$hero_image = $configs['hero_image'] ?? '';

// Fetch all active categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Fetch all active promotional banners
$banners = $pdo->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC, id DESC")->fetchAll();

// Fetch all products
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
    <title>NusaBay - Toko Serba Ada Modern</title>
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
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(1deg); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        /* Custom Carousel Styles */
        .carousel-slide {
            transition: opacity 0.8s ease-in-out;
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
                        <a href="index.php?page=register" class="text-sm font-bold text-white bg-indigo-600 px-4 py-2 rounded-xl hover:bg-indigo-700 transition shadow-md shadow-indigo-600/25">Daftar</a>
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

    <!-- Top Slideshow Banner Slider (Jika Banner Ada) -->
    <?php if (!empty($banners)): ?>
        <section class="relative bg-slate-900 dark:bg-black h-[280px] md:h-[400px] w-full overflow-hidden">
            <!-- Slides Container -->
            <div id="carousel-container" class="relative w-full h-full">
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="carousel-slide absolute inset-0 w-full h-full transition-opacity duration-700 <?= $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' ?>">
                        <img src="<?= htmlspecialchars($banner['image_url']) ?>" alt="Banner Image" class="w-full h-full object-cover">
                        <!-- Glassmorphic Banner Info overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent flex items-end">
                            <div class="max-w-6xl mx-auto px-8 pb-10 w-full">
                                <div class="max-w-xl space-y-2 md:space-y-3">
                                    <h2 class="text-2xl md:text-5xl font-black text-white leading-tight font-display drop-shadow-md">
                                        <?= htmlspecialchars($banner['title']) ?>
                                    </h2>
                                    <p class="text-xs md:text-sm text-slate-200 font-light line-clamp-2 drop-shadow-sm">
                                        <?= htmlspecialchars($banner['description']) ?>
                                    </p>
                                    <?php if ($banner['link_url']): ?>
                                        <a href="<?= htmlspecialchars($banner['link_url']) ?>" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl text-xs transition active:scale-95 shadow-lg shadow-indigo-600/25 mt-2">
                                            <span>Lihat Promo</span>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Slide Navigation Buttons -->
            <button id="prevSlide" class="absolute left-6 top-1/2 -translate-y-1/2 z-20 h-10 w-10 md:h-12 md:w-12 rounded-full bg-white/20 backdrop-blur-md text-white border border-white/10 flex items-center justify-center hover:bg-white/30 transition focus:outline-none">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button id="nextSlide" class="absolute right-6 top-1/2 -translate-y-1/2 z-20 h-10 w-10 md:h-12 md:w-12 rounded-full bg-white/20 backdrop-blur-md text-white border border-white/10 flex items-center justify-center hover:bg-white/30 transition focus:outline-none">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Indicators (dots) -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-25 flex space-x-2.5">
                <?php foreach ($banners as $index => $banner): ?>
                    <button class="carousel-dot h-2 rounded-full transition-all duration-300 <?= $index === 0 ? 'bg-white w-6' : 'bg-white/40 w-2' ?>" data-index="<?= $index ?>"></button>
                <?php endforeach; ?>
            </div>
        </section>
    <?php else: ?>
        <!-- Hero Section Fallback (Jika Banner Kosong) -->
        <header class="bg-gradient-to-b from-white to-slate-50/50 dark:from-slate-900 dark:to-slate-950 py-24 border-b border-slate-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <!-- Text Info -->
                <div class="space-y-6 text-center md:text-left">
                    <h1 class="text-4xl md:text-6xl font-black text-slate-900 dark:text-white leading-tight tracking-tight font-display">
                        <?= htmlspecialchars($hero_title) ?>
                    </h1>
                    <p class="text-base md:text-lg text-slate-500 dark:text-slate-400 leading-relaxed font-light font-sans">
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
    <?php endif; ?>

    <!-- Catalog Section -->
    <main id="products" class="max-w-6xl mx-auto px-6 py-20 flex-1 w-full">
        
        <!-- Live Search Bar and Headline -->
        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-10 gap-6 border-b border-slate-100 dark:border-slate-800 pb-8">
            <div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Katalog Produk</h2>
                <p class="text-sm text-slate-400 mt-2 font-sans">Daftar produk serba ada terlengkap dengan penawaran terbaik hari ini.</p>
            </div>
            
            <!-- Live Search Bar + Autocomplete Box Wrapper -->
            <div class="relative w-full md:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" id="product-search" autocomplete="off" placeholder="Cari produk impianmu..." class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-2xl pl-12 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-sm">
                
                <!-- Autocomplete Suggestion Dropdown Box -->
                <div id="search-suggestions" class="absolute left-0 right-0 mt-2 bg-white dark:bg-slate-900 border border-slate-150 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden hidden z-40">
                    <div id="suggestions-list" class="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                        <!-- Injected by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Horizontal Category Pills Filter -->
        <div class="flex items-center space-x-2.5 overflow-x-auto pb-4 mb-8 scrollbar-hide select-none" id="category-pills">
            <button 
                data-id="all" 
                class="category-pill whitespace-nowrap px-5 py-2.5 text-xs font-bold rounded-xl transition duration-150 active:scale-95 bg-primary text-white shadow-md shadow-primary/20 cursor-pointer">
                Semua Produk
            </button>
            <?php foreach ($categories as $cat): ?>
                <button 
                    data-id="<?= $cat['id'] ?>" 
                    class="category-pill whitespace-nowrap px-5 py-2.5 text-xs font-bold rounded-xl transition duration-150 active:scale-95 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-950 dark:hover:text-white cursor-pointer">
                    <?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endforeach; ?>
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
                    <div class="product-card bg-white dark:bg-slate-900 rounded-3xl shadow-sm overflow-hidden flex flex-col border border-slate-100 dark:border-slate-800/80 hover:shadow-xl hover:-translate-y-1.5 transition duration-300 group" 
                         data-id="<?= $product['id'] ?>"
                         data-category="<?= $product['category_id'] ?? '' ?>" 
                         data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>" 
                         data-desc="<?= strtolower(htmlspecialchars($product['description'] ?? '')) ?>">
                        <!-- Image Container -->
                        <div class="relative overflow-hidden aspect-[4/3] bg-slate-50 dark:bg-slate-950">
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
                                <h3 class="font-bold text-slate-800 dark:text-white text-base leading-tight group-hover:text-primary transition product-title" data-original="<?= htmlspecialchars($product['name']) ?>"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="text-slate-400 dark:text-slate-450 text-xs line-clamp-2 leading-relaxed"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-primary font-extrabold text-lg font-display">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
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
                                    <button disabled class="w-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-655 font-bold py-3 px-4 rounded-2xl cursor-not-allowed text-sm">
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
                <p class="text-xs text-slate-400 mt-1">Coba masukkan kata kunci pencarian atau ganti filter kategori.</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 mt-auto border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-6 text-center text-xs space-y-2">
            <p>&copy; <?= date('Y') ?> NusaBay. All rights reserved.</p>
            <p class="text-slate-600 font-light">Desain antarmuka premium & dinamis untuk kemudahan berbelanja Anda.</p>
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

            // Banner Slideshow Logic
            const slides = $('.carousel-slide');
            const dots = $('.carousel-dot');
            let currentSlide = 0;
            let slideInterval = null;

            function showSlide(index) {
                if (slides.length === 0) return;
                slides.removeClass('opacity-100 z-10').addClass('opacity-0 z-0');
                dots.removeClass('bg-white w-6').addClass('bg-white/40 w-2');
                
                currentSlide = (index + slides.length) % slides.length;
                
                $(slides[currentSlide]).removeClass('opacity-0 z-0').addClass('opacity-100 z-10');
                $(dots[currentSlide]).removeClass('bg-white/40 w-2').addClass('bg-white w-6');
            }

            function startSlideShow() {
                slideInterval = setInterval(function() {
                    showSlide(currentSlide + 1);
                }, 5000);
            }

            function stopSlideShow() {
                clearInterval(slideInterval);
            }

            $('#prevSlide').on('click', function() {
                stopSlideShow();
                showSlide(currentSlide - 1);
                startSlideShow();
            });

            $('#nextSlide').on('click', function() {
                stopSlideShow();
                showSlide(currentSlide + 1);
                startSlideShow();
            });

            $('.carousel-dot').on('click', function() {
                const idx = parseInt($(this).data('index'));
                stopSlideShow();
                showSlide(idx);
                startSlideShow();
            });

            if (slides.length > 0) {
                startSlideShow();
            }

            // Category Filter & Search Logic
            let activeCategory = 'all';
            let searchQuery = '';

            // Handle Category Pills Click
            $('.category-pill').on('click', function() {
                $('.category-pill').removeClass('bg-primary text-white shadow-md shadow-primary/20').addClass('bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-800');
                $(this).removeClass('bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-800').addClass('bg-primary text-white shadow-md shadow-primary/20');
                
                activeCategory = $(this).data('id').toString();
                filterCatalog();
            });

            // Handle Live Search Input
            $('#product-search').on('input', function() {
                searchQuery = $(this).val().toLowerCase().trim();
                filterCatalog();
                renderSearchSuggestions();
            });

            // Filter Catalog Products
            function filterCatalog() {
                let matchCount = 0;
                
                $('.product-card').each(function() {
                    const cardCat = $(this).data('category').toString();
                    const name = $(this).data('name');
                    const desc = $(this).data('desc');
                    const titleEl = $(this).find('.product-title');
                    const originalTitle = titleEl.data('original');

                    // Check Category Filter
                    const matchesCategory = (activeCategory === 'all' || cardCat === activeCategory);
                    // Check Search query
                    const matchesSearch = (searchQuery === '' || name.includes(searchQuery) || desc.includes(searchQuery));

                    if (matchesCategory && matchesSearch) {
                        $(this).fadeIn(200);
                        matchCount++;
                        
                        // Highlight matching keywords
                        if (searchQuery !== '') {
                            const regex = new RegExp(`(${escapeRegExp(searchQuery)})`, 'gi');
                            const highlighted = originalTitle.replace(regex, '<mark class="bg-indigo-100 text-indigo-900 dark:bg-indigo-900/50 dark:text-indigo-200 rounded-md px-1 py-0.5">$1</mark>');
                            titleEl.html(highlighted);
                        } else {
                            titleEl.text(originalTitle);
                        }
                    } else {
                        $(this).fadeOut(150);
                    }
                });

                if (matchCount === 0) {
                    $('#no-search-results').removeClass('hidden').fadeIn(200);
                } else {
                    $('#no-search-results').addClass('hidden');
                }
            }

            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            // Render Search Suggestions Autocomplete Dropdown
            function renderSearchSuggestions() {
                const suggBox = $('#search-suggestions');
                const list = $('#suggestions-list');
                list.empty();

                if (searchQuery === '') {
                    suggBox.addClass('hidden');
                    return;
                }

                let matches = [];
                $('.product-card').each(function() {
                    const name = $(this).data('name');
                    const desc = $(this).data('desc');
                    
                    if (name.includes(searchQuery) || desc.includes(searchQuery)) {
                        const id = $(this).data('id');
                        const originalName = $(this).find('.product-title').data('original');
                        const price = $(this).find('.font-display').text();
                        const image = $(this).find('img').attr('src');
                        matches.push({ id, name: originalName, price, image });
                    }
                });

                if (matches.length === 0) {
                    suggBox.addClass('hidden');
                    return;
                }

                // Show top 5 matches
                matches.slice(0, 5).forEach(prod => {
                    const item = $(`
                        <div class="flex items-center space-x-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition select-none">
                            <img src="${prod.image}" class="h-10 w-10 object-cover rounded-lg border border-slate-100 dark:border-slate-800">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">${prod.name}</h4>
                                <p class="text-[10px] text-primary font-bold font-mono mt-0.5">${prod.price}</p>
                            </div>
                        </div>
                    `);

                    item.on('click', function() {
                        $('#product-search').val(prod.name);
                        searchQuery = prod.name.toLowerCase();
                        filterCatalog();
                        suggBox.addClass('hidden');
                    });

                    list.append(item);
                });

                suggBox.removeClass('hidden');
            }

            // Hide search suggestions on clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#product-search, #search-suggestions').length) {
                    $('#search-suggestions').addClass('hidden');
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
                                const newBadge = $('<span id="cart-badge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-md shadow-primary/20 font-sans"></span>');
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
