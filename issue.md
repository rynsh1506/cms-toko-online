# Implementasi Fitur Show Password & Validasi Keamanan Password

**Target Audiens:** Junior Programmer / AI Assistant
**Tujuan:**
1. Menambahkan tombol "Show/Hide Password" pada halaman Login dan Register.
2. Menambahkan validasi keamanan password pada halaman Register (minimal 8 karakter, wajib mengandung huruf besar, huruf kecil, angka, dan simbol).

---

## 🛠️ Tugas 1: Fitur Show/Hide Password

Anda perlu memodifikasi file HTML/PHP view dan file JavaScript (frontend) untuk halaman Login dan Register.

### Langkah 1: Modifikasi View Login (`views/public/login.php`)
Buka file `views/public/login.php` dan cari bagian `<input type="password" ...>`. Ubah struktur HTML pembungkusnya menjadi seperti ini:

```html
<div>
    <label class="block text-slate-700 dark:text-slate-400 text-xs font-bold mb-1.5">Password</label>
    <div class="relative">
        <input type="password" name="password" id="password-input" required placeholder="••••••••"
            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white px-3.5 py-2.5 pr-12 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-sm">
        
        <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-350 focus:outline-none cursor-pointer">
            <!-- Icon Mata Terbuka (Show) -->
            <svg id="eye-icon-show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <!-- Icon Mata Tertutup (Hide) - Default disembunyikan -->
            <svg id="eye-icon-hide" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
            </svg>
        </button>
    </div>
</div>
```

### Langkah 2: Modifikasi View Register (`views/public/register.php`)
Lakukan perubahan HTML yang persis sama pada `views/public/register.php` di bagian input password. 
*Catatan Penting: Pastikan class CSS input memiliki `pr-12` (padding-right) agar teks yang diketik panjang tidak bertumpuk di bawah ikon mata.*

### Langkah 3: Tambahkan Logika JavaScript (Frontend)
Tambahkan kode JavaScript berikut ke bagian paling bawah di dalam fungsi `$(document).ready(...)` pada **kedua file berikut**: 
1. `assets/js/pages/login.js`
2. `assets/js/pages/register.js`

```javascript
// Fitur Toggle Show/Hide Password
$('#toggle-password').on('click', function () {
    const passwordInput = $('#password-input');
    const eyeIconShow = $('#eye-icon-show');
    const eyeIconHide = $('#eye-icon-hide');
    
    if (passwordInput.attr('type') === 'password') {
        passwordInput.attr('type', 'text');
        eyeIconShow.addClass('hidden');
        eyeIconHide.removeClass('hidden');
    } else {
        passwordInput.attr('type', 'password');
        eyeIconShow.removeClass('hidden');
        eyeIconHide.addClass('hidden');
    }
});
```

---

## 🛠️ Tugas 2: Validasi Kekuatan Password (Pendaftaran)

Sistem harus menolak pendaftaran jika password tidak memenuhi syarat: 
- Minimal 8 karakter
- Minimal 1 huruf besar (A-Z)
- Minimal 1 huruf kecil (a-z)
- Minimal 1 angka (0-9)
- Minimal 1 simbol/karakter unik (!@#$% dsb.)

### Langkah 1: Validasi Backend (PHP)
Buka file `services/AuthFlowService.php`. Cari metode `register(array $data)`.
Tambahkan validasi keamanan ini sebelum fungsi pengecekan persetujuan `agree_tos`.

```php
// Cek kompleksitas password
if (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[^a-zA-Z0-9]/', $password)
) {
    return $this->error(
        'Password minimal 8 karakter dan harus mengandung huruf besar, huruf kecil, angka, serta simbol.', 
        'index.php?page=register'
    );
}
```

### Langkah 2: Validasi Frontend (JavaScript)
Meskipun ada validasi backend, kita juga perlu mencegah form disubmit dari awal jika password lemah. Buka `assets/js/pages/register.js` dan tambahkan validasi ini di dalam fungsi `$('#register-form').on('submit', function(e) { ... })` tepat **setelah `e.preventDefault();`**.

```javascript
const password = $('input[name="password"]').val();
const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/;

if (!passwordRegex.test(password)) {
    $('#alert-container').html(`
        <div class="bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500 text-rose-800 dark:text-rose-400 p-3.5 rounded-r-xl mb-4 text-xs font-semibold">
            Password minimal 8 karakter dan harus mengandung huruf besar, huruf kecil, angka, serta simbol.
        </div>
    `);
    return false; // Berhenti di sini, batalkan proses ajax
}
```

---

## ✅ Daftar Periksa (Checklist) QA
1. **[ ]** Buka halaman Login. Ketik password, klik ikon mata. Teks password harus terlihat. Klik lagi, harus kembali tersembunyi.
2. **[ ]** Lakukan hal yang sama (poin 1) di halaman Register.
3. **[ ]** Coba mendaftar akun baru dengan password `12345` (kurang dari 8 char). Pastikan muncul pesan error.
4. **[ ]** Coba daftar dengan `passwordkecil` (hanya huruf kecil). Pastikan muncul pesan error.
5. **[ ]** Coba daftar dengan `PasswordBesarKecil1` (tanpa simbol). Pastikan muncul pesan error.
6. **[ ]** Coba daftar dengan password kuat `P@ssword123`. Pastikan pendaftaran lolos validasi keamanan dan diarahkan ke halaman verifikasi email.
