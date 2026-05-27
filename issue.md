# Perencanaan Fitur: Penjaminan Ketersediaan Gambar Dummy (Seed Images)

**Target Audiens:** Junior Programmer / AI Assistant
**Tujuan:** Memastikan semua gambar yang di-generate oleh `setup_fresh.php` (saat ini menggunakan link Unsplash) selalu dapat dimuat dan tidak ada link mati (broken link). Gambar yang bagus adalah kunci utama untuk menarik visitor, sehingga keandalan gambar demo sangat krusial.

---

## 🛑 Masalah Saat Ini
File `setup_fresh.php` menggunakan direct link ke Unsplash (`https://images.unsplash.com/photo-...`). 
Link eksternal ini sangat berisiko:
1. **Author menghapus foto:** Jika fotografer menghapus foto tersebut dari Unsplash, link akan mengembalikan error 404 (Not Found).
2. **Perubahan parameter URL:** Unsplash terkadang mengubah arsitektur URL atau parameter resize mereka.
3. **Koneksi / Rate Limiting:** Server pengguna mungkin mengalami kendala koneksi ke server Unsplash saat demo dijalankan.

---

## 🛠️ Solusi & Tahapan Implementasi

Solusi terbaik dan paling permanen untuk mengatasi masalah ini adalah **mengunduh (download) semua gambar tersebut dan menyimpannya secara lokal di dalam repositori proyek**, lalu mengubah path di database agar mengarah ke file lokal tersebut. 

Sebagai lapisan keamanan tambahan (fallback), kita juga akan menambahkan mekanisme gambar *placeholder* bawaan.

Berikut adalah langkah-langkah detail yang harus Anda implementasikan:

### Tahap 1: Persiapan Direktori & Pengunduhan Gambar (Local Assets)

1. **Buat Direktori Khusus:**
   Buat sebuah folder baru di dalam direktori `assets` untuk menyimpan gambar-gambar dummy (seed data).
   - Buat folder: `assets/images/seed/`
   - Buat juga folder untuk gambar cadangan mutlak: `assets/images/placeholder/`

2. **Unduh Semua Gambar dari `setup_fresh.php`:**
   Buka file `setup_fresh.php` dan temukan semua URL `https://images.unsplash.com/...` (berada di bagian kueri `INSERT` untuk produk dan banner).
   - Unduh setiap gambar tersebut satu per satu.
   - Simpan ke dalam folder `assets/images/seed/`.
   - **PENTING:** Beri nama file yang deskriptif dan seragam agar rapi. 
     Contoh: `product-fashion-1.jpg`, `product-elektronik-1.jpg`, `banner-promo-1.jpg`.

3. **Siapkan Gambar Fallback (Placeholder):**
   Siapkan satu gambar default yang bagus (bisa berupa logo NusaBay dengan latar belakang elegan atau ilustrasi "Image Not Found" yang estetik).
   - Simpan di `assets/images/placeholder/default-product.jpg`.

### Tahap 2: Modifikasi `setup_fresh.php`

Ubah semua data URL gambar pada sintaks `INSERT INTO` di file `setup_fresh.php`.

**Sebelum:**
```php
$stmtProd->execute([$cat_ids['fashion'], 'Kemeja Flannel Klasik Indigo', 'Deskripsi...', 189000.00, 15, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&auto=format&fit=crop&q=60']);
```

**Sesudah:**
```php
$stmtProd->execute([$cat_ids['fashion'], 'Kemeja Flannel Klasik Indigo', 'Deskripsi...', 189000.00, 15, 'assets/images/seed/product-fashion-1.jpg']);
```

*Lakukan hal ini untuk **semua** produk dan banner yang ada di `setup_fresh.php`.*

### Tahap 3: Implementasi Fallback Handler di Frontend (Jaring Pengaman)

Meskipun gambar sudah lokal, file bisa saja tidak sengaja terhapus. Untuk memastikan UI tetap terlihat profesional dan tidak menampilkan ikon gambar pecah (broken image icon), tambahkan atribut `onerror` pada tag `<img />` di halaman publik.

1. **Buka file view publik yang menampilkan produk/banner:**
   - `views/public/home.php`
   - `views/public/product_detail.php`
   - `views/public/cart.php`
   - (dan file lain yang menampilkan gambar dari database)

2. **Modifikasi Tag Image (`<img>`):**
   Ubah tag image dengan menambahkan penanganan error JavaScript sebaris.

   **Contoh Implementasi:**
   ```html
   <img 
       src="<?= htmlspecialchars($product['image_url'] ?? 'assets/images/placeholder/default-product.jpg') ?>" 
       alt="<?= htmlspecialchars($product['name']) ?>"
       onerror="this.onerror=null;this.src='assets/images/placeholder/default-product.jpg';"
       class="w-full h-full object-cover ..."
   >
   ```
   *Penjelasan: `this.onerror=null` mencegah infinite loop jika gambar placeholder ternyata juga hilang.*

### Tahap 4: Script Verifikasi (Opsional untuk CI/CD atau Pengecekan Mandiri)

Buat sebuah script PHP sederhana bernama `check_seed_images.php` di root direktori (atau folder `scripts/`) yang membaca `setup_fresh.php`, mengekstrak semua path gambar (`assets/images/seed/...`), dan mengecek apakah file tersebut benar-benar ada secara fisik (`file_exists()`). Script ini berguna untuk QA memastikan tidak ada gambar yang lupa di-commit.

---

## ✅ Daftar Periksa (Checklist) QA / Validasi

1. **[ ]** Folder `assets/images/seed/` beserta isinya telah terbuat dan di-commit ke Git.
2. **[ ]** File `setup_fresh.php` tidak lagi mengandung domain `images.unsplash.com`, melainkan path lokal.
3. **[ ]** Eksekusi ulang `php setup_fresh.php` pada database percobaan, pastikan berjalan lancar.
4. **[ ]** Buka halaman Beranda (`home.php`). Pastikan semua gambar produk dan banner muncul dengan sempurna tanpa koneksi internet yang kencang (karena sudah di-host lokal).
5. **[ ]** Tes fitur Fallback: Coba ubah nama salah satu file gambar di `assets/images/seed/` untuk mensimulasikan gambar hilang. Refresh halaman web, pastikan yang muncul adalah gambar placeholder (tidak ada icon image pecah).
