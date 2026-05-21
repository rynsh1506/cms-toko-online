<?php
require_once __DIR__ . '/BaseController.php';

class AdminPageController extends BaseController
{
    private string $adminPage;

    public function __construct(PDO $pdo, string $adminPage)
    {
        parent::__construct($pdo);
        $this->adminPage = $adminPage;
    }

    public function handle(): void
    {
        checkAdmin();
        $pdo = $this->pdo;
        $admin_page = $this->adminPage;
        require __DIR__ . '/../views/admin/layout.php';
    }
}
