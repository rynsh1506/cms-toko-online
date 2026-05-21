// Live Image Preview for Profile Upload
    document.getElementById('avatar-input').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('avatar-preview-img');
                const placeholder = document.getElementById('avatar-placeholder');

                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');

                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            }
            reader.readAsDataURL(file);
        }
    });

    $(document).ready(function() {
        // AJAX Update Profile Info
        $('#profile-info-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = new FormData(this);
            formData.append('ajax', 1);

            const btn = $('#btn-save-profile');
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
                        btn.prop('disabled', false).text('Simpan Perubahan');
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem saat memperbarui profil.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.prop('disabled', false).text('Simpan Perubahan');
                }
            });
        });

        // AJAX Update Password
        $('#profile-password-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = $('#btn-save-password');
            btn.prop('disabled', true).text('Mengubah...');

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
                            form[0].reset();
                            btn.prop('disabled', false).text('Ubah Password');
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonColor: '#4f46e5'
                        });
                        btn.prop('disabled', false).text('Ubah Password');
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem saat mengubah password.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.prop('disabled', false).text('Ubah Password');
                }
            });
        });
    });
