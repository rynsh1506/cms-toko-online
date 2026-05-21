const modal = document.getElementById('promoModal');
    const modalTitle = document.getElementById('modalTitle');
    const promoForm = document.getElementById('promoForm');

    const promoId = document.getElementById('promoId');
    const promoCode = document.getElementById('promoCode');
    const promoType = document.getElementById('promoType');
    const promoValue = document.getElementById('promoValue');
    const promoMinOrder = document.getElementById('promoMinOrder');
    const promoMaxUses = document.getElementById('promoMaxUses');
    const promoExpiresAt = document.getElementById('promoExpiresAt');
    const promoActive = document.getElementById('promoActive');

    function openAddPromoModal() {
        modalTitle.innerText = "Tambah Kode Promo";
        promoForm.action = "index.php?page=admin_promo_process&action=add";

        promoId.value = "";
        promoCode.value = "";
        promoType.value = "percentage";
        promoValue.value = "";
        promoMinOrder.value = "0";
        promoMaxUses.value = "100";

        // Default expiry in 30 days
        const date = new Date();
        date.setDate(date.getDate() + 30);
        promoExpiresAt.value = date.toISOString().slice(0, 16);
        promoActive.value = "1";

        modal.classList.remove('hidden');
    }

    function openEditPromoModal(promo) {
        modalTitle.innerText = "Edit Kode Promo";
        promoForm.action = "index.php?page=admin_promo_process&action=edit";

        promoId.value = promo.id;
        promoCode.value = promo.code;
        promoType.value = promo.discount_type;
        promoValue.value = Math.floor(promo.discount_value);
        promoMinOrder.value = Math.floor(promo.min_order);
        promoMaxUses.value = promo.max_uses;

        // Convert SQL datetime to local datetime-local format
        const localDate = promo.expires_at.replace(" ", "T").substring(0, 16);
        promoExpiresAt.value = localDate;
        promoActive.value = promo.is_active;

        modal.classList.remove('hidden');
    }

    function closePromoModal() {
        modal.classList.add('hidden');
    }

    $(document).ready(function() {
        // Save (Add / Edit) Promo AJAX
        $('#promoForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const data = form.serialize() + '&ajax=1';

            const btn = $('#btn-save-promo');
            btn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: data,
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
                        btn.prop('disabled', false).text('Simpan');
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem saat menyimpan kode promo.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.prop('disabled', false).text('Simpan');
                }
            });
        });

        // Delete Promo AJAX
        $('.btn-delete-promo').on('click', function() {
            const btn = $(this);
            const id = btn.data('id');

            Swal.fire({
                title: 'Hapus Kode Promo?',
                text: 'Apakah Anda yakin ingin menghapus kode promo ini? Tindakan ini permanen.',
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
                        url: 'index.php?page=admin_promo_process&action=delete&id=' + id + '&ajax=1',
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
                                btn.closest('.promo-row').fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.promo-row').length === 0) {
                                        $('#promo-table-body').html(`
                                            <tr id="empty-row">
                                                <td colspan="8" class="p-8 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800">Belum ada kode promo yang didaftarkan.</td>
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
                                text: 'Terjadi kesalahan sistem saat menghapus kode promo.',
                                icon: 'error',
                                confirmButtonColor: '#4f46e5'
                            });
                        }
                    });
                }
            });
        });
    });
