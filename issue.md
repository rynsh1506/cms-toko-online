# Perencanaan Perbaikan CSRF Validation secara Menyeluruh

## Deskripsi Masalah
Sistem validasi CSRF (Cross-Site Request Forgery) saat ini menyebabkan banyak error ("ngaco") pada pengoperasian aplikasi. Hal ini umumnya disebabkan oleh implementasi yang tidak merata, seperti:
1. Ada form HTML yang tidak menyertakan input tersembunyi untuk `csrf_token`.
2. Permintaan AJAX/Fetch API (terutama di halaman Admin/Dashboard) gagal mengirimkan token CSRF melalui *header* atau *payload* data.
3. Inkonsistensi pengecekan di level Controller, sehingga request yang seharusnya aman malah diblokir.
4. Token di dalam session (`$_SESSION['csrf_token']`) mungkin berubah atau tidak disinkronisasi dengan *meta tag* di *frontend*.

## Tujuan
Melakukan standardisasi, perbaikan, dan injeksi CSRF token ke seluruh penjuru aplikasi (Backend & Frontend) agar sistem kembali aman tanpa mengorbankan fungsionalitas pengguna (bebas dari error 403 / *Invalid Token*).

---

## Tahapan Implementasi (Instruksi Detail untuk Programmer / AI)

### Tahap 1: Perbaikan Backend & Standarisasi Controller
1. **Buka file `controllers/BaseController.php`** (atau di mana pun metode validasi CSRF berada).
2. **Modifikasi metode pengecekan CSRF:**
   - Pastikan pengecekan HANYA berjalan pada *Request Method* yang memanipulasi data: `POST`, `PUT`, `PATCH`, `DELETE`. (Abaikan jika method adalah `GET` atau `OPTIONS`).
   - Ubah logika penarikan token agar bisa membaca dari dua sumber:
     a. `$_POST['csrf_token']` (Untuk form tradisional HTML).
     b. `$_SERVER['HTTP_X_CSRF_TOKEN']` (Untuk request AJAX dari jQuery/Fetch).
3. **Buat Global Meta Tag:**
   - Masuk ke file layout utama (seperti `views/layouts/header.php`, `views/admin/layout.php`, atau `views/public/header.php`).
   - Tambahkan tag meta berikut di dalam `<head>`: 
     `<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">`

### Tahap 2: Audit dan Perbaikan Form HTML Tradisional
1. **Gunakan fungsi pencarian (grep/search) untuk menemukan semua tag `<form method="POST"`** di dalam folder `views/`.
2. **Injeksi Token:**
   - Di bawah setiap tag `<form...>`, tambahkan input tersembunyi:
     `<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">`
3. **Prioritas Halaman yang Wajib Diperiksa:**
   - `views/auth/login.php` & `views/auth/register.php`
   - Form alamat / profil pengguna.
   - Form Checkout (Cart).

### Tahap 3: Perbaikan AJAX & Javascript (Frontend)
1. **Gunakan pencarian untuk menemukan semua eksekusi `$.ajax`, `$.post`, dan `fetch`** di folder `assets/js/`.
2. **Injeksi Header CSRF secara Global (Untuk jQuery):**
   - Buka file javascript utama yang diload di semua halaman (misal `assets/js/main.js` atau buat *script tag* di layout).
   - Tambahkan konfigurasi ini agar jQuery selalu mengirim header otomatis:
     ```javascript
     $.ajaxSetup({
         headers: {
             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
         }
     });
     ```
3. **Perbaikan Request `FormData`:**
   - Pada file `assets/js/pages/admin-products.js` atau JS lain yang menggunakan `new FormData(this)`, pastikan kita secara manual menyisipkan CSRF token jika backend masih menuntut parameter POST (bukan header).
   - `formData.append('csrf_token', $('meta[name="csrf-token"]').attr('content'));`

### Tahap 4: Testing & Quality Assurance
Setelah semua tahap selesai, lakukan simulasi pengujian wajib:
- [ ] Lakukan **Login** dan **Register**.
- [ ] Buka halaman **Admin > Kelola Produk**. Lakukan Tambah Produk, Edit Produk (termasuk upload gambar via AJAX), dan Hapus Produk.
- [ ] Buka modal **Kelola Varian**, coba Tambah dan Hapus Varian.
- [ ] Lakukan simulasi pembelian dari sisi User (Tambah ke keranjang, proses Checkout).
- [ ] Jika tidak ada pesan *Error 403* atau *CSRF Validation Failed*, maka tugas dinyatakan Selesai.

---
**Catatan untuk Implementator:** Jangan hapus validasi CSRF dari sistem (jangan mem-bypass security). Tujuan tugas ini adalah **memperbaiki cara token dikirimkan** oleh frontend agar sesuai dengan ekspektasi backend.
