<?php

class OrderService
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get a bank account for update (locking).
     */
    public function getActiveBankAccountForUpdate($bankAccountId)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM bank_accounts WHERE id = ? AND is_active = 1 FOR UPDATE");
        $stmt->execute([$bankAccountId]);
        return $stmt->fetch();
    }

    /**
     * Get a promo code for update (locking).
     */
    public function getPromoCodeForUpdate($promoCodeId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM promo_codes WHERE id = ? FOR UPDATE");
        $stmt->execute([$promoCodeId]);
        return $stmt->fetch();
    }

    /**
     * Increment the usage count of a promo code.
     */
    public function incrementPromoUsage($promoCodeId)
    {
        $stmt = $this->pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?");
        return $stmt->execute([$promoCodeId]);
    }

    /**
     * Lock and get product details for checkout.
     */
    public function lockProductForUpdate($productId)
    {
        $stmt = $this->pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lock and get product variant details.
     */
    public function lockVariantForUpdate($variantId, $productId)
    {
        $stmt = $this->pdo->prepare("SELECT id, variant_name, variant_value, additional_price, stock FROM product_variants WHERE id = ? AND product_id = ? FOR UPDATE");
        $stmt->execute([$variantId, $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new order.
     */
    public function createOrder($userId, $customerName, $customerPhone, $customerAddress, $finalTotal, $uniqueCode, $bankAccountId, $promoCodeId, $discountAmount)
    {
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
    public function addOrderItem($orderId, $productId, $variantId, $variantInfo, $quantity, $price)
    {
        $stmt = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, variant_id, variant_info, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$orderId, $productId, $variantId, $variantInfo, $quantity, $price]);
    }

    /**
     * Deduct product stock.
     */
    public function deductProductStock($productId, $quantity)
    {
        $stmt = $this->pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        return $stmt->execute([$quantity, $productId]);
    }

    /**
     * Deduct variant stock.
     */
    public function deductVariantStock($variantId, $quantity)
    {
        $stmt = $this->pdo->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ?");
        return $stmt->execute([$quantity, $variantId]);
    }

    /**
     * Find order for validation during cancellation.
     */
    public function getOrderForUpdate($orderId, $userId)
    {
        $stmt = $this->pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ? FOR UPDATE");
        $stmt->execute([$orderId, $userId]);
        return $stmt->fetch();
    }

    /**
     * Update order status to cancelled.
     */
    public function cancelOrder($orderId)
    {
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
    public function getOrderItems($orderId)
    {
        $stmt = $this->pdo->prepare("SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Restore stock for a product.
     */
    public function restoreProductStock($productId, $quantity)
    {
        $stmt = $this->pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        return $stmt->execute([$quantity, $productId]);
    }

    /**
     * Restore stock for a variant.
     */
    public function restoreVariantStock($variantId, $quantity)
    {
        $stmt = $this->pdo->prepare("UPDATE product_variants SET stock = stock + ? WHERE id = ?");
        return $stmt->execute([$quantity, $variantId]);
    }

    /**
     * Add a bank account.
     */
    public function addBankAccount($bankName, $accountNumber, $accountName)
    {
        $stmt = $this->pdo->prepare("INSERT INTO bank_accounts (bank_name, account_number, account_name, is_active) VALUES (?, ?, ?, 1)");
        return $stmt->execute([$bankName, $accountNumber, $accountName]);
    }

    /**
     * Get a bank account by ID.
     */
    public function getBankAccountById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM bank_accounts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Toggle active status of a bank account.
     */
    public function toggleBankAccountStatus($id, $newStatus)
    {
        $stmt = $this->pdo->prepare("UPDATE bank_accounts SET is_active = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
    }

    /**
     * Delete a bank account.
     */
    public function deleteBankAccount($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM bank_accounts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all active bank accounts.
     * 
     * @return array
     */
    public function getActiveBankAccounts(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM bank_accounts WHERE is_active = 1");
        return $stmt->fetchAll();
    }

    /**
     * Get all bank accounts ordered by ID descending.
     * 
     * @return array
     */
    public function getAllBankAccounts(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM bank_accounts ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Check if a promo code exists.
     */
    public function checkPromoCodeExists($code)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM promo_codes WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Check if a promo code exists excluding self.
     */
    public function checkPromoCodeExistsExcludingSelf($code, $id)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM promo_codes WHERE code = ? AND id != ?");
        $stmt->execute([$code, $id]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Add a promo code.
     */
    public function addPromoCode($code, $discountType, $discountValue, $minOrder, $maxUses, $isActive, $expiresAt)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO promo_codes (code, discount_type, discount_value, min_order, max_uses, is_active, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$code, $discountType, $discountValue, $minOrder, $maxUses, $isActive, $expiresAt]);
    }

    /**
     * Update a promo code.
     */
    public function updatePromoCode($id, $code, $discountType, $discountValue, $minOrder, $maxUses, $isActive, $expiresAt)
    {
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
    public function deletePromoCode($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM promo_codes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get a promo code by code string.
     */
    public function getPromoCodeByCode($code)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM promo_codes WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    /**
     * Lock order for status update.
     */
    public function lockOrderForUpdate($orderId)
    {
        $stmt = $this->pdo->prepare("SELECT status FROM orders WHERE id = ? FOR UPDATE");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus($orderId, $status)
    {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }

    /**
     * Get order details joined with bank account info.
     */
    public function getOrderWithBankDetails($orderId)
    {
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
    public function getOrderItemsWithProductInfo($orderId)
    {
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
    public function getAdminPhone()
    {
        $stmtAdmin = $this->pdo->query("SELECT phone FROM users WHERE role = 'admin' LIMIT 1");
        $admin_user = $stmtAdmin->fetch();
        if ($admin_user && !empty($admin_user['phone'])) {
            return preg_replace('/[^0-9]/', '', $admin_user['phone']);
        }
        return '6281234567890';
    }


    public function updateStatusAndSyncStock(int $order_id, string $status): void
    {
        try {
            $this->pdo->beginTransaction();
            $order = $this->lockOrderForUpdate($order_id);
            if (!$order) throw new \Exception("Pesanan tidak ditemukan.");

            $old_status = $order['status'];
            if ($old_status !== $status) {
                $this->updateOrderStatus($order_id, $status);
                $items = $this->getOrderItems($order_id);

                // Jika pesanan di-cancel, kembalikan stok
                if ($status === 'cancelled' && $old_status !== 'cancelled') {
                    foreach ($items as $item) {
                        $qty = intval($item['quantity']);
                        $item['variant_id'] ? $this->restoreVariantStock((int)$item['variant_id'], $qty) : $this->restoreProductStock((int)$item['product_id'], $qty);
                    }
                }
                // Jika cancel dipulihkan, kurangi stok kembali
                if ($old_status === 'cancelled' && $status !== 'cancelled') {
                    foreach ($items as $item) {
                        $qty = intval($item['quantity']);
                        $item['variant_id'] ? $this->deductVariantStock((int)$item['variant_id'], $qty) : $this->deductProductStock((int)$item['product_id'], $qty);
                    }
                }
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function processCheckout(int $user_id, array $postData, array $checkout_keys, array $cart_session): array
    {
        $customer_name = sanitize_input($postData['customer_name'] ?? '');
        $customer_phone = sanitize_input($postData['customer_phone'] ?? '');
        $customer_address = sanitize_input($postData['customer_address'] ?? '');
        $bank_account_id = intval($postData['bank_account_id'] ?? 0);
        $promo_code_id = !empty($postData['promo_code_id']) ? intval($postData['promo_code_id']) : null;

        if (empty($customer_name) || empty($customer_phone) || empty($customer_address) || $bank_account_id <= 0) {
            throw new \Exception('Harap lengkapi semua data pengiriman dan pilih metode pembayaran.');
        }
        if (empty($checkout_keys)) throw new \Exception("Pilih minimal satu produk untuk di-checkout.");

        try {
            $this->pdo->beginTransaction();
            if (!$this->getActiveBankAccountForUpdate($bank_account_id)) throw new \Exception("Metode pembayaran yang dipilih tidak valid.");

            $items_to_process = [];
            $total_pure = 0;

            foreach ($checkout_keys as $cart_key) {
                if (!isset($cart_session[$cart_key])) continue;
                $qty = intval($cart_session[$cart_key]);
                if ($qty <= 0) continue;

                $parts = explode('-', $cart_key);
                $pId = intval($parts[0] ?? 0);
                $vId = intval($parts[1] ?? 0);
                if ($pId <= 0) throw new \Exception("Format produk tidak valid.");

                $product = $this->lockProductForUpdate($pId);
                if (!$product) throw new \Exception("Produk tidak ditemukan.");

                $effective_price = floatval($product['price']);
                $effective_stock = intval($product['stock']);
                $variant_info_str = null;

                if ($vId > 0) {
                    $variant = $this->lockVariantForUpdate($vId, $pId);
                    if (!$variant) throw new \Exception("Varian tidak valid.");
                    $effective_price += floatval($variant['additional_price']);
                    $effective_stock = intval($variant['stock']);
                    $variant_info_str = $variant['variant_name'] . ': ' . $variant['variant_value'];
                }

                if ($qty > $effective_stock) throw new \Exception("Stok tidak mencukupi.");
                $total_pure += ($effective_price * $qty);
                $items_to_process[] = ['cart_key' => $cart_key, 'product_id' => $pId, 'variant_id' => $vId > 0 ? $vId : null, 'variant_info' => $variant_info_str, 'quantity' => $qty, 'price' => $effective_price];
            }

            if (empty($items_to_process)) throw new \Exception("Tidak ada item valid.");

            $discount_amount = 0;
            if ($promo_code_id !== null) {
                $promo = $this->getPromoCodeForUpdate($promo_code_id);
                if (!$promo || !$promo['is_active'] || strtotime($promo['expires_at']) < time() || $promo['used_count'] >= $promo['max_uses'] || $total_pure < $promo['min_order']) {
                    throw new \Exception("Kode promo tidak valid atau syarat tidak terpenuhi.");
                }
                $discount_amount = ($promo['discount_type'] === 'percentage') ? ($promo['discount_value'] / 100) * $total_pure : $promo['discount_value'];
                $discount_amount = min($discount_amount, $total_pure);
                $this->incrementPromoUsage($promo_code_id);
            }

            $unique_code = rand(100, 999);
            $final_total = ($total_pure - $discount_amount) + $unique_code;

            $order_id = $this->createOrder($user_id, $customer_name, $customer_phone, $customer_address, $final_total, $unique_code, $bank_account_id, $promo_code_id, $discount_amount);

            $processed_keys = [];
            foreach ($items_to_process as $item) {
                $this->addOrderItem($order_id, $item['product_id'], $item['variant_id'], $item['variant_info'], $item['quantity'], $item['price']);
                $item['variant_id'] ? $this->deductVariantStock($item['variant_id'], $item['quantity']) : $this->deductProductStock($item['product_id'], $item['quantity']);
                $processed_keys[] = $item['cart_key'];
            }

            $this->pdo->commit();
            return ['order_id' => $order_id, 'final_total' => $final_total, 'unique_code' => $unique_code, 'processed_keys' => $processed_keys];
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Get all promo codes ordered by ID descending.
     * 
     * @return array
     */
    public function getAllPromoCodes(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM promo_codes ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get all orders for admin view.
     * 
     * @return array
     */
    public function getAllOrdersForAdmin(): array
    {
        $stmt = $this->pdo->query("
            SELECT o.*, u.name as buyer_name, b.bank_name, b.account_number, b.account_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN bank_accounts b ON o.bank_account_id = b.id
            ORDER BY o.id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get all order items mapped with product info.
     * 
     * @return array
     */
    public function getAllOrderItemsForAdmin(): array
    {
        $stmt = $this->pdo->query("
            SELECT oi.*, p.name as product_name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
        ");
        return $stmt->fetchAll();
    }
}
