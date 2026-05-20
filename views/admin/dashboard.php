<?php
require_once __DIR__ . '/../../config/db.php';

// Fetch advanced KPI stats
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_income = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'pending' AND status != 'cancelled'")->fetchColumn() ?? 0;
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$out_of_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock = 0")->fetchColumn();

// Recent orders
$stmt = $pdo->query("
    SELECT o.*, u.name as buyer_name 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC LIMIT 5
");
$recent_orders = $stmt->fetchAll();
?>

<!-- Include Chart.js -->
<script src="assets/js/chart.umd.min.js"></script>

<!-- Title section -->
<div class="mb-8 font-sans">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight font-display">Dashboard Overview</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Selamat datang kembali, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>. NusaBay Analytics System aktif.</p>
</div>

<!-- Stats grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8 font-sans">
    <!-- Stat 1: Total Produk -->
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between transition hover:shadow-md">
        <div>
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Produk</span>
            <span class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 block font-display"><?= $total_products ?></span>
        </div>
        <div class="h-10 w-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </div>
    </div>

    <!-- Stat 2: Total Pesanan -->
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between transition hover:shadow-md">
        <div>
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Pesanan</span>
            <span class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 block font-display"><?= $total_orders ?></span>
        </div>
        <div class="h-10 w-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center text-blue-650 dark:text-blue-400">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
    </div>

    <!-- Stat 3: Pendapatan -->
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between transition hover:shadow-md">
        <div>
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Pendapatan</span>
            <span class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 block font-display">Rp <?= number_format($total_income, 0, ',', '.') ?></span>
        </div>
        <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1" />
            </svg>
        </div>
    </div>

    <!-- Stat 4: Pesanan Pending -->
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between transition hover:shadow-md">
        <div>
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Pending Orders</span>
            <span class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 block font-display"><?= $pending_orders ?></span>
        </div>
        <div class="h-10 w-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-650 dark:text-amber-400">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>

    <!-- Stat 5: Stok Habis -->
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between transition hover:shadow-md">
        <div>
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Produk Habis</span>
            <span class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 block font-display"><?= $out_of_stock ?></span>
        </div>
        <div class="h-10 w-10 rounded-xl bg-rose-50 dark:bg-rose-950/40 flex items-center justify-center text-rose-600 dark:text-rose-400">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938-4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Chart 1: Daily Revenue Trend -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
        <h3 class="text-md font-bold text-slate-900 dark:text-white font-display mb-4">Tren Pendapatan Harian (7 Hari Terakhir)</h3>
        <div class="h-64 relative">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Chart 2: User Registrations Area -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
        <h3 class="text-md font-bold text-slate-900 dark:text-white font-display mb-4">Registrasi Pengguna Baru (7 Hari Terakhir)</h3>
        <div class="h-64 relative">
            <canvas id="registrationsChart"></canvas>
        </div>
    </div>

    <!-- Chart 3: Category Sales Doughnut -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
        <h3 class="text-md font-bold text-slate-900 dark:text-white font-display mb-4">Porsi Penjualan Per Kategori</h3>
        <div class="h-64 relative flex justify-center">
            <canvas id="categorySalesChart"></canvas>
        </div>
    </div>

    <!-- Chart 4: Order Status Distribution -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
        <h3 class="text-md font-bold text-slate-900 dark:text-white font-display mb-4">Distribusi Status Pesanan</h3>
        <div class="h-64 relative">
            <canvas id="orderStatusChart"></canvas>
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
                                <div class="font-semibold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($order['buyer_name']) ?></div>
                                <div class="text-xs text-slate-400 dark:text-slate-500 font-mono"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                            </td>
                            <td class="p-4 font-bold text-slate-800 dark:text-slate-200 font-mono">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                            <td class="p-4">
                                <?php if ($order['status'] === 'pending'): ?>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400">Pending</span>
                                <?php elseif ($order['status'] === 'paid'): ?>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 dark:bg-blue-950/20 text-blue-750 dark:text-blue-400">Paid</span>
                                <?php elseif ($order['status'] === 'shipped'): ?>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-50 dark:bg-indigo-950/20 text-indigo-750 dark:text-indigo-400">Shipped</span>
                                <?php elseif ($order['status'] === 'done'): ?>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-750 dark:text-emerald-400">Done</span>
                                <?php elseif ($order['status'] === 'cancelled'): ?>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <a href="index.php?page=admin_orders" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 hover:underline">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="assets/js/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // Load chart data from API
    $.getJSON('index.php?page=dashboard_api', function(response) {
        if (!response.success) return;

        const data = response.data;
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? '#334155' : '#f1f5f9';
        const labelColor = isDark ? '#94a3b8' : '#64748b';

        // 1. Revenue Chart
        new Chart(document.getElementById('revenueChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: data.earnings_trend.map(d => d.date),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: data.earnings_trend.map(d => d.amount),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: labelColor } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor } }
                }
            }
        });

        // 2. Registrations Area Chart
        new Chart(document.getElementById('registrationsChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: data.registration_trend.map(d => d.date),
                datasets: [{
                    label: 'User Baru',
                    data: data.registration_trend.map(d => d.count),
                    borderColor: '#a855f7',
                    backgroundColor: 'rgba(168, 85, 247, 0.15)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: labelColor } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, stepSize: 1 } }
                }
            }
        });

        // 3. Category Sales Doughnut Chart
        const categories = data.category_sales.length > 0 ? data.category_sales : [{ category_name: 'Belum Ada Penjualan', total_qty: 1 }];
        const catColors = ['#6366f1', '#a855f7', '#3b82f6', '#ec4899', '#f59e0b', '#10b981', '#ef4444'];
        new Chart(document.getElementById('categorySalesChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: categories.map(c => c.category_name),
                datasets: [{
                    data: categories.map(c => c.total_qty),
                    backgroundColor: catColors.slice(0, categories.length),
                    borderWidth: isDark ? 2 : 1,
                    borderColor: isDark ? '#1e293b' : '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { color: labelColor, boxWidth: 12 }
                    }
                }
            }
        });

        // 4. Order Status Bar Chart
        new Chart(document.getElementById('orderStatusChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Pending', 'Paid', 'Shipped', 'Done', 'Cancelled'],
                datasets: [{
                    data: [
                        data.order_status.pending,
                        data.order_status.paid,
                        data.order_status.shipped,
                        data.order_status.done,
                        data.order_status.cancelled
                    ],
                    backgroundColor: ['#f59e0b', '#3b82f6', '#6366f1', '#10b981', '#ef4444'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: labelColor } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, stepSize: 1 } }
                }
            }
        });
    });
});
</script>
