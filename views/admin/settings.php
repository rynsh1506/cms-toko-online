<?php
require_once __DIR__ . '/../../config/db.php';

// Fetch all landing configs
$stmt = $pdo->query("SELECT * FROM landing_configs");
$configs_raw = $stmt->fetchAll();
$configs = [];
foreach ($configs_raw as $c) {
    $configs[$c['section_key']] = $c;
}

// Helper to get value
function getConfigValue($key, $configs) {
    return isset($configs[$key]) ? htmlspecialchars($configs[$key]['content_value']) : '';
}
?>

<!-- Title Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 font-sans">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Pengaturan Toko</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola informasi publik toko, kontak, dan tautan sosial media.</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 font-sans">
    <form id="settingsForm" action="index.php?page=config_process" method="POST" enctype="multipart/form-data" class="space-y-8">

        <?= csrf_field() ?> 
        <!-- Hero Section -->
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-5 font-display">Tampilan Beranda (Hero)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1.5 text-sm">Judul Utama (Hero Title)</label>
                    <input type="text" name="config[hero_title]" value="<?= getConfigValue('hero_title', $configs) ?>" required
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1.5 text-sm">Warna Utama (Primary Color)</label>
                    <input type="color" name="config[primary_color]" value="<?= getConfigValue('primary_color', $configs) ?>" required
                        class="w-full h-11 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-2 py-1 rounded-xl focus:outline-none cursor-pointer transition">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1.5 text-sm">Deskripsi Singkat (Hero Subtitle)</label>
                    <textarea name="config[hero_subtitle]" rows="2" required
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm leading-relaxed"><?= getConfigValue('hero_subtitle', $configs) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-5 font-display">Informasi Footer</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1.5 text-sm">Tentang Toko (Footer Description)</label>
                    <textarea name="config[footer_description]" rows="2" required
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm leading-relaxed"><?= getConfigValue('footer_description', $configs) ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1.5 text-sm">Alamat Lengkap (Footer Address)</label>
                    <textarea name="config[footer_address]" rows="2" required
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm leading-relaxed"><?= getConfigValue('footer_address', $configs) ?></textarea>
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1.5 text-sm">Email Bantuan (Footer Email)</label>
                    <input type="email" name="config[footer_email]" value="<?= getConfigValue('footer_email', $configs) ?>" required
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                </div>
            </div>
        </div>

        <!-- Social Media Section -->
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-5 font-display">Tautan Sosial Media</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1.5 text-sm">WhatsApp (Link wa.me)</label>
                    <input type="url" name="config[social_whatsapp]" value="<?= getConfigValue('social_whatsapp', $configs) ?>"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition text-sm font-mono" placeholder="https://wa.me/628...">
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1.5 text-sm">Instagram (Link)</label>
                    <input type="url" name="config[social_instagram]" value="<?= getConfigValue('social_instagram', $configs) ?>"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 transition text-sm font-mono" placeholder="https://instagram.com/...">
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1.5 text-sm">Facebook (Link)</label>
                    <input type="url" name="config[social_facebook]" value="<?= getConfigValue('social_facebook', $configs) ?>"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm font-mono" placeholder="https://facebook.com/...">
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1.5 text-sm">Twitter / X (Link)</label>
                    <input type="url" name="config[social_twitter]" value="<?= getConfigValue('social_twitter', $configs) ?>"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition text-sm font-mono" placeholder="https://twitter.com/...">
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
            <button type="submit" id="btn-save"
                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition active:scale-[0.98] cursor-pointer">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<script src="assets/js/jquery.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
<script src="assets/js/sweetalert2.all.min.js"></script>
<script src="assets/js/pages/admin-settings.js"></script>
