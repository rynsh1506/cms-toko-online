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

// ========== VARIANT MODAL ==========
let currentProductId = null;

function openVariantModal(productId, productName) {
    currentProductId = productId;
    document.getElementById('variant-modal-product-name').textContent = productName;
    document.getElementById('variant-modal').style.display = 'flex';
    document.getElementById('add-variant-form').reset();
    loadVariants();
}

function closeVariantModal() {
    document.getElementById('variant-modal').style.display = 'none';
    currentProductId = null;
}

function loadVariants() {
    const list = document.getElementById('variant-list');
    list.innerHTML = '<p class="text-xs text-slate-400 text-center py-4">Memuat...</p>';
    $.getJSON('index.php?page=admin_variant_process&action=list&product_id=' + currentProductId, function(data) {
        if (data.status !== 'success') {
            list.innerHTML = '<p class="text-xs text-rose-500 text-center py-4">Gagal memuat varian.</p>';
            return;
        }
        if (data.variants.length === 0) {
            list.innerHTML = '<p class="text-sm text-slate-400 text-center py-4" id="variant-empty-msg">Belum ada varian untuk produk ini.</p>';
            return;
        }
        list.innerHTML = '';
        // Group header
        const header = '<div class="grid grid-cols-12 gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider px-3 mb-1"><span class="col-span-3">Nama</span><span class="col-span-3">Nilai</span><span class="col-span-3">+Harga</span><span class="col-span-2">Stok</span><span class="col-span-1"></span></div>';
        list.innerHTML = header;
        data.variants.forEach(function(v) {
            list.innerHTML += `
            <div class="variant-row grid grid-cols-12 gap-2 items-center bg-slate-50 dark:bg-slate-700/50 rounded-xl px-3 py-2 text-sm" data-id="${v.id}">
                <span class="col-span-3 font-medium text-slate-700 dark:text-slate-200 edit-name">${escHtml(v.variant_name)}</span>
                <span class="col-span-3 text-slate-600 dark:text-slate-300 edit-value">${escHtml(v.variant_value)}</span>
                <span class="col-span-3 font-mono text-slate-600 dark:text-slate-300 edit-price">${parseInt(v.additional_price) > 0 ? '+Rp ' + Number(v.additional_price).toLocaleString('id-ID') : 'Rp 0'}</span>
                <span class="col-span-2 font-mono text-slate-600 dark:text-slate-300 edit-stock">${v.stock}</span>
                <div class="col-span-1 flex space-x-1">
                    <button onclick="editVariantInline(this, ${v.id}, '${escHtml(v.variant_name)}', '${escHtml(v.variant_value)}', ${v.additional_price}, ${v.stock})" class="p-1 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/40 text-slate-400 hover:text-indigo-600 transition" title="Edit">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="deleteVariant(${v.id})" class="p-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/40 text-slate-400 hover:text-rose-600 transition" title="Hapus">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>`;
        });
    });
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// Add Variant (Sudah diperbaiki dengan popup SweetAlert)
$('#add-variant-form').on('submit', function(e) {
    e.preventDefault();
    const name  = $('#new-variant-name').val().trim();
    const value = $('#new-variant-value').val().trim();
    const price = $('#new-variant-price').val();
    const stock = $('#new-variant-stock').val();

    if (!name || !value) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Nama varian dan nilai wajib diisi.'
        });
        return;
    }

    const btnSubmit = $('#btn-submit-variant');
    const originalBtnText = btnSubmit.html();
    btnSubmit.prop('disabled', true).text('Menyimpan...');

    $.post('index.php?page=admin_variant_process&action=add', {
        product_id: currentProductId,
        variant_name: name,
        variant_value: value,
        additional_price: price,
        stock: stock
    }, function(data) {
        btnSubmit.prop('disabled', false).html(originalBtnText);

        if (data.status === 'success') {
            $('#add-variant-form')[0].reset();
            loadVariants();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message || 'Varian baru berhasil ditambahkan.',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message
            });
        }
    }, 'json').fail(function() {
        btnSubmit.prop('disabled', false).html(originalBtnText);
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Sistem',
            text: 'Gagal terhubung ke server.'
        });
    });
});

// Edit Variant (Sudah ditambahkan SweetAlert)
function editVariantInline(btn, id, name, value, price, stock) {
    const newName  = prompt('Nama Varian:', name);
    if (newName === null) return;
    const newVal   = prompt('Nilai:', value);
    if (newVal === null) return;
    const newPrice = prompt('Harga Tambahan (angka):', price);
    if (newPrice === null) return;
    const newStock = prompt('Stok:', stock);
    if (newStock === null) return;

    $.post('index.php?page=admin_variant_process&action=edit', {
        id: id, variant_name: newName, variant_value: newVal, additional_price: newPrice, stock: newStock
    }, function(data) {
        if (data.status === 'success') {
            loadVariants();
            Swal.fire({
                icon: 'success',
                title: 'Diperbarui!',
                text: data.message || 'Data varian berhasil diubah.',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message
            });
        }
    }, 'json').fail(function() {
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Sistem',
            text: 'Gagal terhubung ke server.'
        });
    });
}

// Delete Variant (Sudah ditambahkan SweetAlert Confirm)
function deleteVariant(id) {
    Swal.fire({
        title: 'Hapus Varian?',
        text: 'Data varian ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('index.php?page=admin_variant_process&action=delete', { id: id }, function(data) {
                if (data.status === 'success') {
                    loadVariants();
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: data.message || 'Varian berhasil dihapus.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message
                    });
                }
            }, 'json').fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Gagal terhubung ke server.'
                });
            });
        }
    });
}

// Close modal on backdrop click
$('#variant-modal').on('click', function(e) {
    if (e.target === this) closeVariantModal();
});
