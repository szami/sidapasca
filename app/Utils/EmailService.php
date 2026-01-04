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
    /**
     * Send email using configured driver (SMTP or GAS)
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

        $driver = $config['driver'] ?? 'smtp';

        if ($driver === 'gas') {
            return self::sendViaGas($to, $subject, $body, $config['api_url'], $config);
        }

        // Default: SMTP
        return self::sendViaSmtp($to, $subject, $body, $config);
    }

    /**
     * Send email using Google Apps Script Webhook
     */
    private static function sendViaGas($to, $subject, $body, $apiUrl, $config = [])
    {
        if (empty($apiUrl))
            throw new \Exception("GAS API URL is empty");

        $payload = json_encode([
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'name' => $config['from_name'] ?? 'Pascasarjana ULM',
            'replyTo' => $config['from_email'] ?? '' // GAS uses this for reply-to
        ]);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        // Bypass SSL verification for local dev issues (Laragon cafile missing)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("GAS Connection Error: $error");
        }

        $json = json_decode($response, true);
        if ($httpCode != 200 || ($json['status'] ?? '') !== 'success') {
            throw new \Exception("GAS Error: " . ($json['message'] ?? $response));
        }

        return true;
    }

    /**
     * Send email using configured SMTP settings
     */
    private static function sendViaSmtp($to, $subject, $body, $config)
    {
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
            '{no_billing}' => $participant['no_billing'] ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Test connection based on driver settings
     */
    public static function testConnection($config)
    {
        $driver = $config['driver'] ?? 'smtp';

        if ($driver === 'gas') {
            $apiUrl = $config['api_url'] ?? '';
            if (empty($apiUrl))
                throw new \Exception("URL Script kosong");
            if (!filter_var($apiUrl, FILTER_VALIDATE_URL))
                throw new \Exception("Format URL tidak valid");

            // Check connectivity
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            // Bypass SSL verification for local dev
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error)
                throw new \Exception("Koneksi gagal: $error");

            // If reachable, consider it success for basic connectivity check
            return true;
        }

        // SMTP Mode
        $mail = new PHPMailer(true);
        $debugOutput = ''; // Initialize early

        try {
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = $config['smtp_encryption'];
            $mail->Port = $config['smtp_port'];

            // Timeout settings
            $mail->Timeout = 15;
            $mail->SMTPDebug = 2; // Enable verbose debug output

            // Capture debug output
            $mail->Debugoutput = function ($str, $level) use (&$debugOutput) {
                $debugOutput .= "$str\n";
            };

            // Test connection
            if (!$mail->smtpConnect()) {
                throw new \Exception("SMTP Connect returned false.");
            }
            $mail->smtpClose();

            return true;
        } catch (\Throwable $e) {
            // Append debug log to error message
            // Clean up log to ensure UTF-8 for JSON safety
            $debugOutput = mb_convert_encoding($debugOutput, 'UTF-8', 'UTF-8');
            $log = !empty($debugOutput) ? "\nLog:\n$debugOutput" : "";

            throw new \Exception("Connection failed: " . $e->getMessage() . " $log");
        }
    }
}
