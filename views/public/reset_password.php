<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

// Security: only allow access if reset was authorized via OTP verification
if (empty($_SESSION['reset_authorized']) || empty($_SESSION['reset_email'])) {
    $_SESSION['error'] = 'Sesi reset password tidak valid. Silakan mulai ulang.';
    redirect('index.php?page=forgot_password');
}

$email = $_SESSION['reset_email'];

$stmt = $pdo->query("SELECT content_value FROM landing_configs WHERE section_key = 'primary_color'");
$primary_color = $stmt->fetchColumn() ?: '#6366f1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru - NusaBay</title>
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
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex items-center justify-center min-h-screen p-6 relative overflow-hidden transition-colors duration-300">

    <div class="absolute w-96 h-96 bg-primary/5 rounded-full blur-3xl -top-12 -left-12 -z-10 animate-pulse"></div>
    <div class="absolute w-96 h-96 bg-primary/5 rounded-full blur-3xl -bottom-12 -right-12 -z-10 animate-pulse"></div>

    <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-xl dark:shadow-none max-w-md w-full border border-slate-100 dark:border-slate-800 transition-colors duration-300">
        <div class="text-center mb-8">
            <a href="index.php?page=home" class="inline-flex items-center space-x-2 justify-center mb-3">
                <svg class="h-9 w-9 rounded-xl shadow-lg shadow-indigo-500/20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="12" fill="url(#logo-grad-rp)" />
                    <rect x="10" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                    <rect x="30" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                    <rect x="20" y="6" width="8" height="36" rx="2" fill="#ffffff" transform="rotate(-32 24 24)" />
                    <defs>
                        <linearGradient id="logo-grad-rp" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#6366f1"/>
                            <stop offset="1" stop-color="#a855f7"/>
                        </linearGradient>
                    </defs>
                </svg>
                <span class="font-extrabold text-2xl tracking-tight text-slate-900 dark:text-white font-display">Nusa<span class="text-primary">Bay</span></span>
            </a>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white font-display">Buat Password Baru</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Masukkan password baru Anda. Minimal 8 karakter dengan kombinasi huruf besar, kecil, angka, dan simbol.</p>
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

        <form id="reset-password-form" action="index.php?page=forgot_password_process&action=reset_password" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

            <div>
                <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-1.5">Password Baru</label>
                <div class="relative">
                    <input type="password" name="password" id="password-input" required placeholder="••••••••"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 pr-12 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-sm">
                    <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-350 focus:outline-none cursor-pointer">
                        <svg id="eye-icon-show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eye-icon-hide" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-1.5">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input type="password" name="password_confirm" id="password-confirm-input" required placeholder="••••••••"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 pr-12 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-sm">
                    <button type="button" id="toggle-password-confirm" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-350 focus:outline-none cursor-pointer">
                        <svg id="eye-icon-show-confirm" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eye-icon-hide-confirm" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Password strength indicator -->
            <div class="text-[10px] text-slate-400 dark:text-slate-500 space-y-0.5" id="password-rules">
                <p id="rule-length" class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700 inline-block"></span> Minimal 8 karakter</p>
                <p id="rule-upper" class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700 inline-block"></span> Huruf besar (A-Z)</p>
                <p id="rule-lower" class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700 inline-block"></span> Huruf kecil (a-z)</p>
                <p id="rule-number" class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700 inline-block"></span> Angka (0-9)</p>
                <p id="rule-symbol" class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700 inline-block"></span> Simbol (!@#$%)</p>
            </div>

            <button type="submit" id="btn-reset-password"
                class="w-full bg-primary text-white font-bold py-3 px-4 rounded-xl hover:opacity-90 active:scale-[0.98] transition shadow-lg shadow-primary/10 text-sm">
                Simpan Password Baru
            </button>
        </form>

        <p class="text-center text-xs text-slate-500 mt-6">
            Kembali ke <a href="index.php?page=login" class="text-primary font-bold hover:underline">Masuk</a>
        </p>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>
    <script src="assets/js/pages/reset_password.js"></script>
</body>
</html>
