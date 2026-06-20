$(document).ready(function() {
    // Show/Hide Password Toggle
    $('.toggle-password').on('click', function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const showIcon = $(this).find('.eye-icon-show');
        const hideIcon = $(this).find('.eye-icon-hide');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            showIcon.addClass('hidden');
            hideIcon.removeClass('hidden');
        } else {
            input.attr('type', 'password');
            showIcon.removeClass('hidden');
            hideIcon.addClass('hidden');
        }
    });

    // Real-time password strength indicator and validation
    $('#password, #password_confirmation').on('input', function() {
        const pw = $('#password').val();
        const pwConf = $('#password_confirmation').val();
        
        // update rules
        const hasLength = pw.length >= 8;
        const hasUpperAndLower = /[A-Z]/.test(pw) && /[a-z]/.test(pw);
        const hasNumber = /[0-9]/.test(pw);
        const hasSpecial = /[^a-zA-Z0-9]/.test(pw);

        updateRule('#req-length', hasLength);
        updateRule('#req-upper', hasUpperAndLower);
        updateRule('#req-number', hasNumber);
        updateRule('#req-special', hasSpecial);

        // update strength indicator
        let strength = 0;
        if (hasLength) strength++;
        if (hasUpperAndLower) strength++;
        if (hasNumber) strength++;
        if (hasSpecial) strength++;

        if (pw.length > 0) {
            $('#password-strength-container').show();
            updateStrengthBars(strength);
        } else {
            $('#password-strength-container').hide();
        }

        // enable/disable submit button
        if (strength === 4 && pw === pwConf) {
            $('#btn-register').prop('disabled', false);
        } else {
            $('#btn-register').prop('disabled', true);
        }
    });

    // Make sure button starts disabled
    $('#btn-register').prop('disabled', true);

    function updateRule(selector, passed) {
        const li = $(selector);
        const svg = li.find('svg');
        if (passed) {
            li.removeClass('text-slate-600 dark:text-slate-400').addClass('text-emerald-600 dark:text-emerald-400 font-medium');
            svg.removeClass('text-slate-300 dark:text-slate-600').addClass('text-emerald-500');
        } else {
            li.removeClass('text-emerald-600 dark:text-emerald-400 font-medium').addClass('text-slate-600 dark:text-slate-400');
            svg.removeClass('text-emerald-500').addClass('text-slate-300 dark:text-slate-600');
        }
    }

    function updateStrengthBars(strength) {
        const bars = [$('#strength-bar-1'), $('#strength-bar-2'), $('#strength-bar-3'), $('#strength-bar-4')];
        const text = $('#strength-text');

        // Reset classes
        bars.forEach(bar => bar.removeClass('bg-rose-500 bg-amber-500 bg-emerald-500 bg-indigo-500'));

        switch(strength) {
            case 1:
                bars[0].addClass('bg-rose-500');
                text.text('Sangat Lemah').removeClass('text-amber-500 text-emerald-500 text-indigo-500').addClass('text-rose-500');
                break;
            case 2:
                bars[0].addClass('bg-amber-500');
                bars[1].addClass('bg-amber-500');
                text.text('Lemah').removeClass('text-rose-500 text-emerald-500 text-indigo-500').addClass('text-amber-500');
                break;
            case 3:
                bars[0].addClass('bg-emerald-500');
                bars[1].addClass('bg-emerald-500');
                bars[2].addClass('bg-emerald-500');
                text.text('Cukup Kuat').removeClass('text-rose-500 text-amber-500 text-indigo-500').addClass('text-emerald-500');
                break;
            case 4:
                bars[0].addClass('bg-indigo-500');
                bars[1].addClass('bg-indigo-500');
                bars[2].addClass('bg-indigo-500');
                bars[3].addClass('bg-indigo-500');
                text.text('Sangat Kuat').removeClass('text-rose-500 text-amber-500 text-emerald-500').addClass('text-indigo-500');
                break;
            default:
                text.text('');
        }
    }

    // Handle Register AJAX
    $('#register-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#btn-register');

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
});
