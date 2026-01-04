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
        echo \App\Utils\View::render('auth.login', ['captcha' => $captcha]);
    }

    public function login()
    {
        $email = Request::get('email');
        $password = Request::get('password'); // This acts as DOB
        $captchaInput = Request::get('captcha');

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
        if ($participant['tgl_lahir'] !== $password) {
            $captcha = \App\Utils\SimpleCaptcha::generate();
            echo \App\Utils\View::render('auth.login', [
                'errors' => ['Tanggal lahir salah (Format: YYYY-MM-DD)'],
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
        // Admin specific login if needed, or unified.
        // For simplicity, let's say /admin has basic auth or a separate login.
        // agents.md implies "users (Admin login)".
        // Let's make a simple Basic Auth or hardcoded for now, or use Leaf Auth properly?
        // "Auth: Gunakan Session management bawaan Leaf."

        // Let's implement Admin Login route logic here if posted
        $username = Request::get('username');
        $password = Request::get('password');

        $user = \App\Models\User::where('username', $username)->first();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin'] = $user['id'];
            $_SESSION['admin_role'] = $user['role'] ?? 'superadmin'; // Default fallback
            response()->redirect('/admin');
        } else {
            response()->redirect('/admin?error=1'); // /admin shows login if not auth
        }
    }

    public function logout()
    {
        session_destroy();
        response()->redirect('/');
    }
}
