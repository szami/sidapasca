<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SIDA Pasca ULM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 50%, #1e1b4b 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .floating-shapes::before,
        .floating-shapes::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 20s infinite;
        }

        .floating-shapes::before {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            top: -100px;
            right: -100px;
        }

        .floating-shapes::after {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            bottom: -50px;
            left: -50px;
            animation-delay: -10s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }

        .input-premium {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .input-premium:focus {
            background: white;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .btn-premium {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #7c3aed 100%);
            background-size: 200% 200%;
            animation: gradient-shift 3s ease infinite;
            transition: all 0.3s ease;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px -10px rgba(99, 102, 241, 0.5);
        }

        @keyframes gradient-shift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .logo-glow {
            filter: drop-shadow(0 0 20px rgba(99, 102, 241, 0.3));
        }

        .pulse-ring {
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-ring {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>

<body class="gradient-bg">
    <div class="floating-shapes"></div>

    <div class="min-h-screen flex items-center justify-center p-4 relative z-10">
        <div class="w-full max-w-md">
            <!-- Logo & Branding -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-sm mb-4 logo-glow">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">SIDA Pasca</h1>
                <p class="text-white/60 text-sm">Sistem Informasi & Data Admisi Pascasarjana</p>
                <p class="text-white/40 text-xs mt-1">Universitas Lambung Mangkurat</p>
            </div>

            <!-- Login Card -->
            <div class="glass-card rounded-2xl shadow-2xl p-8">
                <div class="text-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800">Masuk ke Panel Admin</h2>
                    <p class="text-gray-500 text-sm mt-1">Silakan masukkan kredensial Anda</p>
                </div>

                <?php if (isset($error) && $error): ?>
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-red-800 font-medium text-sm">Login Gagal</p>
                            <p class="text-red-600 text-xs"><?= $error ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="/admin/login" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Username
                            </span>
                        </label>
                        <input type="text" name="username"
                            class="input-premium w-full px-4 py-3 rounded-xl text-gray-800"
                            placeholder="Masukkan username" required autocomplete="username">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Password
                            </span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="password"
                                class="input-premium w-full px-4 py-3 rounded-xl text-gray-800 pr-12"
                                placeholder="Masukkan password" required autocomplete="current-password">
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="btn-premium w-full py-3.5 rounded-xl text-white font-semibold text-sm tracking-wide flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Masuk ke Dashboard
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="px-3 bg-white text-gray-400">atau</span>
                    </div>
                </div>

                <!-- Back to Portal -->
                <a href="/"
                    class="block w-full py-3 rounded-xl border-2 border-gray-200 text-gray-600 font-medium text-sm text-center hover:bg-gray-50 hover:border-gray-300 transition">
                    ← Kembali ke Portal Peserta
                </a>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-white/40 text-xs">
                    © <?= date('Y') ?> Program Pascasarjana ULM
                </p>
                <p class="text-white/30 text-xs mt-1">
                    SIDA Pasca <?= \App\Utils\Version::getWithPrefix() ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }
    </script>
</body>

</html>