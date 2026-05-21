<?php
// Pastikan $configs ada. Jika tidak, query DB
if (!isset($configs)) {
    $stmt = $pdo->query("SELECT section_key, content_value FROM landing_configs");
    $configs_raw = $stmt->fetchAll();
    $configs = [];
    foreach ($configs_raw as $c) {
        $configs[$c['section_key']] = $c['content_value'];
    }
}
$footer_desc = $configs['footer_description'] ?? 'NusaBay adalah toko online tepercaya untuk segala kebutuhan Anda. Belanja aman, cepat, dan mudah.';
$footer_addr = $configs['footer_address'] ?? 'Jl. Merdeka No. 45, Jakarta Pusat, DKI Jakarta 10110';
$footer_email = $configs['footer_email'] ?? 'support@nusabay.com';
$fb_link = $configs['social_facebook'] ?? '';
$ig_link = $configs['social_instagram'] ?? '';
$tw_link = $configs['social_twitter'] ?? '';
$wa_link = $configs['social_whatsapp'] ?? '';
?>
<footer class="bg-slate-900 text-slate-400 py-12 mt-auto border-t border-slate-800 print:hidden transition-colors duration-300 font-sans">
    <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-8">
        <div class="col-span-1 md:col-span-2">
            <a href="index.php?page=home" class="text-2xl font-black tracking-tight text-white font-display flex items-center space-x-2 mb-4">
                <svg class="h-8 w-8 rounded-lg shadow-lg shadow-indigo-500/20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="12" fill="url(#logo-grad-footer)" />
                    <rect x="10" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                    <rect x="30" y="8" width="8" height="32" rx="2" fill="#ffffff" />
                    <rect x="20" y="6" width="8" height="36" rx="2" fill="#ffffff" transform="rotate(-32 24 24)" />
                    <defs>
                        <linearGradient id="logo-grad-footer" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#6366f1"/>
                            <stop offset="1" stop-color="#a855f7"/>
                        </linearGradient>
                    </defs>
                </svg>
                <span>Nusa<span class="text-indigo-400">Bay</span></span>
            </a>
            <p class="text-sm text-slate-400 leading-relaxed max-w-sm mb-6">
                <?= htmlspecialchars($footer_desc) ?>
            </p>
            <div class="flex items-center space-x-4">
                <?php if($fb_link): ?>
                <a href="<?= htmlspecialchars($fb_link) ?>" target="_blank" class="h-10 w-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:bg-indigo-600 hover:text-white transition">
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M22.675 0h-21.35C.597 0 0 .597 0 1.325v21.351C0 23.403.597 24 1.325 24H12.82v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116c.73 0 1.323-.597 1.323-1.324V1.325C24 .597 23.403 0 22.675 0z"/></svg>
                </a>
                <?php endif; ?>
                <?php if($ig_link): ?>
                <a href="<?= htmlspecialchars($ig_link) ?>" target="_blank" class="h-10 w-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:bg-pink-600 hover:text-white transition">
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                </a>
                <?php endif; ?>
                <?php if($tw_link): ?>
                <a href="<?= htmlspecialchars($tw_link) ?>" target="_blank" class="h-10 w-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:bg-sky-500 hover:text-white transition">
                    <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                </a>
                <?php endif; ?>
                <?php if($wa_link): ?>
                <a href="<?= htmlspecialchars($wa_link) ?>" target="_blank" class="h-10 w-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:bg-green-500 hover:text-white transition">
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.333 4.993L2 22l5.233-1.371a9.936 9.936 0 004.777 1.218h.005c5.505 0 9.987-4.479 9.988-9.986A9.972 9.972 0 0012.012 2zm5.73 14.184c-.313.88-1.56 1.621-2.148 1.68-.482.05-1.107.078-2.61-.54-2.023-.831-3.328-2.887-3.428-3.021-.1-.133-.805-.968-.805-1.847 0-.878.46-1.31.625-1.488.164-.179.359-.224.479-.224h.343c.108 0 .252-.041.396.302.144.343.493 1.205.536 1.293.043.088.072.19.014.302-.057.113-.086.183-.172.283-.086.102-.18.228-.258.309-.086.088-.176.183-.076.353.1.171.444.733.953 1.187.658.583 1.21.764 1.38.849.171.085.271.071.371-.044.1-.115.43-.5.545-.672.115-.172.23-.143.389-.085.158.058 1.005.474 1.178.56.172.087.288.13.33.202.043.072.043.415-.27 1.294z"/></svg>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div>
            <h4 class="text-white font-bold mb-4 font-display">Informasi</h4>
            <ul class="space-y-2 text-sm font-semibold">
                <li><a href="index.php?page=faq" class="hover:text-white transition">Tanya Jawab (FAQ)</a></li>
                <li><a href="index.php?page=tos" class="hover:text-white transition">Syarat & Ketentuan</a></li>
                <li><a href="index.php?page=privacy" class="hover:text-white transition">Kebijakan Privasi</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-white font-bold mb-4 font-display">Hubungi Kami</h4>
            <ul class="space-y-3 text-sm">
                <li class="flex items-start space-x-2">
                    <svg class="h-4 w-4 mt-0.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span><?= htmlspecialchars($footer_addr) ?></span>
                </li>
                <li class="flex items-center space-x-2">
                    <svg class="h-4 w-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span><?= htmlspecialchars($footer_email) ?></span>
                </li>
            </ul>
        </div>
    </div>
    <div class="max-w-6xl mx-auto px-6 mt-12 pt-6 border-t border-slate-800 text-center text-xs font-mono">
        <p>&copy; <?= date('Y') ?> NusaBay. Hak cipta dilindungi undang-undang.</p>
    </div>
</footer>
