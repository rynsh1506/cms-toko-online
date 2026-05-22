# Technical Debt & Architecture Refactoring Plan

Berdasarkan hasil audit sistem CMS Toko Online, arsitektur dasar telah di-refactor menjadi MVC dan Service Pattern yang cukup solid. Perlindungan dasar seperti PDO (SQL Injection), CSRF Token, dan pemisahan Javascript sudah diterapkan dengan baik. Perbaikan keamanan kritikal (*Quick Wins*) juga telah dieksekusi (menggunakan `random_int()` dan `random_bytes()` untuk generator keamanan).

Namun, ada beberapa utang teknis (*technical debt*) struktural yang harus diatasi agar aplikasi siap untuk skala yang jauh lebih besar di masa depan. Silakan eksekusi fitur-fitur di bawah ini secara bertahap.

## 🎯 Tugas Junior Programmer / Tim Developer Selanjutnya

### 1. Implementasi Autoloader (PSR-4)
**Masalah:** Saat ini aplikasi menggunakan sangat banyak `require_once __DIR__ . '/...'` di berbagai file. Hal ini membuat kode tidak *scalable* ketika file Controller dan Service semakin bertambah banyak, dan rentan terjadi *fatal error* jika urutan load salah.
**Tugas:**
- Buat file `composer.json` di root project.
- Implementasikan standar PSR-4 dengan struktur `namespace App\Controllers;` dan `namespace App\Services;`.
- Pindahkan semua class ke namespace masing-masing.
- Jalankan `composer dump-autoload` dan ganti semua kumpulan `require_once` di `index.php` menjadi cukup memanggil `require __DIR__ . '/vendor/autoload.php';`.

### 2. Dependency Injection (DI) Container
**Masalah:** Objek `$pdo` dilempar (di-pass) secara manual berulang-ulang ke Controller dan Service (Contoh di `ProductController`: `$service = new ProductManagementService(new ProductService($this->pdo), ...)`). Pola instalasi manual seperti ini (Hardcoded Dependencies) akan berubah menjadi *spaghetti code* saat satu Service butuh 3-5 dependensi tambahan.
**Tugas:**
- Buat class `Container` sederhana atau manfaatkan library ringan seperti `PHP-DI`.
- Lakukan registrasi *PDO instance* dan *Service-service* ke dalam container.
- Sesuaikan `index.php` dan Controller agar otomatis mengambil dependensi dari Container (menggunakan constructor injection secara dinamis).

### 3. Request Validator Class
**Masalah:** Validasi input seperti `if (empty($name))` atau proses sanitasi `sanitize_input($_POST['name'])` tersebar luas di lapisan Controller dan Service. Hal ini melanggar *Single Responsibility Principle* dan membuat pengujian logika inti menjadi sulit.
**Tugas:**
- Buat abstraksi class `Request` khusus yang bertugas menangani segala jenis validasi form.
- Contoh implementasi akhir: `$validatedData = $request->validate(['name' => 'required', 'price' => 'numeric']);`
- Bersihkan Controller dan Service dari proses cek validasi mentah, sehingga Service hanya fokus mengeksekusi logika bisnis.

### 4. Sistem Migrasi Database (Database Migrations)
**Masalah:** Pembuatan dan pembaruan skema database masih menggunakan eksekusi mentah file SQL atau script `setup_fresh.php`. Cara ini sangat rawan saat bekerja di dalam sebuah tim, di mana status database masing-masing anggota bisa tidak sinkron.
**Tugas:**
- Terapkan sistem migrasi database berbasis PHP (bisa kustom atau memakai package yang sudah ada seperti `Phinx`).
- Buat struktur di mana setiap modifikasi struktur tabel direpresentasikan oleh satu file migrasi ter-versioning (misal: `20231010_123456_create_users_table.php`).
- Pastikan ada command line/skrip sederhana untuk mengeksekusi `migrate` (menjalankan struktur baru) dan `rollback` (mengembalikan struktur).

---
**Catatan untuk Implementator:**  
Harap kerjakan secara berurutan. Prioritaskan Autoloader terlebih dahulu untuk mempermudah manajemen file sebelum mengeksekusi implementasi lainnya. Jangan lupa tambahkan *Unit Test* ringan untuk setiap sistem baru yang Anda buat jika memungkinkan.
