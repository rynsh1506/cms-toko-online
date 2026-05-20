<?php
require_once __DIR__ . '/../../config/db.php';

// Fetch stats
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_income = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'pending' AND status != 'cancelled'")->fetchColumn() ?? 0;

// Recent orders
$stmt = $pdo->query("
    SELECT o.*, u.name as buyer_name 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC LIMIT 5
");
$recent_orders = $stmt->fetchAll();
?>

<!-- Title section -->
<div class="mb-8 font-sans">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Dashboard Overview</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Selamat datang kembali, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>. Berikut adalah ringkasan performa toko Anda.</p>
</div>

<!-- Stats grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8 font-sans">
    <!-- Stat card 1 -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between transition-colors duration-300">
        <div>
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Produk</span>
            <span class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1 block font-display"><?= $total_products ?></span>
        </div>
        <div class="h-12 w-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-650 dark:text-indigo-400">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </div>
    </div>

    <!-- Stat card 2 -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between transition-colors duration-300">
        <div>
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Pesanan</span>
            <span class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1 block font-display"><?= $total_orders ?></span>
        </div>
        <div class="h-12 w-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-650 dark:text-emerald-400">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
    </div>

    <!-- Stat card 3 -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between sm:col-span-2 lg:col-span-1 transition-colors duration-300">
        <div>
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Pendapatan</span>
            <span class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1 block font-display">Rp <?= number_format($total_income, 0, ',', '.') ?></span>
        </div>
        <div class="h-12 w-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-650 dark:text-amber-400">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1" />
            </svg>
        </div>
    </div>
</div>

<!-- Recent orders card table -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden font-sans transition-colors duration-300">
    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-white dark:bg-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white font-display">Pesanan Terbaru</h3>
        <a href="index.php?page=admin_orders" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 hover:underline">Kelola Semua Pesanan</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 text-xs font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">
                    <th class="p-4 pl-6">Order ID</th>
                    <th class="p-4">Pembeli</th>
                    <th class="p-4">Total</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-700 text-sm">
                <?php if (empty($recent_orders)): ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-450 dark:text-slate-500 bg-white dark:bg-slate-800">Belum ada pesanan yang masuk saat ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition bg-white dark:bg-slate-800">
                            <td class="p-4 pl-6 font-bold text-slate-800 dark:text-slate-200">#<?= $order['id'] ?></td>
                            <td class="p-4">
                                <div class="font-semibold text-slate-850 dark:text-slate-200"><?= htmlspecialchars($order['buyer_name']) ?></div>
                                <div class="text-xs text-slate-400 dark:text-slate-500 font-mono"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                            </td>
                            <td class="p-4 font-bold text-slate-850 dark:text-slate-200 font-mono">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                            <td class="p-4">
                                <?php if ($order['status'] === 'pending'): ?>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 dark:bg-amber-950/20 text-amber-750 dark:text-amber-400">Pending</span>
                                <?php elseif ($order['status'] === 'paid'): ?>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 dark:bg-blue-950/20 text-blue-750 dark:text-blue-400">Paid</span>
                                <?php elseif ($order['status'] === 'shipped'): ?>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-50 dark:bg-indigo-950/20 text-indigo-750 dark:text-indigo-400">Shipped</span>
                                <?php elseif ($order['status'] === 'done'): ?>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-750 dark:text-emerald-400">Done</span>
                                <?php elseif ($order['status'] === 'cancelled'): ?>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-750 dark:text-rose-450">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <a href="index.php?page=admin_orders" class="text-xs font-bold text-indigo-650 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 hover:underline">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
