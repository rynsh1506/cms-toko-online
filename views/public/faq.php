<?php
$stmt = $pdo->query("SELECT section_key, content_value FROM landing_configs");
$configs_raw = $stmt->fetchAll();
$configs = [];
foreach ($configs_raw as $c) {
    $configs[$c['section_key']] = $c['content_value'];
}
$primary_color = $configs['primary_color'] ?? '#6366f1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanya Jawab (FAQ) - NusaBay</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <script src="assets/js/tailwind.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { primary: '<?= $primary_color ?>' }
                }
            }
        }
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <link href="assets/css/fonts.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, .font-display { font-family: 'Outfit', sans-serif; }
        .faq-answer { display: none; }
        .faq-active .faq-answer { display: block; }
        .faq-active .faq-icon { transform: rotate(180deg); }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-300">

    <!-- Navbar Minimalis -->
    <nav class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-6">
            <div class="flex justify-between items-center h-20">
                <a href="index.php?page=home" class="text-xl font-black tracking-tight text-slate-900 dark:text-white hover:opacity-85 transition font-display flex items-center space-x-2">
                    <svg class="h-8 w-8 rounded-xl shadow-lg shadow-primary/20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="12" fill="url(#logo-grad)" />
                        <rect x="10" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                        <rect x="30" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                        <rect x="20" y="6" width="8" height="36" rx="2" fill="#ffffff" transform="rotate(-32 24 24)" />
                        <defs>
                            <linearGradient id="logo-grad" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                                <stop stop-color="<?= $primary_color ?>"/>
                                <stop offset="1" stop-color="#a855f7"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span>Nusa<span class="text-primary">Bay</span></span>
                </a>
                <a href="index.php?page=home" class="text-sm font-bold text-slate-500 hover:text-slate-900 dark:hover:text-white transition">Kembali ke Beranda</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-3xl mx-auto px-6 py-16 flex-1 w-full">
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 dark:text-white font-display tracking-tight mb-4">Tanya Jawab</h1>
            <p class="text-slate-500 dark:text-slate-400 max-w-lg mx-auto">Kami mengumpulkan beberapa pertanyaan yang paling sering ditanyakan pelanggan. Jika Anda tidak menemukan jawaban, jangan ragu hubungi kami.</p>
        </div>

        <div class="space-y-4">
            <!-- Item 1 -->
            <div class="faq-item bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden transition-all shadow-sm">
                <button class="faq-btn w-full px-6 py-5 flex justify-between items-center text-left focus:outline-none">
                    <span class="font-bold text-slate-800 dark:text-slate-200">Bagaimana cara berbelanja di NusaBay?</span>
                    <svg class="faq-icon h-5 w-5 text-slate-400 transform transition-transform duration-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="faq-answer px-6 pb-6 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Anda bisa mulai dengan mendaftarkan akun. Setelah memiliki akun, silakan verifikasi email Anda. Anda dapat menjelajahi produk di halaman utama, memasukkannya ke dalam keranjang, lalu melakukan proses <i>checkout</i> (pembayaran) dan mentransfer dana sesuai dengan jumlah tagihan.
                </div>
            </div>

            <!-- Item 2 -->
            <div class="faq-item bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden transition-all shadow-sm">
                <button class="faq-btn w-full px-6 py-5 flex justify-between items-center text-left focus:outline-none">
                    <span class="font-bold text-slate-800 dark:text-slate-200">Metode pembayaran apa saja yang didukung?</span>
                    <svg class="faq-icon h-5 w-5 text-slate-400 transform transition-transform duration-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="faq-answer px-6 pb-6 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Saat ini kami mendukung pembayaran melalui Transfer Bank Manual (BCA, Mandiri, BNI, BRI, dll). Instruksi pembayaran dan nomor rekening lengkap akan diberikan pada halaman <i>checkout</i> dan di dalam invoice pesanan Anda.
                </div>
            </div>

            <!-- Item 3 -->
            <div class="faq-item bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden transition-all shadow-sm">
                <button class="faq-btn w-full px-6 py-5 flex justify-between items-center text-left focus:outline-none">
                    <span class="font-bold text-slate-800 dark:text-slate-200">Bagaimana cara mengonfirmasi pembayaran saya?</span>
                    <svg class="faq-icon h-5 w-5 text-slate-400 transform transition-transform duration-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="faq-answer px-6 pb-6 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Setelah Anda melakukan transfer bank, buka menu "Daftar Pesanan", pilih pesanan Anda, dan klik tombol "Konfirmasi WhatsApp". Anda akan langsung diarahkan ke nomor WhatsApp admin kami dengan menyertakan Order ID Anda agar pesanan cepat diproses.
                </div>
            </div>

            <!-- Item 4 -->
            <div class="faq-item bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden transition-all shadow-sm">
                <button class="faq-btn w-full px-6 py-5 flex justify-between items-center text-left focus:outline-none">
                    <span class="font-bold text-slate-800 dark:text-slate-200">Apakah saya bisa membatalkan pesanan?</span>
                    <svg class="faq-icon h-5 w-5 text-slate-400 transform transition-transform duration-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="faq-answer px-6 pb-6 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Ya, Anda dapat membatalkan pesanan Anda sendiri selama statusnya masih "Menunggu Pembayaran". Jika Anda sudah melakukan pembayaran atau barang sudah dikirim, pembatalan tidak dapat dilakukan melalui sistem dan Anda harus menghubungi admin secara langsung.
                </div>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/components/footer.php'; ?>

    <script src="assets/js/pages/faq.js"></script>
</body>
</html>
