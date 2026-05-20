<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

// Fetch Configurations for Dynamic Styles
$stmt = $pdo->query("SELECT section_key, content_value FROM landing_configs");
$configs_raw = $stmt->fetchAll();
$configs = [];
foreach ($configs_raw as $c) {
    $configs[$c['section_key']] = $c['content_value'];
}
$primary_color = $configs['primary_color'] ?? '#6366f1';

$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    if (count($ids) > 0) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll();
        
        foreach ($products as $p) {
            $qty = $_SESSION['cart'][$p['id']];
            $subtotal = $p['price'] * $qty;
            $total_price += $subtotal;
            
            $cart_items[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'price' => $p['price'],
                'image_url' => $p['image_url'],
                'stock' => $p['stock'],
                'qty' => $qty,
                'subtotal' => $subtotal
            ];
        }
    }
}

// Count Cart Items for badge
$cart_count = 0;
foreach ($_SESSION['cart'] as $qty) {
    $cart_count += $qty;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Pro-Store CMS</title>
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
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-300">

    <!-- Navbar -->
    <nav class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 sticky top-0 z-50 transition-colors duration-300">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex justify-between items-center h-20">
                <a href="index.php?page=home" class="text-2xl font-black tracking-tight text-slate-900 dark:text-white hover:opacity-85 transition font-display flex items-center space-x-2">
                    <span class="h-9 w-9 rounded-xl bg-primary flex items-center justify-center font-bold text-white text-lg shadow-lg shadow-primary/20 font-display">P</span>
                    <span>Pro-Store <span class="text-primary">Toko</span></span>
                </a>
                <div class="flex items-center space-x-6">
                    <a href="index.php?page=home" class="text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition flex items-center space-x-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali Belanja</span>
                    </a>
                    
                    <!-- Cart badge icon -->
                    <a href="index.php?page=cart" id="cart-link" class="relative p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span id="cart-badge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-md shadow-primary/20 <?= $cart_count > 0 ? '' : 'hidden' ?>">
                            <?= $cart_count ?>
                        </span>
                    </a>

                    <?php if (isAuth()): ?>
                        <a href="index.php?page=orders" class="text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition">Pesanan Saya</a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="index.php?page=admin" class="text-sm font-bold text-slate-700 dark:text-slate-200 hover:text-slate-900 bg-slate-100 dark:bg-slate-800 px-3.5 py-1.5 rounded-xl transition">Admin Panel</a>
                        <?php endif; ?>
                        <a href="index.php?page=auth_process&action=logout" class="text-sm font-bold text-red-500 hover:text-red-700 transition">Logout</a>
                    <?php endif; ?>

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
    <main class="max-w-4xl mx-auto px-6 py-12 flex-1 w-full">
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-8 font-display">Keranjang Belanja</h1>

        <!-- Alert messages container -->
        <div id="cart-alert"></div>

        <!-- Empty state placeholder -->
        <div id="cart-empty-placeholder" class="<?= empty($cart_items) ? '' : 'hidden' ?> bg-white dark:bg-slate-900 rounded-3xl p-16 text-center border border-slate-100 dark:border-slate-800 shadow-sm max-w-md mx-auto">
            <svg class="h-12 w-12 text-slate-300 dark:text-slate-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <p class="text-slate-400 dark:text-slate-500 font-semibold mb-6">Keranjang belanja Anda masih kosong.</p>
            <a href="index.php?page=home" class="inline-block bg-primary text-white font-bold py-3.5 px-6 rounded-2xl hover:opacity-90 transition text-sm shadow-lg shadow-primary/25">
                Cari Produk Terbaik
            </a>
        </div>

        <!-- Cart items list -->
        <?php if (!empty($cart_items)): ?>
            <div id="cart-card" class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm overflow-hidden border border-slate-100 dark:border-slate-800 transition-colors duration-300">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                <th class="p-4 pl-6">Produk</th>
                                <th class="p-4">Harga</th>
                                <th class="p-4 text-center w-48">Jumlah</th>
                                <th class="p-4 text-right">Subtotal</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cart-table-body" class="divide-y divide-slate-50 dark:divide-slate-800 text-sm">
                            <?php foreach ($cart_items as $item): ?>
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition cart-item-row" data-product-id="<?= $item['id'] ?>">
                                    <td class="p-4 pl-6 flex items-center space-x-4">
                                        <img src="<?= htmlspecialchars($item['image_url'] ?? 'https://placehold.co/100') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="h-14 w-14 object-cover rounded-xl border border-slate-100 dark:border-slate-850 shadow-sm">
                                        <span class="font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($item['name']) ?></span>
                                    </td>
                                    <td class="p-4 font-semibold text-slate-600 dark:text-slate-400 font-mono">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td class="p-4">
                                        <div class="flex items-center justify-center space-x-1.5">
                                            <input type="number" value="<?= $item['qty'] ?>" min="1" max="<?= $item['stock'] ?>" 
                                                class="input-qty w-16 px-2 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-850 dark:text-white rounded-lg text-center focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-xs font-bold font-mono"
                                                data-product-id="<?= $item['id'] ?>">
                                        </div>
                                        <p class="text-center text-[10px] text-slate-400 dark:text-slate-500 mt-1 font-semibold">Tersedia: <?= $item['stock'] ?> pcs</p>
                                    </td>
                                    <td class="subtotal-cell p-4 text-right font-extrabold text-slate-800 dark:text-white font-mono">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                    <td class="p-4 text-center">
                                        <button type="button" class="btn-remove-item text-rose-500 hover:text-rose-700 font-bold text-xs hover:underline transition" data-product-id="<?= $item['id'] ?>">Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary & Checkout Button -->
                <div class="p-8 bg-slate-50/50 dark:bg-slate-800/20 flex flex-col sm:flex-row justify-between items-center border-t border-slate-100 dark:border-slate-800 gap-6">
                    <button type="button" id="btn-clear-cart" class="text-xs text-rose-500 hover:text-rose-700 font-bold hover:underline">Kosongkan Keranjang</button>
                    
                    <div class="text-right space-y-4 w-full sm:w-auto">
                        <div class="flex items-baseline justify-end space-x-2">
                            <span class="text-slate-500 dark:text-slate-400 text-xs font-semibold">Total Sementara:</span>
                            <span id="grand-total" class="text-2xl font-black text-primary font-display">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                        </div>
                        <a href="index.php?page=checkout" class="block w-full sm:inline-block bg-primary text-white font-bold py-3.5 px-8 rounded-2xl shadow-xl shadow-primary/25 hover:opacity-90 active:scale-[0.98] transition text-sm text-center">
                            Lanjut ke Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-8 mt-auto border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-6 text-center text-xs">
            <p>&copy; <?= date('Y') ?> Pro-Store CMS. Powered by Mini-Framework.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            // Helper to format currency
            function formatRupiah(num) {
                return 'Rp ' + Number(num).toLocaleString('id-ID');
            }

            // AJAX Update Quantity on input change
            $('.input-qty').on('change input', function() {
                const input = $(this);
                const productId = input.data('product-id');
                const qty = input.val();
                
                $.ajax({
                    url: 'index.php?page=cart_process&action=update',
                    type: 'POST',
                    data: { product_id: productId, qty: qty, ajax: 1 },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Update subtotal
                            input.closest('tr').find('.subtotal-cell').text(formatRupiah(response.subtotal));
                            // Update total
                            $('#grand-total').text(formatRupiah(response.total_price));
                            // Update input value (in case it got adjusted to max stock)
                            input.val(response.qty);
                            // Update badge
                            $('#cart-badge').text(response.cart_count);
                            
                            if (response.error_message) {
                                Swal.fire({
                                    title: 'Perhatian!',
                                    text: response.error_message,
                                    icon: 'warning',
                                    confirmButtonColor: '<?= $primary_color ?>'
                                });
                            }
                        } else if (response.status === 'removed') {
                            input.closest('tr').fadeOut(300, function() {
                                $(this).remove();
                                checkEmptyCart();
                            });
                        }
                    }
                });
            });

            // AJAX Remove Item
            $('.btn-remove-item').on('click', function() {
                const btn = $(this);
                const productId = btn.data('product-id');
                
                $.ajax({
                    url: 'index.php?page=cart_process&action=remove&id=' + productId + '&ajax=1',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            btn.closest('tr').fadeOut(300, function() {
                                $(this).remove();
                                $('#grand-total').text(formatRupiah(response.total_price));
                                $('#cart-badge').text(response.cart_count);
                                checkEmptyCart();
                            });
                        }
                    }
                });
            });

            // AJAX Clear Cart
            $('#btn-clear-cart').on('click', function() {
                Swal.fire({
                    title: 'Kosongkan Keranjang?',
                    text: 'Apakah Anda yakin ingin menghapus semua produk dari keranjang belanja?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Kosongkan!',
                    cancelButtonText: 'Batal',
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#1f2937'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'index.php?page=cart_process&action=clear&ajax=1',
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    $('#cart-card').fadeOut(300, function() {
                                        $(this).remove();
                                        $('#cart-badge').text(0).addClass('hidden');
                                        checkEmptyCart();
                                    });
                                }
                            }
                        });
                    }
                });
            });

            // Check if cart is empty and show placeholder
            function checkEmptyCart() {
                if ($('#cart-table-body tr').length === 0) {
                    $('#cart-card').remove();
                    $('#cart-badge').addClass('hidden');
                    $('#cart-empty-placeholder').removeClass('hidden').fadeIn(300);
                }
            }
        });
    </script>
</body>
</html>
