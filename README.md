# NusaBay - CMS Toko Online Premium

NusaBay adalah platform Content Management System (CMS) Toko Online yang dibangun menggunakan PHP Native (tanpa Composer) untuk kemudahan integrasi dan deploy pada hosting cPanel / shared hosting.

## Fitur Utama

- **Authentication & Security Overhaul**: Sistem registrasi, login, logout, verifikasi kode OTP email, dengan enkripsi hashing aman (BCRYPT).
- **Service Layer Architecture**: Logika bisnis dan kueri database dipisahkan dari controller ke dalam Service classes (`AuthService`, `ProductService`, `OrderService`) untuk kode yang rapi dan scalable.
- **Dynamic Footer & Essential Pages**: Pengaturan dinamis sosial media, alamat, deskripsi footer dari panel admin, serta halaman Syarat & Ketentuan (TOS), Kebijakan Privasi, dan FAQ.
- **Sistem Checkout & Stok Aman**: Pengurangan stok aman menggunakan row locking (`FOR UPDATE`) selama transaksi pemesanan untuk menghindari race conditions.
- **Kode Promo**: Dukungan diskon persentase/nominal belanja dengan validasi masa berlaku dan kuota.
- **Konfigurasi Berbasis Lingkungan (`.env`)**: Membaca kredensial database dan SMTP secara aman dan terenkripsi menggunakan parser `.env` buatan sendiri (cPanel friendly).

## Struktur Direktori Utama

```
├── config/              # Inisialisasi Database, Helper, Mailer, dan .env Loader
├── controllers/         # Menangani HTTP request dan routing logika controller
├── services/            # Service Layer (AuthService, ProductService, OrderService)
├── views/               # Tampilan UI publik dan admin panel
├── uploads/             # Direktori penyimpanan gambar produk dan avatar user
├── .env.example         # Template konfigurasi variabel lingkungan
├── index.php            # Front Controller & Main router
└── setup_fresh.php      # Skrip instalasi & seeding database awal
```

## Persyaratan Sistem

- PHP 7.4 atau versi di atasnya
- Ext-PDO & PDO_MySQL enabled
- Web Server (Apache/Nginx/cPanel)
- Koneksi Internet (untuk SMTP/PHPMailer pengiriman OTP)

## Petunjuk Instalasi & Setup

1. **Unduh/Klon Proyek**:
   Unduh file proyek dan tempatkan di direktori web server Anda (misal `htdocs` atau `public_html`).

2. **Konfigurasi Lingkungan (`.env`)**:
   Salin file `.env.example` menjadi `.env` di root direktori proyek.
   ```bash
   cp .env.example .env
   ```
   Buka file `.env` dan sesuaikan nilainya:
   - **DB_URL**: Format url database MySQL Anda (`mysql://user:pass@host:port/dbname?ssl-mode=REQUIRED`)
   - **SMTP_HOST / SMTP_PORT / SMTP_USER / SMTP_PASS**: Kredensial email Anda untuk pengiriman kode OTP.
   - **APP_ENV**: Ubah ke `production` untuk menyembunyikan log error detail database.

3. **Inisialisasi Database**:
   Jalankan file `setup_fresh.php` dari browser Anda (misalnya: `http://localhost/cms-toko-online/setup_fresh.php`) untuk membuat tabel-tabel database secara otomatis dan menginput data awal (seed data).

4. **Selesai**:
   Aplikasi NusaBay siap digunakan! Buka `index.php` di browser Anda.

---

*Dikembangkan dengan penuh dedikasi untuk kemudahan deployment & keamanan prima.*
