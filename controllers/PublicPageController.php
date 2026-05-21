<?php
require_once __DIR__ . '/BaseController.php';

class PublicPageController extends BaseController
{
    private string $viewFile;

    public function __construct(PDO $pdo, string $viewFile)
    {
        parent::__construct($pdo);
        $this->viewFile = $viewFile;
    }

    public function handle(): void
    {
        $pdo = $this->pdo;
        require __DIR__ . '/../views/public/' . $this->viewFile;
    }
}
