<?php
// Banners are fetched via controller
?>

<!-- Title Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 font-sans">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Kelola Banner Promo</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Atur slideshow banner promosi yang tampil di halaman utama toko Anda.</p>
    </div>
    <button
        onclick="openAddBannerModal()"
        class="inline-flex items-center space-x-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition shadow-lg shadow-indigo-600/25 active:scale-[0.98] cursor-pointer">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Tambah Banner</span>
    </button>
</div>

<!-- Banners Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 font-sans" id="banners-container">
    <?php if (empty($banners)): ?>
        <div class="col-span-full bg-white dark:bg-slate-800 p-8 rounded-2xl text-center text-slate-400 dark:text-slate-500 border border-slate-100 dark:border-slate-700">
            Belum ada banner promosi. Klik tombol "Tambah Banner" untuk memulai.
        </div>
    <?php else: ?>
        <?php foreach ($banners as $banner): ?>
            <div class="banner-card bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-700 shadow-sm flex flex-col justify-between transition hover:shadow-md">
                <div>
                    <!-- Banner Image Preview -->
                    <div class="h-44 w-full bg-slate-100 dark:bg-slate-900 relative overflow-hidden">
                        <img src="<?= htmlspecialchars($banner['image_url']) ?>" alt="Banner Image" class="w-full h-full object-cover">
                        <div class="absolute top-3 left-3 px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-900/80 backdrop-blur-md text-white font-mono">
                            Order: <?= $banner['sort_order'] ?>
                        </div>
                        <div class="absolute top-3 right-3">
                            <?php if ($banner['is_active']): ?>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500 text-white uppercase">Aktif</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-500 text-white uppercase">Nonaktif</span>
                            <?php class_exists('btn'); endif; ?>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white font-display line-clamp-1">
                            <?= htmlspecialchars($banner['title'] ?? 'Tanpa Judul') ?>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 line-clamp-2">
                            <?= htmlspecialchars($banner['description'] ?? 'Tidak ada deskripsi.') ?>
                        </p>
                        <?php if ($banner['link_url']): ?>
                            <div class="mt-3 flex items-center space-x-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                <span class="truncate font-mono"><?= htmlspecialchars($banner['link_url']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-700/50 flex justify-end space-x-3">
                    <button
                        onclick="openEditBannerModal(<?= htmlspecialchars(json_encode($banner)) ?>)"
                        class="px-3.5 py-1.5 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-655 text-xs font-bold rounded-lg transition">
                        Edit
                    </button>
                    <button
                        data-id="<?= $banner['id'] ?>"
                        class="btn-delete-banner px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition">
                        Hapus
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal CRUD Banner -->
<div id="bannerModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100 dark:border-slate-800">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white font-display" id="modalTitle">Tambah Banner Baru</h3>
                <button onclick="closeBannerModal()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form id="bannerForm" action="index.php?page=admin_banner_process&action=add" method="POST" enctype="multipart/form-data">

                <?= csrf_field() ?>
                <input type="hidden" name="id" id="bannerId">
                <div class="p-6 space-y-4 font-sans">
                    <!-- Title -->
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Judul Banner</label>
                        <input type="text" name="title" id="bannerTitle" required placeholder="Contoh: Diskon Gajian 50%"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Deskripsi Singkat</label>
                        <input type="text" name="description" id="bannerDesc" placeholder="Contoh: Dapatkan ekstra promo cashback khusus minggu ini."
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                    </div>

                    <!-- Link URL -->
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Link Aksi (Tautan)</label>
                        <input type="text" name="link_url" id="bannerLink" placeholder="Contoh: #products atau index.php?page=home"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm font-mono">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Sort Order -->
                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Urutan Tampil (Urutan)</label>
                            <input type="number" name="sort_order" id="bannerSort" required min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>

                        <!-- Status Aktif -->
                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Status</label>
                            <select name="is_active" id="bannerActive" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                                <option value="1">Aktif (Tampilkan)</option>
                                <option value="0">Nonaktif (Sembunyikan)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Image File Input -->
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Gambar Banner (Rekomendasi 1200x400)</label>
                        <div id="imagePreviewContainer" class="mb-2.5 hidden">
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mb-1.5 font-bold uppercase tracking-wider">Gambar Saat Ini:</p>
                            <img src="" id="bannerImagePreview" class="h-24 w-full object-cover rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        </div>
                        <input type="file" name="image" accept="image/png, image/jpeg" id="bannerImageFile"
                            class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 dark:file:bg-indigo-950/40 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 file:cursor-pointer"/>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5">Mendukung format JPG dan PNG. Maks. 3MB.</p>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end space-x-3 bg-slate-50 dark:bg-slate-800/50">
                    <button type="button" onclick="closeBannerModal()" class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 text-sm transition">Batal</button>
                    <button type="submit" id="btn-save-banner" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-md shadow-indigo-600/10 transition active:scale-[0.98]">Simpan</button>
                </div>
            </form>
        </div>
    </div>
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
<script src="assets/js/pages/admin-banners.js"></script>
