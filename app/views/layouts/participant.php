<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Peserta - SIDA Pasca ULM</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#fbbf24',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/participant/dashboard" class="flex-shrink-0 flex items-center">
                        <?php $logo = \App\Models\Setting::get('app_logo'); ?>
                        <?php if ($logo): ?>
                            <img class="h-8 w-auto mr-3" src="<?php echo $logo; ?>" alt="Logo">
                        <?php else: ?>
                            <i class="fas fa-graduation-cap text-blue-600 text-2xl mr-2"></i>
                        <?php endif; ?>
                        <span class="font-bold text-xl text-gray-800">SIDA Pasca ULM</span>
                    </a>
                </div>

                <!-- Right Menu -->
                <div class="flex items-center space-x-4">
                    <button onclick="openGuideModal()"
                        class="text-gray-500 hover:text-blue-600 font-medium text-sm flex items-center transition-colors">
                        <i class="fas fa-book mr-1"></i> <span class="hidden sm:inline">Panduan</span>
                    </button>
                    <div class="h-6 w-px bg-gray-300 mx-2 hidden sm:block"></div>
                    <a href="/logout"
                        class="text-red-600 hover:text-red-700 font-medium text-sm flex items-center transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i> <span class="hidden sm:inline">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow">
        <?php echo $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                &copy;
                <?php echo date('Y'); ?> Pascasarjana Universitas Lambung Mangkurat. All rights reserved.
            </p>
        </div>
    </footer>

</body>

</html>