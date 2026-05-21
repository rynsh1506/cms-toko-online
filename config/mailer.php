<?php
// Load PHPMailer secara manual
require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Konfigurasi Server SMTP
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'] ?? 'riangg4315@gmail.com';           
        $mail->Password   = $_ENV['SMTP_PASS'] ?? 'dsnp ovdq qrml hmkp';        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 587);

        // Penerima & Pengirim
        $from_email = $_ENV['SMTP_FROM_EMAIL'] ?? 'riangg4315@gmail.com';
        $from_name = $_ENV['SMTP_FROM_NAME'] ?? 'NusaBay Team';
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to);

        // Konten Email
        $mail->isHTML(false); 
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Fallback catat ke log jika gagal kirim asli
        error_log("Gagal mengirim email: {$mail->ErrorInfo}");
        return false;
    }
}
