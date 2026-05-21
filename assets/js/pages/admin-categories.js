$(document).ready(function() {
        // Toggle to Edit mode
        $(document).on('click', '.btn-edit-category', function() {
            const btn = $(this);
            const id = btn.data('id');
            const name = btn.data('name');
            const icon = btn.data('icon');
            const color = btn.data('color');

            $('#form-title').text('Edit Kategori');
            $('#category-id').val(id);
            $('#category-name').val(name);
            $('#category-icon').val(icon);
            $('#category-color').val(color);

            $('#category-form').attr('action', 'index.php?page=admin_category_process&action=edit');
            $('#btn-save-category').text('Perbarui Kategori');
            $('#btn-cancel-edit').removeClass('hidden');
        });

        // Reset to Add mode
        function resetForm() {
            $('#form-title').text('Tambah Kategori');
            $('#category-id').val('');
            $('#category-name').val('');
            $('#category-icon').val('');
            $('#category-color').val('');

            $('#category-form').attr('action', 'index.php?page=admin_category_process&action=add');
            $('#btn-save-category').text('Simpan Kategori');
            $('#btn-cancel-edit').addClass('hidden');
        }

        $('#btn-cancel-edit').on('click', resetForm);

        // AJAX Add / Edit Category
        $('#category-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = $('#btn-save-category');
            const originalBtnText = btn.text();
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
                        btn.prop('disabled', false).text(originalBtnText);
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem saat menyimpan kategori.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.prop('disabled', false).text(originalBtnText);
                }
            });
        });

        // AJAX Delete Category
        $(document).on('click', '.btn-delete-category', function() {
            const btn = $(this);
            const id = btn.data('id');

            Swal.fire({
                title: 'Hapus Kategori?',
                text: 'Menghapus kategori ini juga akan menyetel kategori produk yang berkaitan menjadi NULL/kosong. Yakin?',
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
                        url: 'index.php?page=admin_category_process&action=delete&id=' + id + '&ajax=1',
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
                                btn.closest('.category-row').fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.category-row').length === 0) {
                                        $('#category-table-body').html(`
                                            <tr id="empty-category-row">
                                                <td colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800">Belum ada kategori terdaftar. Tambahkan kategori di sebelah kiri.</td>
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
                                text: 'Terjadi kesalahan sistem saat menghapus kategori.',
                                icon: 'error',
                                confirmButtonColor: '#4f46e5'
                            });
                        }
                    });
                }
            });
        });
    });
