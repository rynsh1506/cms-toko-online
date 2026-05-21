<?php
// Pastikan admin sudah login
if (!isAuth() || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Akses ditolak. Anda bukan admin.";
    redirect('index.php?page=login');
}

require_once __DIR__ . '/../../config/db.php';

// Fetch updated admin profile dynamically
$stmt_admin = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_admin->execute([$_SESSION['user_id']]);
$admin_info = $stmt_admin->fetch();

$admin_name = htmlspecialchars($admin_info['name'] ?? $_SESSION['name'] ?? 'Admin');
$admin_email = htmlspecialchars($admin_info['email'] ?? '');
$admin_avatar = $admin_info['avatar_url'] ? htmlspecialchars($admin_info['avatar_url']) : null;

// Menentukan menu aktif
$current_page = $_GET['page'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - NusaBay</title>
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
                        primary: '#6366f1',
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
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-300">

    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar -->
        <aside class="w-full md:w-64 md:sticky md:top-0 md:h-screen bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 flex-shrink-0 flex flex-col justify-between border-r border-slate-200 dark:border-slate-800 shadow-xl transition-colors duration-300">
            <div>
                <!-- Brand Header -->
                <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
                    <a href="index.php?page=admin" class="flex items-center space-x-2">
                                                <svg class="h-8 w-8 rounded-lg shadow-lg shadow-indigo-500/20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="48" height="48" rx="12" fill="url(#logo-grad-nav-side)"/>
                            <rect x="10" y="8" width="8" height="32" rx="2" fill="#ffffff"/>
                            <rect x="30" y="8" width="8" height="32" rx="2" fill="#ffffff"/>
                            <rect x="20" y="6" width="8" height="36" rx="2" fill="#ffffff" transform="rotate(-32 24 24)"/>
                            <defs>
                                <linearGradient id="logo-grad-nav-side" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#6366f1"/>
                                    <stop offset="1" stop-color="#a855f7"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <span class="font-extrabold text-xl tracking-tight text-slate-900 dark:text-white font-display">Nusa<span class="text-indigo-500 dark:text-indigo-400">Bay</span></span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 space-y-1.5">
                    <a href="index.php?page=admin" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page === 'admin' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2 2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="index.php?page=admin_products" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page === 'admin_products' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Kelola Produk</span>
                    </a>

                    <a href="index.php?page=admin_categories" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page === 'admin_categories' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Kelola Kategori</span>
                    </a>

                    <a href="index.php?page=admin_banks" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page === 'admin_banks' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span>Rekening Bank</span>
                    </a>

                    <a href="index.php?page=admin_promos" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page === 'admin_promos' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 0h4m-4 0H8m12 3v10a2 2 0 01-2 2H6a2 2 0 01-2-2V11" />
                        </svg>
                        <span>Kode Promo</span>
                    </a>

                    <a href="index.php?page=admin_banners" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page === 'admin_banners' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Banner Promo</span>
                    </a>

                    <a href="index.php?page=admin_orders" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page === 'admin_orders' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span>Kelola Pesanan</span>
                    </a>

                    <a href="index.php?page=admin_settings" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page === 'admin_settings' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Pengaturan Toko</span>
                    </a>
                </nav>
            </div>

            <!-- Footer / User Info & Storefront link -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/50">
                <div class="flex items-center justify-between mb-4">
                    <a href="index.php?page=admin_profile" class="flex items-center space-x-3 hover:opacity-85 transition">
                        <?php if ($admin_avatar): ?>
                            <img class="h-9 w-9 rounded-full object-cover border border-slate-300 dark:border-slate-700" src="<?= $admin_avatar ?>" alt="Avatar">
                        <?php else: ?>
                            <div class="h-9 w-9 rounded-full bg-indigo-600 flex items-center justify-center font-bold text-white text-sm">
                                <?= strtoupper(substr($admin_name, 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="overflow-hidden">
                            <p class="text-xs font-semibold text-slate-800 dark:text-white leading-none truncate"><?= $admin_name ?></p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 truncate"><?= $admin_email ?></p>
                        </div>
                    </a>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="index.php?page=home" class="flex items-center justify-center space-x-1.5 py-1.5 rounded bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-xs text-slate-700 dark:text-white transition font-medium">
                        <span>Toko</span>
                    </a>
                    <a href="index.php?page=auth_process&action=logout" class="flex items-center justify-center space-x-1.5 py-1.5 rounded bg-rose-100 dark:bg-rose-600/20 text-xs text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-600/30 hover:text-red-700 dark:hover:text-red-300 transition font-medium">
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-h-screen overflow-hidden">
            <!-- Header bar -->
            <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-8 flex justify-between items-center shadow-sm transition-colors duration-300">
                <div class="flex items-center space-x-3">
                    <button class="md:hidden text-slate-500 focus:outline-none" id="menu-toggle">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white font-display">Back-office Control Panel</h2>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Dark mode toggle -->
                    <button id="theme-toggle" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        <!-- Sun Icon -->
                        <svg id="theme-toggle-sun" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-3.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                        <!-- Moon Icon -->
                        <svg id="theme-toggle-moon" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <div class="text-xs text-slate-500 hidden sm:block">
                        Server Time: <span class="font-mono font-medium"><?= date('d M Y, H:i') ?></span>
                    </div>
                </div>
            </header>

            <!-- Inner view content -->
            <div class="p-8 flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 transition-colors duration-300">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-400 p-4 rounded-r-lg mb-6 shadow-sm flex items-start space-x-3">
                        <svg class="h-5 w-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold">Berhasil!</p>
                            <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-4 rounded-r-lg mb-6 shadow-sm flex items-start space-x-3">
                        <svg class="h-5 w-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938-4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold">Error!</p>
                            <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Render the actual sub page -->
                <?php require __DIR__ . '/' . $admin_page; ?>
            </div>
        </main>
    </div>

    <!-- Script to toggle mobile sidebar & theme switch -->
    <script>
        document.getElementById('menu-toggle')?.addEventListener('click', function() {
            const sidebar = document.querySelector('aside');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('w-full');
            sidebar.classList.toggle('absolute');
            sidebar.classList.toggle('z-50');
            sidebar.classList.toggle('h-screen');
        });

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
    </script>
</body>
</html>
