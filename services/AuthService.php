<?php

class AuthService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Check if email is already registered.
     */
    public function isEmailRegistered($email) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return (bool)$stmt->fetch();
    }

    /**
     * Register a new user.
     */
    public function registerUser($name, $email, $password, $role = 'user') {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $verification_token = sprintf("%06d", mt_rand(100000, 999999));
        
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role, verification_token) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password, $role, $verification_token]);
        
        return [
            'id' => $this->pdo->lastInsertId(),
            'name' => $name,
            'email' => $email,
            'verification_token' => $verification_token
        ];
    }

    /**
     * Count the number of total users.
     */
    public function countUsers() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Authenticate user credentials.
     */
    public function authenticate($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    /**
     * Get user details by email.
     */
    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Get user details by verification token.
     */
    public function getUserByToken($token) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE verification_token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    /**
     * Verify the user's email.
     */
    public function verifyUserEmail($userId) {
        $stmt = $this->pdo->prepare("UPDATE users SET email_verified_at = NOW(), verification_token = NULL WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Update/Regenerate the verification token/OTP.
     */
    public function updateVerificationToken($userId, $newToken) {
        $stmt = $this->pdo->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
        return $stmt->execute([$newToken, $userId]);
    }
}
