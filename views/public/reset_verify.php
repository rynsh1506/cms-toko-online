<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

$email = sanitize_input($_GET['email'] ?? $_SESSION['reset_email'] ?? '');

$stmt = $pdo->query("SELECT content_value FROM landing_configs WHERE section_key = 'primary_color'");
$primary_color = $stmt->fetchColumn() ?: '#6366f1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Kode Reset - NusaBay</title>
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
        .otp-digit { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="flex h-screen overflow-hidden bg-white dark:bg-slate-950 transition-colors duration-300">

    <!-- Left Side: Branding / Image (61.8% Golden Ratio) -->
    <div class="hidden lg:flex lg:w-[61.8%] relative bg-slate-900 overflow-hidden">
        <img src="assets/images/auth-bg.png" alt="Auth Background" class="absolute inset-0 w-full h-full object-cover opacity-80 mix-blend-screen">
        
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>

        <!-- Branding Content -->
        <div class="relative z-10 flex flex-col justify-end p-16 text-white h-full w-full">
            <div class="max-w-2xl">
                <a href="index.php?page=home" class="inline-flex items-center space-x-3 mb-8">
                    <!-- Geometric NusaBay Logo -->
                    <svg class="h-10 w-10 rounded-xl shadow-lg shadow-indigo-500/20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="12" fill="url(#logo-grad-rv)" />
                        <rect x="10" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                        <rect x="30" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                        <rect x="20" y="6" width="8" height="36" rx="2" fill="#ffffff" transform="rotate(-32 24 24)" />
                        <defs>
                            <linearGradient id="logo-grad-rv" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#6366f1"/>
                                <stop offset="1" stop-color="#a855f7"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="font-extrabold text-4xl tracking-tight font-display">Nusa<span class="text-primary">Bay</span></span>
                </a>
                <h1 class="text-4xl md:text-5xl font-bold font-display leading-tight mb-6">
                    Platform e-commerce premium untuk kebutuhan gaya hidup Anda.
                </h1>
                <p class="text-lg text-slate-300">
                    Temukan ribuan produk berkualitas tinggi dengan pengalaman belanja modern, cepat, dan aman.
                </p>
            </div>
        </div>
    </div>

    <!-- Right Side: Form (38.2% Golden Ratio) -->
    <div class="w-full lg:w-[38.2%] flex flex-col justify-center px-8 sm:px-12 md:px-16 lg:px-12 xl:px-16 overflow-y-auto bg-slate-50 dark:bg-slate-950 relative">
        <!-- Optional Blob on mobile -->
        <div class="absolute w-96 h-96 bg-primary/5 rounded-full blur-3xl -top-12 -right-12 -z-10 animate-pulse lg:hidden"></div>

        <div class="w-full max-w-md mx-auto my-auto py-12">
            <!-- Mobile Logo -->
            <div class="lg:hidden mb-8">
                <a href="index.php?page=home" class="inline-flex items-center space-x-2">
                    <svg class="h-8 w-8 rounded-lg shadow-lg shadow-indigo-500/20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="12" fill="url(#logo-grad-rv-mobile)" />
                        <rect x="10" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                        <rect x="30" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                        <rect x="20" y="6" width="8" height="36" rx="2" fill="#ffffff" transform="rotate(-32 24 24)" />
                        <defs>
                            <linearGradient id="logo-grad-rv-mobile" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#6366f1"/>
                                <stop offset="1" stop-color="#a855f7"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="font-extrabold text-2xl tracking-tight text-slate-900 dark:text-white font-display">Nusa<span class="text-primary">Bay</span></span>
                </a>
            </div>

            <div class="mb-8">
                <h2 class="text-3xl font-bold text-slate-800 dark:text-white font-display">Verifikasi Kode Reset</h2>
                <p class="text-sm text-slate-500 mt-2">Masukkan 6-digit kode OTP yang telah dikirim ke email Anda.</p>
            </div>

            <div id="alert-container">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <form id="reset-verify-form" action="index.php?page=forgot_password_process&action=verify_code" method="POST" class="space-y-6">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-1.5">Alamat Email</label>
                    <input type="email" name="email" id="email-field" required value="<?= htmlspecialchars($email) ?>" placeholder="nama@email.com"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-sm" <?= !empty($email) ? 'readonly' : '' ?>>
                </div>

                <div>
                    <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-3 text-center">Masukkan 6-Digit Kode OTP</label>
                    <div class="flex justify-between gap-2" id="otp-input-container">
                        <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                        <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                        <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                        <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                        <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                        <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                    </div>
                    <input type="hidden" name="code" id="reset-code">
                </div>

                <button type="submit" id="btn-verify-reset"
                    class="w-full bg-primary text-white font-bold py-3 px-4 rounded-xl hover:opacity-90 active:scale-[0.98] transition shadow-lg shadow-primary/10 text-sm mt-4">
                    Verifikasi Kode
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-slate-500">
                Tidak menerima kode?
                <button type="button" id="btn-resend" class="text-primary font-bold hover:underline ml-1 disabled:opacity-50 disabled:cursor-not-allowed">
                    Kirim Ulang Kode
                </button>
                <span id="countdown-text" class="block mt-2 text-slate-400 hidden">Silakan tunggu <span id="countdown-timer">30</span> detik sebelum kirim ulang.</span>
            </div>

            <p class="text-center text-xs text-slate-500 mt-4">
                Kembali ke <a href="index.php?page=login" class="text-primary font-bold hover:underline">Masuk</a>
            </p>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>
    <script src="assets/js/pages/reset_verify.js"></script>
</body>
</html>
