$(document).ready(function() {
        // AJAX Add Bank Account
        $('#add-bank-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = $('#btn-save-bank');
            btn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize() + '&ajax=1',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Sukses!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#4f46e5'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonColor: '#4f46e5'
                        });
                        btn.prop('disabled', false).text('Simpan Rekening');
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem saat menyimpan rekening.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.prop('disabled', false).text('Simpan Rekening');
                }
            });
        });

        // AJAX Toggle Active Status
        $(document).on('click', '.btn-toggle-status', function() {
            const btn = $(this);
            const id = btn.data('id');
            const badge = btn.find('.badge-status');

            $.ajax({
                url: 'index.php?page=admin_bank_process&action=toggle&id=' + id + '&ajax=1',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.is_active) {
                            badge.removeClass('bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400')
                                 .addClass('bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400')
                                 .text('Aktif');
                        } else {
                            badge.removeClass('bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400')
                                 .addClass('bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400')
                                 .text('Nonaktif');
                        }

                        // SweetAlert toast notification
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                    }
                }
            });
        });

        // AJAX Delete Bank Account
        $(document).on('click', '.btn-delete-bank', function() {
            const btn = $(this);
            const id = btn.data('id');

            Swal.fire({
                title: 'Hapus Rekening?',
                text: 'Apakah Anda yakin ingin menghapus rekening bank ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#1f2937'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'index.php?page=admin_bank_process&action=delete&id=' + id + '&ajax=1',
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonColor: '#4f46e5'
                                });
                                btn.closest('.bank-row').fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.bank-row').length === 0) {
                                        $('#bank-table-body').html(`
                                            <tr id="empty-bank-row">
                                                <td colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800">Belum ada rekening terdaftar. Tambahkan rekening bank di sebelah kiri.</td>
                                            </tr>
                                        `);
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonColor: '#4f46e5'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Terjadi kesalahan sistem saat menghapus rekening.',
                                icon: 'error',
                                confirmButtonColor: '#4f46e5'
                            });
                        }
                    });
                }
            });
        });
    });
