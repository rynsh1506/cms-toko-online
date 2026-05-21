<?php
class ProfileService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function updateProfile(int $user_id, array $data, ?array $file): void
    {
        $name = sanitize_input($data['name'] ?? '');
        $email = sanitize_input($data['email'] ?? '');
        $phone = sanitize_input($data['phone'] ?? '');
        $bio = sanitize_input($data['bio'] ?? '');

        if (empty($name) || empty($email)) throw new \Exception('Nama dan Email wajib diisi.');

        $stmtCheck = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmtCheck->execute([$email, $user_id]);
        if ($stmtCheck->fetch()) throw new \Exception('Email sudah digunakan oleh akun lain.');

        $avatar_url = null;
        if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($file['error'] !== UPLOAD_ERR_OK) throw new \Exception("Gagal mengunggah foto profil.");

            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mime = mime_content_type($file['tmp_name']);

            if (!in_array($file_ext, $allowed_exts) || !in_array($mime, $allowed_mimes)) {
                throw new \Exception("Format file tidak valid. Harap gunakan format JPG, JPEG, PNG, atau GIF.");
            }
            if ($file['size'] > 2 * 1024 * 1024) throw new \Exception("Ukuran file terlalu besar. Maksimal 2MB.");

            $upload_dir = __DIR__ . '/../uploads/avatars';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . '/' . $new_filename;

            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new \Exception("Gagal memindahkan file ke direktori tujuan.");
            }

            $avatar_url = 'uploads/avatars/' . $new_filename;
            $stmtOld = $this->pdo->prepare("SELECT avatar_url FROM users WHERE id = ?");
            $stmtOld->execute([$user_id]);
            $old_avatar = $stmtOld->fetchColumn();
            if ($old_avatar && file_exists(__DIR__ . '/../' . $old_avatar)) @unlink(__DIR__ . '/../' . $old_avatar);
        }

        if ($avatar_url) {
            $stmt = $this->pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, bio = ?, avatar_url = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $bio, $avatar_url, $user_id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, bio = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $bio, $user_id]);
        }
    }

    public function changePassword(int $user_id, array $data): void
    {
        $old_password = $data['old_password'] ?? '';
        $new_password = $data['new_password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? '';

        if (empty($old_password) || empty($new_password) || empty($confirm_password)) throw new \Exception('Semua kolom password wajib diisi.');
        if ($new_password !== $confirm_password) throw new \Exception('Konfirmasi password baru tidak cocok.');

        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $current_hash = $stmt->fetchColumn();

        if (!password_verify($old_password, $current_hash)) throw new \Exception('Password lama yang Anda masukkan salah.');

        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmtUpdate = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmtUpdate->execute([$new_hash, $user_id]);
    }
}
