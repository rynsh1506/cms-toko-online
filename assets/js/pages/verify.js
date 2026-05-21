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
