<?php
require_once __DIR__ . '/../config/mailer.php';

class AuthFlowService
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(array $data): array
    {
        $name = sanitize_input($data['name'] ?? '');
        $email = sanitize_input($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $agreeTos = isset($data['agree_tos']);

        if ($name === '' || $email === '' || $password === '') {
            return $this->error('Semua field harus diisi!', 'index.php?page=register');
        }

        // Backend password complexity check
        if (
            strlen($password) < 8 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[^a-zA-Z0-9]/', $password)
        ) {
            return $this->error(
                'Password minimal 8 karakter dan harus mengandung huruf besar, huruf kecil, angka, serta simbol.',
                'index.php?page=register'
            );
        }

        if (!$agreeTos) {
            return $this->error('Anda harus menyetujui Syarat & Ketentuan serta Kebijakan Privasi.', 'index.php?page=register');
        }

        if ($this->authService->isEmailRegistered($email)) {
            return $this->error('Email sudah terdaftar!', 'index.php?page=register');
        }

        try {
            $role = $this->authService->countUsers() === 0 ? 'admin' : 'user';
            $user = $this->authService->registerUser($name, $email, $password, $role);
            $this->sendVerificationMail($email, $name, $user['verification_token']);

            return $this->success(
                'Registrasi berhasil! Silakan verifikasi email Anda...',
                'index.php?page=verify',
                [
                    'flash' => 'Pendaftaran berhasil! Kode verifikasi telah dikirim ke email Anda. Silakan verifikasi akun Anda.',
                    'session' => ['verify_email' => $email],
                ]
            );
        } catch (PDOException $e) {
            return $this->error('Gagal menyimpan data: ' . $e->getMessage(), 'index.php?page=register');
        }
    }

    public function login(array $data): array
    {
        $email = sanitize_input($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if ($email === '' || $password === '') {
            return $this->error('Email dan password harus diisi!', 'index.php?page=login');
        }

        $user = $this->authService->authenticate($email, $password);
        if (!$user) {
            return $this->error('Email atau password salah!', 'index.php?page=login');
        }

        if ($user['email_verified_at'] === null) {
            $verifyUrl = 'index.php?page=verify&email=' . urlencode($email);
            return $this->error(
                'Harap verifikasi email Anda terlebih dahulu.',
                $verifyUrl,
                ['session' => ['verify_email' => $email]]
            );
        }

        $redirectUrl = $user['role'] === 'admin' ? 'index.php?page=admin' : 'index.php?page=home';

        return $this->success(
            'Login berhasil! Mengalihkan...',
            $redirectUrl,
            [
                'session' => [
                    'user_id' => $user['id'],
                    'name' => $user['name'],
                    'role' => $user['role'],
                ],
            ]
        );
    }

    public function resendCode(array $data): array
    {
        $email = sanitize_input($data['email'] ?? '');

        if ($email === '') {
            return $this->error('Email harus diisi!', 'index.php?page=verify');
        }

        $user = $this->authService->getUserByEmail($email);
        if (!$user) {
            return $this->error('Email tidak terdaftar!', 'index.php?page=verify');
        }

        if ($user['email_verified_at'] !== null) {
            return $this->success('Email sudah terverifikasi!', 'index.php?page=login');
        }

        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $this->authService->updateVerificationToken($user['id'], $otp);
        $this->sendVerificationMail($email, $user['name'], $otp, true);

        return $this->success(
            'Kode verifikasi baru berhasil dikirim!',
            'index.php?page=verify&email=' . urlencode($email),
            ['flash' => 'Kode verifikasi baru telah dikirim.']
        );
    }

    private function sendVerificationMail(string $email, string $name, string $token, bool $resend = false): void
    {
        $subject = $resend
            ? 'Verifikasi Pendaftaran Akun NusaBay (Kirim Ulang)'
            : 'Verifikasi Pendaftaran Akun NusaBay';

        $body = "Halo {$name},\n\n"
            . ($resend ? "Berikut adalah Kode Verifikasi baru Anda:\n\n" : "Terima kasih telah mendaftar di NusaBay.\nSilakan masukkan Kode Verifikasi berikut untuk memverifikasi akun Anda:\n\n")
            . "KODE: {$token}\n\n"
            . "Selamat berbelanja!\nNusaBay Team";

        sendMail($email, $subject, $body);
    }

    private function success(string $message, string $redirectUrl, array $extra = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'redirect_url' => $redirectUrl,
        ] + $extra;
    }

    private function error(string $message, string $redirectUrl, array $extra = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'redirect_url' => $redirectUrl,
        ] + $extra;
    }
}
