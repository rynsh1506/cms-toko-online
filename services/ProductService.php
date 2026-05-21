<?php

class ProductService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all products (optionally filter by category).
     */
    public function getAllProducts($categoryId = null) {
        if ($categoryId) {
            $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? ORDER BY p.id DESC");
            $stmt->execute([$categoryId]);
        } else {
            $stmt = $this->pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
        }
        return $stmt->fetchAll();
    }

    /**
     * Get product detail by ID.
     */
    public function getProductById($id) {
        $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Add a new product to the catalog.
     */
    public function addProduct($categoryId, $name, $description, $price, $stock, $imageUrl) {
        $stmt = $this->pdo->prepare("INSERT INTO products (category_id, name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$categoryId, $name, $description, $price, $stock, $imageUrl]);
    }

    /**
     * Update an existing product.
     */
    public function updateProduct($id, $categoryId, $name, $description, $price, $stock, $imageUrl) {
        $stmt = $this->pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, image_url = ? WHERE id = ?");
        return $stmt->execute([$categoryId, $name, $description, $price, $stock, $imageUrl, $id]);
    }

    /**
     * Delete a product by its ID.
     */
    public function deleteProduct($id) {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Deduct stock for a product.
     */
    public function deductStock($id, $quantity) {
        $stmt = $this->pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        return $stmt->execute([$quantity, $id, $quantity]);
    }

    /**
     * Get all categories.
     */
    public function getCategories() {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get all variants of a product.
     */
    public function getVariantsByProductId($productId) {
        $stmt = $this->pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Get specific variant details by ID and product ID.
     */
    public function getVariantByIdAndProductId($variantId, $productId) {
        $stmt = $this->pdo->prepare("SELECT * FROM product_variants WHERE id = ? AND product_id = ?");
        $stmt->execute([$variantId, $productId]);
        return $stmt->fetch();
    }
}
