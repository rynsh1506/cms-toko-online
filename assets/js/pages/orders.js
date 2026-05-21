$(document).ready(function() {
            // Cancel order handler
            $('.btn-cancel-order').on('click', function() {
                const orderId = $(this).data('order-id');
                const btn = $(this);
                const row = btn.closest('tr');

                Swal.fire({
                    title: 'Batalkan Pesanan?',
                    text: 'Apakah Anda yakin ingin membatalkan pesanan ini? Tindakan ini akan mengembalikan stok produk.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Kembali',
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#1f2937'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'index.php?page=order_cancel',
                            type: 'POST',
                            data: { order_id: orderId },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonColor: (window.NusaBayOrders || {}).primaryColor || '#6366f1',
                                        background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                        color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#1f2937'
                                    }).then(() => {
                                        // Update status badge
                                        row.find('td:nth-child(4)').html('<span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400">Dibatalkan</span>');
                                        // Remove cancel button
                                        btn.remove();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonColor: (window.NusaBayOrders || {}).primaryColor || '#6366f1',
                                        background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                        color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#1f2937'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan sistem saat memproses pembatalan.',
                                    icon: 'error',
                                    confirmButtonColor: (window.NusaBayOrders || {}).primaryColor || '#6366f1',
                                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                    color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#1f2937'
                                });
                            }
                        });
                    }
                });
            });

            // Theme toggle elements
            const themeToggleBtn = document.getElementById('theme-toggle');
            const themeToggleSun = document.getElementById('theme-toggle-sun');
            const themeToggleMoon = document.getElementById('theme-toggle-moon');

            // Set initial toggle icons
            if (document.documentElement.classList.contains('dark')) {
                themeToggleSun.classList.remove('hidden');
            } else {
                themeToggleMoon.classList.remove('hidden');
            }

            // Theme toggle click handler
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
        });
