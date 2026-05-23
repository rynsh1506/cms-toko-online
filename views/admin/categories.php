<?php
// Categories are fetched via controller
?>

<!-- Title -->
<div class="mb-8 font-sans">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Kelola Kategori Produk</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola kategori untuk mengelompokkan produk-produk toko online Anda.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 font-sans">
    <!-- Form Tambah / Edit Kategori (Left Column) -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 h-fit transition-colors duration-300">
        <h3 id="form-title" class="text-lg font-bold mb-4 text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-2 font-display">Tambah Kategori</h3>
        <form id="category-form" action="index.php?page=admin_category_process&action=add" method="POST" class="space-y-4">

            <?= csrf_field() ?>
            <input type="hidden" name="id" id="category-id" value="">

            <div>
                <label class="block text-slate-700 dark:text-slate-300 text-xs font-bold mb-1.5">Nama Kategori</label>
                <input type="text" name="name" id="category-name" required placeholder="misal: Elektronik, Pakaian"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
            </div>

            <div>
                <label class="block text-slate-700 dark:text-slate-300 text-xs font-bold mb-1.5">Icon (SVG path / emoji / CSS class)</label>
                <input type="text" name="icon" id="category-icon" placeholder="misal: ⚡, shirt, atau bi-laptop"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
            </div>

            <div>
                <label class="block text-slate-700 dark:text-slate-300 text-xs font-bold mb-1.5">Warna (Hex atau class Tailwind)</label>
                <input type="text" name="color" id="category-color" placeholder="misal: #6366f1 atau indigo"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
            </div>

            <div class="flex space-x-2">
                <button type="submit" id="btn-save-category" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl text-sm shadow-md shadow-indigo-600/10 transition active:scale-[0.98]">
                    Simpan Kategori
                </button>
                <button type="button" id="btn-cancel-edit" class="hidden bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold py-3 px-4 rounded-xl text-sm transition">
                    Batal
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Kategori (Right Column) -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display font-semibold">Daftar Kategori</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">
                        <th class="p-4 pl-6 w-16 text-center">Icon</th>
                        <th class="p-4">Nama Kategori</th>
                        <th class="p-4">Slug</th>
                        <th class="p-4">Warna</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="category-table-body" class="divide-y divide-slate-50 dark:divide-slate-700 text-sm">
                    <?php if (empty($categories)): ?>
                        <tr id="empty-category-row">
                            <td colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800">Belum ada kategori terdaftar. Tambahkan kategori di sebelah kiri.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition category-row bg-white dark:bg-slate-800" data-id="<?= $cat['id'] ?>">
                                <td class="p-4 pl-6 text-center">
                                    <span class="text-xl"><?= htmlspecialchars($cat['icon'] ?: '📁') ?></span>
                                </td>
                                <td class="p-4 font-bold text-slate-800 dark:text-slate-200 category-name-text">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </td>
                                <td class="p-4 font-semibold text-slate-500 dark:text-slate-400 font-mono text-xs">
                                    <?= htmlspecialchars($cat['slug']) ?>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-3.5 h-3.5 rounded-full inline-block border border-slate-200 dark:border-slate-600" style="background-color: <?= htmlspecialchars($cat['color'] ?: '#6366f1') ?>"></span>
                                        <span class="text-xs text-slate-500 dark:text-slate-450"><?= htmlspecialchars($cat['color'] ?: 'Default') ?></span>
                                    </div>
                                </td>
                                <td class="p-4 text-center space-x-1">
                                    <button type="button"
                                            data-id="<?= $cat['id'] ?>"
                                            data-name="<?= htmlspecialchars($cat['name']) ?>"
                                            data-icon="<?= htmlspecialchars($cat['icon'] ?? '') ?>"
                                            data-color="<?= htmlspecialchars($cat['color'] ?? '') ?>"
                                       class="btn-edit-category inline-block p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition"
                                       title="Edit">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button type="button" data-id="<?= $cat['id'] ?>"
                                       class="btn-delete-category inline-block p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition"
                                       title="Hapus">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
<script src="assets/js/pages/admin-categories.js"></script>
