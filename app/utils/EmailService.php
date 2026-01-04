<?php

namespace App\Utils;

use App\Models\EmailConfiguration;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    /**
     * Send email using configured SMTP settings
     */
    public static function send($to, $subject, $body, $participantData = null)
    {
        $config = EmailConfiguration::get();

        if (!$config) {
            throw new \Exception('Email configuration not found');
        }

        // Replace placeholders if participant data provided
        if ($participantData) {
            $subject = self::replaceVariables($subject, $participantData);
            $body = self::replaceVariables($body, $participantData);
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = $config['smtp_encryption'];
            $mail->Port = $config['smtp_port'];
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            throw new \Exception("Email sending failed: {$mail->ErrorInfo}");
        }
    }

    /**
     * Replace placeholder variables in template
     */
    public static function replaceVariables($text, $participant)
    {
        $replacements = [
            '{nama}' => $participant['nama_lengkap'] ?? '',
            '{nomor_peserta}' => $participant['nomor_peserta'] ?? '',
            '{prodi}' => $participant['nama_prodi'] ?? '',
            '{semester}' => $participant['semester_nama'] ?? '',
            '{email}' => $participant['email'] ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Test SMTP connection
     */
    public static function testConnection($config)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = $config['smtp_encryption'];
            $mail->Port = $config['smtp_port'];

            // Timeout settings
            $mail->Timeout = 10;
            $mail->SMTPDebug = 0;

            // Test connection
            $mail->smtpConnect();
            $mail->smtpClose();

            return true;
        } catch (Exception $e) {
            throw new \Exception("Connection failed: {$mail->ErrorInfo}");
        }
    }
}
