<?php
require_once __DIR__ . '/../../config/db.php';

// Fetch all bank accounts
$stmt = $pdo->query("SELECT * FROM bank_accounts ORDER BY id DESC");
$banks = $stmt->fetchAll();
?>

<!-- Title -->
<div class="mb-8 font-sans">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Rekening Bank / Metode Pembayaran</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola metode pembayaran transfer bank yang dapat dipilih pelanggan saat checkout.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 font-sans">
    <!-- Form Tambah Rekening (Left Column) -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 h-fit transition-colors duration-300">
        <h3 class="text-lg font-bold mb-4 text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-2 font-display">Tambah Rekening</h3>
        <form id="add-bank-form" action="index.php?page=admin_bank_process&action=add" method="POST" class="space-y-4">

            <?= csrf_field() ?>
            <div>
                <label class="block text-slate-700 dark:text-slate-300 text-xs font-bold mb-1.5">Nama Bank (misal: BCA, Mandiri)</label>
                <input type="text" name="bank_name" id="bank_name" required placeholder="BCA"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
            </div>

            <div>
                <label class="block text-slate-700 dark:text-slate-300 text-xs font-bold mb-1.5">Nomor Rekening</label>
                <input type="text" name="account_number" id="account_number" required placeholder="123-4567-890"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
            </div>

            <div>
                <label class="block text-slate-700 dark:text-slate-300 text-xs font-bold mb-1.5">Nama Pemilik Rekening</label>
                <input type="text" name="account_name" id="account_name" required placeholder="PT Toko Online"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
            </div>

            <button type="submit" id="btn-save-bank" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl text-sm shadow-md shadow-indigo-600/10 transition active:scale-[0.98]">
                Simpan Rekening
            </button>
        </form>
    </div>

    <!-- Tabel Daftar Rekening (Right Column) -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white font-display font-semibold">Daftar Rekening Aktif</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">
                        <th class="p-4 pl-6">Bank</th>
                        <th class="p-4">No. Rekening</th>
                        <th class="p-4">Atas Nama</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="bank-table-body" class="divide-y divide-slate-50 dark:divide-slate-700 text-sm">
                    <?php if (empty($banks)): ?>
                        <tr id="empty-bank-row">
                            <td colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800">Belum ada rekening terdaftar. Tambahkan rekening bank di sebelah kiri.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($banks as $bank): ?>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition bank-row bg-white dark:bg-slate-800" data-id="<?= $bank['id'] ?>">
                                <td class="p-4 pl-6 font-bold text-slate-800 dark:text-slate-200">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-600 text-xs uppercase font-mono"><?= htmlspecialchars($bank['bank_name']) ?></span>
                                </td>
                                <td class="p-4 font-semibold text-slate-800 dark:text-slate-200 font-mono"><?= htmlspecialchars($bank['account_number']) ?></td>
                                <td class="p-4 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($bank['account_name']) ?></td>
                                <td class="p-4 text-center">
                                    <button type="button" class="btn-toggle-status inline-block" data-id="<?= $bank['id'] ?>">
                                        <?php if ($bank['is_active']): ?>
                                            <span class="badge-status px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 hover:opacity-80 transition cursor-pointer">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge-status px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 hover:opacity-80 transition cursor-pointer">Nonaktif</span>
                                        <?php endif; ?>
                                    </button>
                                </td>
                                <td class="p-4 text-center">
                                    <button type="button" data-id="<?= $bank['id'] ?>"
                                       class="btn-delete-bank inline-block p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition"
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
<script src="assets/js/pages/admin-bank-accounts.js"></script>
