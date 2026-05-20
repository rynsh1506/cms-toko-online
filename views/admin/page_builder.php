<?php
// Pastikan ini dijalankan lewat router (index.php) dan sudah dicek checkAdmin()
require_once __DIR__ . '/../../config/db.php';

// Ambil konfigurasi saat ini
$stmt = $pdo->query("SELECT section_key, content_value, type FROM landing_configs");
$configs_raw = $stmt->fetchAll();
$configs = [];
foreach ($configs_raw as $c) {
    $configs[$c['section_key']] = $c;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Builder - Pro-Store CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">
    
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-gray-800 text-white w-64 p-6 hidden md:block">
            <h2 class="text-2xl font-bold mb-6">Pro-Store CMS</h2>
            <ul>
                <li class="mb-4"><a href="index.php?page=admin" class="hover:text-blue-300">Dashboard</a></li>
                <li class="mb-4"><a href="index.php?page=page_builder" class="text-blue-400 font-semibold">Page Builder</a></li>
                <!-- Nanti tambahkan menu Produk dan Pesanan -->
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Dynamic Page Builder</h1>
                <a href="index.php?page=auth_process&action=logout" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</a>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded mb-6">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow-md max-w-3xl">
                <!-- Penting: enctype="multipart/form-data" untuk upload file -->
                <form action="index.php?page=config_process" method="POST" enctype="multipart/form-data">
                    
                    <h3 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Pengaturan Hero Section</h3>
                    
                    <!-- Hero Title -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Judul Utama (Hero Title)</label>
                        <input type="text" name="config[hero_title]" 
                            value="<?= htmlspecialchars($configs['hero_title']['content_value'] ?? '') ?>" 
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
                    </div>
                    
                    <!-- Hero Subtitle -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Sub-judul (Hero Subtitle)</label>
                        <textarea name="config[hero_subtitle]" rows="3"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"><?= htmlspecialchars($configs['hero_subtitle']['content_value'] ?? '') ?></textarea>
                    </div>

                    <!-- Primary Color -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Warna Utama (Primary Color)</label>
                        <div class="flex items-center space-x-3">
                            <input type="color" name="config[primary_color]" 
                                value="<?= htmlspecialchars($configs['primary_color']['content_value'] ?? '#2563eb') ?>" 
                                class="h-10 w-16 p-1 rounded border">
                            <span class="text-gray-500 text-sm">Warna ini akan digunakan pada elemen penting (tombol, teks sorotan, dsb.)</span>
                        </div>
                    </div>

                    <!-- Hero Image Upload -->
                    <div class="mb-8">
                        <label class="block text-gray-700 font-bold mb-2">Gambar Banner (Hero Image)</label>
                        <?php if(!empty($configs['hero_image']['content_value'])): ?>
                            <div class="mb-3">
                                <p class="text-sm text-gray-500 mb-1">Gambar saat ini:</p>
                                <img src="uploads/<?= htmlspecialchars($configs['hero_image']['content_value']) ?>" alt="Current Hero" class="h-32 object-cover rounded shadow">
                            </div>
                        <?php endif; ?>
                        
                        <input type="file" name="hero_image" accept="image/png, image/jpeg" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                        <p class="text-xs text-gray-500 mt-2">Hanya mendukung JPG dan PNG. Kosongkan jika tidak ingin mengubah.</p>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition duration-300">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
            
        </div>
    </div>

</body>
</html>
