<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

function enviarMail($to, $subject, $htmlBody) {
    $cfg = require __DIR__ . '/mail_config.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $cfg["host"];
        $mail->SMTPAuth   = true;
        $mail->Username   = $cfg["username"];
        $mail->Password   = $cfg["password"];
        $mail->Port       = $cfg["port"];
        $mail->CharSet    = "UTF-8";

        // TLS (STARTTLS)
        if (($cfg["secure"] ?? "") === "tls") {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (($cfg["secure"] ?? "") === "ssl") {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->setFrom($cfg["from_email"], $cfg["from_name"]);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}