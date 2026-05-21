<?php

class VariantService
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function listByProduct(int $productId): array
    {
        if ($productId <= 0) {
            return $this->error('ID Produk tidak valid.');
        }

        try {
            return [
                'status' => 'success',
                'variants' => $this->productService->getVariantsByProductId($productId),
            ];
        } catch (Exception $e) {
            return $this->error('Gagal memuat varian: Terjadi kesalahan database.');
        }
    }

    public function create(array $data): array
    {
        $productId = intval($data['product_id'] ?? 0);
        $name = trim(sanitize_input($data['variant_name'] ?? ''));
        $value = trim(sanitize_input($data['variant_value'] ?? ''));
        $additionalPrice = floatval($data['additional_price'] ?? 0);
        $stock = intval($data['stock'] ?? 0);

        if ($productId <= 0 || $name === '' || $value === '') {
            return $this->error('Nama varian, nilai, dan produk wajib diisi.');
        }

        try {
            if (!$this->productService->getProductById($productId)) {
                return $this->error('Produk tidak ditemukan di database.');
            }

            $newId = $this->productService->addVariant($productId, $name, $value, $additionalPrice, $stock);
            if (!$newId) {
                return $this->error('Gagal menyimpan varian.');
            }

            return [
                'status' => 'success',
                'message' => 'Varian produk berhasil ditambahkan.',
                'variant' => [
                    'id' => $newId,
                    'product_id' => $productId,
                    'variant_name' => $name,
                    'variant_value' => $value,
                    'additional_price' => $additionalPrice,
                    'stock' => $stock,
                ],
            ];
        } catch (Exception $e) {
            return $this->error('Gagal menyimpan varian. Silakan coba lagi.');
        }
    }

    public function update(array $data): array
    {
        $id = intval($data['id'] ?? 0);
        $name = trim(sanitize_input($data['variant_name'] ?? ''));
        $value = trim(sanitize_input($data['variant_value'] ?? ''));
        $additionalPrice = floatval($data['additional_price'] ?? 0);
        $stock = intval($data['stock'] ?? 0);

        if ($id <= 0 || $name === '' || $value === '') {
            return $this->error('Data tidak lengkap (Nama Varian dan Nilai wajib diisi).');
        }

        try {
            $this->productService->updateVariant($id, $name, $value, $additionalPrice, $stock);
            return ['status' => 'success', 'message' => 'Data varian berhasil diperbarui.'];
        } catch (Exception $e) {
            return $this->error('Gagal memperbarui data varian.');
        }
    }

    public function delete(int $id): array
    {
        if ($id <= 0) {
            return $this->error('ID Varian tidak valid.');
        }

        try {
            $this->productService->deleteVariant($id);
            return ['status' => 'success', 'message' => 'Varian berhasil dihapus.'];
        } catch (Exception $e) {
            return $this->error('Gagal menghapus varian.');
        }
    }

    private function error(string $message): array
    {
        return ['status' => 'error', 'message' => $message];
    }
}
