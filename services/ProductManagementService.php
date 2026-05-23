<?php

class ProductManagementService
{
    private ProductService $productService;
    private string $uploadDir;

    public function __construct(ProductService $productService, string $uploadDir)
    {
        $this->productService = $productService;
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
    }

    public function create(array $data, array $files): array
    {
        $payload = $this->productPayload($data);
        $upload = $this->storeImage($files['image'] ?? null, true);

        if (!$upload['success']) {
            return $this->error($upload['message']);
        }

        if (!$this->productService->addProduct(
            $payload['category_id'],
            $payload['name'],
            $payload['description'],
            $payload['price'],
            $payload['stock'],
            $upload['path']
        )) {
            return $this->error('Gagal menambahkan produk ke database.');
        }

        return $this->success('Produk berhasil ditambahkan!');
    }

    public function update(array $data, array $files): array
    {
        $id = intval($data['id'] ?? 0);
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return $this->error('Produk tidak ditemukan.');
        }

        $payload = $this->productPayload($data);
        $imageUrl = $product['image_url'];
        $upload = $this->storeImage($files['image'] ?? null, false);

        if (!$upload['success']) {
            return $this->error($upload['message']);
        }

        if (!empty($upload['path'])) {
            $this->deleteLocalImage($imageUrl);
            $imageUrl = $upload['path'];
        }

        if (!$this->productService->updateProduct(
            $id,
            $payload['category_id'],
            $payload['name'],
            $payload['description'],
            $payload['price'],
            $payload['stock'],
            $imageUrl
        )) {
            return $this->error('Gagal memperbarui produk.');
        }

        return $this->success('Produk berhasil diperbarui!');
    }

    public function delete(int $id): array
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return $this->error('Produk tidak ditemukan.');
        }

        $this->deleteLocalImage($product['image_url']);

        if (!$this->productService->deleteProduct($id)) {
            return $this->error('Gagal menghapus produk dari database.');
        }

        return $this->success('Produk berhasil dihapus!');
    }

    private function productPayload(array $data): array
    {
        return [
            'name' => sanitize_input($data['name'] ?? ''),
            'description' => sanitize_input($data['description'] ?? ''),
            'category_id' => !empty($data['category_id']) ? intval($data['category_id']) : null,
            'price' => floatval($data['price'] ?? 0),
            'stock' => intval($data['stock'] ?? 0),
        ];
    }

    private function storeImage(?array $file, bool $required): array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $required ? $this->error('Gambar produk wajib diupload.') : $this->success('Tidak ada gambar baru.', ['path' => null]);
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->error('Gagal menerima file upload.');
        }

        $allowedMimes = ['image/jpeg', 'image/png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMimes, true)) {
            return $this->error('Format file tidak didukung. Harap gunakan JPG atau PNG.');
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        $extension = $mime === 'image/png' ? 'png' : 'jpg';
        $filename = 'prod_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

        if (!move_uploaded_file($file['tmp_name'], $this->uploadDir . $filename)) {
            return $this->error('Gagal memindahkan file upload.');
        }

        return $this->success('Gambar berhasil diupload.', ['path' => 'uploads/' . $filename]);
    }

    private function deleteLocalImage(?string $imageUrl): void
    {
        if (!$imageUrl || strpos($imageUrl, 'uploads/') !== 0) {
            return;
        }

        $path = dirname($this->uploadDir) . '/' . $imageUrl;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private function success(string $message, array $extra = []): array
    {
        return ['success' => true, 'message' => $message] + $extra;
    }

    private function error(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
