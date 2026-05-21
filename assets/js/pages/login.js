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
                            if (response.redirect_url) {
                                setTimeout(() => {
                                    window.location.href = response.redirect_url;
                                }, 1500);
                            }
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
