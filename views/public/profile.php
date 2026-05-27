<?php
// Variables $configs, $primary_color, $user
// are provided by ProfileViewController.php

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
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - NusaBay</title>
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
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-300">

    <!-- Navbar -->
    <nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-slate-100 dark:border-slate-800 transition-colors duration-300">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex justify-between items-center h-20">
                <a href="index.php?page=home" class="text-2xl font-black tracking-tight text-slate-900 dark:text-white hover:opacity-85 transition font-display flex items-center space-x-2">
                    <svg class="h-9 w-9 rounded-xl shadow-lg shadow-indigo-500/20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="12" fill="url(#logo-grad-profile)" />
                        <rect x="10" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                        <rect x="30" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                        <rect x="20" y="6" width="8" height="36" rx="2" fill="#ffffff" transform="rotate(-32 24 24)" />
                        <defs>
                            <linearGradient id="logo-grad-profile" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#6366f1"/>
                                <stop offset="1" stop-color="#a855f7"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span>Nusa<span class="text-primary">Bay</span></span>
                </a>
                <div class="flex items-center space-x-6">
                    <a href="index.php?page=cart" id="cart-link" class="relative p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition flex items-center justify-center">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span id="cart-badge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-md shadow-primary/20 <?= $cart_count > 0 ? '' : 'hidden' ?>">
                            <?= $cart_count ?>
                        </span>
                    </a>
                    
                    <a href="index.php?page=home" class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">Beranda</a>
                    <a href="index.php?page=orders" class="text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">Pesanan Saya</a>
                    <a href="index.php?page=profile" class="text-sm font-bold text-primary transition">Profil Saya</a>
                    <a href="index.php?page=auth_process&action=logout" class="text-sm font-bold text-red-500 hover:text-red-700 transition">Logout</a>

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

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto px-6 py-12 flex-1 w-full space-y-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white font-display">Pengaturan Profil</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ubah biodata, foto profil, dan kredensial keamanan akun Anda.</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/50 rounded-2xl text-emerald-800 dark:text-emerald-400 text-sm font-semibold">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 rounded-2xl text-rose-800 dark:text-rose-450 text-sm font-semibold">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Profile Card Preview -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col items-center text-center transition-colors duration-300">
                    <!-- Avatar Preview -->
                    <div class="relative group">
                        <div id="avatar-container" class="h-32 w-32 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 border-4 border-indigo-50 dark:border-slate-900 shadow-md">
                            <?php if (!empty($user['avatar_url'])): ?>
                                <img id="avatar-preview-img" class="h-full w-full object-cover" src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Avatar Preview">
                            <?php else: ?>
                                <div id="avatar-placeholder" class="h-full w-full flex items-center justify-center font-bold text-slate-400 dark:text-slate-500 text-4xl uppercase bg-slate-100 dark:bg-slate-800">
                                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?>
                                </div>
                                <img id="avatar-preview-img" class="h-full w-full object-cover hidden" src="" alt="Avatar Preview">
                            <?php endif; ?>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-slate-800 dark:text-white mt-4 font-display" id="display-user-name"><?= htmlspecialchars($user['name']) ?></h3>
                    <span class="px-2.5 py-1 text-[11px] font-bold tracking-wider uppercase rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 mt-1">Pelanggan</span>

                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-4 leading-relaxed italic" id="display-user-bio">
                        <?= !empty($user['bio']) ? htmlspecialchars($user['bio']) : '"Belum menulis deskripsi bio."' ?>
                    </p>

                    <div class="w-full border-t border-slate-100 dark:border-slate-800/80 my-5"></div>

                    <div class="w-full text-left space-y-3">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">Email</span>
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate" id="display-user-email"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">No. Handphone</span>
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 font-mono" id="display-user-phone"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : '-' ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Forms -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Card 1: Informasi Profil -->
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-8 shadow-sm transition-colors duration-300">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 font-display flex items-center space-x-2">
                        <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Informasi Profil</span>
                    </h2>

                    <form id="profile-info-form" action="index.php?page=profile_process&action=update_profile" method="POST" enctype="multipart/form-data" class="space-y-5">
                        <?= csrf_field() ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Nama Lengkap</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Alamat Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">No. Handphone</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" placeholder="+628123...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Foto Profil (Avatar)</label>
                                <input type="file" id="avatar-input" name="avatar" accept="image/*" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl px-4 py-1.5 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/40 dark:file:text-indigo-400 hover:file:opacity-90 transition cursor-pointer">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Alamat Lengkap</label>
                            <textarea name="address" rows="3" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" placeholder="Jalan, RT/RW, Kecamatan, Kota, Kode Pos..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Bio Singkat</label>
                            <textarea name="bio" rows="3" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" placeholder="Tulis bio singkat mengenai diri Anda..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" id="btn-save-profile" class="bg-primary hover:opacity-90 text-white font-bold py-2.5 px-6 rounded-xl text-xs tracking-wider uppercase transition shadow-lg shadow-primary/25 active:scale-[0.98] cursor-pointer">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>

                <!-- Card 2: Kredensial Keamanan -->
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-8 shadow-sm transition-colors duration-300">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 font-display flex items-center space-x-2">
                        <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Ubah Kredensial Password</span>
                    </h2>

                    <form id="profile-password-form" action="index.php?page=profile_process&action=change_password" method="POST" class="space-y-5">
                        <?= csrf_field() ?>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Password Lama</label>
                            <input type="password" name="old_password" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Password Baru</label>
                                <input type="password" name="new_password" id="new-password-input" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" id="btn-save-password" class="bg-primary hover:opacity-90 text-white font-bold py-2.5 px-6 rounded-xl text-xs tracking-wider uppercase transition shadow-lg shadow-primary/25 active:scale-[0.98] cursor-pointer">Ubah Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/components/footer.php'; ?>

    <script src="assets/js/jquery.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script src="assets/js/sweetalert2.all.min.js"></script>
    
    <!-- Theme logic script -->
    <script>
        const sunIcon = document.getElementById('theme-toggle-sun');
        const moonIcon = document.getElementById('theme-toggle-moon');
        const themeBtn = document.getElementById('theme-toggle');

        function updateIcons() {
            if (document.documentElement.classList.contains('dark')) {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        }

        updateIcons();

        themeBtn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateIcons();
        });

        // Live preview avatar upload
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreviewImg = document.getElementById('avatar-preview-img');
        const avatarPlaceholder = document.getElementById('avatar-placeholder');

        if (avatarInput) {
            avatarInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreviewImg.src = e.target.result;
                        avatarPreviewImg.classList.remove('hidden');
                        if (avatarPlaceholder) {
                            avatarPlaceholder.classList.add('hidden');
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>
</body>
</html>
