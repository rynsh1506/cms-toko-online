<?php
// Variables $configs, $primary_color, $active_banks, $cart_items, $total_price
// are provided by CheckoutViewController.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NusaBay</title>
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
                    <a href="index.php?page=cart" class="text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition flex items-center space-x-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Keranjang</span>
                    </a>

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

    <main class="max-w-6xl mx-auto px-6 py-12 flex-1 w-full grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6 font-display">Detail Pengiriman</h2>

                <div id="checkout-alert"></div>

                <form id="checkout-form" action="index.php?page=checkout_process" method="POST" class="space-y-6">
                    <input type="hidden" name="promo_code_id" id="hidden_promo_id" value="">
                    <div>
                        <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-1.5">Nama Penerima</label>
                        <input type="text" name="customer_name" required value="<?= htmlspecialchars($_SESSION['name'] ?? '') ?>"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-sm">
                    </div>

                    <div>
                        <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-1.5">Nomor WhatsApp / HP</label>
                        <input type="text" name="customer_phone" required placeholder="081234567890"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-sm">
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Harap gunakan nomor aktif untuk konfirmasi pesanan via WhatsApp.</p>
                    </div>

                    <div>
                        <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-1.5">Alamat Lengkap</label>
                        <textarea name="customer_address" required rows="3" placeholder="Jalan, RT/RW, Kecamatan, Kota, Kode Pos"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-3">Pilih Metode Pembayaran</label>
                        <?php if (empty($active_banks)): ?>
                            <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 p-4 rounded-xl text-amber-700 dark:text-amber-400 text-xs font-semibold">
                                Tidak ada metode pembayaran transfer bank yang aktif saat ini. Hubungi admin toko.
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <?php foreach ($active_banks as $index => $bank): ?>
                                    <label class="relative flex flex-col p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl cursor-pointer hover:border-slate-300 dark:hover:border-slate-700 focus:outline-none transition group select-none">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 font-extrabold text-[10px] rounded uppercase font-mono">
                                                <?= htmlspecialchars($bank['bank_name']) ?>
                                            </span>
                                            <input type="radio" name="bank_account_id" value="<?= $bank['id'] ?>" required <?= $index === 0 ? 'checked' : '' ?>
                                                class="h-4 w-4 text-primary focus:ring-primary border-slate-300 dark:border-slate-700 cursor-pointer">
                                        </div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-1 font-mono tracking-wider"><?= htmlspecialchars($bank['account_number']) ?></span>
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 font-semibold">a.n. <?= htmlspecialchars($bank['account_name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" id="btn-submit-checkout" <?= empty($active_banks) ? 'disabled' : '' ?>
                        class="w-full bg-primary text-white font-bold py-3.5 px-4 rounded-2xl hover:opacity-90 active:scale-[0.98] transition text-sm shadow-xl shadow-primary/10 <?= empty($active_banks) ? 'opacity-50 cursor-not-allowed' : '' ?>">
                        Selesaikan Pesanan
                    </button>
                </form>
            </div>
        </div>

        <div>
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm sticky top-24">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800 pb-3 font-display">Ringkasan Pesanan</h3>

                <div class="space-y-4 mb-6 max-h-[22rem] overflow-y-auto pr-2">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="flex justify-between items-start text-xs gap-3">
                            <span class="text-slate-600 dark:text-slate-300 leading-relaxed">
                                <?= htmlspecialchars($item['name']) ?>
                                <span class="text-slate-400 dark:text-slate-500 font-semibold whitespace-nowrap ml-1">x<?= $item['qty'] ?></span>
                            </span>
                            <span class="text-slate-800 dark:text-slate-200 font-bold whitespace-nowrap font-mono mt-0.5">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex justify-between items-center mb-2">
                    <span class="text-slate-600 dark:text-slate-300 text-xs font-bold">Total Sementara</span>
                    <span class="text-slate-900 dark:text-white font-bold font-mono" id="summary-subtotal">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                </div>

                <div class="hidden justify-between items-center text-xs text-emerald-600 dark:text-emerald-400 font-bold mb-4" id="promo-discount-row">
                    <span>Diskon Promo (<span id="promo-code-applied"></span>)</span>
                    <span class="font-mono">-Rp <span id="promo-discount-value">0</span></span>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex justify-between items-center mb-2">
                    <span class="text-slate-800 dark:text-white text-sm font-bold">Total Tagihan</span>
                    <span class="text-primary text-xl font-extrabold font-mono" id="summary-total">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 pt-4 mb-4">
                    <label class="block text-slate-700 dark:text-slate-300 text-xs font-bold mb-2">Punya Kode Promo?</label>
                    <div class="flex space-x-2">
                        <input type="text" id="promo-input" placeholder="Masukkan kode promo" class="flex-1 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3 py-1.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-xs uppercase font-mono">
                        <button type="button" id="btn-apply-promo" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-white dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-bold rounded-xl transition">
                            Terapkan
                        </button>
                    </div>
                    <p id="promo-status-msg" class="text-[10px] mt-1.5 hidden font-semibold"></p>
                </div>

                <div class="flex justify-between items-center text-[10px] text-slate-500 dark:text-slate-400 mb-6 font-semibold">
                    <span>Kode Unik Transfer</span>
                    <span class="font-bold text-amber-600 dark:text-amber-500">(Dibuat otomatis)</span>
                </div>

                <div class="bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 p-4 rounded-2xl text-[10px] text-indigo-700 dark:text-indigo-400 flex items-start space-x-2.5">
                    <svg class="h-4 w-4 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="leading-relaxed">Kode unik transfer 3 digit akan ditambahkan ke total belanja Anda untuk verifikasi otomatis setelah pesanan diselesaikan.</p>
                </div>
            </div>
        </div>

    </main>

    <?php require __DIR__ . '/components/footer.php'; ?>

    <script src="assets/js/jquery.min.js"></script>
    <script>
        window.NusaBayCheckout = {
            subtotal: <?= json_encode((float) $total_price) ?>
        };
    </script>
    <script src="assets/js/pages/checkout.js"></script>
</body>
</html>
