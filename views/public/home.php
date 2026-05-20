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
$primary_color = $configs['primary_color'] ?? '#2563eb';
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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo / Name -->
                <a href="index.php?page=home" class="text-xl font-bold text-gray-800 hover:text-gray-600 transition">
                    Pro-Store <span class="text-[<?= $primary_color ?>]">Toko</span>
                </a>
                
                <!-- Nav Links -->
                <div class="flex items-center space-x-4">
                    <a href="index.php?page=cart" class="relative flex items-center text-gray-600 hover:text-gray-900 transition">
                        <!-- Shopping Cart Icon -->
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <?php if ($cart_count > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-[<?= $primary_color ?>] text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                <?= $cart_count ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="index.php?page=admin" class="text-sm font-semibold text-gray-700 hover:text-gray-900 transition">Admin Panel</a>
                    <?php else: ?>
                        <a href="index.php?page=login" class="text-sm font-semibold text-gray-700 hover:text-gray-900 transition">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="bg-white border-b">
        <div class="max-w-6xl mx-auto px-4 py-16 flex flex-col md:flex-row items-center justify-between">
            <div class="md:w-1/2 mb-8 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 leading-tight">
                    <?= htmlspecialchars($hero_title) ?>
                </h1>
                <p class="text-lg text-gray-600 mb-8">
                    <?= htmlspecialchars($hero_subtitle) ?>
                </p>
                <a href="#products" class="bg-[<?= $primary_color ?>] text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:opacity-90 transition duration-300">
                    Mulai Belanja
                </a>
            </div>
            
            <div class="md:w-1/2 flex justify-center">
                <?php if (!empty($hero_image)): ?>
                    <img src="uploads/<?= htmlspecialchars($hero_image) ?>" alt="Hero Image" class="max-w-md w-full object-cover rounded-lg shadow-2xl">
                <?php else: ?>
                    <img src="https://images.unsplash.com/photo-1472851294608-062f824d29cc?w=600&auto=format&fit=crop&q=60" alt="Default Hero Image" class="max-w-md w-full object-cover rounded-lg shadow-2xl">
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Catalog Section -->
    <main id="products" class="max-w-6xl mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 border-b pb-4 text-center md:text-left">Katalog Produk</h2>
        
        <?php if (empty($products)): ?>
            <p class="text-center text-gray-500 py-12">Belum ada produk yang tersedia saat ini.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col border border-gray-100">
                        <img src="<?= htmlspecialchars($product['image_url'] ?? 'https://placehold.co/400x300') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-48 w-full object-cover">
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg mb-1"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="text-gray-500 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($product['description']) ?></p>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-[<?= $primary_color ?>] font-bold text-lg">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                                    <span class="text-xs text-gray-500">Stok: <?= $product['stock'] ?></span>
                                </div>

                                <?php if ($product['stock'] > 0): ?>
                                    <form action="index.php?page=cart_process&action=add" method="POST">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="w-full bg-[<?= $primary_color ?>] text-white font-semibold py-2 px-4 rounded hover:opacity-90 transition duration-300 flex items-center justify-center space-x-2">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>Keranjang</span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button disabled class="w-full bg-gray-300 text-gray-500 font-semibold py-2 px-4 rounded cursor-not-allowed">
                                        Habis
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-auto py-8">
        <div class="max-w-6xl mx-auto px-4 text-center text-sm text-gray-400">
            <p>&copy; <?= date('Y') ?> Pro-Store CMS. Powered by Mini-Framework.</p>
        </div>
    </footer>

</body>
</html>
