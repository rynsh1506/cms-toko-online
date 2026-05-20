<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Sederhananya, jika mendaftar pertama kali, kita bisa buat role 'admin' atau 'user'.
        // Untuk Pro-Store CMS ini, biarkan default 'admin' untuk demo jika tabel kosong, tapi untuk kemanan kita biarkan 'user',
        // atau kita set 'admin' secara hardcode untuk user pertama.
        
        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Semua field harus diisi!";
            redirect('index.php?page=register');
        }

        // Cek apakah email sudah ada
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Email sudah terdaftar!";
            redirect('index.php?page=register');
        }

        // Cek apakah tabel kosong, jika iya, jadikan admin
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();
        $role = ($count == 0) ? 'admin' : 'user';

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $role]);
            
            $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
            redirect('index.php?page=login');
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Gagal menyimpan data: " . $e->getMessage();
            redirect('index.php?page=register');
        }
    } 
    
    elseif ($action === 'login') {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Email dan password harus diisi!";
            redirect('index.php?page=login');
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login sukses
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                redirect('index.php?page=admin');
            } else {
                redirect('index.php?page=home');
            }
        } else {
            $_SESSION['error'] = "Email atau password salah!";
            redirect('index.php?page=login');
        }
    }
} elseif ($action === 'logout') {
    session_destroy();
    redirect('index.php?page=login');
} else {
    redirect('index.php?page=home');
}
