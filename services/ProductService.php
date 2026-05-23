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
            $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name,
                (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.id) as variant_count,
                (SELECT COALESCE(SUM(stock), 0) FROM product_variants pv WHERE pv.product_id = p.id) as total_variant_stock
                FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? ORDER BY p.id DESC");
            $stmt->execute([$categoryId]);
        } else {
            $stmt = $this->pdo->query("SELECT p.*, c.name as category_name,
                (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.id) as variant_count,
                (SELECT COALESCE(SUM(stock), 0) FROM product_variants pv WHERE pv.product_id = p.id) as total_variant_stock
                FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
        }
        return $stmt->fetchAll();
    }

    /**
     * Count products with filters (category and search).
     */
    public function countProductsFiltered($categoryId = 'all', $searchQuery = '') {
        $where_clauses = [];
        $query_params = [];
        if ($categoryId !== 'all') {
            $where_clauses[] = "category_id = ?";
            $query_params[] = $categoryId;
        }
        if ($searchQuery !== '') {
            $where_clauses[] = "(name LIKE ? OR description LIKE ?)";
            $query_params[] = "%$searchQuery%";
            $query_params[] = "%$searchQuery%";
        }
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = "WHERE " . implode(" AND ", $where_clauses);
        }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM products $where_sql");
        $stmt->execute($query_params);
        return $stmt->fetchColumn();
    }

    /**
     * Get products paginated with filters.
     */
    public function getProductsFilteredPaginated($categoryId = 'all', $searchQuery = '', $offset = 0, $limit = 12) {
        $where_clauses = [];
        $query_params = [];
        if ($categoryId !== 'all') {
            $where_clauses[] = "p.category_id = ?";
            $query_params[] = $categoryId;
        }
        if ($searchQuery !== '') {
            $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $query_params[] = "%$searchQuery%";
            $query_params[] = "%$searchQuery%";
        }
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = "WHERE " . implode(" AND ", $where_clauses);
        }

        $query_params[] = $limit;
        $query_params[] = $offset;

        $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name,
            (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.id) as variant_count,
            (SELECT COALESCE(SUM(stock), 0) FROM product_variants pv WHERE pv.product_id = p.id) as total_variant_stock
            FROM products p LEFT JOIN categories c ON p.category_id = c.id $where_sql ORDER BY p.id DESC LIMIT ? OFFSET ?");
        // Limit and Offset must be bound as INT, so execute with array is tricky if PDO emulation is off,
        // but normally it works if PDO::ATTR_EMULATE_PREPARES is true. Better to bind.

        foreach ($query_params as $index => $param) {
            $stmt->bindValue($index + 1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get products paginated with advanced filters for Admin.
     */
    public function getAdminProductsPaginated($filters, $page = 1, $perPage = 10) {
        $where_clauses = [];
        $query_params = [];
        
        if (!empty($filters['search'])) {
            $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $query_params[] = "%{$filters['search']}%";
            $query_params[] = "%{$filters['search']}%";
        }
        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $where_clauses[] = "p.category_id = ?";
            $query_params[] = $filters['category'];
        }
        if (!empty($filters['min_price'])) {
            $where_clauses[] = "p.price >= ?";
            $query_params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where_clauses[] = "p.price <= ?";
            $query_params[] = $filters['max_price'];
        }
        if (!empty($filters['start_date'])) {
            $where_clauses[] = "DATE(p.created_at) >= ?";
            $query_params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where_clauses[] = "DATE(p.created_at) <= ?";
            $query_params[] = $filters['end_date'];
        }

        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = "WHERE " . implode(" AND ", $where_clauses);
        }

        $offset = ($page - 1) * $perPage;

        $query = "SELECT SQL_CALC_FOUND_ROWS p.*, c.name as category_name,
            (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.id) as variant_count,
            (SELECT COALESCE(SUM(stock), 0) FROM product_variants pv WHERE pv.product_id = p.id) as total_variant_stock
            FROM products p LEFT JOIN categories c ON p.category_id = c.id 
            $where_sql 
            ORDER BY p.id DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($query);
        
        $paramIndex = 1;
        foreach ($query_params as $param) {
            $stmt->bindValue($paramIndex++, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue($paramIndex++, (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex, (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        $total = $this->pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
        
        return [
            'data' => $data,
            'total_items' => (int)$total,
            'current_page' => (int)$page,
            'per_page' => (int)$perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get product detail by ID.
     */
    public function getProductById($id) {
        $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name,
            (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.id) as variant_count,
            (SELECT COALESCE(SUM(stock), 0) FROM product_variants pv WHERE pv.product_id = p.id) as total_variant_stock
            FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
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
     * Get all categories ordered by ID descending.
     * 
     * @return array
     */
    public function getAllCategoriesDesc(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY id DESC");
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

    /**
     * Check if category slug exists.
     */
    public function checkCategorySlugExists($slug) {
        $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Check if category slug exists excluding self.
     */
    public function checkCategorySlugExistsExcludingSelf($slug, $id) {
        $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Add a category.
     */
    public function addCategory($name, $slug, $icon, $color) {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, slug, icon, color) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $slug, $icon, $color]);
    }

    /**
     * Update a category.
     */
    public function updateCategory($id, $name, $slug, $icon, $color) {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, slug = ?, icon = ?, color = ? WHERE id = ?");
        return $stmt->execute([$name, $slug, $icon, $color, $id]);
    }

    /**
     * Nullify product category relations.
     */
    public function nullifyProductsCategory($categoryId) {
        $stmt = $this->pdo->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?");
        return $stmt->execute([$categoryId]);
    }

    /**
     * Delete a category.
     */
    public function deleteCategory($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get variant by ID.
     */
    public function getVariantById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM product_variants WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Add a product variant.
     */
    public function addVariant($productId, $variantName, $variantValue, $additionalPrice, $stock) {
        $stmt = $this->pdo->prepare("INSERT INTO product_variants (product_id, variant_name, variant_value, additional_price, stock) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$productId, $variantName, $variantValue, $additionalPrice, $stock])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Update a product variant.
     */
    public function updateVariant($id, $variantName, $variantValue, $additionalPrice, $stock) {
        $stmt = $this->pdo->prepare("UPDATE product_variants SET variant_name = ?, variant_value = ?, additional_price = ?, stock = ? WHERE id = ?");
        return $stmt->execute([$variantName, $variantValue, $additionalPrice, $stock, $id]);
    }

    /**
     * Delete a product variant.
     */
    public function deleteVariant($id) {
        $stmt = $this->pdo->prepare("DELETE FROM product_variants WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
