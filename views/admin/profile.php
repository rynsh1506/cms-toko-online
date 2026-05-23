<?php
// Query details admin terbaru
require_once __DIR__ . '/../../config/db.php';
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();
?>
<div class="max-w-5xl mx-auto space-y-8 font-sans">

    <!-- Header Page -->
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-5">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white font-display">Pengaturan Profil</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ubah biodata, foto profil, dan kredensial keamanan akun Anda.</p>
        </div>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Column 1: Info and Avatar Preview -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm flex flex-col items-center text-center transition-colors duration-300">
                <!-- Avatar Preview -->
                <div class="relative group">
                    <div id="avatar-container" class="h-32 w-32 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-700 border-4 border-indigo-50 dark:border-slate-900 shadow-md">
                        <?php if ($admin['avatar_url']): ?>
                            <img id="avatar-preview-img" class="h-full w-full object-cover" src="<?= htmlspecialchars($admin['avatar_url']) ?>" alt="Avatar Preview">
                        <?php else: ?>
                            <div id="avatar-placeholder" class="h-full w-full flex items-center justify-center font-bold text-slate-400 dark:text-slate-500 text-4xl uppercase">
                                <?= strtoupper(substr($admin['name'], 0, 2)) ?>
                            </div>
                            <img id="avatar-preview-img" class="h-full w-full object-cover hidden" src="" alt="Avatar Preview">
                        <?php endif; ?>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-slate-805 dark:text-white mt-4 font-display" id="display-admin-name"><?= htmlspecialchars($admin['name']) ?></h3>
                <span class="px-2.5 py-1 text-[11px] font-bold tracking-wider uppercase rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 mt-1">Administrator</span>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-4 leading-relaxed italic" id="display-admin-bio">
                    <?= $admin['bio'] ? htmlspecialchars($admin['bio']) : '"Belum menulis deskripsi bio."' ?>
                </p>

                <div class="w-full border-t border-slate-100 dark:border-slate-700/50 my-5"></div>

                <div class="w-full text-left space-y-3">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">Email</span>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate" id="display-admin-email"><?= htmlspecialchars($admin['email']) ?></p>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">No. Handphone</span>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 font-mono" id="display-admin-phone"><?= $admin['phone'] ? htmlspecialchars($admin['phone']) : '-' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 2 & 3: Forms -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Card 1: Biodata -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm transition-colors duration-300">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4 font-display flex items-center space-x-2">
                    <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Informasi Profil</span>
                </h2>

                <form id="profile-info-form" action="index.php?page=admin_profile_process&action=update_profile" method="POST" enctype="multipart/form-data" class="space-y-5">

                    <?= csrf_field() ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Nama Lengkap</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Alamat Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">No. Handphone</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition" placeholder="+628123...">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Foto Profil (Avatar)</label>
                            <input type="file" id="avatar-input" name="avatar" accept="image/*" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-4 py-2 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/40 dark:file:text-indigo-400 hover:file:opacity-90 transition cursor-pointer">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Bio Singkat</label>
                        <textarea name="bio" rows="3" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition" placeholder="Tulis bio singkat mengenai diri Anda..."><?= htmlspecialchars($admin['bio'] ?? '') ?></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" id="btn-save-profile" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs tracking-wider uppercase transition shadow-lg shadow-indigo-600/20 active:scale-[0.98]">Simpan Perubahan</button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Ubah Password -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm transition-colors duration-300">
                <h2 class="text-lg font-bold text-slate-805 dark:text-white mb-4 font-display flex items-center space-x-2">
                    <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Ubah Kredensial Password</span>
                </h2>

                <form id="profile-password-form" action="index.php?page=admin_profile_process&action=change_password" method="POST" class="space-y-5">

                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Password Lama</label>
                        <input type="password" name="old_password" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Password Baru</label>
                            <input type="password" name="new_password" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" id="btn-save-password" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs tracking-wider uppercase transition shadow-lg shadow-indigo-600/20 active:scale-[0.98]">Ubah Password</button>
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>

<script src="assets/js/jquery.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
<script src="assets/js/sweetalert2.all.min.js"></script>
<script src="assets/js/pages/admin-profile.js"></script>
