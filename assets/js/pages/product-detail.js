$(document).ready(function () {
        const pageConfig = window.NusaBayProductDetail || {};
        // Theme Toggle
        const themeBtn = document.getElementById('theme-toggle');
        const sun = document.getElementById('theme-toggle-sun');
        const moon = document.getElementById('theme-toggle-moon');
        if (document.documentElement.classList.contains('dark')) { sun.classList.remove('hidden'); } else { moon.classList.remove('hidden'); }
        themeBtn.addEventListener('click', function () {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light');
                sun.classList.add('hidden'); moon.classList.remove('hidden');
            } else {
                document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark');
                moon.classList.add('hidden'); sun.classList.remove('hidden');
            }
        });

        // Backend Constants
        const basePrice = Number(pageConfig.basePrice || 0);
        const hasVariants = Boolean(pageConfig.hasVariants);

        // Variant Selection Logic (Hanya bisa pilih SATU Varian yang mengontrol Stok)
        $(document).on('click', '.variant-btn', function () {
            if ($(this).is(':disabled')) return;

            const variantId = $(this).data('variant-id');

            // Toggle unselect logic: if clicking already selected non-normal variant, revert to Normal
            if ($(this).hasClass('selected') && variantId !== 0) {
                $('#btn-variant-normal').click();
                return;
            }

            const variantName = $(this).data('variant-name');
            const variantValue = $(this).data('variant-value');
            const addPrice = parseFloat($(this).data('additional-price')) || 0;
            const stock = parseInt($(this).data('stock')) || 0;

            // Hapus seleksi semua tombol lain
            $('.variant-btn').removeClass('selected').css({'background-color': '', 'color': '', 'border-color': ''});

            // Tandai yang diklik
            $(this).addClass('selected').css({'background-color': pageConfig.primaryColor || '#6366f1', 'color': 'white', 'border-color': 'transparent'});

            // Set Form Inputs
            $('#selected-variant-id').val(variantId);
            if (variantId === 0) {
                $('#selected-variant-info').val('');
            } else {
                $('#selected-variant-info').val(variantName + ': ' + variantValue);
            }

            // Update Harga
            const finalPrice = basePrice + addPrice;
            $('#display-price').text('Rp ' + finalPrice.toLocaleString('id-ID'));
            if (addPrice > 0) { $('#price-note').removeClass('hidden'); } else { $('#price-note').addClass('hidden'); }

            // Update Stok Display & Kuantitas Input
            $('#qty-input').attr('max', stock);

            // Kalau input qty saat ini lebih besar dari stok varian, turunkan otomatis
            let currentQty = parseInt($('#qty-input').val()) || 1;
            if (currentQty > stock) {
                $('#qty-input').val(stock);
            }

            // Update Indikator Stok UI
            $('#stock-dot').removeClass('bg-emerald-500 bg-amber-500 bg-rose-500');
            $('#stock-text').removeClass('text-emerald-600 text-amber-600 text-rose-600 dark:text-emerald-400 dark:text-amber-400 dark:text-rose-400');

            if (stock > 5) {
                $('#stock-dot').addClass('bg-emerald-500');
                $('#stock-text').addClass('text-emerald-600 dark:text-emerald-400').text('Tersedia: ' + stock + ' pcs');
                enableButtons();
            } else if (stock > 0) {
                $('#stock-dot').addClass('bg-amber-500');
                $('#stock-text').addClass('text-amber-600 dark:text-amber-400').text('Sisa sedikit: ' + stock + ' pcs');
                enableButtons();
            } else {
                $('#stock-dot').addClass('bg-rose-500');
                $('#stock-text').addClass('text-rose-600 dark:text-rose-400').text('Stok varian habis');
                disableButtons('Stok Habis');
            }
        });

        function enableButtons() {
            $('#btn-add-cart, #btn-buy-now').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
            $('#cart-btn-text').text('+ Keranjang');
            $('#buy-now-btn-text').text('Beli');
        }

        function disableButtons(text) {
            $('#btn-add-cart, #btn-buy-now').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            $('#cart-btn-text').text(text);
            $('#buy-now-btn-text').text('Beli');
        }

        // Qty controls
        $('#qty-minus').on('click', function () {
            let val = parseInt($('#qty-input').val()) || 1;
            if (val > 1) { $('#qty-input').val(val - 1); }
        });

        $('#qty-plus').on('click', function () {
            let val = parseInt($('#qty-input').val()) || 1;
            const max = parseInt($('#qty-input').attr('max')) || 999;

            if (val < max) {
                $('#qty-input').val(val + 1);
            } else {
                showToast('Maksimal jumlah stok tercapai.', 'error');
            }
        });

        // Toast helper
        function showToast(message, type) {
            const bg = type === 'success' ? 'bg-emerald-600' : 'bg-rose-600';
            const toast = $(`<div class="flex items-center space-x-2.5 px-5 py-3 rounded-2xl text-white font-bold text-xs shadow-2xl transition-all duration-300 transform translate-y-4 opacity-0 ${bg}"><span>${message}</span></div>`);
            $('#toast-container').append(toast);
            setTimeout(() => toast.removeClass('translate-y-4 opacity-0'), 10);
            setTimeout(() => { toast.addClass('translate-y-4 opacity-0'); setTimeout(() => toast.remove(), 300); }, 3000);
        }

        // AJAX Add to Cart
        $('#add-to-cart-form').on('submit', function (e) {
            e.preventDefault();

            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).addClass('opacity-70');
            $('#cart-btn-text').text('Memproses...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize() + '&ajax=1',
                dataType: 'json',
                success: function (data) {
                    if (data.status === 'success') {
                        showToast(data.message, 'success');
                        const badge = $('#cart-badge');
                        badge.text(data.cart_count).removeClass('hidden');
                    } else {
                        showToast(data.message, 'error');
                    }
                },
                error: function () { showToast('Terjadi kesalahan koneksi. Coba lagi.', 'error'); },
                complete: function () {
                    btn.prop('disabled', false).removeClass('opacity-70');
                    $('#cart-btn-text').text('+ Keranjang');
                }
            });
        });

        // Expose buyNowBtnAction ke global window
        window.buyNowBtnAction = function() {
            const pId = Number(pageConfig.productId || 0);
            const vId = $('#selected-variant-id').val() || 0;
            const qty = $('#qty-input').val() || 1;
            const vInfo = $('#selected-variant-info').val() || '';

            $('#btn-buy-now').prop('disabled', true).addClass('opacity-70');
            $('#buy-now-btn-text').text('Memproses...');

            $.ajax({
                url: 'index.php?page=cart_process&action=direct_checkout',
                type: 'POST',
                data: {
                    product_id: pId,
                    variant_id: vId,
                    quantity: qty,
                    variant_info: vInfo,
                    ajax: 1
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        showToast(response.message || 'Gagal memproses checkout.', 'error');
                        $('#btn-buy-now').prop('disabled', false).removeClass('opacity-70');
                        $('#buy-now-btn-text').text('Beli');
                    }
                },
                error: function() {
                    showToast('Terjadi kesalahan sistem.', 'error');
                    $('#btn-buy-now').prop('disabled', false).removeClass('opacity-70');
                    $('#buy-now-btn-text').text('Beli');
                }
            });
        };

    });
