<?php
// Categories are fetched via controller and products will be fetched via AJAX
?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 font-sans">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Kelola Produk</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tambah, edit, atau hapus katalog produk yang tampil di toko online Anda.</p>
    </div>
    <button onclick="openAddModal()" class="flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold shadow-md shadow-indigo-600/10 transition active:scale-[0.98]">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Tambah Produk</span>
    </button>
</div>

<!-- FILTER PANEL -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 mb-6 font-sans transition-colors duration-300">
    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
        <div class="lg:col-span-2">
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Cari Produk</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" id="filter-search" name="search" placeholder="Nama atau deskripsi produk..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Kategori</label>
            <select id="filter-category" name="category" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                <option value="all">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Harga Min (Rp)</label>
            <input type="number" id="filter-min-price" name="min_price" placeholder="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Harga Max (Rp)</label>
            <input type="number" id="filter-max-price" name="max_price" placeholder="~" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
        </div>
        <button type="submit" class="hidden"></button>
    </form>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden font-sans transition-colors duration-300">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">
                    <th class="p-4 pl-6 w-20">Gambar</th>
                    <th class="p-4">Nama Produk</th>
                    <th class="p-4">Kategori</th>
                    <th class="p-4">Harga</th>
                    <th class="p-4">Stok</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="product-table-body" class="divide-y divide-slate-50 dark:divide-slate-700 text-sm">
                <!-- Skeleton loading or dynamic content will be rendered here by JS -->
                <tr>
                    <td colspan="6" class="p-8 text-center text-slate-400 dark:text-slate-500">
                        <div class="flex justify-center items-center space-x-2">
                            <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Memuat produk...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Pagination Controls -->
    <div class="p-4 border-t border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between text-sm">
        <div class="text-slate-500 dark:text-slate-400 mb-4 sm:mb-0">
            Menampilkan <span id="meta-start" class="font-bold text-slate-900 dark:text-white">0</span> - <span id="meta-end" class="font-bold text-slate-900 dark:text-white">0</span> dari <span id="meta-total" class="font-bold text-slate-900 dark:text-white">0</span> produk
        </div>
        <div id="pagination-buttons" class="flex space-x-1">
            <!-- Buttons rendered via JS -->
        </div>
    </div>
</div>

<div id="productModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100 dark:border-slate-800">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white font-display" id="modalTitle">Tambah Produk Baru</h3>
                <button onclick="closeModal()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="productForm" action="index.php?page=admin_product_process&action=add" method="POST" enctype="multipart/form-data">

                <?= csrf_field() ?>
                <input type="hidden" name="id" id="prodId">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Nama Produk</label>
                        <input type="text" name="name" id="prodName" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                    </div>

                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Deskripsi</label>
                        <textarea name="description" id="prodDesc" rows="3" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Kategori</label>
                        <select name="category_id" id="prodCategoryId" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Harga (Rp)</label>
                            <input type="number" name="price" id="prodPrice" required min="0"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>

                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Stok</label>
                            <input type="number" name="stock" id="prodStock" required min="0"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Gambar Produk</label>
                        <div id="imagePreviewContainer" class="mb-2.5 hidden">
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mb-1.5 font-bold uppercase tracking-wider">Gambar Saat Ini:</p>
                            <img src="" id="prodImagePreview" class="h-24 object-cover rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        </div>
                        <input type="file" name="image" accept="image/png, image/jpeg" id="prodImageFile"
                            class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 dark:file:bg-indigo-950/40 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 file:cursor-pointer"/>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5">Mendukung format JPG dan PNG. Maks. 2MB.</p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end space-x-3 bg-slate-50 dark:bg-slate-800/50">
                    <button type="button" onclick="closeModal()" class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 text-sm transition">Batal</button>
                    <button type="submit" id="btn-save-product" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-md shadow-indigo-600/10 transition active:scale-[0.98]">Simpan</button>
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

<div id="variant-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5); backdrop-filter:blur(4px);">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto font-sans">
        <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="text-lg font-extrabold text-slate-800 dark:text-white font-display">Kelola Varian</h3>
                <p id="variant-modal-product-name" class="text-xs text-slate-400 mt-0.5"></p>
            </div>
            <button onclick="closeVariantModal()" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 border-b border-slate-100 dark:border-slate-700">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Daftar Varian</h4>
            <div id="variant-list" class="space-y-2 min-h-[60px]">
                <p class="text-sm text-slate-400 text-center py-4" id="variant-empty-msg">Belum ada varian untuk produk ini.</p>
            </div>
        </div>

        <div class="p-6">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Tambah Varian Baru</h4>
            <form id="add-variant-form" class="space-y-3">
                <?= csrf_field() ?>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 block">Nama Varian <span class="text-rose-500">*</span></label>
                        <input type="text" id="new-variant-name" placeholder="contoh: Ukuran" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 block">Nilai <span class="text-rose-500">*</span></label>
                        <input type="text" id="new-variant-value" placeholder="contoh: XL" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 block">Harga Tambahan (Rp)</label>
                        <input type="number" id="new-variant-price" value="0" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 block">Stok</label>
                        <input type="number" id="new-variant-stock" value="0" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>
                <button type="submit" id="btn-submit-variant" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition active:scale-[0.98]">
                    + Tambah Varian
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Variant -->
<div id="edit-variant-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5); backdrop-filter:blur(4px);">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md font-sans">
        <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-extrabold text-slate-800 dark:text-white font-display">Edit Varian</h3>
            <button onclick="closeEditVariantModal()" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="edit-variant-form" class="p-6 space-y-4">
            <input type="hidden" id="edit-variant-id">
            
            <div>
                <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 block">Nama Varian <span class="text-rose-500">*</span></label>
                <input type="text" id="edit-variant-name" placeholder="contoh: Ukuran" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 block">Nilai <span class="text-rose-500">*</span></label>
                <input type="text" id="edit-variant-value" placeholder="contoh: XL" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 block">Harga Tambahan (Rp)</label>
                <input type="number" id="edit-variant-price" value="0" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1 block">Stok</label>
                <input type="number" id="edit-variant-stock" value="0" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeEditVariantModal()" class="flex-1 py-2.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-600 dark:hover:bg-slate-700 text-slate-800 dark:text-white text-sm font-bold rounded-xl transition active:scale-[0.98]">
                    Batal
                </button>
                <button type="submit" id="btn-submit-edit-variant" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition active:scale-[0.98]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/pages/admin-products.js"></script>
