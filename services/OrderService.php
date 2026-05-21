<?php

class OrderService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get a bank account for update (locking).
     */
    public function getActiveBankAccountForUpdate($bankAccountId) {
        $stmt = $this->pdo->prepare("SELECT id FROM bank_accounts WHERE id = ? AND is_active = 1 FOR UPDATE");
        $stmt->execute([$bankAccountId]);
        return $stmt->fetch();
    }

    /**
     * Get a promo code for update (locking).
     */
    public function getPromoCodeForUpdate($promoCodeId) {
        $stmt = $this->pdo->prepare("SELECT * FROM promo_codes WHERE id = ? FOR UPDATE");
        $stmt->execute([$promoCodeId]);
        return $stmt->fetch();
    }

    /**
     * Increment the usage count of a promo code.
     */
    public function incrementPromoUsage($promoCodeId) {
        $stmt = $this->pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?");
        return $stmt->execute([$promoCodeId]);
    }

    /**
     * Lock and get product details for checkout.
     */
    public function lockProductForUpdate($productId) {
        $stmt = $this->pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lock and get product variant details.
     */
    public function lockVariantForUpdate($variantId, $productId) {
        $stmt = $this->pdo->prepare("SELECT id, variant_name, variant_value, additional_price, stock FROM product_variants WHERE id = ? AND product_id = ? FOR UPDATE");
        $stmt->execute([$variantId, $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new order.
     */
    public function createOrder($userId, $customerName, $customerPhone, $customerAddress, $finalTotal, $uniqueCode, $bankAccountId, $promoCodeId, $discountAmount) {
        $stmt = $this->pdo->prepare("
            INSERT INTO orders (user_id, customer_name, customer_phone, customer_address, total_price, unique_code, bank_account_id, promo_code_id, discount_amount, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $userId, 
            $customerName, 
            $customerPhone, 
            $customerAddress, 
            $finalTotal, 
            $uniqueCode,
            $bankAccountId,
            $promoCodeId,
            $discountAmount
        ]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Add an item to an order.
     */
    public function addOrderItem($orderId, $productId, $variantId, $variantInfo, $quantity, $price) {
        $stmt = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, variant_id, variant_info, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$orderId, $productId, $variantId, $variantInfo, $quantity, $price]);
    }

    /**
     * Deduct product stock.
     */
    public function deductProductStock($productId, $quantity) {
        $stmt = $this->pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        return $stmt->execute([$quantity, $productId]);
    }

    /**
     * Deduct variant stock.
     */
    public function deductVariantStock($variantId, $quantity) {
        $stmt = $this->pdo->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ?");
        return $stmt->execute([$quantity, $variantId]);
    }

    /**
     * Find order for validation during cancellation.
     */
    public function getOrderForUpdate($orderId, $userId) {
        $stmt = $this->pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ? FOR UPDATE");
        $stmt->execute([$orderId, $userId]);
        return $stmt->fetch();
    }

    /**
     * Update order status to cancelled.
     */
    public function cancelOrder($orderId) {
        $stmt = $this->pdo->prepare("
            UPDATE orders 
            SET status = 'cancelled', 
                cancel_reason = 'Dibatalkan oleh pembeli', 
                cancelled_at = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$orderId]);
    }

    /**
     * Get order items for restoring stock.
     */
    public function getOrderItems($orderId) {
        $stmt = $this->pdo->prepare("SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Restore stock for a product.
     */
    public function restoreProductStock($productId, $quantity) {
        $stmt = $this->pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        return $stmt->execute([$quantity, $productId]);
    }

    /**
     * Restore stock for a variant.
     */
    public function restoreVariantStock($variantId, $quantity) {
        $stmt = $this->pdo->prepare("UPDATE product_variants SET stock = stock + ? WHERE id = ?");
        return $stmt->execute([$quantity, $variantId]);
    }

    /**
     * Add a bank account.
     */
    public function addBankAccount($bankName, $accountNumber, $accountName) {
        $stmt = $this->pdo->prepare("INSERT INTO bank_accounts (bank_name, account_number, account_name, is_active) VALUES (?, ?, ?, 1)");
        return $stmt->execute([$bankName, $accountNumber, $accountName]);
    }

    /**
     * Get a bank account by ID.
     */
    public function getBankAccountById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM bank_accounts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Toggle active status of a bank account.
     */
    public function toggleBankAccountStatus($id, $newStatus) {
        $stmt = $this->pdo->prepare("UPDATE bank_accounts SET is_active = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
    }

    /**
     * Delete a bank account.
     */
    public function deleteBankAccount($id) {
        $stmt = $this->pdo->prepare("DELETE FROM bank_accounts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Check if a promo code exists.
     */
    public function checkPromoCodeExists($code) {
        $stmt = $this->pdo->prepare("SELECT id FROM promo_codes WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Check if a promo code exists excluding self.
     */
    public function checkPromoCodeExistsExcludingSelf($code, $id) {
        $stmt = $this->pdo->prepare("SELECT id FROM promo_codes WHERE code = ? AND id != ?");
        $stmt->execute([$code, $id]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Add a promo code.
     */
    public function addPromoCode($code, $discountType, $discountValue, $minOrder, $maxUses, $isActive, $expiresAt) {
        $stmt = $this->pdo->prepare("
            INSERT INTO promo_codes (code, discount_type, discount_value, min_order, max_uses, is_active, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$code, $discountType, $discountValue, $minOrder, $maxUses, $isActive, $expiresAt]);
    }

    /**
     * Update a promo code.
     */
    public function updatePromoCode($id, $code, $discountType, $discountValue, $minOrder, $maxUses, $isActive, $expiresAt) {
        $stmt = $this->pdo->prepare("
            UPDATE promo_codes 
            SET code = ?, discount_type = ?, discount_value = ?, min_order = ?, max_uses = ?, is_active = ?, expires_at = ?
            WHERE id = ?
        ");
        return $stmt->execute([$code, $discountType, $discountValue, $minOrder, $maxUses, $isActive, $expiresAt, $id]);
    }

    /**
     * Delete a promo code.
     */
    public function deletePromoCode($id) {
        $stmt = $this->pdo->prepare("DELETE FROM promo_codes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get a promo code by code string.
     */
    public function getPromoCodeByCode($code) {
        $stmt = $this->pdo->prepare("SELECT * FROM promo_codes WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    /**
     * Lock order for status update.
     */
    public function lockOrderForUpdate($orderId) {
        $stmt = $this->pdo->prepare("SELECT status FROM orders WHERE id = ? FOR UPDATE");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus($orderId, $status) {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }

    /**
     * Get order details joined with bank account info.
     */
    public function getOrderWithBankDetails($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, b.bank_name, b.account_number, b.account_name 
            FROM orders o
            LEFT JOIN bank_accounts b ON o.bank_account_id = b.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    /**
     * Get order items joined with product info.
     */
    public function getOrderItemsWithProductInfo($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT oi.*, p.name, p.image_url 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Get admin phone number.
     */
    public function getAdminPhone() {
        $stmtAdmin = $this->pdo->query("SELECT phone FROM users WHERE role = 'admin' LIMIT 1");
        $admin_user = $stmtAdmin->fetch();
        if ($admin_user && !empty($admin_user['phone'])) {
            return preg_replace('/[^0-9]/', '', $admin_user['phone']);
        }
        return '6281234567890';
    }
}
