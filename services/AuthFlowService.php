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
        $passwordConfirm = $data['password_confirm'] ?? '';
        $agreeTos = isset($data['agree_tos']);

        if ($name === '' || $email === '' || $password === '') {
            return $this->error('Semua field harus diisi!', 'index.php?page=register');
        }

        if ($password !== $passwordConfirm) {
            return $this->error('Konfirmasi password tidak cocok!', 'index.php?page=register');
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

    // ===== FORGOT PASSWORD FLOW =====

    public function forgotPassword(array $data): array
    {
        $email = sanitize_input($data['email'] ?? '');

        if ($email === '') {
            return $this->error('Email harus diisi!', 'index.php?page=forgot_password');
        }

        $user = $this->authService->getUserByEmail($email);
        if (!$user) {
            return $this->error('Email tidak terdaftar di sistem kami.', 'index.php?page=forgot_password');
        }

        if ($user['email_verified_at'] === null) {
            return $this->error('Akun belum diverifikasi. Silakan verifikasi email terlebih dahulu.', 'index.php?page=verify&email=' . urlencode($email));
        }

        $otp = sprintf('%06d', random_int(100000, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $this->authService->setResetToken($user['id'], $otp, $expiresAt);
        $this->sendResetMail($email, $user['name'], $otp);

        return $this->success(
            'Kode OTP telah dikirim ke email Anda.',
            'index.php?page=reset_verify',
            [
                'flash' => 'Kode reset password telah dikirim ke email Anda. Berlaku 15 menit.',
                'session' => ['reset_email' => $email],
            ]
        );
    }

    public function resendResetCode(array $data): array
    {
        $email = sanitize_input($data['email'] ?? '');

        if ($email === '') {
            return $this->error('Email harus diisi!', 'index.php?page=reset_verify');
        }

        $user = $this->authService->getUserByEmail($email);
        if (!$user) {
            return $this->error('Email tidak terdaftar!', 'index.php?page=reset_verify');
        }

        $otp = sprintf('%06d', random_int(100000, 999999));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $this->authService->setResetToken($user['id'], $otp, $expiresAt);
        $this->sendResetMail($email, $user['name'], $otp, true);

        return $this->success(
            'Kode OTP baru berhasil dikirim!',
            'index.php?page=reset_verify',
            ['flash' => 'Kode reset password baru telah dikirim.']
        );
    }

    public function verifyResetCode(array $data): array
    {
        $email = sanitize_input($data['email'] ?? '');
        $code = sanitize_input($data['code'] ?? '');

        if ($email === '' || $code === '') {
            return $this->error('Email dan kode OTP harus diisi!', 'index.php?page=reset_verify');
        }

        $user = $this->authService->getUserByResetToken($email, $code);
        if (!$user) {
            return $this->error('Kode OTP salah atau sudah kedaluwarsa!', 'index.php?page=reset_verify');
        }

        // Generate a temporary session token to authorize the password reset form
        $resetSession = bin2hex(random_bytes(32));

        return $this->success(
            'Kode OTP valid! Silakan buat password baru.',
            'index.php?page=reset_password',
            [
                'session' => [
                    'reset_email' => $email,
                    'reset_authorized' => $resetSession,
                ],
            ]
        );
    }

    public function resetPassword(array $data): array
    {
        $email = sanitize_input($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirm'] ?? '';

        if ($email === '' || $password === '') {
            return $this->error('Semua field harus diisi!', 'index.php?page=reset_password');
        }

        if ($password !== $passwordConfirm) {
            return $this->error('Konfirmasi password tidak cocok!', 'index.php?page=reset_password');
        }

        // Password complexity check (same as registration)
        if (
            strlen($password) < 8 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[^a-zA-Z0-9]/', $password)
        ) {
            return $this->error(
                'Password minimal 8 karakter dan harus mengandung huruf besar, huruf kecil, angka, serta simbol.',
                'index.php?page=reset_password'
            );
        }

        $user = $this->authService->getUserByEmail($email);
        if (!$user) {
            return $this->error('Email tidak ditemukan!', 'index.php?page=forgot_password');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->authService->updatePassword($user['id'], $hashedPassword);
        $this->authService->clearResetToken($user['id']);

        // Clear reset session data
        unset($_SESSION['reset_email'], $_SESSION['reset_authorized']);

        return $this->success(
            'Password berhasil direset! Silakan login dengan password baru.',
            'index.php?page=login',
            ['flash' => 'Password Anda berhasil diubah. Silakan masuk dengan password baru.']
        );
    }

    private function sendResetMail(string $email, string $name, string $token, bool $resend = false): void
    {
        $subject = $resend
            ? 'Reset Password NusaBay (Kirim Ulang)'
            : 'Reset Password Akun NusaBay';

        $body = "Halo {$name},\n\n"
            . ($resend ? "Berikut adalah Kode Reset Password baru Anda:\n\n" : "Anda telah meminta untuk mereset password akun NusaBay Anda.\nSilakan masukkan kode berikut untuk melanjutkan:\n\n")
            . "KODE: {$token}\n\n"
            . "Kode ini berlaku selama 15 menit.\n"
            . "Jika Anda tidak meminta reset password, abaikan email ini.\n\n"
            . "Salam,\nNusaBay Team";

        sendMail($email, $subject, $body);
    }
}
