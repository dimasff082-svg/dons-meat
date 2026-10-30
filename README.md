# Sistem Informasi Penjualan Daging Sapi DONS MEAT

Sistem informasi berbasis web yang dirancang khusus untuk memfasilitasi penjualan daging sapi berkualitas bagi usaha **DONS MEAT**, melayani pelanggan online secara praktis (Guest Checkout dengan pembayaran otomatis Midtrans Snap) serta pencatatan transaksi langsung di toko fisik (POS In-Store) yang tersinkronisasi dalam satu sumber persediaan stok tunggal.

Aplikasi dibangun berdasarkan spesifikasi dokumen resmi [PRD_DONS_MEAT_FINAL.md](PRD_DONS_MEAT_FINAL.md) menggunakan arsitektur **Laravel 13**, **Tailwind CSS v4**, **Blade Templating**, dan integrasi **Midtrans Snap Sandbox**.

---

## 1. Fitur Utama Sistem

### A. Fitur Publik & Pelanggan
1. **Beranda & Profil Usaha**:
   - Menampilkan identitas usaha, jam operasional, alamat gerai, kontak WhatsApp, dan informasi pengiriman/pengambilan.
   - Menampilkan **Harga Standar Daging Sapi per Kilogram** yang berlaku secara publik (dikelola eksklusif oleh Pemilik di database).
2. **Katalog Produk & Pemilihan Berat Paket**:
   - Pencarian produk dan filter kategori (*Daging Utuh*, *Potongan Khusus*, *Tulang*).
   - Pemilihan opsi berat paket daging yang telah ditentukan (misal: 250g, 500g, 1 kg, 2 kg) dengan perhitungan harga proporsional di sisi server.
   - Indikator ketersediaan stok riil dan deteksi otomatis bila stok habis.
3. **Keranjang Belanja (Shopping Cart)**:
   - Penyimpanan berbasis sesi tanpa mewajibkan pendaftaran atau login akun pelanggan.
   - Validasi ulang harga dan ketersediaan stok aktual secara otomatis.
4. **Guest Checkout Ringkas**:
   - Checkout cepat tanpa registrasi akun (hanya nama, nomor telepon WhatsApp, dan catatan).
   - Pilihan metode pemenuhan:
     - **Ambil di Toko (Pickup)**: Biaya ongkir Rp 0.
     - **Kurir Antar (Delivery)**: Wajib memilih zona pengiriman aktif dan mengisi alamat tujuan lengkap.
   - **Mekanisme Reservasi Stok Atomik**: Stok fisik toko tetap utuh saat checkout, sementara stok cadangan (*reserved stock*) dinaikkan sementara dengan proteksi row-level lock untuk mencegah *overselling*.
5. **Pelacakan Pesanan Aman (Private Token Tracking)**:
   - Akses status via token acak 40 karakter (`/pesanan/{publicToken}`).
   - Perlindungan privasi: nomor telepon pelanggan disensor otomatis (contoh: `0812****7890`).
   - Countdown timer batas waktu pembayaran (*payment deadline*).
   - Tombol bayar dengan integrasi modal popup **Midtrans Snap JS** langsung di halaman pelacakan.

### B. Fitur Internal (Admin & Pemilik)
1. **Tanpa Role Kasir (Zero Cashier Role)**:
   - Akses internal hanya terbagi menjadi 2 hak akses: **Pemilik (Owner)** dan **Admin**. Tidak ada akun/role kasir.
2. **Penjualan Langsung di Toko (POS In-Store)**:
   - Admin/pemilik dapat melayani pembelian tunai di gerai toko.
   - Menghitung kembalian uang tunai, langsung memotong stok fisik secara instan, dan mencetak nota/struk pesanan kasir toko.
3. **Manajemen Persediaan Stok & Audit Log**:
   - Pencatatan stok masuk (*inbound*), penyesuaian (*adjustment*), dan afkir/rusak (*damaged*).
   - Riwayat mutasi lengkap (*Stock Movements*) mencatat perubahan stok fisik, stok cadangan, serta saldo akhir.
   - Deteksi produk di bawah ambang batas minimum (*Low Stock Alert*).
