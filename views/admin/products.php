<?php
// Fetch all categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Fetch all products with category
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll();
?>

<!-- Title & CTA -->
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

<!-- Products Table -->
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
                <?php if (empty($products)): ?>
                    <tr id="empty-row">
                        <td colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800">Belum ada produk. Klik tombol "Tambah Produk" untuk memulai.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition product-row bg-white dark:bg-slate-800" data-id="<?= $product['id'] ?>">
                            <td class="p-4 pl-6">
                                <img src="<?= htmlspecialchars($product['image_url'] ?? 'https://placehold.co/100x100') ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="h-12 w-12 object-cover rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($product['name']) ?></div>
                                <div class="text-xs text-slate-400 dark:text-slate-500 line-clamp-1 mt-0.5"><?= htmlspecialchars($product['description'] ?? '') ?></div>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300">
                                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                </span>
                            </td>
                            <td class="p-4 font-bold text-slate-800 dark:text-slate-200 font-mono">Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                            <td class="p-4">
                                <?php if ($product['stock'] > 5): ?>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono"><?= $product['stock'] ?> pcs</span>
                                <?php elseif ($product['stock'] > 0): ?>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 font-mono"><?= $product['stock'] ?> pcs</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <button 
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($product)) ?>)"
                                        class="p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition" 
                                        title="Edit">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button 
                                        data-id="<?= $product['id'] ?>"
                                        class="btn-delete-product p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition" 
                                        title="Hapus">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pop-up CRUD Produk -->
<div id="productModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100 dark:border-slate-800">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white font-display" id="modalTitle">Tambah Produk Baru</h3>
                <button onclick="closeModal()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form id="productForm" action="index.php?page=admin_product_process&action=add" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="prodId">
                <div class="p-6 space-y-4">
                    <!-- Nama Produk -->
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Nama Produk</label>
                        <input type="text" name="name" id="prodName" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Deskripsi</label>
                        <textarea name="description" id="prodDesc" rows="3" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm"></textarea>
                    </div>

                    <!-- Kategori -->
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
                        <!-- Harga -->
                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Harga (Rp)</label>
                            <input type="number" name="price" id="prodPrice" required min="0"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>

                        <!-- Stok -->
                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Stok</label>
                            <input type="number" name="stock" id="prodStock" required min="0"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>
                    </div>

                    <!-- Gambar Upload -->
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

                <!-- Modal Actions -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end space-x-3 bg-slate-50 dark:bg-slate-800/50">
                    <button type="button" onclick="closeModal()" class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 text-sm transition">Batal</button>
                    <button type="submit" id="btn-save-product" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-md shadow-indigo-600/10 transition active:scale-[0.98]">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/sweetalert2.all.min.js"></script>
<script>
    const modal = document.getElementById('productModal');
    const modalTitle = document.getElementById('modalTitle');
    const productForm = document.getElementById('productForm');
    
    const prodId = document.getElementById('prodId');
    const prodName = document.getElementById('prodName');
    const prodDesc = document.getElementById('prodDesc');
    const prodCategoryId = document.getElementById('prodCategoryId');
    const prodPrice = document.getElementById('prodPrice');
    const prodStock = document.getElementById('prodStock');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const prodImagePreview = document.getElementById('prodImagePreview');
    const prodImageFile = document.getElementById('prodImageFile');

    function openAddModal() {
        modalTitle.innerText = "Tambah Produk Baru";
        productForm.action = "index.php?page=admin_product_process&action=add";
        
        prodId.value = "";
        prodName.value = "";
        prodDesc.value = "";
        prodCategoryId.value = "";
        prodPrice.value = "";
        prodStock.value = "";
        prodImageFile.required = true;
        imagePreviewContainer.classList.add('hidden');
        prodImagePreview.src = "";
        
        modal.classList.remove('hidden');
    }

    function openEditModal(product) {
        modalTitle.innerText = "Edit Produk";
        productForm.action = "index.php?page=admin_product_process&action=edit";
        
        prodId.value = product.id;
        prodName.value = product.name;
        prodDesc.value = product.description;
        prodCategoryId.value = product.category_id || "";
        prodPrice.value = Math.floor(product.price);
        prodStock.value = product.stock;
        prodImageFile.required = false;

        if (product.image_url) {
            prodImagePreview.src = product.image_url;
            imagePreviewContainer.classList.remove('hidden');
        } else {
            imagePreviewContainer.classList.add('hidden');
        }
        
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    $(document).ready(function() {
        // AJAX Submit Form (Supports Image Upload via FormData)
        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = new FormData(this);
            formData.append('ajax', 1);
            
            const btn = $('#btn-save-product');
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
                        text: 'Terjadi kesalahan sistem saat menyimpan produk.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.prop('disabled', false).text('Simpan');
                }
            });
        });

        // AJAX Delete Product
        $('.btn-delete-product').on('click', function() {
            const btn = $(this);
            const id = btn.data('id');
            
            Swal.fire({
                title: 'Hapus Produk?',
                text: 'Apakah Anda yakin ingin menghapus produk ini dari katalog?',
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
                        url: 'index.php?page=admin_product_process&action=delete&id=' + id + '&ajax=1',
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
                                btn.closest('.product-row').fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.product-row').length === 0) {
                                        $('#product-table-body').html(`
                                            <tr id="empty-row">
                                                <td colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800">Belum ada produk. Klik tombol "Tambah Produk" untuk memulai.</td>
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
                                text: 'Terjadi kesalahan sistem saat menghapus produk.',
                                icon: 'error',
                                confirmButtonColor: '#4f46e5'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
