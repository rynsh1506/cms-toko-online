<?php

class LandingService
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get all configurations.
     */
    public function getAllConfigs()
    {
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
    public function getConfigValueByKey($key)
    {
        $stmt = $this->pdo->prepare("SELECT content_value FROM landing_configs WHERE section_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn();
    }

    /**
     * Update configuration value by key.
     */
    public function updateConfigValue($key, $value)
    {
        $stmt = $this->pdo->prepare("UPDATE landing_configs SET content_value = ? WHERE section_key = ? AND (type = 'text' OR type = 'color')");
        return $stmt->execute([$value, $key]);
    }

    /**
     * Update landing hero image.
     */
    public function updateHeroImage($filename)
    {
        $stmt = $this->pdo->prepare("UPDATE landing_configs SET content_value = ? WHERE section_key = 'hero_image'");
        return $stmt->execute([$filename]);
    }

    /**
     * Get all banners.
     */
    public function getAllBanners()
    {
        $stmt = $this->pdo->query("SELECT * FROM banners ORDER BY sort_order ASC, id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get active banners for frontend.
     */
    public function getActiveBanners()
    {
        $stmt = $this->pdo->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC, id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get single banner.
     */
    public function getBannerById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Add banner.
     */
    public function addBanner($title, $description, $image_url, $link_url, $is_active, $sort_order)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO banners (title, description, image_url, link_url, is_active, sort_order)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$title, $description, $image_url, $link_url, $is_active, $sort_order]);
    }

    /**
     * Update banner.
     */
    public function updateBanner($id, $title, $description, $image_url, $link_url, $is_active, $sort_order)
    {
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
    public function deleteBanner($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM banners WHERE id = ?");
        return $stmt->execute([$id]);
    }


    public function addBannerWithImage(array $data, ?array $file): bool
    {
        $title = sanitize_input($data['title'] ?? '');
        $description = sanitize_input($data['description'] ?? '');
        $link_url = sanitize_input($data['link_url'] ?? '');
        $sort_order = intval($data['sort_order'] ?? 0);
        $is_active = intval($data['is_active'] ?? 1);

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) throw new \Exception("Gambar banner wajib diupload.");

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png'])) throw new \Exception("Format gambar banner tidak didukung. Gunakan JPG atau PNG.");

        $ext = ($mime === 'image/png') ? 'png' : 'jpg';
        $new_filename = 'banner_' . time() . '_' . rand(100, 999) . '.' . $ext;
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) throw new \Exception("Gagal memindahkan file upload.");

        if (!$this->addBanner($title, $description, 'uploads/' . $new_filename, $link_url, $is_active, $sort_order)) throw new \Exception("Gagal menyimpan banner ke database.");
        return true;
    }

    public function updateBannerWithImage(array $data, ?array $file): bool
    {
        $id = intval($data['id'] ?? 0);
        $title = sanitize_input($data['title'] ?? '');
        $description = sanitize_input($data['description'] ?? '');
        $link_url = sanitize_input($data['link_url'] ?? '');
        $sort_order = intval($data['sort_order'] ?? 0);
        $is_active = intval($data['is_active'] ?? 1);

        $banner = $this->getBannerById($id);
        if (!$banner) throw new \Exception("Banner tidak ditemukan.");
        $image_url = $banner['image_url'];

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $mime = mime_content_type($file['tmp_name']);
            if (!in_array($mime, ['image/jpeg', 'image/png'])) throw new \Exception("Format gambar banner tidak didukung.");

            $ext = ($mime === 'image/png') ? 'png' : 'jpg';
            $new_filename = 'banner_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) throw new \Exception("Gagal memindahkan file upload.");

            if ($image_url && strpos($image_url, 'uploads/') === 0 && file_exists(__DIR__ . '/../' . $image_url)) {
                @unlink(__DIR__ . '/../' . $image_url);
            }
            $image_url = 'uploads/' . $new_filename;
        }

        if (!$this->updateBanner($id, $title, $description, $image_url, $link_url, $is_active, $sort_order)) throw new \Exception("Gagal memperbarui database.");
        return true;
    }

    public function deleteBannerWithImage(int $id): bool
    {
        $banner = $this->getBannerById($id);
        if (!$banner) throw new \Exception("Banner tidak ditemukan.");

        $image_url = $banner['image_url'];
        if ($image_url && strpos($image_url, 'uploads/') === 0 && file_exists(__DIR__ . '/../' . $image_url)) {
            @unlink(__DIR__ . '/../' . $image_url);
        }

        return $this->deleteBanner($id);
    }
}