4. **Manajemen Pesanan Masuk (Order Fulfillment)**:
   - Filter lengkap berdasarkan status (*Perlu Disiapkan*, *Menunggu Bayar*, *Siap Diambil*, *Dalam Pengiriman*, *Selesai*, dll).
   - Alur transisi pemenuhan pesanan tervalidasi:
     - *Pickup*: `processing` &rarr; `ready_for_pickup` &rarr; `completed`.
     - *Delivery*: `processing` &rarr; `on_delivery` &rarr; `completed`.
   - **Kebijakan Pembatalan**: Pesanan yang sudah lunas (*Paid*) dilindungi dan **tidak dapat dibatalkan** melalui sistem.
5. **Laporan Penjualan & Omzet**:
   - Perhitungan omzet murni dari pesanan yang berstatus **Lunas (Paid)** dalam rentang tanggal yang dipilih.
   - Rekap omzet online vs penjualan langsung toko secara terpisah maupun gabungan.
   - Metrik KPI: Total Omzet, Total Transaksi, Rata-rata Nilai Order (AOV).
   - Rekapitulasi harian dan daftar transaksi yang dapat diekspor langsung ke **file CSV** (UTF-8 Excel kompatibel).
6. **Khusus Hak Akses Pemilik**:
   - Pengaturan harga standar daging sapi/kg publik.
   - Pengaturan batas kedaluwarsa pembayaran pesanan (menit) dan zona waktu.
   - Manajemen akun internal (tambah, edit, dan nonaktifkan akun admin).

---

## 2. Prasyarat Sistem & Kebutuhan Runtime

Pastikan lingkungan lokal Anda memenuhi persyaratan berikut:

- **PHP**: Versi `^8.3` atau lebih tinggi (Direkomendasikan PHP 8.3 / 8.4 / 8.5) dengan ekstensi `pdo`, `pdo_sqlite` / `pdo_mysql`, `curl`, `mbstring`, `openssl`, `json`.
- **Composer**: Versi 2.8+
- **Node.js**: Versi 18+ atau 20+ & NPM
- **Database**: SQLite (default lokal/testing) atau MySQL 8.0+

---

## 3. Panduan Instalasi & Pengaturan Lokal

Ikuti langkah-langkah berikut secara berurutan untuk menjalankan aplikasi dari awal:

### Langkah 1: Kloning / Buka Direktori Project
```bash
cd c:\magang\dons-meat
```

### Langkah 2: Salin File Konfigurasi Environment
```bash
copy .env.example .env
```

### Langkah 3: Install Dependensi PHP
```bash
composer install
```

### Langkah 4: Generate Application Key
```bash
php artisan key:generate
```

### Langkah 5: Konfigurasi Database & Midtrans pada File `.env`
Buka file `.env` di teks editor, lalu sesuaikan koneksi database dan kredensial Midtrans Sandbox Anda:

```dotenv
APP_NAME="DONS MEAT"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Makassar

# Pilihan Database (SQLite atau MySQL):
DB_CONNECTION=sqlite
# Atau jika menggunakan MySQL lokal:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=dons_meat
# DB_USERNAME=root
# DB_PASSWORD=

# Konfigurasi Midtrans Sandbox (Dapatkan dari dashboard.midtrans.com)
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxx
```

> **Perhatian Penting**: Jangan pernah mengaktifkan `MIDTRANS_IS_PRODUCTION=true` atau menggunakan Server Key Production sebelum pemilik usaha siap melakukan transaksi komersial nyata.

### Langkah 6: Jalankan Database Migration & Seeder
Perintah ini akan membuat seluruh tabel yang diperlukan dan mengisi data awal demo (kategori daging, produk, pilihan gramasi, tarif zona pengiriman flat, pengaturan usaha, serta akun internal default):

```bash
php artisan migrate --seed
```

### Langkah 7: Install & Kompilasi Aset Frontend
```bash
npm install
npm run build
```

---

## 4. Akun Internal Default (Pengujian Lokal)

Setelah database diseed, Anda dapat masuk ke panel internal melalui halaman login di `/admin/login`:

| Peran (Role) | Email Login | Password Default | Hak Akses Utama |
|---|---|---|---|
| **Pemilik (Owner)** | `owner@donsmeat.local` | `password` | Kontrol penuh: laporan, harga standar sapi/kg, manajemen user admin, pengaturan usaha, stok, dan pesanan. |
| **Admin** | `admin@donsmeat.local` | `password` | Operasional harian: katalog produk, persediaan stok, kasir toko (penjualan langsung), dan pemrosesan pesanan. |

