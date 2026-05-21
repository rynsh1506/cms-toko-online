<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/LandingService.php';
require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../services/ProductService.php';

class CheckoutViewController extends BaseController
{
    public function handle(): void
    {
        $pdo = $this->pdo;
        // Pastikan pengguna sudah login
        if (!isAuth()) {
            $_SESSION['error'] = "Anda harus login terlebih dahulu untuk melakukan checkout.";
            redirect('index.php?page=login');
        }

        // Pastikan keranjang tidak kosong
        if (empty($_SESSION['cart'])) {
            $_SESSION['error'] = "Keranjang Anda kosong.";
            redirect('index.php?page=cart');
        }

        // Pastikan ada item yang dipilih dari halaman cart
        if (empty($_SESSION['selected_cart_keys'])) {
            $_SESSION['error'] = "Pilih minimal satu produk untuk di-checkout.";
            redirect('index.php?page=cart');
        }

        $landingService = new LandingService($pdo);
        $orderService = new OrderService($pdo);
        $productService = new ProductService($pdo);

        // Fetch Configurations for Dynamic Styles
        $configs = $landingService->getAllConfigs();
        $primary_color = $configs['primary_color'] ?? '#6366f1';

        // Fetch Active Bank Accounts
        $active_banks = [];
        $banks = $pdo->query("SELECT * FROM bank_accounts WHERE is_active = 1")->fetchAll();
        foreach ($banks as $bank) {
            $active_banks[] = $bank;
        }

        // Hitung ringkasan HANYA untuk item yang dipilih (dicentang)
        $cart_items = [];
        $total_price = 0;
        $checkout_keys = $_SESSION['selected_cart_keys'];

        foreach ($checkout_keys as $cart_key) {
            // Lewati jika karena alasan tertentu key tidak ada di keranjang aktual
            if (!isset($_SESSION['cart'][$cart_key])) continue;

            $qty = intval($_SESSION['cart'][$cart_key]);

            // Ambil detail produk & varian dari Meta Session biar cepat
            if (isset($_SESSION['cart_meta'][$cart_key])) {
                $meta = $_SESSION['cart_meta'][$cart_key];
                $name = $meta['name'];
                if (!empty($meta['variant_info'])) {
                    $name .= ' (' . $meta['variant_info'] . ')'; // Tambahkan label varian ke nama produk
                }
                $price = $meta['price'];
            } else {
                // Fallback jika meta hilang
                $parts = explode('-', $cart_key);
                $pId = intval($parts[0] ?? 0);
                $vId = intval($parts[1] ?? 0);

                $p = $productService->getProductById($pId);
                if (!$p) continue;

                $name = $p['name'];
                $price = floatval($p['price']);

                if ($vId > 0) {
                    $v = $productService->getVariantByIdAndProductId($vId, $pId);
                    if ($v) {
                        $name .= ' (' . $v['variant_name'] . ': ' . $v['variant_value'] . ')';
                        $price += floatval($v['additional_price']);
                    }
                }
            }

            $subtotal = $price * $qty;
            $total_price += $subtotal;

            $cart_items[] = [
                'name' => $name,
                'qty' => $qty,
                'subtotal' => $subtotal
            ];
        }

        // Jika ternyata keranjang filter kosong (misal dari sesi usang)
        if (empty($cart_items)) {
            $_SESSION['error'] = "Pesanan tidak valid, silakan ulangi.";
            redirect('index.php?page=cart');
        }

        require __DIR__ . '/../views/public/checkout.php';
    }
}
