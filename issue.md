# Implementasi Advanced Search, Filtering, & Server-Side Pagination pada Halaman Kelola Produk

**Untuk:** Junior Programmer / AI Implementer

## Tujuan Utama
Merombak halaman Kelola Produk di Admin Panel (`views/admin/products.php`) agar menggunakan **Server-Side Pagination** dan **Asynchronous Filtering (AJAX)**. Data produk tidak boleh di-load semuanya sekaligus (karena akan membebani server jika data mencapai puluhan ribu). Total produk dan tombol halaman (pagination) harus kalkulatif dan dinamis sesuai parameter pencarian/filter.

Desain UI harus premium, canggih, dan *responsive* di berbagai perangkat.

---

## 🛠️ Tahap 1: Penyesuaian Backend (Service Layer)
**Lokasi File:** `services/ProductManagementService.php` atau `services/ProductService.php`

Buat sebuah fungsi baru (misal: `getPaginatedProducts`) untuk merangkai query secara dinamis berdasarkan parameter filter dari frontend.

### Kriteria Query:
1. **Dinamis `WHERE` Clause:** 
   - Tangkap variabel pencarian nama (`LIKE '%...%'`).
   - Tangkap filter kategori (`category_id = ?`).
   - Tangkap filter *Range* Harga (`price >= ? AND price <= ?`).
   - Tangkap filter Tanggal (`created_at >= ? AND created_at <= ?`).
2. **Kalkulasi Total Data yang Akurat:**
   - Gunakan fungsi MySQL `SQL_CALC_FOUND_ROWS` di awal perintah `SELECT`, atau lakukan query `SELECT COUNT(*)` terpisah yang membawa kondisi `WHERE` yang **sama persis** dengan query utamanya.
   - Mengapa ini penting? Jika ada 10.000 data di tabel, dan *user* mem-filter kata kunci "Baju" (yang jumlahnya 100), maka sistem *pagination* harus dihitung berdasarkan angka 100, bukan 10.000.
3. **Pagination Clause:**
   - Tambahkan `LIMIT ? OFFSET ?` pada akhir query utama. Pastikan variabel di-bind sebagai `PDO::PARAM_INT` agar tidak terjadi error SQL syntax.

**Output Fungsi:** Harus mengembalikan array assosiatif berisi: `['data' => $products, 'total_items' => $total]`.

---

## 🛠️ Tahap 2: Pembuatan API Endpoint (Controller Layer)
**Lokasi File:** `controllers/ProductController.php`

Ubah logika pada metode yang melayani halaman admin produk atau buatkan blok khusus penanganan AJAX.

### Kriteria Logika:
1. **Tangkap Parameter GET:** Ambil parameter `page`, `per_page`, `search`, `category`, `min_price`, `max_price`, dll dari request.
2. **Hitung Meta Pagination:**
   - Ambil `total_items` dari Service.
   - Hitung `total_pages = ceil($total_items / $per_page)`.
3. **Format Respons (AJAX JSON):**
   - Pastikan endpoint dapat membedakan antara *request rendering* halaman penuh (saat pertama kali load) dengan *request AJAX* (saat user nge-filter data).
   - Jika `$_SERVER['HTTP_X_REQUESTED_WITH']` adalah `xmlhttprequest` (AJAX), respons wajib berbentuk JSON:
   ```json
   {
       "success": true,
       "data": [ ...array produk... ],
       "meta": {
           "current_page": 1,
           "per_page": 10,
           "total_items": 100,
           "total_pages": 10
       }
   }
   ```

---

## 🎨 Tahap 3: Pembuatan UI/Frontend yang Canggih
**Lokasi File:** `views/admin/products.php`

Hapus *query raw* PDO di awal file ini. Rombak bagian atas tabel untuk menyisipkan antarmuka pencarian dan filter *Glassmorphism* atau *Modern Minimalist Tailwind*.

### Elemen UI yang Wajib Ada:
1. **Search Bar Utama:** Input teks dengan ikon kaca pembesar (kiri) untuk mengetik nama produk.
2. **Filter Bar / Panel (Responsif):**
   - **Kategori Dropdown:** `<select>` dengan daftar kategori yang ada.
   - **Range Harga:** Input numerik bersebelahan untuk Harga Min dan Harga Max.
   - **Range Tanggal (Opsional/Bonus):** Input tipe tanggal untuk `Start Date` dan `End Date`.
3. **Loading Skeleton / State:**
   - Saat AJAX sedang berjalan, kosongkan `<tbody>` dan ganti dengan *Skeleton Loading Bar* yang menyala/berkedip lembut (menggunakan animasi `animate-pulse` bawaan Tailwind).
4. **Pagination Control:**
   - Terletak di bawah tabel. Berisi tombol "Previous", rentang angka halaman (1, 2, 3...), dan tombol "Next".
   - Nonaktifkan tombol "Previous" jika di halaman 1, dan "Next" jika di halaman terakhir.

---

## ⚡ Tahap 4: Logika JavaScript Asynchronous (Frontend)
**Lokasi File:** `assets/js/pages/admin-products.js`

Gunakan JavaScript ES6 (`fetch` API atau `jQuery.ajax`) untuk menghidupkan UI di Tahap 3.

### Alur Kerja (Workflow):
1. Buat fungsi `loadProducts(page = 1)`.
2. Saat fungsi terpanggil:
   - Ambil *value* dari input (Search, Category, Min Price, Max Price).
   - Rakit variabel-variabel tersebut ke dalam URL Query Params (contoh: `?page=1&search=baju&min_price=50000`).
   - Tampilkan elemen *Loading Skeleton* pada tabel.
3. Lakukan HTTP `GET` request ke Backend.
4. Saat respons JSON sukses diterima:
   - Kosongkan tabel.
   - *Looping* data JSON produk, rakit menjadi HTML `<tr>`, dan injeksikan (`innerHTML`) kembali ke dalam tabel.
   - Eksekusi logika *rendering* untuk tombol Pagination. Jika `total_pages` adalah 5, buatkan 5 tombol angka di DOM. Pasang *Event Listener* klik pada tombol-tombol tersebut agar memanggil kembali fungsi `loadProducts(halaman_tujuan)`.

---
**Pesan Penutup untuk Implementer:**
Prioritaskan struktur tabel yang responsif (`overflow-x-auto`) sehingga di HP pun admin tetap dapat melakukan geser (scroll) kolom harga dan stok tanpa merusak antarmuka secara keseluruhan. Perhatikan keamanan! Selalu pakai **Prepared Statements** saat merakit string `WHERE` untuk mencegah SQL Injection!
