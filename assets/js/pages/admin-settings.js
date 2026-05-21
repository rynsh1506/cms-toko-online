$(document).ready(function() {
    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        formData.append('ajax', 1);

        const btn = $('#btn-save');
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
                    btn.prop('disabled', false).text('Simpan Pengaturan');
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan sistem saat menyimpan pengaturan.',
                    icon: 'error',
                    confirmButtonColor: '#4f46e5'
                });
                btn.prop('disabled', false).text('Simpan Pengaturan');
            }
        });
    });
});
