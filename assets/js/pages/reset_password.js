$(document).ready(function() {
    // Show/Hide Password Toggle
    $('#toggle-password').on('click', function() {
        const input = $('#password-input');
        const show = $('#eye-icon-show');
        const hide = $('#eye-icon-hide');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            show.addClass('hidden');
            hide.removeClass('hidden');
        } else {
            input.attr('type', 'password');
            show.removeClass('hidden');
            hide.addClass('hidden');
        }
    });

    $('#toggle-password-confirm').on('click', function() {
        const input = $('#password-confirm-input');
        const show = $('#eye-icon-show-confirm');
        const hide = $('#eye-icon-hide-confirm');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            show.addClass('hidden');
            hide.removeClass('hidden');
        } else {
            input.attr('type', 'password');
            show.removeClass('hidden');
            hide.addClass('hidden');
        }
    });

    // Real-time password strength indicator
    $('#password-input').on('input', function() {
        const pw = $(this).val();
        updateRule('#rule-length', pw.length >= 8);
        updateRule('#rule-upper', /[A-Z]/.test(pw));
        updateRule('#rule-lower', /[a-z]/.test(pw));
        updateRule('#rule-number', /[0-9]/.test(pw));
        updateRule('#rule-symbol', /[^a-zA-Z0-9]/.test(pw));
    });

    function updateRule(selector, passed) {
        const dot = $(selector).find('span');
        if (passed) {
            dot.removeClass('bg-slate-300 dark:bg-slate-700').addClass('bg-emerald-500');
            $(selector).removeClass('text-slate-400 dark:text-slate-500').addClass('text-emerald-600 dark:text-emerald-400');
        } else {
            dot.removeClass('bg-emerald-500').addClass('bg-slate-300 dark:bg-slate-700');
            $(selector).removeClass('text-emerald-600 dark:text-emerald-400').addClass('text-slate-400 dark:text-slate-500');
        }
    }

    // Form Submit
    $('#reset-password-form').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const btn = $('#btn-reset-password');
        const password = $('input[name="password"]').val();
        const passwordConfirm = $('input[name="password_confirm"]').val();
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/;

        // Client-side validation
        if (!passwordRegex.test(password)) {
            $('#alert-container').html(`
                <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                    Password minimal 8 karakter dan harus mengandung huruf besar, huruf kecil, angka, serta simbol.
                </div>
            `);
            return false;
        }

        if (password !== passwordConfirm) {
            $('#alert-container').html(`
                <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                    Konfirmasi password tidak cocok!
                </div>
            `);
            return false;
        }

        btn.prop('disabled', true).text('Menyimpan...');
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
                    }, 1500);
                } else {
                    $('#alert-container').html(`
                        <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                            ${response.message}
                        </div>
                    `);
                    btn.prop('disabled', false).text('Simpan Password Baru');
                }
            },
            error: function() {
                $('#alert-container').html(`
                    <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
                        Terjadi kesalahan pada server. Harap coba lagi.
                    </div>
                `);
                btn.prop('disabled', false).text('Simpan Password Baru');
            }
        });
    });
});
