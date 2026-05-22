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

    let currentPage = 1;
    let productsPerPage = 10;

    function fetchProducts(page = 1) {
        currentPage = page;
        const form = document.getElementById('filter-form');
        const formData = new FormData(form);
        const searchParams = new URLSearchParams();
        
        for (const [key, value] of formData.entries()) {
            if (value) searchParams.append(key, value);
        }
        searchParams.append('p', page);
        searchParams.append('per_page', productsPerPage);

        // Show skeleton loader
        const tbody = document.getElementById('product-table-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="p-8 text-center text-slate-400 dark:text-slate-500">
                    <div class="flex justify-center items-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Memuat produk...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch(\`index.php?page=admin_product_process&action=fetch&\${searchParams.toString()}\`)
            .then(response => response.json())
            .then(res => {
                if(res.success) {
                    renderTable(res.data);
                    renderPagination(res.meta);
                } else {
                    tbody.innerHTML = \`<tr><td colspan="6" class="p-8 text-center text-rose-500">Gagal memuat data</td></tr>\`;
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = \`<tr><td colspan="6" class="p-8 text-center text-rose-500">Terjadi kesalahan sistem</td></tr>\`;
            });
    }

    function renderTable(products) {
        const tbody = document.getElementById('product-table-body');
        if (products.length === 0) {
            tbody.innerHTML = \`
                <tr id="empty-row">
                    <td colspan="6" class="p-8 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800">Tidak ada produk yang ditemukan.</td>
                </tr>
            \`;
            return;
        }

        let html = '';
        products.forEach(product => {
            const hasVars = parseInt(product.variant_count || 0) > 0;
            const totalVariantStock = parseInt(product.total_variant_stock || 0);
            const normalStock = parseInt(product.stock || 0);
            const displayStock = hasVars ? (totalVariantStock + normalStock) : normalStock;
            const stockLabel = hasVars ? \` pcs (Total: \${normalStock} Normal + \${totalVariantStock} Varian)\` : ' pcs';
            
            let stockBadge = '';
            if (displayStock > 5) {
                stockBadge = \`<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-mono">\${displayStock}\${stockLabel}</span>\`;
            } else if (displayStock > 0) {
                stockBadge = \`<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 font-mono">\${displayStock}\${stockLabel}</span>\`;
            } else {
                stockBadge = \`<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400">\${hasVars ? 'Habis' : 'Habis'}</span>\`;
            }

            const imgUrl = product.image_url ? product.image_url : 'https://placehold.co/100x100';
            const priceStr = new Intl.NumberFormat('id-ID').format(product.price);
            
            const prodJson = JSON.stringify(product).replace(/"/g, '&quot;');
            const prodNameEsc = String(product.name).replace(/'/g, "\\\\'");

            html += \`
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition product-row bg-white dark:bg-slate-800" data-id="\${product.id}">
                    <td class="p-4 pl-6">
                        <img src="\${imgUrl}" alt="\${product.name}" class="h-12 w-12 object-cover rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                    </td>
                    <td class="p-4">
                        <div class="font-semibold text-slate-800 dark:text-slate-200">\${product.name}</div>
                        <div class="text-xs text-slate-400 dark:text-slate-500 line-clamp-1 mt-0.5">\${product.description || ''}</div>
                    </td>
                    <td class="p-4">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300">
                            \${product.category_name || 'Uncategorized'}
                        </span>
                    </td>
                    <td class="p-4 font-bold text-slate-800 dark:text-slate-200 font-mono">Rp \${priceStr}</td>
                    <td class="p-4">\${stockBadge}</td>
                    <td class="p-4">
                        <div class="flex items-center justify-center space-x-2">
                            <button onclick="openEditModal(\${prodJson})" class="p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition" title="Edit">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            <button onclick="openVariantModal(\${product.id}, '\${prodNameEsc}')" class="p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition" title="Kelola Varian">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 10V5a2 2 0 012-2zm0 0V3" /></svg>
                            </button>
                            <button data-id="\${product.id}" class="btn-delete-product p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition" title="Hapus">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            \`;
        });
        tbody.innerHTML = html;
    }

    function renderPagination(meta) {
        const totalItems = meta.total_items;
        const current = meta.current_page;
        const last = meta.total_pages;
        const perPage = meta.per_page;

        const start = totalItems === 0 ? 0 : ((current - 1) * perPage) + 1;
        const end = Math.min(current * perPage, totalItems);

        document.getElementById('meta-start').innerText = start;
        document.getElementById('meta-end').innerText = end;
        document.getElementById('meta-total').innerText = totalItems;

        let btnsHtml = '';
        if (totalItems > 0) {
            btnsHtml += \`<button onclick="fetchProducts(\${current > 1 ? current - 1 : 1})" \${current === 1 ? 'disabled' : ''} class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 \${current === 1 ? 'text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800 cursor-not-allowed' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 bg-white dark:bg-slate-800'} transition text-sm font-semibold">Prev</button>\`;
            
            for (let i = 1; i <= last; i++) {
                if (i === current) {
                    btnsHtml += \`<button class="px-3 py-1.5 rounded-lg border border-indigo-600 bg-indigo-600 text-white font-bold text-sm shadow-sm transition cursor-default">\${i}</button>\`;
                } else if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
                    btnsHtml += \`<button onclick="fetchProducts(\${i})" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 bg-white dark:bg-slate-800 transition text-sm font-semibold">\${i}</button>\`;
                } else if (i === current - 2 || i === current + 2) {
                    btnsHtml += \`<span class="px-2 py-1.5 text-slate-400">...</span>\`;
                }
            }

            btnsHtml += \`<button onclick="fetchProducts(\${current < last ? current + 1 : last})" \${current === last ? 'disabled' : ''} class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 \${current === last ? 'text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800 cursor-not-allowed' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 bg-white dark:bg-slate-800'} transition text-sm font-semibold">Next</button>\`;
        }
        document.getElementById('pagination-buttons').innerHTML = btnsHtml;
    }

    $(document).ready(function() {
        
        // Initial fetch
        fetchProducts(1);

        // Filter events
        let debounceTimer;
        $('#filter-form input').on('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchProducts(1), 500);
        });

        $('#filter-form select').on('change', function() {
            fetchProducts(1);
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            fetchProducts(1);
        });

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

        // AJAX Delete Product (Using Event Delegation)
        $(document).on('click', '.btn-delete-product', function() {
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
                                    fetchProducts(currentPage); // reload to maintain pagination
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
