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
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;
        $mail->Username   = 'riangg4315@gmail.com';           
        $mail->Password   = 'dsnp ovdq qrml hmkp';        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Penerima & Pengirim
        $mail->setFrom('riangg4315@gmail.com', 'NusaBay Team');
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
