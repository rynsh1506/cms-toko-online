<?php

class DashboardService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get basic KPI stats.
     */
    public function getKPIStats() {
        $stats = [];
        
        $stats['total_products'] = intval($this->pdo->query("SELECT COUNT(*) FROM products")->fetchColumn());
        $stats['total_orders'] = intval($this->pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn());
        $stats['total_income'] = floatval($this->pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'pending' AND status != 'cancelled'")->fetchColumn() ?? 0);
        $stats['pending_orders'] = intval($this->pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn());
        $stats['out_of_stock'] = intval($this->pdo->query("SELECT COUNT(*) FROM products WHERE stock = 0")->fetchColumn());

        return $stats;
    }

    /**
     * Get recent orders for dashboard.
     */
    public function getRecentOrders($limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.name as buyer_name 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.id DESC LIMIT ?
        ");
        // Bind integer parameter securely
        $stmt->bindValue(1, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get daily earnings trend for past X days.
     */
    public function getEarningsTrend($days = 7) {
        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stmt = $this->pdo->prepare("
                SELECT SUM(total_price) as total 
                FROM orders 
                WHERE DATE(created_at) = ? AND status NOT IN ('pending', 'cancelled')
            ");
            $stmt->execute([$date]);
            $total = $stmt->fetchColumn() ?? 0;
            
            $trend[] = [
                'date' => date('d M', strtotime($date)),
                'amount' => floatval($total)
            ];
        }
        return $trend;
    }

    /**
     * Get order status counts breakdown.
     */
    public function getOrderStatusCounts() {
        $stmt = $this->pdo->query("
            SELECT status, COUNT(*) as count 
            FROM orders 
            GROUP BY status
        ");
        $counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $statuses = ['pending', 'paid', 'shipped', 'done', 'cancelled'];
        $breakdown = [];
        foreach ($statuses as $status) {
            $breakdown[$status] = intval($counts[$status] ?? 0);
        }
        return $breakdown;
    }

    /**
     * Get quantity sold per category.
     */
    public function getCategorySales() {
        $stmt = $this->pdo->query("
            SELECT c.name as category_name, SUM(oi.quantity) as total_qty
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status NOT IN ('pending', 'cancelled')
            GROUP BY c.id
            ORDER BY total_qty DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get registration trend of new users.
     */
    public function getRegistrationTrend($days = 7) {
        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count 
                FROM users 
                WHERE DATE(created_at) = ?
            ");
            $stmt->execute([$date]);
            $count = $stmt->fetchColumn() ?? 0;
            
            $trend[] = [
                'date' => date('d M', strtotime($date)),
                'count' => intval($count)
            ];
        }
        return $trend;
    }
}
