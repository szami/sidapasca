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
        if (!isset($_SESSION['admin'])) {
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

        $config = [
            'smtp_host' => Request::get('smtp_host'),
            'smtp_port' => Request::get('smtp_port'),
            'smtp_username' => Request::get('smtp_username'),
            'smtp_password' => Request::get('smtp_password'),
            'smtp_encryption' => Request::get('smtp_encryption'),
        ];

        try {
            EmailService::testConnection($config);
            echo json_encode(['success' => true, 'message' => 'Koneksi berhasil!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
