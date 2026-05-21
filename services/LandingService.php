<?php

class LandingService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all configurations.
     */
    public function getAllConfigs() {
        $stmt = $this->pdo->query("SELECT section_key, content_value, type FROM landing_configs");
        $results = $stmt->fetchAll();
        $configs = [];
        foreach ($results as $row) {
            $configs[$row['section_key']] = $row['content_value'];
        }
        return $configs;
    }

    /**
     * Get a specific configuration value.
     */
    public function getConfigValueByKey($key) {
        $stmt = $this->pdo->prepare("SELECT content_value FROM landing_configs WHERE section_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn();
    }

    /**
     * Update configuration value by key.
     */
    public function updateConfigValue($key, $value) {
        $stmt = $this->pdo->prepare("UPDATE landing_configs SET content_value = ? WHERE section_key = ? AND (type = 'text' OR type = 'color')");
        return $stmt->execute([$value, $key]);
    }

    /**
     * Update landing hero image.
     */
    public function updateHeroImage($filename) {
        $stmt = $this->pdo->prepare("UPDATE landing_configs SET content_value = ? WHERE section_key = 'hero_image'");
        return $stmt->execute([$filename]);
    }

    /**
     * Get all banners.
     */
    public function getAllBanners() {
        $stmt = $this->pdo->query("SELECT * FROM banners ORDER BY sort_order ASC, id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get active banners for frontend.
     */
    public function getActiveBanners() {
        $stmt = $this->pdo->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC, id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get single banner.
     */
    public function getBannerById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Add banner.
     */
    public function addBanner($title, $description, $image_url, $link_url, $is_active, $sort_order) {
        $stmt = $this->pdo->prepare("
            INSERT INTO banners (title, description, image_url, link_url, is_active, sort_order)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$title, $description, $image_url, $link_url, $is_active, $sort_order]);
    }

    /**
     * Update banner.
     */
    public function updateBanner($id, $title, $description, $image_url, $link_url, $is_active, $sort_order) {
        $stmt = $this->pdo->prepare("
            UPDATE banners 
            SET title = ?, description = ?, image_url = ?, link_url = ?, is_active = ?, sort_order = ?
            WHERE id = ?
        ");
        return $stmt->execute([$title, $description, $image_url, $link_url, $is_active, $sort_order, $id]);
    }

    /**
     * Delete banner.
     */
    public function deleteBanner($id) {
        $stmt = $this->pdo->prepare("DELETE FROM banners WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
