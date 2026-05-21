const modal = document.getElementById('bannerModal');
    const modalTitle = document.getElementById('modalTitle');
    const bannerForm = document.getElementById('bannerForm');

    const bannerId = document.getElementById('bannerId');
    const bannerTitle = document.getElementById('bannerTitle');
    const bannerDesc = document.getElementById('bannerDesc');
    const bannerLink = document.getElementById('bannerLink');
    const bannerSort = document.getElementById('bannerSort');
    const bannerActive = document.getElementById('bannerActive');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const bannerImagePreview = document.getElementById('bannerImagePreview');
    const bannerImageFile = document.getElementById('bannerImageFile');

    function openAddBannerModal() {
        modalTitle.innerText = "Tambah Banner Baru";
        bannerForm.action = "index.php?page=admin_banner_process&action=add";

        bannerId.value = "";
        bannerTitle.value = "";
        bannerDesc.value = "";
        bannerLink.value = "";
        bannerSort.value = "0";
        bannerActive.value = "1";
        bannerImageFile.required = true;
        imagePreviewContainer.classList.add('hidden');
        bannerImagePreview.src = "";

        modal.classList.remove('hidden');
    }

    function openEditBannerModal(banner) {
        modalTitle.innerText = "Edit Banner";
        bannerForm.action = "index.php?page=admin_banner_process&action=edit";

        bannerId.value = banner.id;
        bannerTitle.value = banner.title || "";
        bannerDesc.value = banner.description || "";
        bannerLink.value = banner.link_url || "";
        bannerSort.value = banner.sort_order;
        bannerActive.value = banner.is_active;
        bannerImageFile.required = false;

        if (banner.image_url) {
            bannerImagePreview.src = banner.image_url;
            imagePreviewContainer.classList.remove('hidden');
        } else {
            imagePreviewContainer.classList.add('hidden');
        }

        modal.classList.remove('hidden');
    }

    function closeBannerModal() {
        modal.classList.add('hidden');
    }

    $(document).ready(function() {
        // Save (Add / Edit) Banner AJAX
        $('#bannerForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = new FormData(this);
            formData.append('ajax', 1);

            const btn = $('#btn-save-banner');
            btn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
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
                        text: 'Terjadi kesalahan sistem saat menyimpan banner.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.prop('disabled', false).text('Simpan');
                }
            });
        });

        // Delete Banner AJAX
        $('.btn-delete-banner').on('click', function() {
            const btn = $(this);
            const id = btn.data('id');

            Swal.fire({
                title: 'Hapus Banner Promo?',
                text: 'Apakah Anda yakin ingin menghapus banner ini? Tindakan ini tidak dapat dibatalkan.',
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
                        url: 'index.php?page=admin_banner_process&action=delete&id=' + id + '&ajax=1',
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
                                btn.closest('.banner-card').fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.banner-card').length === 0) {
                                        $('#banners-container').html(`
                                            <div class="col-span-full bg-white dark:bg-slate-800 p-8 rounded-2xl text-center text-slate-400 dark:text-slate-500 border border-slate-100 dark:border-slate-700">
                                                Belum ada banner promosi. Klik tombol "Tambah Banner" untuk memulai.
                                            </div>
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
                                text: 'Terjadi kesalahan sistem saat menghapus banner.',
                                icon: 'error',
                                confirmButtonColor: '#4f46e5'
                            });
                        }
                    });
                }
            });
        });
    });
