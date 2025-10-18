<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/vendor/autoload.php";

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // SMTP server (for Gmail)
    $mail->SMTPAuth = true;
    $mail->Username = 'mathewsanjose93@gmail.com'; // Replace with your Gmail
    $mail->Password = 'uhmptoqzbqoiilpf';  // Replace with your Gmail App Password (NOT the Gmail password)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS encryption
    $mail->Port = 587; // Port for TLS

    // Recipients
    $mail->setFrom('mathewsanjose93@gmail.com', 'phpmailer'); // Sender's email and name
    $mail->addAddress('mathewsanjose93@gmail.com'); // Recipient's email address, dynamically set this

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset';
    $mail->Body    = 'Click <a href="http://localhost/Seventeasdiner/send-password-reset.php?token=YOUR_TOKEN_HERE">here</a> to reset your password.';

    // Send the email
    if ($mail->send()) {
        echo "Message sent successfully!";
    } else {
        echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
    }
} catch (Exception $e) {
    echo "Message could not be sent. PHPMailer Error: {$mail->ErrorInfo}";
}

return $mail;
