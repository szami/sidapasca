<?php ob_start(); ?>
<div class="bg-white rounded-lg shadow-xl overflow-hidden flex flex-col md:flex-row max-w-4xl mx-auto">
    <!-- Left Side: Branding -->
    <div class="md:w-1/2 bg-primary p-12 text-white flex flex-col justify-center items-center text-center">
        <h1 class="text-4xl font-bold mb-4">SIDA Pasca</h1>
        <p class="text-lg opacity-90">Sistem Informasi & Data Admisi Pascasarjana ULM</p>
        <div class="mt-8">
            <!-- Placeholder for Logo -->
            <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center text-3xl mx-auto">
                🎓
            </div>
        </div>
    </div>

    <!-- Right Side: Form -->
    <div class="md:w-1/2 p-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Login Peserta</h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">
                    <?php echo implode('<br>', $errors); ?>
                </span>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-600 text-sm font-medium mb-1">Email</label>
                <input type="email" name="email"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:outline-none"
                    placeholder="email@contoh.com" required>
            </div>

            <div>
                <label class="block text-gray-600 text-sm font-medium mb-1">Tanggal Lahir</label>
                <input type="date" name="password"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:outline-none"
                    required>
                <p class="text-xs text-gray-400 mt-1">Digunakan sebagai password</p>
            </div>

            <!-- Captcha Field -->
            <div>
                <label class="block text-gray-600 text-sm font-medium mb-1">Verifikasi</label>
                <div class="bg-gray-100 px-4 py-3 rounded-lg mb-2 border border-gray-300">
                    <p class="text-gray-800 font-semibold">
                        <?php echo $captcha['question'] ?? 'Berapa hasil dari 2 + 2?'; ?></p>
                </div>
                <input type="number" name="captcha"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:outline-none"
                    placeholder="Masukkan jawaban" required>
            </div>

            <div class="pt-4">
                <button type="submit"
                    class="w-full bg-primary hover:bg-blue-800 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Masuk
                </button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>