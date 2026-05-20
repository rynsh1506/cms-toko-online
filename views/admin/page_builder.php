<?php
// Pastikan ini dijalankan lewat router (index.php) dan sudah dicek checkAdmin()
require_once __DIR__ . '/../../config/db.php';

// Ambil konfigurasi saat ini
$stmt = $pdo->query("SELECT section_key, content_value, type FROM landing_configs");
$configs_raw = $stmt->fetchAll();
$configs = [];
foreach ($configs_raw as $c) {
    $configs[$c['section_key']] = $c;
}
?>

<!-- Title section -->
<div class="mb-8 font-sans">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Dynamic Page Builder</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kustomisasi konten halaman depan toko online Anda secara instan tanpa menulis kode.</p>
</div>

<div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 max-w-3xl font-sans transition-colors duration-300">
    <!-- Penting: enctype="multipart/form-data" untuk upload file -->
    <form id="config-form" action="index.php?page=config_process" method="POST" enctype="multipart/form-data">
        
        <h3 class="text-lg font-bold mb-6 text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3 font-display">Pengaturan Hero Section</h3>
        
        <!-- Hero Title -->
        <div class="mb-5">
            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-2 text-sm">Judul Utama (Hero Title)</label>
            <input type="text" name="config[hero_title]" 
                value="<?= htmlspecialchars($configs['hero_title']['content_value'] ?? '') ?>" 
                required
                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
        </div>
        
        <!-- Hero Subtitle -->
        <div class="mb-5">
            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-2 text-sm">Sub-judul (Hero Subtitle)</label>
            <textarea name="config[hero_subtitle]" rows="3" required
                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm"><?= htmlspecialchars($configs['hero_subtitle']['content_value'] ?? '') ?></textarea>
        </div>

        <!-- Primary Color -->
        <div class="mb-6">
            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-2 text-sm">Warna Utama (Primary Color)</label>
            <div class="flex items-center space-x-4 p-4 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-150 dark:border-slate-850">
                <input type="color" name="config[primary_color]" 
                    value="<?= htmlspecialchars($configs['primary_color']['content_value'] ?? '#2563eb') ?>" 
                    class="h-10 w-16 p-1 rounded-lg border border-slate-200 dark:border-slate-800 cursor-pointer bg-white dark:bg-slate-900">
                <span class="text-slate-500 dark:text-slate-400 text-xs leading-relaxed">Warna utama ini akan diaplikasikan langsung pada tombol utama, ikon, tautan, dan teks aksen dekoratif di halaman depan.</span>
            </div>
        </div>

        <!-- Hero Image Upload -->
        <div class="mb-8">
            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-2 text-sm">Gambar Banner (Hero Image)</label>
            <div id="hero-img-preview-box" class="mb-4 p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-150 dark:border-slate-850 inline-block <?= empty($configs['hero_image']['content_value']) ? 'hidden' : '' ?>">
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mb-2 font-semibold uppercase tracking-wider">Gambar saat ini:</p>
                <img id="hero-image-preview" src="<?= !empty($configs['hero_image']['content_value']) ? 'uploads/' . htmlspecialchars($configs['hero_image']['content_value']) : '' ?>" alt="Current Hero" class="h-32 object-cover rounded-lg shadow-sm border border-slate-200 dark:border-slate-800">
            </div>
            
            <input type="file" name="hero_image" id="hero-image-file" accept="image/png, image/jpeg" 
                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 dark:file:bg-indigo-950/40 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 file:cursor-pointer"/>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-2.5">Mendukung format JPG dan PNG. Biarkan kosong jika tidak ingin mengganti gambar.</p>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
            <button type="submit" id="btn-save-configs" class="bg-indigo-650 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl transition duration-150 text-sm shadow-md shadow-indigo-650/10 active:scale-[0.98]">
                Simpan Konfigurasi
            </button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Image preview before upload
        $('#hero-image-file').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#hero-image-preview').attr('src', e.target.result);
                    $('#hero-img-preview-box').removeClass('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        // AJAX Form Submission
        $('#config-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = new FormData(this);
            formData.append('ajax', 1);
            
            const btn = $('#btn-save-configs');
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
                        btn.prop('disabled', false).text('Simpan Konfigurasi');
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem saat menyimpan pengaturan.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.prop('disabled', false).text('Simpan Konfigurasi');
                }
            });
        });
    });
</script>