---

## 5. Menjalankan Aplikasi

Jalankan perintah berikut di terminal terpisah:

### Terminal 1: Web Server Laravel
```bash
php artisan serve
```
Aplikasi akan dapat diakses melalui browser di: `http://localhost:8000`

### Terminal 2: Vite Dev Server (Opsional untuk Pengembangan Aset)
```bash
npm run dev
```

### Terminal 3: Laravel Task Scheduler (Auto-Expire Pesanan Unpaid)
Untuk memastikan pesanan online yang belum dibayar kedaluwarsa secara otomatis dan stok cadangannya dilepas kembali setelah batas waktu berakhir:
```bash
php artisan schedule:work
```
Atau Anda dapat mengeksekusi pemeriksaan kedaluwarsa secara manual kapan saja dengan perintah:
```bash
php artisan orders:expire-unpaid
```

---

## 6. Panduan Pengujian Pembayaran Midtrans Sandbox

Aplikasi ini menggunakan alur **Midtrans Snap Popup** dengan validasi server-to-server (**Webhook HTTP Notification**).

### A. Pengujian Checkout & Popup Snap di Browser
1. Buka katalog di `http://localhost:8000/katalog`.
2. Pilih produk daging (misal: *Has Dalam Tenderloin* atau *Tulang Konro*), pilih berat kemasan (misal: *500 g*), lalu klik **+ Masukkan Keranjang**.
3. Buka keranjang di `/keranjang`, lalu lanjutkan ke `/checkout`.
4. Isi data pemesan sebagai tamu (Guest), pilih metode pemenuhan (*Ambil di Toko* atau *Kurir Antar*).
5. Setelah submit, Anda akan diarahkan ke halaman pelacakan pesanan publik `/pesanan/{publicToken}`.
6. Klik tombol merah **"Bayar Sekarang (Midtrans)"**. Modal Snap akan muncul menampilkan metode pembayaran Sandbox (QRIS, Virtual Account BCA/Mandiri/BNI/BRI, GoPay).

### B. Pengujian Webhook Server-to-Server di Komputer Lokal
Karena komputer lokal (`localhost`) tidak dapat diakses langsung oleh server cloud Midtrans, Anda dapat menggunakan alat tunnel seperti **Ngrok**:

1. Jalankan tunnel ke port lokal:
   ```bash
   ngrok http 8000
   ```
2. Salin URL publik HTTPS yang diberikan Ngrok (contoh: `https://abcd-1234.ngrok-free.app`).
3. Masuk ke Dashboard Midtrans Sandbox: [https://dashboard.sandbox.midtrans.com/](https://dashboard.sandbox.midtrans.com/).
4. Buka menu **Settings &rarr; Configuration**.
5. Pada kolom **Payment Notification URL**, masukkan URL webhook aplikasi Anda:
   ```
   https://abcd-1234.ngrok-free.app/midtrans/notification
   ```
6. Simpan konfigurasi. Setiap transaksi yang diselesaikan di simulator Midtrans akan mengirim notifikasi webhook yang diverifikasi signature SHA-512 secara otomatis oleh backend aplikasi.

---

## 7. Menjalankan Rangkaian Pengujian Otomatis (Automated Tests)

Sistem dilengkapi rangkaian pengujian unit dan fitur komprehensif (84 tes otomatis) yang menguji skenario autentikasi internal, katalog, POS toko, checkout tamu, reservasi atomik, webhook idempotensi Midtrans, pelepasan stok kedaluwarsa, pemenuhan pesanan, dan laporan omzet:

Jalankan test suite menggunakan PHPUnit / Artisan:
```bash
php artisan test
```
Atau jalankan runner langsung:
```bash
vendor/bin/phpunit
```

Untuk memformat kode sesuai standar styling Laravel Pint:
```bash
vendor/bin/pint --format agent
```

---

## 8. Ringkasan Hak Cipta & Informasi

- **Nama Usaha**: DONS MEAT
- **Lingkup Proyek**: MVP Sistem Informasi Penjualan Daging Sapi Terintegrasi
- **Status Proyek**: Selesai & Terverifikasi (Tahap 0 s/d Tahap 7 Selesai Penuh)
