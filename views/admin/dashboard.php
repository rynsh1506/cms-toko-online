<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pro-Store CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">
    
    <div class="min-h-screen flex">
        <!-- Sidebar Dummy -->
        <div class="bg-gray-800 text-white w-64 p-6 hidden md:block">
            <h2 class="text-2xl font-bold mb-6">Pro-Store CMS</h2>
            <ul>
                <li class="mb-4"><a href="#" class="hover:text-blue-300">Dashboard</a></li>
                <li class="mb-4"><a href="#" class="hover:text-blue-300">Produk</a></li>
                <li class="mb-4"><a href="#" class="hover:text-blue-300">Pesanan</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Selamat Datang, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>!</h1>
                <a href="index.php?page=auth_process&action=logout" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <p>Ini adalah halaman Admin Dashboard. Anda bisa melihat halaman ini karena Anda memiliki role <strong>admin</strong>.</p>
            </div>
        </div>
    </div>

</body>
</html>
