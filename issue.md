# Issue: Perbaikan Tampilan Card Produk (Pemisahan Baris Harga dan Tombol Aksi)

## Deskripsi Masalah
Pada kartu produk di halaman utama, informasi **Harga** dan **Tombol Aksi** (seperti tombol "Tambah ke Keranjang" atau link "Pilih Varian") saat ini diletakkan dalam satu baris horizontal (`flex items-center justify-between`).

Hal ini menimbulkan masalah visual jika harga produk memiliki nominal yang sangat tinggi (panjang karakter banyak), sehingga teks harga saling bertabrakan (nabrak) dengan tombol aksi di sebelahnya. Selain itu, tombol "Pilih Varian" saat ini memiliki teks "Pilih Varian" di samping ikonnya, sedangkan tombol "Tambah ke Keranjang" hanya berupa ikon (tidak konsisten).

---

## Langkah-langkah Implementasi (Panduan untuk Programmer/AI)

### Tahap 1: Restrukturisasi HTML di View Utama
1. Buka file [home.php](file:///home/xamnes/Projects/cms-toko-online/views/public/home.php).
2. Cari baris kontainer harga dan tombol aksi (sekitar baris 109 hingga 150):
   ```html
   <div class="mt-6 flex items-center justify-between">
       ...
   </div>
   ```
3. Ubah pembungkus terluar tersebut agar tidak menggunakan `flex justify-between`, melainkan menggunakan struktur bertumpuk ke bawah (vertikal) dengan jarak/gap yang sesuai, misalnya menggunakan kelas Tailwind CSS `mt-6 space-y-3` atau sejenisnya.
4. Buat blok **Harga** berada pada satu baris penuh tersendiri.
5. Buat baris baru di bawahnya untuk membungkus **Tombol Aksi** dan atur posisinya agar rapi (misalnya disejajarkan ke kanan dengan `flex justify-end` atau selebar penuh).

### Tahap 2: Penyederhanaan Tombol Aksi (Hanya Ikon)
1. Pada bagian tombol "Pilih Varian" (`<a>` tag):
   ```html
   <a href="index.php?page=product_detail&id=<?= $product['id'] ?>" class="flex items-center space-x-1.5 bg-primary hover:bg-primary/90 text-white text-xs font-bold px-3 py-2.5 rounded-2xl transition duration-200 active:scale-95 shadow-sm shadow-primary/20">
       <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">...</svg>
       <span>Pilih Varian</span>
   </a>
   ```
2. Hapus tag `<span>Pilih Varian</span>` beserta teks di dalamnya.
3. Sesuaikan kelas styling tombol tersebut agar memiliki padding dan bentuk persegi yang sama persis dengan tombol "Tambah ke Keranjang" (contoh: gunakan padding `p-3` dan `rounded-2xl`).

### Tahap 3: Validasi Kerapian Desain
- Muat ulang halaman utama toko (`http://localhost:8000`).
- Pastikan harga produk dan tombol aksi tidak lagi berada dalam satu baris horizontal.
- Pastikan kedua tombol aksi (Tambah ke Keranjang & Pilih Varian) memiliki ukuran, bentuk, dan konsistensi visual yang sama (hanya menampilkan ikon tanpa teks).
