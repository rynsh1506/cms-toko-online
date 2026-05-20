<?php

/**
 * Kirim email simulasi ke file log
 */
function sendMail($to, $subject, $body) {
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    $log_file = $log_dir . '/emails.log';
    
    $timestamp = date('Y-m-d H:i:s');
    $email_content = "==================================================\n";
    $email_content .= "TIMESTAMP: $timestamp\n";
    $email_content .= "TO: $to\n";
    $email_content .= "SUBJECT: $subject\n";
    $email_content .= "BODY:\n$body\n";
    $email_content .= "==================================================\n\n";
    
    file_put_contents($log_file, $email_content, FILE_APPEND);
    
    // Juga coba kirim via mail PHP bawaan (jika ada MTA lokal)
    @mail($to, $subject, $body, "From: no-reply@prostore.com\r\nContent-Type: text/plain; charset=utf-8\r\n");
    
    return true;
}
