<?php

namespace App\Controllers;

use Leaf\Auth;
use Leaf\Http\Request;

class AuthController
{
    public function loginView()
    {
        // Generate captcha
        $captcha = \App\Utils\SimpleCaptcha::generate();

        $errors = [];
        if (Request::get('error') === 'maintenance') {
            $errors[] = \App\Models\Setting::get('maintenance_message', 'Sistem sedang dalam pemeliharaan. Silakan coba lagi beberapa saat lagi.');
        }

        echo \App\Utils\View::render('auth.login', [
            'captcha' => $captcha,
            'errors' => $errors
        ]);
    }

    public function login()
    {
        $email = Request::get('email');
        $password = Request::get('password'); // This acts as DOB
        $captchaInput = Request::get('captcha');

        // 0. Maintenance Mode Check
        $maintenance = \App\Models\Setting::get('maintenance_mode', 'off');
        if ($maintenance === 'on') {
            $msg = \App\Models\Setting::get('maintenance_message', 'Sistem sedang dalam pemeliharaan. Silakan coba lagi beberapa saat lagi.');
            $captcha = \App\Utils\SimpleCaptcha::generate();
            echo \App\Utils\View::render('auth.login', [
                'errors' => [$msg],
                'captcha' => $captcha
            ]);
            return;
        }

        // Verify captcha first
        if (!\App\Utils\SimpleCaptcha::verify($captchaInput)) {
            $captcha = \App\Utils\SimpleCaptcha::generate();
            echo \App\Utils\View::render('auth.login', [
                'errors' => ['Jawaban captcha salah!'],
                'captcha' => $captcha
            ]);
            return;
        }

        // Simple auth logic based on instructions
        // Participant Login: Email + DOB (YYYY-MM-DD)

        $participant = \App\Models\Participant::where('email', $email)->first();

        if (!$participant) {
            // Check if Admin
            // $user = \App\Models\User::where('username', $email)->first(); // Reusing email field for username input for simplicity? 
            // Agent.md says: Admin Login (users table), Participant Login (participants table).
            // Public portal is for Participants. Admin dashboard is for Admin.
            // Let's separate or detect.
            // Left as is, logic issue handled?
            $captcha = \App\Utils\SimpleCaptcha::generate();
            echo \App\Utils\View::render('auth.login', [
                'errors' => ['Email tidak ditemukan'],
                'captcha' => $captcha
            ]);
            return;
        }

        // Validate DOB (Password)
        // Validate DOB (Password)
        // Check strict string equality first (fastest)
        $isValidDate = false;

        // DB is YYYY-MM-DD
        if ($participant['tgl_lahir'] === $password) {
            $isValidDate = true;
        } else {
            // Flexible parsing to handle browser input (Y-m-d) vs User Expectation
            try {
                // 1. Normalize Database Date
                $dbDateObj = date_create($participant['tgl_lahir']);
                $dbDateStandard = $dbDateObj ? date_format($dbDateObj, 'Y-m-d') : null;

                // 2. Normalize Input Date
                // HTML5 input type="date" sends Y-m-d
                $inputDateObj = date_create_from_format('Y-m-d', $password);

                // If not Y-m-d, try d-m-Y (fallback)
                if (!$inputDateObj) {
                    $inputDateObj = date_create_from_format('d-m-Y', $password);
                }

                $inputDateStandard = $inputDateObj ? date_format($inputDateObj, 'Y-m-d') : 'invalid';

                // Compare normalized Y-m-d strings
                if ($dbDateStandard && $inputDateStandard && $dbDateStandard === $inputDateStandard) {
                    $isValidDate = true;
                }
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        }

        if (!$isValidDate) {
            $captcha = \App\Utils\SimpleCaptcha::generate();
            echo \App\Utils\View::render('auth.login', [
                'errors' => ['Tanggal lahir salah. Pastikan tanggal lahir sesuai dengan data pendaftaran (dd-mm-yyyy).'],
                'captcha' => $captcha
            ]);
            return;
        }

        // Check Payment Status
        if ($participant['status_pembayaran'] != 1) {
            $captcha = \App\Utils\SimpleCaptcha::generate();
            echo \App\Utils\View::render('auth.login', [
                'errors' => ['Anda terdaftar, namun belum melakukan pembayaran biaya admisi pasca.'],
                'captcha' => $captcha
            ]);
            return;
        }

        // Login Success
        // Use Session
        $_SESSION['user'] = $participant['id']; // Array access for Leaf Db v3 / PDO
        $_SESSION['role'] = 'participant';

        response()->redirect('/dashboard');
    }

    public function adminLogin()
    {
        // For simplicity, let's say /admin has basic auth or a separate login.
        // agents.md implies "users (Admin login)".
        // Let's make a simple Basic Auth or hardcoded for now, or use Leaf Auth properly?
        // "Auth: Gunakan Session management bawaan Leaf."

        // Let's implement Admin Login route
        $username = request()->get('username');
        $password = request()->get('password');

        $user = \App\Models\User::where('username', $username)->first();

        // Check Maintenance Mode (Only allow superadmin if on)
        $maintenance = \App\Models\Setting::get('maintenance_mode', 'off');
        if ($maintenance === 'on' && (!$user || ($user['role'] ?? 'admin') !== 'superadmin')) {
            response()->redirect('/admin?error=maintenance');
            exit;
        }

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables with role information
            $_SESSION['admin'] = $user['id'];
            $_SESSION['admin_role'] = $user['role'] ?? 'admin'; // Default to admin if no role
            $_SESSION['admin_username'] = $user['username'];

            // Set prodi_id if admin_prodi
            if ($user['role'] === 'admin_prodi' && isset($user['prodi_id'])) {
                $_SESSION['admin_prodi_id'] = $user['prodi_id'];
            }

            response()->redirect('/admin');
        } else {
            response()->redirect('/admin?error=1');
        }
    }

    public function logout()
    {
        session_destroy();
        response()->redirect('/');
    }
}
