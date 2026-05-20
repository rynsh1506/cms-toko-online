# Panduan Deployment (Deployment Guide) - Pro-Store CMS

Dokumen ini berisi panduan langkah-demi-langkah untuk melakukan deployment aplikasi **Pro-Store CMS** (Mini-Framework PHP Native) ke layanan hosting web seperti cPanel, Hostinger, InfinityFree, atau ProFreeHost.

---

## 1. Persyaratan Server (Server Requirements)
* **PHP**: Versi 8.0 atau yang lebih baru.
* **Database**: MySQL / MariaDB dengan ekstensi **PDO** diaktifkan.
* **Web Server**: Apache (direkomendasikan, untuk mendukung proteksi `.htaccess`) atau Nginx.
* **Modul Apache**: `mod_rewrite` diaktifkan (untuk proteksi direktori).

---

## 2. Langkah-Langkah Deployment

### Langkah 1: Persiapkan File Proyek
1. Download seluruh *source code* dari repositori ini ke komputer Anda.
2. Pastikan file `.htaccess` di root proyek ikut tersalin (beberapa sistem operasi menyembunyikan file dengan awalan titik).

### Langkah 2: Konfigurasi Database
1. Buat database MySQL baru melalui cPanel (MySQL Database Wizard) atau control panel hosting Anda. Catat informasi berikut:
   * **Database Host** (biasanya `localhost`, atau alamat IP tertentu jika menggunakan external DB).
   * **Database Name**
   * **Database User**
   * **Database Password**
   * **Database Port** (default MySQL adalah `3306`).
2. Duplikat file `config/db.php.example` dan ubah namanya menjadi `config/db.php`.
3. Buka file `config/db.php` dan sesuaikan URL koneksi database pada baris berikut:
   ```php
   define('DB_URL', 'mysql://USERNAME:PASSWORD@HOST:PORT/DATABASE_NAME');
   ```
   *Ganti `USERNAME`, `PASSWORD`, `HOST`, `PORT`, dan `DATABASE_NAME` dengan informasi database Anda.*

### Langkah 3: Upload File ke Hosting
1. Kompres semua folder dan file proyek (termasuk folder `config`, `controllers`, `views`, `uploads`, dll.) ke dalam file `.zip`.
2. Masuk ke **File Manager** di control panel hosting Anda.
3. Buka direktori root website Anda (biasanya bernama `public_html` atau `htdocs`).
4. Upload file `.zip` tersebut, lalu **Extract** di dalam direktori tersebut.
5. Pastikan folder `uploads` memiliki izin akses menulis (**CHMOD 755** atau **777** jika diperlukan oleh server hosting Anda) agar pembeli/admin bisa mengunggah gambar produk.

### Langkah 4: Inisialisasi Database (Migration)
Untuk membuat tabel-tabel database yang diperlukan, akses file setup secara berurutan melalui browser Anda:
1. Jalankan Skema Inti:  
   `https://domain-anda.com/setup_db.php`
2. Jalankan Konfigurasi Landing Page:  
   `https://domain-anda.com/setup_configs_db.php`
3. Jalankan Pengaturan Produk:  
   `https://domain-anda.com/setup_products_db.php`
4. Jalankan Skema Transaksi:  
   `https://domain-anda.com/setup_orders_db.php`

> [!WARNING]
> **PENTING UNTUK KEAMANAN (CRITICAL SECURITY STEP)**
> Setelah semua proses setup database di atas berhasil dijalankan, **SEGERA HAPUS** file-file berikut dari server hosting Anda melalui File Manager:
> * `setup_db.php`
> * `setup_configs_db.php`
> * `setup_products_db.php`
> * `setup_orders_db.php`
> 
> Membiarkan file-file ini tetap berada di server dapat dimanfaatkan oleh pihak tidak bertanggung jawab untuk mereset database atau mencuri data sensitif Anda.

---

## 3. Fitur Keamanan Bawaan
Aplikasi ini sudah dilengkapi dengan berkas `.htaccess` di direktori root yang berfungsi untuk:
1. **Mencegah Directory Browsing**: Mencegah pengunjung melihat isi folder secara transparan jika folder tersebut tidak memiliki file indeks.
2. **Memblokir Folder Sistem**: Mencegah akses HTTP langsung ke folder `config/`, `controllers/`, `views/`, dan `.git/`. Pengunjung yang mencoba mengakses folder tersebut akan mendapatkan pesan *403 Forbidden*.
