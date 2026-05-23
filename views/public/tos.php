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
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan - NusaBay</title>
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
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 md:p-12 shadow-sm border border-slate-100 dark:border-slate-800">
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white font-display mb-4">Syarat & Ketentuan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 border-b border-slate-100 dark:border-slate-800 pb-6">Pembaruan Terakhir: <?= date('d F Y') ?></p>

            <div class="space-y-8 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                <section>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">1. Pendahuluan</h2>
                    <p>Selamat datang di NusaBay. Dengan mengakses dan menggunakan layanan kami, Anda dianggap telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang berlaku di bawah ini. Jika Anda tidak menyetujui salah satu bagian dari ketentuan ini, Anda tidak diperkenankan untuk menggunakan layanan kami.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">2. Akun Pengguna</h2>
                    <ul class="list-disc pl-5 space-y-2">
                        <li>Anda wajib memberikan informasi yang akurat, lengkap, dan terkini saat melakukan pendaftaran (nama, email, password).</li>
                        <li>Keamanan akun dan kerahasiaan kata sandi (password) adalah tanggung jawab penuh Anda. Kami tidak bertanggung jawab atas kerugian yang timbul akibat kelalaian dalam menjaga keamanan akun.</li>
                        <li>NusaBay berhak untuk menangguhkan atau menghapus akun Anda secara sepihak apabila ditemukan indikasi kecurangan, penyalahgunaan, atau pelanggaran terhadap syarat dan ketentuan.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">3. Transaksi & Pembayaran</h2>
                    <ul class="list-disc pl-5 space-y-2">
                        <li>Semua harga produk yang tertera di platform NusaBay dalam mata uang Rupiah (IDR).</li>
                        <li>Pembayaran wajib dilakukan dalam batas waktu yang telah ditentukan (secara default setelah checkout). Jika melewati batas waktu, pesanan akan dibatalkan secara otomatis.</li>
                        <li>Untuk metode Transfer Bank Manual, Anda wajib melakukan konfirmasi dan mentransfer dana sesuai dengan total tagihan hingga 3 digit kode unik terakhir agar sistem/admin dapat memverifikasinya.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">4. Pengiriman & Pengembalian</h2>
                    <p>Barang akan diproses dan dikirim setelah pembayaran dikonfirmasi lunas. Kami tidak bertanggung jawab atas keterlambatan pengiriman yang disebabkan oleh pihak logistik (kurir). Kebijakan retur (pengembalian barang) hanya berlaku jika barang yang diterima cacat pabrik atau tidak sesuai dengan deskripsi pesanan Anda maksimal 2x24 jam sejak barang diterima.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">5. Perubahan Kebijakan</h2>
                    <p>NusaBay berhak untuk sewaktu-waktu mengubah, menambah, atau mengurangi bagian dari Syarat & Ketentuan ini tanpa pemberitahuan sebelumnya. Kebijakan terbaru akan selalu diperbarui di halaman ini.</p>
                </section>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/components/footer.php'; ?>
</body>
</html>
