<?php
require_once __DIR__ . '/../../config/db.php';

// Fetch all promo codes
$stmt = $pdo->query("SELECT * FROM promo_codes ORDER BY id DESC");
$promos = $stmt->fetchAll();
?>

<!-- Title Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 font-sans">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Kelola Kode Promo</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Buat, edit, dan atur kode promo diskon belanja untuk NusaBay.</p>
    </div>
    <button 
        onclick="openAddPromoModal()"
        class="inline-flex items-center space-x-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition shadow-lg shadow-indigo-600/25 active:scale-[0.98] cursor-pointer">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Tambah Kode Promo</span>
    </button>
</div>

<!-- Promo Codes Table Card -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden font-sans transition-colors duration-300">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">
                    <th class="p-4 pl-6">Kode</th>
                    <th class="p-4">Tipe Diskon</th>
                    <th class="p-4">Nilai Diskon</th>
                    <th class="p-4">Min. Belanja</th>
                    <th class="p-4">Penggunaan</th>
                    <th class="p-4">Masa Berlaku</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="promo-table-body" class="divide-y divide-slate-50 dark:divide-slate-700 text-sm">
                <?php if (empty($promos)): ?>
                    <tr id="empty-row">
                        <td colspan="8" class="p-8 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800">Belum ada kode promo yang didaftarkan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($promos as $promo): ?>
                        <tr class="promo-row hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition bg-white dark:bg-slate-800">
                            <td class="p-4 pl-6 font-bold text-indigo-600 dark:text-indigo-400 font-mono text-base uppercase"><?= htmlspecialchars($promo['code']) ?></td>
                            <td class="p-4 capitalize">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300">
                                    <?= htmlspecialchars($promo['discount_type'] === 'percentage' ? 'Persentase (%)' : 'Potongan Tetap (Rupiah)') ?>
                                </span>
                            </td>
                            <td class="p-4 font-bold text-slate-800 dark:text-slate-200">
                                <?= $promo['discount_type'] === 'percentage' ? number_format($promo['discount_value'], 0) . '%' : 'Rp ' . number_format($promo['discount_value'], 0, ',', '.') ?>
                            </td>
                            <td class="p-4 font-mono text-slate-600 dark:text-slate-400">
                                Rp <?= number_format($promo['min_order'], 0, ',', '.') ?>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center space-x-1.5">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 font-mono"><?= $promo['used_count'] ?></span>
                                    <span class="text-slate-400 dark:text-slate-500">/</span>
                                    <span class="text-slate-500 dark:text-slate-400 font-mono"><?= $promo['max_uses'] ?> kali</span>
                                </div>
                            </td>
                            <td class="p-4 text-xs font-mono text-slate-500 dark:text-slate-400">
                                <?= date('d M Y, H:i', strtotime($promo['expires_at'])) ?>
                            </td>
                            <td class="p-4">
                                <?php if ($promo['is_active'] && strtotime($promo['expires_at']) > time()): ?>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400">Aktif</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400">Nonaktif / Expired</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <button 
                                        onclick="openEditPromoModal(<?= htmlspecialchars(json_encode($promo)) ?>)"
                                        class="p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition" 
                                        title="Edit">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button 
                                        data-id="<?= $promo['id'] ?>"
                                        class="btn-delete-promo p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition" 
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

