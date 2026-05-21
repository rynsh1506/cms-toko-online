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
