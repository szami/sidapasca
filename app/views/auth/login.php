<?php ob_start(); ?>
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row max-w-4xl w-full">
        <!-- Left Side: Branding -->
        <div
            class="md:w-1/2 bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 p-12 text-white flex flex-col justify-center items-center text-center relative overflow-hidden">
            <!-- Decorative Circles -->
            <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-64 h-64 bg-yellow-400 rounded-full blur-3xl"></div>
            </div>

            <div class="relative z-10">
                <div class="mb-8">
                    <img src="https://simari.ulm.ac.id/logo/ulm.png" alt="Logo ULM"
                        class="h-28 w-28 object-contain drop-shadow-xl mx-auto transform hover:scale-105 transition duration-500">
                </div>

                <h1 class="text-3xl font-bold mb-2 tracking-tight">SIDA Pasca</h1>
                <p class="text-blue-100 font-light mb-6 opacity-90">Sistem Informasi & Data Admisi <br> Pascasarjana ULM
                </p>

                <div class="border-t border-white/20 w-16 mx-auto my-6"></div>

                <a href="/"
                    class="inline-flex items-center text-sm text-blue-200 hover:text-white transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="md:w-1/2 p-8 md:p-12 bg-gray-50/50">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Login Peserta</h2>
                <p class="text-sm text-gray-500 mt-2">Masuk menggunakan akun Admisipasca</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm mb-6 text-sm"
                    role="alert">
                    <p class="font-bold mb-1">Gagal Masuk</p>
                    <p><?php echo implode('<br>', $errors); ?></p>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" class="space-y-5">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2 ml-1">Email Peserta</label>
                    <div class="relative">
                        <span
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                        </span>
                        <input type="email" name="email"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none bg-white"
                            placeholder="email@domain.com" required>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2 ml-1">Password (Tanggal Lahir)</label>
                    <div class="relative">
                        <span
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                        <input type="date" name="password"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none bg-white"
                            required>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 ml-1">* Format password sesuai tanggal lahir di sistem</p>
                </div>

                <!-- Captcha Field -->
                <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                    <label class="block text-blue-800 text-xs font-bold mb-2 uppercase tracking-wider">Verifikasi
                        Keamanan</label>
                    <div class="flex items-center space-x-3">
                        <div
                            class="bg-white px-4 py-2 rounded-lg border border-blue-200 font-mono text-lg font-bold text-gray-700 shadow-sm flex-grow text-center tracking-widest">
                            <?php echo $captcha['question'] ?? '2 + 2'; ?> = ?
                        </div>
                        <input type="number" name="captcha"
                            class="w-24 px-4 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none text-center font-bold"
                            placeholder="?" required>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-700 to-indigo-700 hover:from-blue-800 hover:to-indigo-800 text-white font-bold py-3.5 px-4 rounded-xl transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 focus:ring-4 focus:ring-blue-300">
                        Masuk ke Portal
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center border-t pt-6">
                <p class="text-xs text-gray-400">
                    &copy; 2025 Pascasarjana Universitas Lambung Mangkurat
                </p>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>