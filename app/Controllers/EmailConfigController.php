<?php

namespace App\Controllers;

use App\Models\EmailConfiguration;
use App\Utils\EmailService;
use App\Utils\View;
use Leaf\Http\Request;

class EmailConfigController
{
    private function checkAuth()
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::isSuperadmin()) {
            header('Location: /admin');
            exit;
        }
    }

    public function index()
    {
        $this->checkAuth();

        $config = EmailConfiguration::get();

        echo View::render('admin.email.config', [
            'config' => $config
        ]);
    }

    public function save()
    {
        $this->checkAuth();

        $data = [
            'driver' => Request::get('driver') ?? 'smtp',
            'api_url' => Request::get('api_url'),
            'smtp_host' => Request::get('smtp_host'),
            'smtp_port' => Request::get('smtp_port'),
            'smtp_username' => Request::get('smtp_username'),
            'smtp_encryption' => Request::get('smtp_encryption'),
            'from_email' => Request::get('from_email'),
            'from_name' => Request::get('from_name'),
            'is_active' => 1
        ];

        // Only update password if provided
        $password = Request::get('smtp_password');
        if (!empty($password)) {
            $data['smtp_password'] = $password;
        }

        $existingConfig = EmailConfiguration::get();

        if ($existingConfig) {
            EmailConfiguration::update($existingConfig['id'], $data);
        } else {
            if (empty($password)) {
                header('Location: /admin/email/config?error=password_required');
                exit;
            }
            $data['smtp_password'] = $password;
            EmailConfiguration::create($data);
        }

        header('Location: /admin/email/config?success=1');
        exit;
    }

    public function testConnection()
    {
        $this->checkAuth();

        // 1. Suppress HTML errors to ensure clean JSON
        ini_set('display_errors', 0);
        error_reporting(E_ALL);

        // Allow enough time for SMTP timeout (15s + overhead)
        set_time_limit(45);

        // 2. Start fresh output buffer to capture any stray output
        ob_start();

        try {
            $inputPassword = Request::get('smtp_password');

            // If password input is empty, try to get from database
            if (empty($inputPassword)) {
                $existingConfig = EmailConfiguration::get();
                $password = $existingConfig['smtp_password'] ?? '';
            } else {
                $password = $inputPassword;
            }

            $config = [
                'driver' => Request::get('driver'),
                'api_url' => Request::get('api_url'),
                'smtp_host' => Request::get('smtp_host'),
                'smtp_port' => Request::get('smtp_port'),
                'smtp_username' => Request::get('smtp_username'),
                'smtp_password' => $password,
                'smtp_encryption' => Request::get('smtp_encryption'),
            ];

            EmailService::testConnection($config);

            // Clean buffer before sending successful JSON
            if (ob_get_length())
                ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Koneksi berhasil!']);

        } catch (\Throwable $e) {
            // Clean buffer before sending error JSON
            if (ob_get_length())
                ob_clean();
            header('Content-Type: application/json');

            $errorMessage = 'Error: ' . $e->getMessage();
            $json = json_encode(['success' => false, 'message' => $errorMessage]);

            if ($json === false) {
                // JSON Encode failed (likely binary chars), send safe minimal error
                echo json_encode(['success' => false, 'message' => 'Error: JSON Encode Failed. Log contains invalid characters.']);
            } else {
                echo $json;
            }
        }
        exit;
    }
}
