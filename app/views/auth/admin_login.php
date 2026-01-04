<?php ob_start(); ?>
<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
    <h2 class="text-2xl font-bold text-center mb-6">Admin Login</h2>
    <form action="/admin/login" method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-700">Username</label>
            <input type="text" name="username" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block text-gray-700">Password</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
        </div>
        <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">Login</button>
    </form>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>