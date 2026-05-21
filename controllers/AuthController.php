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
        $agree_tos = isset($_POST['agree_tos']);
        
        if (empty($name) || empty($email) || empty($password)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Semua field harus diisi!']);
                exit;
            }
            $_SESSION['error'] = "Semua field harus diisi!";
            redirect('index.php?page=register');
        }

        if (!$agree_tos) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Anda harus menyetujui Syarat & Ketentuan serta Kebijakan Privasi.']);
                exit;
            }
            $_SESSION['error'] = "Anda harus menyetujui Syarat & Ketentuan serta Kebijakan Privasi.";
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
        $verification_token = sprintf("%06d", mt_rand(100000, 999999));
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, verification_token) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $role, $verification_token]);
            
            $user_id = $pdo->lastInsertId();
            
            $_SESSION['verify_email'] = $email;

            // Kirim email konfirmasi
            require_once __DIR__ . '/../config/mailer.php';
            $subject = "Verifikasi Pendaftaran Akun NusaBay";
            $body = "Halo $name,\n\nTerima kasih telah mendaftar di NusaBay.\n"
                  . "Silakan masukkan Kode Verifikasi berikut untuk memverifikasi akun Anda:\n\n"
                  . "KODE: $verification_token\n\n"
                  . "Selamat berbelanja!\nNusaBay Team";
            sendMail($email, $subject, $body);

            $_SESSION['success'] = "Pendaftaran berhasil! Kode verifikasi telah dikirim ke email Anda. Silakan verifikasi akun Anda.";
            
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Registrasi berhasil! Silakan verifikasi email Anda...', 
                    'redirect_url' => 'index.php?page=verify'
                ]);
                exit;
            }
            redirect('index.php?page=verify');
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
            if ($user['email_verified_at'] === null) {
                $_SESSION['verify_email'] = $email;
                $verify_url = "index.php?page=verify&email=" . urlencode($email);
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Harap verifikasi email Anda terlebih dahulu.',
                        'redirect_url' => $verify_url
                    ]);
                    exit;
                }
                $_SESSION['error'] = "Harap verifikasi email Anda terlebih dahulu.";
                redirect($verify_url);
            }

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
    
    elseif ($action === 'resend_code') {
        $email = sanitize_input($_POST['email'] ?? '');
        if (empty($email)) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Email harus diisi!']);
                exit;
            }
            $_SESSION['error'] = "Email harus diisi!";
            redirect('index.php?page=verify');
        }

        $stmt = $pdo->prepare("SELECT id, name, email_verified_at FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Email tidak terdaftar!']);
                exit;
            }
            $_SESSION['error'] = "Email tidak terdaftar!";
            redirect('index.php?page=verify');
        }

        if ($user['email_verified_at'] !== null) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Email sudah terverifikasi!']);
                exit;
            }
            $_SESSION['success'] = "Email sudah terverifikasi!";
            redirect('index.php?page=login');
        }

        $new_otp = sprintf("%06d", mt_rand(100000, 999999));
        $stmtUpdate = $pdo->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
        $stmtUpdate->execute([$new_otp, $user['id']]);

        require_once __DIR__ . '/../config/mailer.php';
        $subject = "Verifikasi Pendaftaran Akun NusaBay (Kirim Ulang)";
        $body = "Halo " . $user['name'] . ",\n\nBerikut adalah Kode Verifikasi baru Anda:\n\n"
              . "KODE: $new_otp\n\n"
              . "Selamat berbelanja!\nNusaBay Team";
        sendMail($email, $subject, $body);

        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Kode verifikasi baru berhasil dikirim!']);
            exit;
        }
        $_SESSION['success'] = "Kode verifikasi baru telah dikirim.";
        redirect("index.php?page=verify&email=" . urlencode($email));
    }
} elseif ($action === 'logout') {
    session_destroy();
    redirect('index.php?page=login');
} else {
    redirect('index.php?page=home');
}
