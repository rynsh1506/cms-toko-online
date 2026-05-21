<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

// Ambil email dari GET atau SESSION
$email = sanitize_input($_GET['email'] ?? $_SESSION['verify_email'] ?? '');

// Fetch primary color
$stmt = $pdo->query("SELECT content_value FROM landing_configs WHERE section_key = 'primary_color'");
$primary_color = $stmt->fetchColumn() ?: '#6366f1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - NusaBay</title>
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
        .otp-digit {
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
            <h2 class="text-xl font-bold text-slate-800 dark:text-white font-display">Verifikasi Akun Anda</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Kami telah mengirimkan 6-digit kode verifikasi ke email Anda.</p>
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

        <form id="verify-form" action="index.php?page=verify_email" method="POST" class="space-y-6">
            <div>
                <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-1.5">Alamat Email</label>
                <input type="email" name="email" id="email-field" required value="<?= htmlspecialchars($email) ?>" placeholder="nama@email.com"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-sm">
            </div>

            <div>
                <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-3 text-center">Masukkan 6-Digit Kode Verifikasi</label>
                
                <!-- Individual digit inputs for sleek premium OTP UI -->
                <div class="flex justify-between gap-2" id="otp-input-container">
                    <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                    <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                    <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                    <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                    <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                    <input type="text" maxlength="1" class="otp-digit w-12 h-12 text-center text-xl font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-900 dark:text-white transition">
                </div>
                <!-- Hidden input to store combined code -->
                <input type="hidden" name="code" id="verification-code">
            </div>
            
            <button type="submit" id="btn-verify"
                class="w-full bg-primary text-white font-bold py-3 px-4 rounded-xl hover:opacity-90 active:scale-[0.98] transition shadow-lg shadow-primary/10 text-sm">
                Verifikasi Akun
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

    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Sleek OTP Digits Navigation Logic
            const inputs = $('.otp-digit');
            
            inputs.on('input', function() {
                const index = inputs.index(this);
                if (this.value && index < inputs.length - 1) {
                    inputs.eq(index + 1).focus();
                }
                combineDigits();
            });

            inputs.on('keydown', function(e) {
                const index = inputs.index(this);
                if (e.key === 'Backspace') {
                    if (!this.value && index > 0) {
                        inputs.eq(index - 1).focus().val('');
                    }
                }
                combineDigits();
            });
            
            inputs.on('paste', function(e) {
                e.preventDefault();
                const pasteData = (e.originalEvent.clipboardData || window.clipboardData).getData('text').trim();
                if (/^\d{6}$/.test(pasteData)) {
                    for (let i = 0; i < inputs.length; i++) {
                        inputs.eq(i).val(pasteData[i]);
                    }
                    inputs.last().focus();
                    combineDigits();
                }
            });

            function combineDigits() {
                let code = '';
                inputs.each(function() {
                    code += this.value;
                });
                $('#verification-code').val(code);
            }

            // Handle OTP Verification Form Submit
            $('#verify-form').on('submit', function(e) {
                e.preventDefault();
                combineDigits();
                
                const form = $(this);
                const btn = $('#btn-verify');
                
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
                                <div class="bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold font-display">
                                    ${response.message}
                                </div>
                            `);
                            setTimeout(() => {
                                window.location.href = response.redirect_url;
                            }, 1000);
                        } else {
                            $('#alert-container').html(`
                                <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                                    ${response.message}
                                </div>
                            `);
                            btn.prop('disabled', false).text('Verifikasi Akun');
                        }
                    },
                    error: function() {
                        $('#alert-container').html(`
                            <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                                Terjadi kesalahan pada server. Harap coba lagi.
                            </div>
                        `);
                        btn.prop('disabled', false).text('Verifikasi Akun');
                    }
                });
            });

            // Resend Verification Code logic with Countdown
            let countdown = 0;
            let timerInterval;

            function startTimer() {
                countdown = 30;
                $('#btn-resend').prop('disabled', true);
                $('#countdown-text').removeClass('hidden');
                $('#countdown-timer').text(countdown);
                
                timerInterval = setInterval(() => {
                    countdown--;
                    $('#countdown-timer').text(countdown);
                    if (countdown <= 0) {
                        clearInterval(timerInterval);
                        $('#btn-resend').prop('disabled', false);
                        $('#countdown-text').addClass('hidden');
                    }
                }, 1000);
            }

            $('#btn-resend').on('click', function() {
                const email = $('#email-field').val();
                if (!email) {
                    $('#alert-container').html(`
                        <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                            Harap isi alamat email terlebih dahulu untuk mengirim ulang kode!
                        </div>
                    `);
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true);
                
                $.ajax({
                    url: 'index.php?page=auth_process&action=resend_code',
                    type: 'POST',
                    data: { email: email },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#alert-container').html(`
                                <div class="bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold font-display">
                                    ${response.message}
                                </div>
                            `);
                            startTimer();
                        } else {
                            $('#alert-container').html(`
                                <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                                    ${response.message}
                                </div>
                            `);
                            btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        $('#alert-container').html(`
                            <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                                Gagal mengirim ulang kode. Silakan coba lagi.
                            </div>
                        `);
                        btn.prop('disabled', false);
                    }
                });
            });

        });
    </script>
</body>
</html>
