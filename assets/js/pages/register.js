$(document).ready(function() {
            // Handle Register AJAX
            $('#register-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = $('#btn-register');

                btn.prop('disabled', true).text('Memproses...');
                $('#alert-container').empty();

                // Frontend password validation
                const password = $('input[name="password"]').val();
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/;

                if (!passwordRegex.test(password)) {
                    $('#alert-container').html(`
                        <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                            Password minimal 8 karakter dan harus mengandung huruf besar, huruf kecil, angka, serta simbol.
                        </div>
                    `);
                    btn.prop('disabled', false).text('Daftar Akun');
                    return false;
                }

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
                            }, 1000);
                        } else {
                            $('#alert-container').html(`
                                <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                                    ${response.message}
                                </div>
                            `);
                            btn.prop('disabled', false).text('Daftar Akun');
                        }
                    },
                    error: function() {
                        $('#alert-container').html(`
                            <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                                Terjadi kesalahan pada server. Harap coba lagi.
                            </div>
                        `);
                        btn.prop('disabled', false).text('Daftar Akun');
                    }
                });
            });

            // Toggle Show/Hide Password
            $('#toggle-password').on('click', function () {
                const passwordInput = $('#password-input');
                const eyeIconShow = $('#eye-icon-show');
                const eyeIconHide = $('#eye-icon-hide');

                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    eyeIconShow.addClass('hidden');
                    eyeIconHide.removeClass('hidden');
                } else {
                    passwordInput.attr('type', 'password');
                    eyeIconShow.removeClass('hidden');
                    eyeIconHide.addClass('hidden');
                }
            });
        });
