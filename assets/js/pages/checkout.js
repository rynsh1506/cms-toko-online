$(document).ready(function() {
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

            // Promo Code Apply Logic
            const checkoutConfig = window.NusaBayCheckout || {};
            let subtotalVal = Number(checkoutConfig.subtotal || 0);
            let appliedPromoId = null;

            $('#btn-apply-promo').on('click', function() {
                const code = $('#promo-input').val().trim();
                const statusMsg = $('#promo-status-msg');

                if (code === '') {
                    statusMsg.text('Masukkan kode promo terlebih dahulu.').removeClass().addClass('text-[10px] mt-1.5 font-semibold text-rose-500').show();
                    return;
                }

                statusMsg.text('Memeriksa...').removeClass().addClass('text-[10px] mt-1.5 font-semibold text-slate-400').show();

                $.ajax({
                    url: 'index.php?page=validate_promo',
                    type: 'POST',
                    data: { code: code, total_price: subtotalVal },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            appliedPromoId = response.promo_id;
                            $('#hidden_promo_id').value = response.promo_id;
                            document.getElementById('hidden_promo_id').value = response.promo_id;

                            statusMsg.text(response.message).removeClass().addClass('text-[10px] mt-1.5 font-semibold text-emerald-500').show();

                            // Update values
                            $('#promo-code-applied').text(response.code);
                            $('#promo-discount-value').text(new Intl.NumberFormat('id-ID').format(response.discount_amount));
                            $('#promo-discount-row').removeClass('hidden').addClass('flex');

                            const finalTotal = subtotalVal - response.discount_amount;
                            $('#summary-total').text('Rp ' + new Intl.NumberFormat('id-ID').format(finalTotal));
                        } else {
                            statusMsg.text(response.message).removeClass().addClass('text-[10px] mt-1.5 font-semibold text-rose-500').show();

                            // Reset values
                            $('#promo-discount-row').removeClass('flex').addClass('hidden');
                            $('#summary-total').text('Rp ' + new Intl.NumberFormat('id-ID').format(subtotalVal));
                            document.getElementById('hidden_promo_id').value = "";
                        }
                    },
                    error: function() {
                        statusMsg.text('Terjadi kesalahan koneksi sistem.').removeClass().addClass('text-[10px] mt-1.5 font-semibold text-rose-500').show();
                    }
                });
            });

            // AJAX Form Submit
            $('#checkout-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = $('#btn-submit-checkout');

                btn.prop('disabled', true).text('Memproses Pesanan...');
                $('#checkout-alert').empty();

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect_url;
                        } else {
                            $('#checkout-alert').html(`
                                <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-4 rounded-r-xl mb-6 text-xs font-semibold">
                                    ${response.message}
                                </div>
                            `);
                            btn.prop('disabled', false).text('Selesaikan Pesanan');
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    },
                    error: function() {
                        $('#checkout-alert').html(`
                            <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-4 rounded-r-xl mb-6 text-xs font-semibold">
                                Terjadi kesalahan sistem saat memproses checkout. Silakan coba kembali.
                            </div>
                        `);
                        btn.prop('disabled', false).text('Selesaikan Pesanan');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                });
            });
        });
