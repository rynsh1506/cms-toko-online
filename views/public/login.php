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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pro-Store CMS</title>
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
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex items-center justify-center min-h-screen p-6 relative overflow-hidden transition-colors duration-300">
    
    <!-- Background Blob effect -->
    <div class="absolute w-96 h-96 bg-primary/5 rounded-full blur-3xl -top-12 -left-12 -z-10 animate-pulse"></div>
    <div class="absolute w-96 h-96 bg-primary/5 rounded-full blur-3xl -bottom-12 -right-12 -z-10 animate-pulse"></div>

    <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-xl dark:shadow-none max-w-md w-full border border-slate-100 dark:border-slate-800 transition-colors duration-300">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="index.php?page=home" class="inline-flex items-center space-x-2 justify-center mb-3">
                <span class="h-9 w-9 rounded-xl bg-primary flex items-center justify-center font-bold text-white text-lg shadow-lg shadow-primary/20 font-display">P</span>
                <span class="font-extrabold text-2xl tracking-tight text-slate-900 dark:text-white font-display">Pro-Store</span>
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle Login AJAX
            $('#login-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = $('#btn-login');
                
                btn.prop('disabled', true).text('Memproses...');
                $('#alert-container').empty();
                
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#alert-container').html(`
                                <div class="bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                                    ${response.message}
                                </div>
                            `);
                            setTimeout(() => {
                                window.location.href = response.redirect_url;
                            }, 800);
                        } else {
                            $('#alert-container').html(`
                                <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                                    ${response.message}
                                </div>
                            `);
                            btn.prop('disabled', false).text('Masuk');
                        }
                    },
                    error: function() {
                        $('#alert-container').html(`
                            <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                                Terjadi kesalahan pada server. Harap coba lagi.
                            </div>
                        `);
                        btn.prop('disabled', false).text('Masuk');
                    }
                });
            });
        });
    </script>

</body>
</html>
