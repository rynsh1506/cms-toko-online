<?php

class CategoryService
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function create(array $data): array
    {
        $name = sanitize_input($data['name'] ?? '');
        $icon = sanitize_input($data['icon'] ?? '');
        $color = sanitize_input($data['color'] ?? '');

        if ($name === '') {
            return $this->error('Nama kategori wajib diisi.');
        }

        $slug = $this->makeUniqueSlug($name);

        if (!$this->productService->addCategory($name, $slug, $icon, $color)) {
            return $this->error('Gagal menyimpan kategori.');
        }

        return $this->success('Kategori berhasil ditambahkan!');
    }

    public function update(array $data): array
    {
        $id = intval($data['id'] ?? 0);
        $name = sanitize_input($data['name'] ?? '');
        $icon = sanitize_input($data['icon'] ?? '');
        $color = sanitize_input($data['color'] ?? '');

        if ($name === '') {
            return $this->error('Nama kategori wajib diisi.');
        }

        $slug = $this->makeUniqueSlug($name, $id);

        if (!$this->productService->updateCategory($id, $name, $slug, $icon, $color)) {
            return $this->error('Gagal memperbarui kategori.');
        }

        return $this->success('Kategori berhasil diperbarui!');
    }

    public function delete(int $id): array
    {
        $this->productService->nullifyProductsCategory($id);

        if (!$this->productService->deleteCategory($id)) {
            return $this->error('Gagal menghapus kategori.');
        }

        return $this->success('Kategori berhasil dihapus!');
    }

    private function makeUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $exists = $excludeId
            ? $this->productService->checkCategorySlugExistsExcludingSelf($slug, $excludeId)
            : $this->productService->checkCategorySlugExists($slug);

        return $exists ? $slug . '-' . time() : $slug;
    }

    private function success(string $message): array
    {
        return ['success' => true, 'message' => $message];
    }

    private function error(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
