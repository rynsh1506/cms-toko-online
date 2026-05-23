# Perencanaan Audit & Refactoring Keseluruhan (Code Standard, Security, & Logging)

## Deskripsi Masalah
Aplikasi saat ini telah memiliki fungsionalitas yang sangat baik. Namun, seiring bertambahnya kompleksitas fitur, kita perlu memastikan bahwa seluruh *codebase* mudah dibaca oleh programmer pemula, aman dari ancaman siber berskala internasional (panduan keamanan global), memiliki sistem *Logging* terpadu agar *error* mudah dilacak (Traceable), dan memiliki pondasi yang kuat agar mudah dikembangkan (Scalable) di masa depan.

## Tujuan Utama
1. **Keamanan Internasional (Security Standard):** Mematuhi panduan dasar OWASP Top 10 (Pencegahan SQL Injection, XSS, pengamanan Session, dan mitigasi bahaya file upload).
2. **Keterbacaan (Readability):** Menjadikan kode sangat mudah dimengerti dengan penamaan variabel logis, struktur komentar (DocBlocks) yang jelas, dan gaya kode terstandarisasi.
3. **Skalabilitas (Scalability):** Memastikan pola arsitektur *Service Layer* / MVC ditaati 100% sehingga penambahan fungsionalitas baru tidak akan merusak sistem yang sudah ada.
4. **Sistem Pelacakan Error (Centralized Logger):** Membuat sistem penulisan *log* terpusat untuk mempermudah investigasi (tracing) jika terjadi kejanggalan atau *error* tersembunyi.

---

## Tahapan Implementasi (Instruksi Detail untuk Junior Programmer / AI)

### Tahap 1: Implementasi Centralized Logger (Sistem Pelacakan)
1. **Buat Class/Helper Logger:**
   - **Tugas:** Buat satu file khusus (misal `config/Logger.php` atau tambahkan fungsi `app_log()` di `config/helpers.php`).
   - **Aksi:** Fungsi/Class ini bertugas menangkap pesan *error*, nama file asal error, baris kode (line number), dan waktu kejadian (timestamp), lalu menyimpannya ke dalam file lokal (misal: `logs/app-YYYY-MM-DD.log`).
2. **Injeksi Try-Catch Global:**
   - **Tugas:** Di dalam seluruh *Service* dan *Controller*.
   - **Aksi:** Bungkus logika krusial (terutama eksekusi database `execute()`) dengan blok `try { ... } catch (Exception $e) { ... }`.
   - **Solusi:** Di dalam blok `catch`, panggil `Logger::error($e->getMessage())` sebelum melempar kembali error atau menampilkan pesan aman ke sisi User (agar detail error teknis tidak bocor ke publik, namun tercatat di *backend*).

### Tahap 2: Audit Keamanan (Security Hardening)
1. **Pencegahan XSS (Cross-Site Scripting) pada View:**
   - **Tugas:** Sisir seluruh file berekstensi `.php` di dalam folder `views/`.
   - **Aksi:** Cari setiap tag `<?= $variable ?>` yang berisi data dari database atau *input* pengguna. Bungkus semuanya dengan fungsi sanitasi.
   - **Solusi:** Buat helper global `esc($string)` di `config/helpers.php` (berisi `htmlspecialchars($string, ENT_QUOTES, 'UTF-8')`). Lalu ubah output menjadi `<?= esc($variable) ?>`.
2. **Keamanan Ekstrem File Upload:**
   - **Tugas:** Periksa fungsi penanganan *file upload* pada layanan seperti `ProductManagementService.php` atau profil pengguna.
   - **Aksi:** Jangan hanya percaya pada ekstensi file yang diunggah. Pastikan validasi mendeteksi tipe MIME yang sebenarnya melalui fungsi backend (contoh: `finfo_file`). Ganti (rename) nama file menjadi nama acak (misal: `uniqid()`) sebelum disimpan untuk menghindari serangan injeksi nama file.
3. **Sapu Bersih SQL Injection:**
   - **Tugas:** Periksa keseluruhan sintaks eksekusi database di folder `services/`.
   - **Aksi:** Pastikan **TIDAK ADA** kueri yang menggabungkan (*concatenate*) string SQL dengan variabel secara langsung (misal: `"SELECT * FROM users WHERE id = " . $id`). Segala macam input dinamis harus menggunakan **Prepared Statements** PDO (`$stmt->prepare()` dan di-bind dengan parameter sesungguhnya).

### Tahap 3: Pembersihan Kode (Clean Code & Readability)
1. **Standardisasi Penamaan:**
   - Variabel biasa gunakan gaya yang konsisten, disarankan `camelCase` (misal: `$totalPrice` atau `$productImage`).
   - Fungsi dan Method harus berbentuk `camelCase` dan diawali dengan kata kerja jelas (contoh: `getUserById()`, bukan sekadar `user()`).
2. **Penambahan Dokumentasi (DocBlocks):**
   - Semua kelas (*class*) di `controllers/` dan `services/` **wajib** diberi komentar penjelasan.
   - Tambahkan blok komentar di atas deklarasi fungsi untuk menjelaskan apa kegunaan fungsi tersebut, parameter apa yang dibutuhkan (`@param`), dan tipe data apa yang dikembalikan (`@return`). Hal ini sangat menolong *programmer pemula*.
3. **Penghapusan Baris "Sampah":**
   - Cari dan hapus seluruh sintaks *debugging* yang tertinggal (`var_dump`, `print_r`, atau `console.log`).
   - Bersihkan baris-baris kode tak terpakai yang dikomentari (*dead code/commented-out blocks*).

### Tahap 4: Restrukturisasi Skalabilitas (Arsitektur)
1. **Pemurnian Controller:**
   - Buka seluruh file di direktori `controllers/`.
   - Jika masih ditemukan adanya eksekusi logika database (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) di dalam controller, segera pindahkan logika tersebut ke dalam sebuah *Class* di dalam folder `services/`. Controller hanya boleh bertugas menangkap *Request* dan merespon dengan *View* atau *JSON*.
2. **Pembersihan Logika dari View:**
   - Buka seluruh file HTML/PHP di `views/`.
   - Pastikan tidak ada satupun *query database* atau perhitungan bisnis berat di dalam layer tampilan. View murni untuk menyajikan elemen Visual (*looping* daftar produk dan *if-else* kondisional ringan).
3. **Implementasi Autoloader Sederhana:**
   - Agar tidak terus menerus memanggil `require_once` di puluhan file, pastikan di dalam `index.php` telah terpasang fungsi `spl_autoload_register()` yang cerdas melacak kelas di folder `controllers/` maupun `services/` secara dinamis.

### Tahap 5: Quality Control & Validation
- Nyalakan pelaporan error (`error_reporting(E_ALL); ini_set('display_errors', 1);`) selama inspeksi di localhost. Cek ke tab konsol pada *Browser Inspect Element*.
- Jika seluruh proses selesai, tidak boleh ada satupun `Notice`, `Warning`, atau *Console Error* yang muncul di layar, apalagi layar kosong (Fatal Error). Pastikan log file (`logs/...`) terisi otomatis setiap ada tangkapan error dari Catch-block.

---
**Catatan Penting untuk Implementator:** 
Jangan terburu-buru. Kerjakan ini bertahap per modul/tahapan. **Ingat:** Pekerjaan Anda di tahap ini bukanlah membuat fungsionalitas baru, melainkan *merapikan dan mengamankan* apa yang sudah ada. Keamanan tingkat internasional berarti tidak menoleransi satu celah XSS pun pada formulir pengguna.
