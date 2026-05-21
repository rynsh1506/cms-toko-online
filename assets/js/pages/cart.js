$(document).ready(function() {
            // Theme toggle
            const themeToggleBtn = document.getElementById('theme-toggle');
            const themeToggleSun = document.getElementById('theme-toggle-sun');
            const themeToggleMoon = document.getElementById('theme-toggle-moon');

            if (document.documentElement.classList.contains('dark')) {
                themeToggleSun.classList.remove('hidden');
            } else {
                themeToggleMoon.classList.remove('hidden');
            }

            themeToggleBtn.addEventListener('click', function() {
                document.documentElement.classList.toggle('dark');
                if (document.documentElement.classList.contains('dark')) {
                    localStorage.setItem('theme', 'dark');
                    themeToggleMoon.classList.add('hidden');
                    themeToggleSun.classList.remove('hidden');
                } else {
                    localStorage.setItem('theme', 'light');
                    themeToggleSun.classList.add('hidden');
                    themeToggleMoon.classList.remove('hidden');
                }
            });

            function formatRupiah(num) {
                return 'Rp ' + Number(num).toLocaleString('id-ID');
            }

            // Fungsi Utama Menghitung Sisi Samping Sesuai Pilihan Checkbox
            function calculateSelectedSummary() {
                let totalPrice = 0;
                let totalCount = 0;

                $('.item-checkbox:checked').each(function() {
                    const row = $(this).closest('.cart-item-row');
                    const price = parseFloat($(this).data('price'));
                    const qty = parseInt(row.find('.input-qty').val()) || 1;

                    totalPrice += price * qty;
                    totalCount += qty;
                });

                $('#summary-total, #grand-total').text(formatRupiah(totalPrice));
                $('#summary-count, #btn-checkout-count').text(totalCount);

                if (totalCount === 0) {
                    $('#btn-checkout-action').addClass('opacity-50 pointer-events-none').prop('disabled', true);
                } else {
                    $('#btn-checkout-action').removeClass('opacity-50 pointer-events-none').prop('disabled', false);
                }
            }

            // Jalankan perhitungan pertama kali load
            calculateSelectedSummary();

            // Event Checkbox Item diubah
            $(document).on('change', '.item-checkbox', function() {
                const totalItems = $('.item-checkbox').length;
                const totalChecked = $('.item-checkbox:checked').length;

                $('#select-all-checkbox').prop('checked', totalItems === totalChecked);
                calculateSelectedSummary();
            });

            // Event Pilih Semua
            $('#select-all-checkbox').on('change', function() {
                $('.item-checkbox').prop('checked', this.checked);
                calculateSelectedSummary();
            });

            var _qtyTimers = {};
            var _qtyXhr = {};

            function sendQtyUpdate(cartKey, qty, inputEl) {
                if (_qtyXhr[cartKey]) _qtyXhr[cartKey].abort();
                _qtyXhr[cartKey] = $.ajax({
                    url: 'index.php?page=cart_process&action=update',
                    type: 'POST',
                    data: { cart_key: cartKey, qty: qty, ajax: 1 },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            inputEl.val(response.qty);
                            calculateSelectedSummary(); // Update total tagihan samping secara live

                            const badge = $('#cart-badge');
                            if (response.cart_count > 0) {
                                badge.text(response.cart_count).removeClass('hidden');
                            } else {
                                badge.addClass('hidden');
                            }

                            if (response.error_message) {
                                Swal.fire({ title: 'Perhatian!', text: response.error_message, icon: 'warning', confirmButtonColor: (window.NusaBayCart || {}).primaryColor || '#6366f1' });
                            }
                        } else if (response.status === 'removed') {
                            inputEl.closest('.cart-item-row').fadeOut(300, function() { $(this).remove(); checkEmptyCart(); });
                        }
                    },
                    complete: function() { delete _qtyXhr[cartKey]; }
                });
            }

            function debouncedQtyUpdate(cartKey, qty, inputEl) {
                clearTimeout(_qtyTimers[cartKey]);
                _qtyTimers[cartKey] = setTimeout(function() {
                    sendQtyUpdate(cartKey, qty, inputEl);
                }, 300);
            }

            $(document).on('click', '.btn-qty-minus', function() {
                const input = $(this).siblings('.input-qty');
                let val = parseInt(input.val()) || 1;
                const cartKey = input.data('cart-key');
                if (val > 1) {
                    input.val(val - 1);
                    debouncedQtyUpdate(cartKey, val - 1, input);
                }
            });

            $(document).on('click', '.btn-qty-plus', function() {
                const input = $(this).siblings('.input-qty');
                let val = parseInt(input.val()) || 1;
                let max = parseInt(input.data('max')) || 999;
                const cartKey = input.data('cart-key');
                if (val < max) {
                    input.val(val + 1);
                    debouncedQtyUpdate(cartKey, val + 1, input);
                }
            });

            // Klik Tombol Checkout "Beli"
            $('#btn-checkout-action').on('click', function(e) {
                e.preventDefault();
                let selectedKeys = [];

                $('.item-checkbox:checked').each(function() {
                    selectedKeys.push($(this).data('cart-key'));
                });

                if (selectedKeys.length === 0) return;

                $.ajax({
                    url: 'index.php?page=cart_process&action=select_checkout',
                    type: 'POST',
                    data: { keys: selectedKeys, ajax: 1 },
                    dataType: 'json',
                    success: function(res) {
                        window.location.href = 'index.php?page=checkout';
                    }
                });
            });

            $(document).on('click', '.btn-remove-item', function() {
                const btn = $(this);
                const cartKey = btn.data('cart-key');
                $.ajax({
                    url: 'index.php?page=cart_process&action=remove&ajax=1',
                    type: 'POST',
                    data: { cart_key: cartKey, ajax: 1 },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            btn.closest('.cart-item-row').fadeOut(300, function() {
                                $(this).remove();

                                const badge = $('#cart-badge');
                                if (response.cart_count > 0) {
                                    badge.text(response.cart_count).removeClass('hidden');
                                } else {
                                    badge.addClass('hidden');
                                }

                                checkEmptyCart();
                            });
                        }
                    }
                });
            });

            $('#btn-clear-cart').on('click', function() {
                Swal.fire({
                    title: 'Kosongkan Keranjang?',
                    text: 'Semua produk pilihanmu akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Kosongkan!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'index.php?page=cart_process&action=clear&ajax=1',
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    $('#cart-container').remove();
                                    $('#cart-badge').text(0).addClass('hidden');
                                    checkEmptyCart();
                                }
                            }
                        });
                    }
                });
            });

            function checkEmptyCart() {
                if ($('.cart-item-row').length === 0) {
                    $('#cart-container').remove();
                    $('#cart-empty-placeholder').removeClass('hidden').fadeIn(300);
                } else {
                    calculateSelectedSummary();
                }
            }
        });