<!-- Modal CRUD Promo -->
<div id="promoModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100 dark:border-slate-800">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white font-display" id="modalTitle">Tambah Kode Promo</h3>
                <button onclick="closePromoModal()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form id="promoForm" action="index.php?page=admin_promo_process&action=add" method="POST">
                <input type="hidden" name="id" id="promoId">
                <div class="p-6 space-y-4">
                    <!-- Code -->
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Kode Promo</label>
                        <input type="text" name="code" id="promoCode" required placeholder="Contoh: NUSA50K"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm uppercase font-mono">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Discount Type -->
                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Tipe Diskon</label>
                            <select name="discount_type" id="promoType" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                            </select>
                        </div>

                        <!-- Discount Value -->
                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Nilai Diskon</label>
                            <input type="number" name="discount_value" id="promoValue" required min="1"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Min Order -->
                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Min. Belanja (Rp)</label>
                            <input type="number" name="min_order" id="promoMinOrder" required min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>

                        <!-- Max Uses -->
                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Maks. Pemakaian</label>
                            <input type="number" name="max_uses" id="promoMaxUses" required min="1" value="100"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Expires At -->
                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Tanggal Kedaluwarsa</label>
                            <input type="datetime-local" name="expires_at" id="promoExpiresAt" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>

                        <!-- Active status -->
                        <div>
                            <label class="block text-slate-700 dark:text-slate-300 font-bold mb-1 text-sm">Status Aktif</label>
                            <select name="is_active" id="promoActive" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end space-x-3 bg-slate-50 dark:bg-slate-800/50">
                    <button type="button" onclick="closePromoModal()" class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 text-sm transition">Batal</button>
                    <button type="submit" id="btn-save-promo" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-md shadow-indigo-600/10 transition active:scale-[0.98]">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/sweetalert2.all.min.js"></script>
<script>
    const modal = document.getElementById('promoModal');
    const modalTitle = document.getElementById('modalTitle');
    const promoForm = document.getElementById('promoForm');
    
    const promoId = document.getElementById('promoId');
    const promoCode = document.getElementById('promoCode');
    const promoType = document.getElementById('promoType');
    const promoValue = document.getElementById('promoValue');
    const promoMinOrder = document.getElementById('promoMinOrder');
    const promoMaxUses = document.getElementById('promoMaxUses');
    const promoExpiresAt = document.getElementById('promoExpiresAt');
    const promoActive = document.getElementById('promoActive');

    function openAddPromoModal() {
        modalTitle.innerText = "Tambah Kode Promo";
        promoForm.action = "index.php?page=admin_promo_process&action=add";
        
        promoId.value = "";
        promoCode.value = "";
        promoType.value = "percentage";
        promoValue.value = "";
        promoMinOrder.value = "0";
        promoMaxUses.value = "100";
        
        // Default expiry in 30 days
        const date = new Date();
        date.setDate(date.getDate() + 30);
        promoExpiresAt.value = date.toISOString().slice(0, 16);
        promoActive.value = "1";
        
        modal.classList.remove('hidden');
    }

    function openEditPromoModal(promo) {
        modalTitle.innerText = "Edit Kode Promo";
        promoForm.action = "index.php?page=admin_promo_process&action=edit";
        
        promoId.value = promo.id;
        promoCode.value = promo.code;
        promoType.value = promo.discount_type;
        promoValue.value = Math.floor(promo.discount_value);
        promoMinOrder.value = Math.floor(promo.min_order);
        promoMaxUses.value = promo.max_uses;
        
        // Convert SQL datetime to local datetime-local format
        const localDate = promo.expires_at.replace(" ", "T").substring(0, 16);
        promoExpiresAt.value = localDate;
        promoActive.value = promo.is_active;
        
        modal.classList.remove('hidden');
    }

    function closePromoModal() {
        modal.classList.add('hidden');
    }

    $(document).ready(function() {
        // Save (Add / Edit) Promo AJAX
        $('#promoForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const data = form.serialize() + '&ajax=1';
            
            const btn = $('#btn-save-promo');
            btn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: data,
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
                        text: 'Terjadi kesalahan sistem saat menyimpan kode promo.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.prop('disabled', false).text('Simpan');
                }
            });
        });

        // Delete Promo AJAX
        $('.btn-delete-promo').on('click', function() {
            const btn = $(this);
            const id = btn.data('id');
            
            Swal.fire({
                title: 'Hapus Kode Promo?',
                text: 'Apakah Anda yakin ingin menghapus kode promo ini? Tindakan ini permanen.',
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
                        url: 'index.php?page=admin_promo_process&action=delete&id=' + id + '&ajax=1',
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
                                btn.closest('.promo-row').fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.promo-row').length === 0) {
                                        $('#promo-table-body').html(`
                                            <tr id="empty-row">
                                                <td colspan="8" class="p-8 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800">Belum ada kode promo yang didaftarkan.</td>
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
                                text: 'Terjadi kesalahan sistem saat menghapus kode promo.',
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
