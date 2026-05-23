<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

// Fetch primary color
$stmt = $pdo->query("SELECT content_value FROM landing_configs WHERE section_key = 'primary_color'");
$primary_color = $stmt->fetchColumn() ?: '#6366f1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NusaBay</title>
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
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex items-center justify-center min-h-screen p-6 relative overflow-hidden transition-colors duration-300">

    <!-- Background Blob effect -->
    <div class="absolute w-96 h-96 bg-primary/5 rounded-full blur-3xl -top-12 -left-12 -z-10 animate-pulse"></div>
    <div class="absolute w-96 h-96 bg-primary/5 rounded-full blur-3xl -bottom-12 -right-12 -z-10 animate-pulse"></div>

    <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-xl dark:shadow-none max-w-md w-full border border-slate-100 dark:border-slate-800 transition-colors duration-300">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="index.php?page=home" class="inline-flex items-center space-x-2 justify-center mb-3">
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
                <span class="font-extrabold text-2xl tracking-tight text-slate-900 dark:text-white font-display">Nusa<span class="text-primary">Bay</span></span>
            </a>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white font-display">Selamat datang kembali!</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Silakan masukkan detail akun Anda untuk masuk.</p>
        </div>

        <!-- Alerts Placeholder -->
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

        <form id="login-form" action="index.php?page=auth_process&action=login" method="POST" class="space-y-4">

            <?= csrf_field() ?>
            <div>
                <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-1.5">Alamat Email</label>
                <input type="email" name="email" required placeholder="nama@email.com"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-sm">
            </div>

            <div>
                <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="••••••••"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-sm">
            </div>

            <button type="submit" id="btn-login"
                class="w-full bg-primary text-white font-bold py-3 px-4 rounded-xl hover:opacity-90 active:scale-[0.98] transition shadow-lg shadow-primary/10 text-sm">
                Masuk
            </button>
        </form>

        <p class="text-center text-xs text-slate-500 mt-6">
            Belum punya akun? <a href="index.php?page=register" class="text-primary font-bold hover:underline">Daftar Sekarang</a>
        </p>
    </div>

    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script src="assets/js/pages/login.js"></script>

</body>
</html>
