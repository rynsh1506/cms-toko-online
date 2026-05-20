<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($name) || empty($email) || empty($password)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Semua field harus diisi!']);
                exit;
            }
            $_SESSION['error'] = "Semua field harus diisi!";
            redirect('index.php?page=register');
        }

        // Cek apakah email sudah ada
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar!']);
                exit;
            }
            $_SESSION['error'] = "Email sudah terdaftar!";
            redirect('index.php?page=register');
        }

        // Cek apakah tabel kosong, jika iya, jadikan admin
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();
        $role = ($count == 0) ? 'admin' : 'user';

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $verification_token = bin2hex(random_bytes(32));
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, verification_token) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $role, $verification_token]);
            
            $user_id = $pdo->lastInsertId();
            
            // Auto Login
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;

            // Kirim email konfirmasi
            require_once __DIR__ . '/../config/mailer.php';
            $verify_link = base_url("index.php?page=verify_email&token=" . $verification_token);
            $subject = "Verifikasi Pendaftaran Akun Pro-Store";
            $body = "Halo $name,\n\nTerima kasih telah mendaftar di Pro-Store CMS.\n"
                  . "Silakan klik link berikut untuk memverifikasi akun Anda:\n"
                  . "$verify_link\n\n"
                  . "Selamat berbelanja!\nPro-Store Team";
            sendMail($email, $subject, $body);

            $_SESSION['success'] = "Pendaftaran berhasil! Akun Anda telah aktif dan login otomatis. Silakan verifikasi email Anda (tautan verifikasi tersimulasi di logs/emails.log).";
            
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Registrasi berhasil! Mengalihkan...', 
                    'redirect_url' => 'index.php?page=home'
                ]);
                exit;
            }
            redirect('index.php?page=home');
        } catch (\PDOException $e) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "Gagal menyimpan data: " . $e->getMessage()]);
                exit;
            }
            $_SESSION['error'] = "Gagal menyimpan data: " . $e->getMessage();
            redirect('index.php?page=register');
        }
    } 
    
    elseif ($action === 'login') {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Email dan password harus diisi!']);
                exit;
            }
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
            
            $redirect_url = ($user['role'] === 'admin') ? 'index.php?page=admin' : 'index.php?page=home';
            
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login berhasil! Mengalihkan...', 
                    'redirect_url' => $redirect_url
                ]);
                exit;
            }
            redirect($redirect_url);
        } else {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Email atau password salah!']);
                exit;
            }
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
