<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/LandingService.php';
require_once __DIR__ . '/../services/AuthService.php';

class ProfileViewController extends BaseController
{
    public function handle(): void
    {
        // Pastikan pengguna sudah login
        if (!isAuth()) {
            $_SESSION['error'] = "Anda harus login terlebih dahulu untuk mengakses profil.";
            redirect('index.php?page=login');
        }

        $pdo = $this->pdo;
        $landingService = new LandingService($pdo);
        $authService = new AuthService($pdo);

        // Ambil konfigurasi untuk visual (misal primary color)
        $configs = $landingService->getAllConfigs();
        $primary_color = $configs['primary_color'] ?? '#6366f1';

        // Ambil data user aktif
        $user = $authService->getUserById($_SESSION['user_id']);

        require __DIR__ . '/../views/public/profile.php';
    }
}
