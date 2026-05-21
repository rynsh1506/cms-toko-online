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
    <title>Kebijakan Privasi - NusaBay</title>
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
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white font-display mb-4">Kebijakan Privasi</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 border-b border-slate-100 dark:border-slate-800 pb-6">Pembaruan Terakhir: <?= date('d F Y') ?></p>

            <div class="space-y-8 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                <section>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">1. Pengumpulan Informasi</h2>
                    <p>NusaBay mengumpulkan informasi identitas pribadi (seperti nama, alamat email, nomor telepon, dan alamat pengiriman) yang Anda berikan secara sadar saat mendaftar akun atau melakukan transaksi. Kami juga mengumpulkan informasi non-pribadi seperti jenis perangkat, peramban (browser), dan data analitik interaksi Anda di dalam platform.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">2. Penggunaan Informasi</h2>
                    <p>Informasi yang kami kumpulkan digunakan untuk tujuan berikut:</p>
                    <ul class="list-disc pl-5 space-y-2 mt-2">
                        <li>Memproses, memverifikasi, dan menyelesaikan transaksi pemesanan Anda.</li>
                        <li>Berkomunikasi dengan Anda mengenai pesanan, konfirmasi pembayaran, dan informasi akun.</li>
                        <li>Mengirimkan email operasional, promosi, atau pembaruan keamanan (Anda dapat memilih untuk berhenti berlangganan promosi).</li>
                        <li>Meningkatkan fungsionalitas dan pengalaman pengguna (UI/UX) di platform kami.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">3. Perlindungan & Keamanan Data</h2>
                    <p>Keamanan data Anda sangat penting bagi kami. Kami menerapkan langkah-langkah keamanan teknis standar industri (termasuk enkripsi kata sandi menggunakan BCRYPT) untuk melindungi data pribadi Anda dari akses yang tidak sah, perubahan, atau pengungkapan. Namun, tidak ada metode transmisi di internet yang 100% aman.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">4. Pembagian Data kepada Pihak Ketiga</h2>
                    <p>NusaBay tidak akan pernah menjual, menyewakan, atau menukar data pribadi Anda kepada pihak ketiga untuk tujuan pemasaran. Kami hanya membagikan data kepada pihak ketiga yang berwenang, seperti penyedia jasa logistik (kurir) semata-mata untuk keperluan pengiriman pesanan Anda, atau jika diwajibkan oleh hukum yang berlaku.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">5. Hubungi Kami</h2>
                    <p>Jika Anda memiliki pertanyaan, kekhawatiran, atau permintaan untuk menghapus data Anda dari sistem kami, silakan hubungi tim dukungan kami melalui tautan yang tersedia di bagian bawah halaman (footer) website ini.</p>
                </section>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/components/footer.php'; ?>
</body>
</html>
