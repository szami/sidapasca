<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIDA Pasca - Universitas Lambung Mangkurat</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

<body class="bg-gray-50 text-gray-800">

    <!-- Navbar -->
    <nav class="absolute top-0 w-full z-10 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-24">
                <!-- Logo Section -->
                <div class="flex items-center space-x-4">
                    <img src="https://simari.ulm.ac.id/logo/ulm.png" alt="Logo ULM" class="h-14 w-14 drop-shadow-md">
                    <div class="hidden md:block">
                        <h1 class="text-white font-bold text-xl leading-none tracking-wide uppercase font-serif">
                            Universitas Lambung Mangkurat
                        </h1>
                        <p class="text-blue-200 text-sm font-medium tracking-widest uppercase mt-1">
                            Program Pascasarjana
                        </p>
                    </div>
                </div>

                <!-- Login Admin Link -->
                <div>
                    <a href="/admin"
                        class="px-5 py-2 rounded-full border border-white/30 text-white hover:bg-white/10 hover:border-white/50 text-sm font-medium transition duration-300 flex items-center gap-2 backdrop-blur-sm">
                        <span class="hidden sm:inline">Administrator</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div
        class="relative bg-gradient-to-br from-[#0c2461] via-[#1e3799] to-[#4a69bd] min-h-screen flex items-center justify-center overflow-hidden">

        <!-- Abstract Background -->
        <div class="absolute inset-0 overflow-hidden">
            <svg class="absolute top-0 left-0 transform -translate-x-1/2 -translate-y-1/2 text-white opacity-[0.03]"
                width="800" height="800" fill="currentColor" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="50" />
            </svg>
            <svg class="absolute bottom-0 right-0 transform translate-x-1/3 translate-y-1/3 text-yellow-400 opacity-[0.05]"
                width="600" height="600" fill="currentColor" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="50" />
            </svg>
        </div>

        <div
            class="relative z-10 w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-2 gap-12 items-center">

            <!-- Left Column: Typography -->
            <div class="text-left text-white space-y-6">
                <div>
                    <span
                        class="inline-block py-1 px-3 rounded-full bg-blue-500/30 text-blue-100 text-xs font-semibold tracking-wider uppercase mb-4 backdrop-blur-sm border border-blue-400/30">
                        Portal Penerimaan Mahasiswa Baru
                    </span>
                    <h2 class="text-4xl md:text-5xl font-extrabold leading-tight tracking-tight">
                        Selamat Datang <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-yellow-500">
                            Calon Mahasiswa Pascasarjana
                        </span>
                    </h2>
                </div>

                <p class="text-lg text-blue-100/80 leading-relaxed font-light max-w-lg">
                    Sistem ini adalah layanan pendukung untuk <strong>Cetak Kartu Ujian & Formulir</strong>. <br>
                    Untuk melakukan pendaftaran akun baru, silakan kunjungi sistem utama kami di <a
                        href="https://admisipasca.ulm.ac.id" target="_blank"
                        class="text-yellow-400 hover:text-yellow-300 underline underline-offset-4 decoration-1">admisipasca.ulm.ac.id</a>.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <a href="/login"
                        class="group relative px-8 py-4 bg-yellow-500 hover:bg-yellow-400 text-blue-900 text-base font-bold rounded-xl shadow-lg hover:shadow-yellow-500/30 transition-all duration-300 transform hover:-translate-y-1 text-center">
                        Login Sistem
                        <span
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all">
                            →
                        </span>
                    </a>
                    <a href="https://admisipasca.ulm.ac.id" target="_blank"
                        class="px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-base font-semibold rounded-xl backdrop-blur-md transition-all duration-300 text-center">
                        Ke Pendaftaran Utama
                    </a>
                </div>
            </div>

            <!-- Right Column: Visual/Card (Optional, simplified for elegance) -->
            <div class="hidden md:block relative">
                <div class="relative w-full aspect-square max-w-md mx-auto">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full opacity-20 blur-3xl animate-pulse">
                    </div>
                    <div
                        class="relative bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-8 shadow-2xl transform rotate-3 hover:rotate-0 transition-all duration-500">
                        <div class="flex items-center space-x-4 mb-6">
                            <div
                                class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center text-blue-900 font-bold text-xl">
                                1
                            </div>
                            <div class="text-white">
                                <h4 class="font-bold">Login Sistem</h4>
                                <p class="text-sm text-blue-200">Silakan isi informasi sesuai yang terdata di
                                    admisipasca.ulm.ac.id</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4 mb-6">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                2
                            </div>
                            <div class="text-white">
                                <h4 class="font-bold">Cek Data</h4>
                                <p class="text-sm text-blue-200">Pastikan data anda benar</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                3
                            </div>
                            <div class="text-white">
                                <h4 class="font-bold">Cetak Dokumen</h4>
                                <p class="text-sm text-blue-200">Download Formulir & Kartu Ujian</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="absolute bottom-6 w-full text-center">
            <p class="text-blue-300/60 text-xs font-light tracking-wide">
                &copy; <?php echo date('Y'); ?> Program Pascasarjana ULM. Hak Cipta Dilindungi.
            </p>
        </div>
    </div>

</body>

</html>