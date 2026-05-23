<?php
// Orders and items are fetched via controller
?>

<!-- Title -->
<div class="mb-8 font-sans">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Kelola Pesanan</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau transaksi masuk, verifikasi bukti pembayaran, dan ubah status pengiriman produk.</p>
</div>

<!-- Orders Table -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden font-sans transition-colors duration-300">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">
                    <th class="p-4 pl-6 w-20">ID Order</th>
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Pembeli</th>
                    <th class="p-4">Total Transfer</th>
                    <th class="p-4">Metode Bayar</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-700 text-sm">
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800">Belum ada pesanan masuk.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition order-row bg-white dark:bg-slate-800" data-id="<?= $order['id'] ?>">
                            <td class="p-4 pl-6 font-bold text-slate-800 dark:text-slate-200">#<?= $order['id'] ?></td>
                            <td class="p-4 text-slate-500 dark:text-slate-400 font-mono text-xs"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                            <td class="p-4">
                                <div class="font-semibold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($order['customer_name']) ?></div>
                                <div class="text-xs text-slate-400 dark:text-slate-500"><?= htmlspecialchars($order['customer_phone']) ?></div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-slate-800 dark:text-slate-200 font-mono">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 font-bold">Kode Unik: +<?= $order['unique_code'] ?></div>
                            </td>
                            <td class="p-4">
                                <?php if ($order['bank_name']): ?>
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 text-indigo-750 dark:text-indigo-400 font-bold text-[10px] uppercase font-mono">
                                        <?= htmlspecialchars($order['bank_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400 dark:text-slate-500 text-xs font-semibold">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 status-cell">
                                <?php if ($order['status'] === 'pending'): ?>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400">Pending</span>
                                <?php elseif ($order['status'] === 'paid'): ?>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400">Paid</span>
                                <?php elseif ($order['status'] === 'shipped'): ?>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400">Shipped</span>
                                <?php elseif ($order['status'] === 'done'): ?>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400">Done</span>
                                <?php elseif ($order['status'] === 'cancelled'): ?>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <button
                                        onclick="openDetailModal(<?= htmlspecialchars(json_encode($order)) ?>, <?= htmlspecialchars(json_encode($order_items[$order['id']] ?? [])) ?>)"
                                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600 hover:text-slate-900 dark:hover:text-white rounded-xl text-xs font-bold transition">
                                        Detail
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

<!-- Modal Detail Order -->
<div id="orderModal" class="fixed inset-0 z-50 overflow-y-auto hidden font-sans">
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100 dark:border-slate-800">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white font-display" id="modalOrderTitle">Detail Order #</h3>
                <button onclick="closeOrderModal()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-slate-150 dark:border-slate-800 transition-colors duration-300">
                    <div>
                        <h4 class="font-bold text-slate-400 dark:text-slate-500 uppercase text-[10px] tracking-wider mb-2">Informasi Pembeli</h4>
                        <table class="w-full text-slate-700 dark:text-slate-300 border-none text-xs">
                            <tr>
                                <td class="py-1 text-slate-450 dark:text-slate-555 w-20">Nama:</td>
                                <td class="py-1 font-semibold text-slate-800 dark:text-slate-200" id="modalBuyerName">-</td>
                            </tr>
                            <tr>
                                <td class="py-1 text-slate-450 dark:text-slate-555">Telepon:</td>
                                <td class="py-1 font-semibold text-slate-800 dark:text-slate-200" id="modalBuyerPhone">-</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 text-slate-450 dark:text-slate-555">Alamat:</td>
                                <td class="py-1.5 leading-relaxed text-slate-600 dark:text-slate-400" id="modalBuyerAddress">-</td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-400 dark:text-slate-500 uppercase text-[10px] tracking-wider mb-2">Informasi Pembayaran</h4>
                        <table class="w-full text-slate-700 dark:text-slate-300 text-xs">
                            <tr>
                                <td class="py-1 text-slate-450 dark:text-slate-555 w-24">Pilihan Bank:</td>
                                <td class="py-1 font-semibold text-slate-800 dark:text-slate-200" id="modalPaymentBank">-</td>
                            </tr>
                            <tr>
                                <td class="py-1 text-slate-450 dark:text-slate-555">No. Rekening:</td>
                                <td class="py-1 font-semibold text-slate-800 dark:text-slate-200 font-mono" id="modalPaymentNumber">-</td>
                            </tr>
                            <tr>
                                <td class="py-1 text-slate-450 dark:text-slate-555">Atas Nama:</td>
                                <td class="py-1 font-semibold text-slate-800 dark:text-slate-200" id="modalPaymentName">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Products Table -->
                <div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs uppercase tracking-wider mb-3">Produk Yang Dipesan</h4>
                    <div class="border border-slate-100 dark:border-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">
                                    <th class="p-3 pl-4">Produk</th>
                                    <th class="p-3 text-center">Qty</th>
                                    <th class="p-3 text-right">Harga</th>
                                    <th class="p-3 text-right pr-4">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="modalProductsList" class="divide-y divide-slate-50 dark:divide-slate-800 text-xs">
                                <!-- Dynamic -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Status Update Section -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-semibold block uppercase">Ubah Status Pesanan</span>
                        <div class="flex items-center space-x-2 mt-1.5" id="statusBadgeContainer">
                            <!-- Current status badge -->
                        </div>
                    </div>
                    <form id="statusForm" action="index.php?page=admin_order_process&action=update_status" method="POST" class="flex items-center space-x-2">

                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" id="statusOrderId">
                        <select name="status" id="statusSelect" class="text-xs px-3.5 py-2 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 transition">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid (Telah Dibayar)</option>
                            <option value="shipped">Shipped (Dikirim)</option>
                            <option value="done">Done (Selesai)</option>
                            <option value="cancelled">Cancelled (Dibatalkan)</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-indigo-600/10 active:scale-[0.98]">
                            Update
                        </button>
                    </form>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end bg-slate-50 dark:bg-slate-800/50">
                <button type="button" onclick="closeOrderModal()" class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 text-sm transition">Tutup</button>
            </div>
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
<script src="assets/js/pages/admin-orders.js"></script>
