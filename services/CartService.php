<?php

class CartService
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
        $this->ensureCartSession();
    }

    public function add(array $data): array
    {
        $productId = intval($data['product_id'] ?? 0);
        $variantId = intval($data['variant_id'] ?? 0);
        $quantity = max(1, intval($data['quantity'] ?? 1));
        $variantInfo = trim(sanitize_input($data['variant_info'] ?? ''));

        $cartItem = $this->buildCartItem($productId, $variantId, $quantity, $variantInfo);
        if (($cartItem['status'] ?? '') === 'error') {
            return $cartItem;
        }

        $cartKey = $cartItem['cart_key'];
        $nextQuantity = ($_SESSION['cart'][$cartKey] ?? 0) + $quantity;

        if ($nextQuantity > $cartItem['stock']) {
            return $this->error("Stok tidak mencukupi (Tersedia: {$cartItem['stock']}).");
        }

        $_SESSION['cart'][$cartKey] = $nextQuantity;
        $_SESSION['cart_meta'][$cartKey] = $cartItem['meta'];

        return $this->success(
            'Berhasil menambahkan ' . htmlspecialchars($cartItem['meta']['name']) . ' ke keranjang.',
            [
                'cart_count' => $this->cartCount(),
                'redirect' => 'index.php?page=cart',
            ]
        );
    }

    public function directCheckout(array $data): array
    {
        $productId = intval($data['product_id'] ?? 0);
        $variantId = intval($data['variant_id'] ?? 0);
        $quantity = max(1, intval($data['quantity'] ?? 1));
        $variantInfo = trim(sanitize_input($data['variant_info'] ?? ''));

        $cartItem = $this->buildCartItem($productId, $variantId, $quantity, $variantInfo);
        if (($cartItem['status'] ?? '') === 'error') {
            return $cartItem;
        }

        if ($quantity > $cartItem['stock']) {
            return $this->error("Stok tidak mencukupi (Tersedia: {$cartItem['stock']}).");
        }

        $cartKey = $cartItem['cart_key'];
        $_SESSION['cart'][$cartKey] = $quantity;
        $_SESSION['cart_meta'][$cartKey] = $cartItem['meta'];
        $_SESSION['selected_cart_keys'] = [$cartKey];

        return $this->success('Item siap checkout.', ['redirect' => 'index.php?page=checkout']);
    }

    public function selectCheckout(array $keys): array
    {
        $_SESSION['selected_cart_keys'] = $keys;

        return $this->success('Item checkout dipilih.');
    }

    public function remove(string $cartKey): array
    {
        unset($_SESSION['cart'][$cartKey], $_SESSION['cart_meta'][$cartKey]);

        return $this->success('Produk dihapus dari keranjang.', $this->summary());
    }

    public function updateQuantity(string $cartKey, int $quantity): array
    {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$cartKey], $_SESSION['cart_meta'][$cartKey]);
            return [
                'status' => 'removed',
                'message' => 'Produk dihapus karena kuantitas diatur 0.',
            ];
        }

        $cartMeta = $this->getCartMeta($cartKey);
        $stock = intval($cartMeta['stock']);
        $price = floatval($cartMeta['price']);
        $message = '';

        if ($quantity > $stock) {
            $quantity = $stock > 0 ? $stock : 1;
            $message = "Jumlah disesuaikan ke batas maksimal ketersediaan stok (Tersedia: {$stock}).";
        }

        $_SESSION['cart'][$cartKey] = $quantity;

        return $this->success(
            'Kuantitas keranjang diperbarui.',
            [
                'qty' => $quantity,
                'subtotal' => $price * $quantity,
                'error_message' => $message,
            ] + $this->summary()
        );
    }

    public function clear(): array
    {
        $_SESSION['cart'] = [];
        $_SESSION['cart_meta'] = [];

        return $this->success('Keranjang dikosongkan.');
    }

    private function buildCartItem(int $productId, int $variantId, int $quantity, string $variantInfo): array
    {
        $product = $this->productService->getProductById($productId);
        if (!$product) {
            return $this->error('Produk tidak ditemukan.');
        }

        $variant = null;
        $stock = intval($product['stock']);

        if ($variantId > 0) {
            $variant = $this->productService->getVariantByIdAndProductId($variantId, $productId);
            if (!$variant) {
                return $this->error('Varian tidak valid.');
            }

            $stock = intval($variant['stock']);
        }

        $additionalPrice = $variant ? floatval($variant['additional_price']) : 0;
        $cartKey = $productId . '-' . $variantId;

        return [
            'success' => true,
            'cart_key' => $cartKey,
            'stock' => $stock,
            'quantity' => $quantity,
            'meta' => [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name' => $product['name'],
                'base_price' => floatval($product['price']),
                'price' => floatval($product['price']) + $additionalPrice,
                'image_url' => $product['image_url'],
                'stock' => $stock,
                'variant_info' => $variantInfo,
            ],
        ];
    }

    private function getCartMeta(string $cartKey): array
    {
        if (!empty($_SESSION['cart_meta'][$cartKey])) {
            return $_SESSION['cart_meta'][$cartKey];
        }

        [$productId, $variantId] = array_map('intval', explode('-', $cartKey) + [0, 0]);
        $product = $this->productService->getProductById($productId);

        if (!$product) {
            return ['stock' => 0, 'price' => 0];
        }

        $stock = intval($product['stock']);
        $price = floatval($product['price']);

        if ($variantId > 0) {
            $variant = $this->productService->getVariantByIdAndProductId($variantId, $productId);
            if ($variant) {
                $stock = intval($variant['stock']);
                $price += floatval($variant['additional_price']);
            }
        }

        return ['stock' => $stock, 'price' => $price];
    }

    private function summary(): array
    {
        $totalPrice = 0;
        $cartCount = 0;

        foreach ($_SESSION['cart'] as $key => $quantity) {
            $price = isset($_SESSION['cart_meta'][$key]) ? floatval($_SESSION['cart_meta'][$key]['price']) : 0;
            $totalPrice += $price * $quantity;
            $cartCount += $quantity;
        }

        return ['total_price' => $totalPrice, 'cart_count' => $cartCount];
    }

    private function cartCount(): int
    {
        return array_sum($_SESSION['cart']);
    }

    private function ensureCartSession(): void
    {
        $_SESSION['cart'] ??= [];
        $_SESSION['cart_meta'] ??= [];
    }

    private function success(string $message, array $extra = []): array
    {
        return ['status' => 'success', 'message' => $message] + $extra;
    }

    private function error(string $message): array
    {
        return ['status' => 'error', 'message' => $message];
    }
}
