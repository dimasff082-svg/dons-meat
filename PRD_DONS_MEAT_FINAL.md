# PRODUCT REQUIREMENTS DOCUMENT (PRD)
# SISTEM INFORMASI PENJUALAN DONS MEAT

> **Status dokumen:** PRD final v1.0 untuk implementasi MVP — keputusan dari pemilik ditetapkan sebagai persyaratan; rekomendasi yang belum dikonfirmasi ditandai terpisah dan dibuat dapat dikonfigurasi bila memungkinkan.  
> **Versi:** 1.0  
> **Tanggal:** 27 Oktober 2026  
> **Bahasa:** Indonesia  
> **Target pembaca:** AI coding agent / AI IDE, developer, pemilik usaha, dan penguji sistem.  
> **Stack utama:** Project Laravel yang sudah tersedia, MySQL, Blade, Tailwind CSS, JavaScript, Midtrans Snap.  
> **Lingkungan saat ini:** Komputer lokal (local development).

---

## 0. Cara Membaca Dokumen Ini

Gunakan dokumen ini sebagai sumber spesifikasi utama untuk membangun sistem penjualan DONS MEAT. Bedakan tiga jenis keputusan berikut:

- **Persyaratan terkonfirmasi:** berasal dari informasi yang telah diberikan pemilik DONS MEAT.
- **Rekomendasi PRD:** keputusan teknis/operasional yang disarankan agar sistem dapat dibangun tanpa terus-menerus menunggu keputusan kecil. Rekomendasi ini bukan klaim bahwa proses tersebut sudah berjalan di DONS MEAT.
- **Di luar cakupan MVP:** fitur yang tidak perlu dibangun pada versi pertama kecuali pemilik secara eksplisit meminta perubahan ruang lingkup.

Jika menemukan konflik antara dokumen ini dan kode yang sudah ada di project Laravel, AI agent harus memeriksa implementasi yang ada, melaporkan konflik secara singkat, lalu memilih perubahan yang paling aman dan sesuai dengan tujuan PRD. Jangan membuat ulang project Laravel atau mengganti file konfigurasi secara membabi buta.

## 1. Ringkasan Produk

DONS MEAT membutuhkan website penjualan daging sapi yang melayani pelanggan online serta mencatat pembelian langsung di toko. Sistem mengelola katalog, pilihan berat yang telah ditentukan, harga produk yang dapat diperbarui, pembayaran online otomatis menggunakan Midtrans, biaya pengiriman, stok yang tersinkron antara kanal online dan toko, pesanan, dan laporan penjualan.

Aplikasi dibangun di atas project Laravel yang telah dimiliki pemilik. Pada MVP, pelanggan tidak diwajibkan mendaftar atau login. Admin dan pemilik masuk ke dashboard internal. Tidak ada role khusus kasir; transaksi langsung di toko dibuat melalui menu penjualan langsung yang hanya dapat diakses oleh admin atau pemilik.

### 1.1 Tujuan utama

1. Memungkinkan pelanggan menemukan produk daging sapi dan melakukan pemesanan dari website.
2. Memudahkan pemilik/admin memperbarui produk, pilihan berat, harga, harga standar daging sapi per kilogram, stok, biaya pengiriman, dan jam operasional.
3. Mengintegrasikan pembayaran online melalui Midtrans Snap dan memperbarui status pembayaran secara otomatis melalui notifikasi server-to-server.
4. Menjaga agar stok untuk pesanan online dan transaksi langsung menggunakan satu sumber data yang konsisten.
5. Menyediakan laporan omzet harian/bulanan, penjualan, dan peringatan stok menipis.
6. Memberikan pengalaman UI yang responsif pada ponsel, tablet, dan laptop/desktop.

### 1.2 Definisi keberhasilan

MVP dianggap memenuhi tujuan jika:

- Pelanggan dapat melihat katalog dan checkout sebagai tamu tanpa membuat akun.
- Pelanggan hanya bisa memilih pilihan berat yang aktif dan tersedia.
- Harga yang tampil di halaman produk dan keranjang sama dengan perhitungan server.
- Pembayaran online berhasil diuji di Midtrans Sandbox dan statusnya diperbarui oleh webhook yang tervalidasi.
- Pesanan menunggu pembayaran otomatis kedaluwarsa sesuai batas waktu yang dikonfigurasi dan stok cadangannya dilepas.
- Penjualan langsung yang dimasukkan admin/pemilik memperbarui stok yang sama dengan stok untuk penjualan online.
- Pesanan yang sudah berstatus dibayar tidak menyediakan aksi pembatalan kepada pelanggan dan tidak dapat dibatalkan melalui alur normal sistem.
- Pemilik dapat melihat laporan omzet harian/bulanan, laporan transaksi, dan daftar stok menipis.
- Tampilan dapat digunakan tanpa layout overflow pada ponsel, tablet, dan desktop.

## 2. Konteks Usaha dan Batasan yang Dikonfirmasi

| Area | Keputusan |
|---|---|
| Nama usaha | DONS MEAT |
| Produk | Daging sapi: daging utuh, potongan khusus, dan tulang |
| Harga produk | Dapat diubah oleh admin/pemilik sesuai kewenangan |
| Harga standar di halaman profil/beranda usaha | Menampilkan harga standar daging sapi per kilogram yang sedang berlaku; nilainya harus dapat diubah melalui dashboard dan tidak boleh diketik langsung pada template |
| Penjualan | Online dan pembelian langsung di toko |
| Pilihan berat | Pelanggan memilih berat yang sudah ditentukan/tersedia, bukan memasukkan berat bebas pada checkout |
| Satuan persediaan | Disimpan sebagai gram di database; ditampilkan kepada pengguna dalam kg atau gram yang mudah dibaca |
| Persediaan | Penjualan online dan langsung harus tersinkron |
| Akun pelanggan | Tidak wajib login; checkout tamu harus didukung |
| Pemenuhan pesanan | Pengambilan di toko dan pengiriman |
| Ongkos kirim | Wajib tersedia dan dapat dikonfigurasi |
| Pembayaran online | Midtrans Snap dengan pembaruan status otomatis |
| Pesanan belum dibayar | Kedaluwarsa otomatis setelah batas waktu yang dikonfigurasi |
| Pesanan dibayar | Tidak dapat dibatalkan melalui alur normal sistem |
| Role kasir | Tidak digunakan dan tidak boleh dibuat sebagai role terpisah |
| Role internal | Pemilik dan admin |
| Notifikasi | Notifikasi pesanan pada dashboard internal |
| Teknologi | Project Laravel yang sudah ada, MySQL, Blade, Tailwind CSS, JavaScript |
| Lingkungan tahap pertama | Komputer lokal |
| Gaya visual | Beranda terinspirasi Meat Tracker; katalog terinspirasi desain meat-shopping di Dribbble; bukan menyalin aset/kode secara langsung |
| Target dokumen | Instruksi terstruktur bagi AI coding agent/AI IDE |

## 3. Pengguna, Aktor, dan Hak Akses

### 3.1 Aktor

1. **Pengunjung/pelanggan:** menjelajahi website, memilih berat produk, membuat pesanan sebagai tamu, membayar melalui Midtrans, serta melihat status pesanan menggunakan tautan/kode pelacakan yang aman.
2. **Admin:** mengelola produk, kategori, pilihan berat, stok, pesanan, biaya pengiriman, dan pengaturan operasional sesuai hak akses.
3. **Pemilik:** memiliki seluruh hak admin serta mengelola akun internal, pengaturan sistem penting, harga standar usaha, dan laporan.

Pelanggan adalah aktor publik dan bukan akun login pada MVP. Jangan memaksa pembuatan akun pelanggan. Data yang diperlukan untuk pemenuhan pesanan disimpan sebagai informasi kontak/snapshot pada order. Dukungan akun pelanggan dapat menjadi pengembangan di masa depan.

### 3.2 Matriks hak akses

| Fitur | Pengunjung/pelanggan | Admin | Pemilik |
|---|---:|---:|---:|
| Melihat beranda, profil usaha, katalog, harga, dan ketersediaan | Ya | Ya | Ya |
| Checkout sebagai tamu | Ya | Ya, untuk pengujian bila diperlukan | Ya, untuk pengujian bila diperlukan |
| Membayar lewat Midtrans | Ya | Dapat menguji melalui alur pelanggan | Dapat menguji melalui alur pelanggan |
| Melihat status pesanan melalui token/kode aman | Hanya pesanan miliknya | Ya | Ya |
| Melihat dashboard internal | Tidak | Ya | Ya |
| Membuat/mengubah produk dan kategori | Tidak | Ya | Ya |
| Mengubah harga produk | Tidak | Ya | Ya |
| Mengubah harga standar per kg di halaman usaha | Tidak | Rekomendasi: tidak; perlu kewenangan pemilik | Ya |
| Menambah stok dan melakukan penyesuaian stok | Tidak | Ya, wajib memberi alasan | Ya |
| Memproses pesanan dan memperbarui status pemenuhan | Tidak | Ya | Ya |
| Melihat laporan penjualan | Tidak | Ya, sesuai kebutuhan operasional | Ya |
| Membuat transaksi pembelian langsung | Tidak | Ya | Ya |
| Mengelola akun admin/menonaktifkan akun | Tidak | Tidak | Ya |
| Mengubah pengaturan keamanan/integrasi sistem | Tidak | Tidak | Ya |
| Membatalkan pesanan yang sudah dibayar | Tidak | Tidak melalui alur normal | Tidak melalui alur normal |

**Ketentuan:** tidak ada role `kasir`. Transaksi langsung dikelola melalui halaman **Penjualan Langsung** dalam dashboard, dan hanya dapat dibuat oleh admin/pemilik yang sudah login. Role admin dan pemilik tidak boleh disamakan hanya berdasarkan penyembunyian menu; seluruh izin harus diverifikasi di server.

## 4. Ruang Lingkup

### 4.1 Termasuk MVP

- Landing page/beranda dan profil usaha.
- Menampilkan harga standar daging sapi/kg yang saat ini berlaku.
- Katalog daging sapi: daging utuh, potongan khusus, dan tulang.
- Pencarian sederhana dan filter kategori.
- Halaman detail produk.
- Opsi berat yang ditetapkan admin per produk (contoh konfigurasi: 250 g, 500 g, 1 kg; contoh ini bukan daftar berat yang wajib digunakan).
- Keranjang dan guest checkout.
- Pilihan pengambilan di toko atau pengiriman.
- Zona/area dan biaya pengiriman yang dikonfigurasi admin.
- Pembuatan order dan pencadangan stok yang aman.
- Midtrans Snap untuk pembayaran online.
- Webhook/status notification Midtrans yang diverifikasi dan idempoten.
- Kedaluwarsa otomatis untuk pesanan yang belum dibayar.
- Dashboard admin/pemilik, notifikasi pesanan baru, pengelolaan pesanan dan status.
- Manajemen produk, kategori, gambar, harga, opsi berat, harga standar usaha, dan stok.
- Transaksi penjualan langsung tanpa role kasir.
- Laporan omzet harian/bulanan, seluruh transaksi, dan stok menipis.
- Riwayat perubahan status order dan pergerakan stok.
- Responsive UI untuk mobile, tablet, dan laptop/desktop.
- Validasi, otorisasi, pengujian, dan dokumentasi instalasi lokal.

### 4.2 Di luar cakupan MVP

- Role kasir tersendiri.
- Wajib login untuk pelanggan.
- Aplikasi Android/iOS native.
- Rekomendasi AI, pengenalan kualitas daging berbasis AI, atau chatbot AI.
- Rating/ulasan produk dan loyalti pelanggan.
- Voucher, poin, kampanye email, atau newsletter.
- Multi-cabang/multi-gudang.
- Integrasi otomatis dengan kurir pihak ketiga atau pelacakan GPS real-time.
- Pengelolaan pembelian pemasok yang lengkap, purchase order, dan hutang pemasok.
- Refund atau pembatalan pesanan setelah dibayar. Kebijakan bisnis saat ini menyatakan pesanan yang telah dibayar tidak dapat dibatalkan melalui alur normal.
- Pengelolaan stok per batch, FEFO, dan tanggal kedaluwarsa sebagai kewajiban MVP. Fitur ini dapat ditambahkan jika operasional DONS MEAT membutuhkannya.
- Offline-first untuk penggunaan dashboard saat internet mati.

## 5. Persyaratan Desain dan Pengalaman Pengguna

### 5.1 Referensi visual

- **Beranda:** https://meattracker.app/ — gunakan inspirasi pola website usaha daging: pesan utama yang jelas, katalog pilihan, harga per berat, info pickup/delivery, jam operasional, dan ajakan berbelanja.
- **Katalog:** https://dribbble.com/shots/27780857-AI-Food-Marketplace-App-UI-UX-Design-Meat-Shopping-Delivery — gunakan inspirasi marketplace produk daging, penemuan produk, kartu produk, harga/kg, pemilihan berat, dan fokus mobile-first.

Referensi adalah panduan desain; jangan menyalin teks, gambar, logo, source code, atau keseluruhan desain milik pihak ketiga. Bangun identitas DONS MEAT sendiri. Fitur AI dari konsep Dribbble bukan bagian dari MVP.

### 5.2 Arah visual yang direkomendasikan

- Nuansa premium, bersih, terpercaya, dan berhubungan dengan produk daging segar.
- Palet dapat memakai merah daging/marun sebagai warna aksen, latar putih gading/abu sangat muda, dan teks gelap. Warna final sebaiknya disimpan dalam design tokens agar mudah diubah.
- Foto produk harus konsisten, jelas, dan memiliki rasio gambar seragam.
- Harga per kg harus terbaca jelas; opsi berat harus mudah dipilih.
- Tombol utama memakai label tindakan yang eksplisit, seperti **Lihat Produk**, **Tambah ke Keranjang**, **Lanjut Checkout**, dan **Bayar Sekarang**.
- Tampilkan status stok dengan bahasa yang jelas, bukan hanya warna.
- Hindari animasi berat, carousel otomatis yang mengganggu, atau elemen dekoratif yang memperlambat pembelian.
- Jangan menampilkan klaim kualitas, halal, sertifikasi, asal daging, atau keamanan pangan yang belum diberikan dan dibuktikan oleh pemilik.

### 5.3 Halaman publik

1. **Beranda**
   - Header/logo DONS MEAT, navigasi, tombol katalog, dan ikon keranjang.
   - Hero dengan foto/visual utama dan CTA ke katalog.
   - Harga standar daging sapi per kg saat ini yang berasal dari pengaturan database.
   - Kategori utama: daging utuh, potongan khusus, dan tulang.
   - Produk unggulan yang dipilih admin.
   - Ringkasan cara pemesanan, opsi ambil di toko/pengiriman, informasi ongkir, alamat/lokasi usaha jika sudah dimasukkan, jam operasional, dan kontak yang diatur admin.
   - Footer yang memuat informasi usaha dan tautan penting.
2. **Katalog produk**
   - Search nama produk, filter kategori, pengurutan relevan/harga bila mudah diterapkan, dan kartu produk.
   - Kartu memuat gambar, nama produk, kategori atau deskripsi ringkas, harga/kg, pilihan berat atau CTA ke detail, dan indikator tersedia/habis.
   - Jangan memberi label tersedia hanya berdasarkan stok fisik; ketersediaan harus menghitung stok yang sudah dicadangkan.
3. **Detail produk**
   - Gambar, nama, deskripsi, harga per kg, opsi berat yang aktif, subtotal untuk berat terpilih, ketersediaan stok, dan tombol tambah ke keranjang.
   - Hanya opsi berat yang aktif dan tersedia yang dapat dipilih.
4. **Keranjang**
   - Daftar produk, opsi berat, kuantitas paket, subtotal, tombol ubah/hapus, dan ringkasan total sementara.
   - Harga dan ketersediaan dihitung ulang dari server saat checkout.
5. **Checkout tamu**
   - Nama dan nomor telepon wajib.
   - Email boleh opsional kecuali kebutuhan teknis/operasional akhirnya menetapkan lain.
   - Pilihan fulfillment: ambil di toko atau pengiriman.
   - Untuk pengiriman, alamat lengkap dan zona pengiriman wajib dipilih/divalidasi.
   - Biaya pengiriman ditampilkan sebelum order dibuat.
   - Ringkasan barang, berat, subtotal, ongkir, total, dan syarat pesanan.
   - Tidak meminta password atau memaksa pendaftaran akun.
6. **Pembayaran**
   - Menginisiasi Midtrans Snap dari backend.
   - Menampilkan instruksi dan status pembayaran yang jelas.
   - Redirect kembali dari Midtrans hanya untuk kenyamanan tampilan; bukan sumber final untuk menyatakan pembayaran berhasil.
7. **Hasil/konfirmasi pembayaran**
   - Menampilkan kode order, jumlah, metode pemenuhan, status pembayaran/order, dan tautan pelacakan yang tidak mudah ditebak.
8. **Pelacakan order tamu**
   - Mengakses order menggunakan token acak berentropi tinggi pada tautan privat. Jangan menyediakan pencarian order hanya berdasarkan ID berurutan.
   - Menampilkan informasi yang diperlukan untuk pemilik order, tanpa membocorkan alamat lengkap/nomor telepon secara tidak perlu.
9. **Profil/Informasi Usaha**
   - Nama usaha, alamat, kontak, jam operasional, metode pengambilan/pengiriman, dan harga standar daging sapi/kg yang terbaru dari pengaturan.

### 5.4 Halaman dashboard internal

- Login admin/pemilik.
- Dashboard ringkasan.
- Pesanan masuk dan detail pesanan.
- Produk dan kategori.
- Pilihan berat produk.
- Stok, stok menipis, histori pergerakan stok, dan penyesuaian.
- Pengaturan harga standar daging sapi/kg.
- Pengaturan zona/ongkos kirim dan jam layanan.
- Penjualan langsung.
- Laporan penjualan dan omzet.
- Manajemen user internal (khusus pemilik).
- Pengaturan umum toko.

### 5.5 Responsivitas

Target pengujian tampilan:

- **Ponsel:** lebar 360–639 px. Navigasi ringkas, menu mobile, kartu produk dua kolom jika konten tetap terbaca, checkout satu kolom, tombol utama mudah disentuh.
- **Tablet:** 640–1023 px. Grid katalog dan dashboard menyesuaikan ruang; sidebar dapat diciutkan.
- **Laptop/desktop:** 1024 px ke atas. Layout dapat menampilkan beberapa kolom katalog, dashboard dengan sidebar, dan ringkasan checkout yang berdampingan bila cukup lebar.

Gunakan utility responsive Tailwind (atau pola responsive yang sudah ada di project), bukan membuat layout terpisah yang menduplikasi seluruh markup. Tidak boleh ada horizontal overflow pada lebar perangkat yang didukung. Semua fungsi utama tetap dapat digunakan pada layar sentuh dan keyboard.

## 6. Aturan Bisnis Utama

### 6.1 Harga produk dan harga standar usaha

- Setiap produk mempunyai `price_per_kg` yang dapat diubah oleh admin/pemilik yang berwenang.
- Halaman profil/beranda usaha menampilkan satu nilai `standard_beef_price_per_kg`, yaitu harga standar daging sapi per kilogram saat ini.
- Nilai harga standar berasal dari pengaturan database dan diubah melalui dashboard; jangan hardcode pada Blade, JavaScript, atau file konfigurasi.
- Harga standar pada halaman usaha adalah informasi harga acuan dan tidak otomatis menggantikan harga produk yang ditampilkan dalam katalog. Produk potongan khusus atau tulang dapat mempunyai harga per kg masing-masing.
- Harga dan berat yang menjadi bagian dari order disimpan sebagai snapshot agar perubahan harga di masa depan tidak mengubah riwayat transaksi.
- Harga Rupiah disimpan sebagai `DECIMAL`, bukan `FLOAT`/`DOUBLE`.
- Total checkout dihitung ulang oleh backend; nilai total yang dikirim oleh browser tidak boleh dipercaya.

### 6.2 Pilihan berat

- Pelanggan memilih satu pilihan berat yang ditetapkan admin per produk (contoh: 250 g, 500 g, 1.000 g, jika opsi itu diaktifkan).
- Opsi berat disimpan dalam gram sebagai bilangan bulat.
- Berat ditampilkan dalam satuan yang mudah dipahami, misalnya 250 g, 500 g, atau 1 kg. Konversi tampilan tidak mengubah nilai sumber di database.
- Jumlah paket dan berat per paket harus dibedakan. Berat total pada order item = berat per paket × jumlah paket.
- Backend harus memvalidasi bahwa opsi berat masih aktif dan stok cukup pada saat checkout.

### 6.3 Perhitungan harga

Untuk produk dengan harga per kilogram:

`harga produk = harga_per_kg × total_berat_gram / 1000`

`subtotal item = pembulatan sesuai aturan Rupiah dari harga produk`

`total order = jumlah subtotal item + biaya pengiriman`

MVP tidak memasukkan voucher/diskon otomatis. Jika diskon ditambahkan di masa depan, aturan pembulatan dan urutan perhitungan harus ditetapkan secara terpisah. Total per order disimpan sebagai `DECIMAL(12,2)` atau tipe uang setara yang konsisten.

### 6.4 Stok dan sinkronisasi kanal

- `stock_gram` berarti berat fisik tercatat yang tersedia/berada di persediaan.
- `reserved_stock_gram` berarti bagian stok yang sudah dicadangkan untuk pesanan online yang belum selesai pembayarannya.
- `available_stock_gram = stock_gram - reserved_stock_gram`.
- Hanya stok tersedia yang boleh ditampilkan sebagai stok siap dipesan.
- Ketika order online dibuat dan berstatus menunggu pembayaran, stok harus dicadangkan di dalam database transaction dan row lock yang sesuai.
- Ketika pembayaran dinyatakan berhasil secara sah, stok fisik berkurang sebesar berat order dan stok cadangan untuk order itu dilepas. Perubahan stok hanya boleh dilakukan sekali walaupun webhook diterima lebih dari satu kali.
- Ketika order belum dibayar kedaluwarsa atau pembayaran gagal, stok cadangan dilepas; stok fisik tidak berkurang.
- Ketika penjualan langsung disimpan, stok fisik berkurang sesuai jumlah berat yang terjual; tidak ada reservasi online.
- Penambahan/pengurangan stok manual harus memerlukan alasan dan dicatat pada `stock_movements`.
- Dilarang menyetel stok langsung tanpa histori pergerakan stok, kecuali proses inisialisasi awal yang juga dicatat.
- Semua operasi pengurangan/reservasi harus memastikan stok tidak negatif.
- Ketika dua checkout terjadi bersamaan, gunakan database transaction, locking yang sesuai (`lockForUpdate()` bila sesuai dengan database), dan validasi ulang ketersediaan sebelum order disimpan.

### 6.5 Pesanan online dan kedaluwarsa

- Kode order harus unik dan tidak berurutan secara mudah ditebak sebagai satu-satunya token akses publik.
- Pesanan online dibuat dengan status `awaiting_payment` dan stok dicadangkan.
- Batas waktu pembayaran harus dapat dikonfigurasi. **Rekomendasi default awal: 60 menit**, dapat diubah setelah pemilik menentukan kebijakan final.
- Kedaluwarsa ditangani melalui notifikasi Midtrans ketika tersedia serta scheduled task/server-side reconciliation agar order lokal yang masih menunggu juga dapat diselesaikan. Jangan hanya mengandalkan tab browser pelanggan.
- Pesanan yang belum dibayar dapat berubah menjadi `expired` setelah batas waktunya dan reservasi dilepaskan satu kali.
- Pesanan yang telah berstatus dibayar tidak boleh ditandai `expired` akibat job yang terlambat atau callback yang datang tidak berurutan.
- Perubahan status harus tersimpan di history.

### 6.6 Pembayaran Midtrans

- Gunakan **Midtrans Snap** dan library PHP resmi `midtrans/midtrans-php` jika kompatibel dengan versi project Laravel/PHP yang ada.
- Integrasi dimulai dengan **Sandbox**, bukan transaksi nyata.
- Transaction token Snap dibuat oleh backend menggunakan Server Key.
- Client Key hanya digunakan sesuai kebutuhan integrasi frontend; Server Key tidak pernah dikirim ke browser.
- Status pembayaran dianggap benar dari webhook/notification Midtrans yang berhasil diverifikasi atau melalui pengecekan status server-to-server yang sah.
- Validasi `signature_key` sesuai dokumentasi resmi Midtrans, termasuk hash SHA-512 dari gabungan `order_id`, `status_code`, `gross_amount`, dan Server Key. Gunakan implementasi library resmi bila tersedia dan pastikan perbandingan dilakukan dengan aman.
- Cocokkan order ID Midtrans, nilai pembayaran, mata uang IDR, dan order internal terkait sebelum memperbarui status.
- Endpoint webhook harus idempoten: notifikasi berulang tidak boleh mendobelkan pengurangan stok, penambahan omzet, atau perubahan status.
- Redirect `finish`, `unfinish`, atau `error` dari frontend bukan bukti final bahwa pembayaran berhasil.
- Status transaksi yang dipetakan harus mempertimbangkan status Midtrans yang relevan, seperti `pending`, `settlement`, `capture` dengan `fraud_status` yang sesuai, `expire`, `deny`, dan `cancel`.
- `pending` berarti pembayaran belum final. Pembayaran yang dinyatakan final berhasil oleh Midtrans mengubah payment status menjadi `paid` dan memicu pengurangan stok/reservasi sesuai aturan yang idempoten.
- Notifikasi `expire`, `deny`, atau `cancel` tidak boleh mengubah order yang sudah sah dibayar menjadi belum dibayar/expired. Perubahan status harus menjaga transisi yang valid dan menangani callback out-of-order.
- Simpan ID transaksi gateway, status terakhir, tipe pembayaran, nominal, waktu pembayaran, dan metadata minimum yang diperlukan untuk audit. Jangan menyimpan data sensitif kartu.

**Kredensial:** alamat email/kata sandi login akun bukan kredensial API Midtrans. PRD ini tidak menyertakan kredensial nyata. Gunakan `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` yang diambil pemilik dari dashboard Midtrans. Jangan meminta, menampilkan, mencetak ke log, atau meng-commit rahasia ke Git. Jika kredensial akun sebelumnya dibagikan di luar tempat yang aman, ganti kata sandi tersebut.

### 6.7 Kebijakan pembatalan

- Pelanggan tidak memiliki tombol pembatalan setelah order berstatus dibayar.
- Sistem tidak boleh menyediakan aksi admin/pemilik untuk membatalkan order yang telah dibayar melalui alur normal MVP.
- Pesanan yang belum dibayar kedaluwarsa otomatis sesuai aturan. Rekomendasi: admin boleh menandai pembatalan untuk order yang belum dibayar jika ada kebutuhan operasional, dengan alasan dan pelepasan reservasi yang aman.
- Refund, pembatalan setelah bayar, dan pengembalian dana manual berada di luar cakupan MVP. Jangan membuat klaim bahwa refund telah diproses otomatis.
- Jika terjadi kesalahan transaksi nyata setelah pembayaran, penanganannya dilakukan di luar alur sistem sesuai prosedur usaha dan dukungan/payment dashboard yang berlaku. Jangan diam-diam mengubah status database untuk menutupi transaksi gateway.

### 6.8 Pengiriman dan pengambilan di toko

- Pelanggan wajib memilih `pickup` atau `delivery`.
- Untuk `pickup`, biaya pengiriman = 0; sistem dapat menampilkan alamat dan jam pengambilan yang disetel admin.
- Untuk `delivery`, pelanggan wajib memasukkan alamat dan memilih area/zona pengiriman aktif.
- Ongkir diambil dari pengaturan zona, bukan angka yang dipercaya dari browser.
- Admin dapat mengatur nama zona, deskripsi area layanan, biaya, status aktif, dan bila diperlukan minimum order.
- Pesanan delivery di luar zona aktif tidak boleh checkout menggunakan delivery; pelanggan dapat memilih pickup jika sesuai.
- **Rekomendasi MVP:** gunakan biaya tetap per zona, tanpa integrasi perhitungan jarak atau kurir pihak ketiga.
- Slot waktu pengambilan/pengiriman boleh sederhana pada MVP: pelanggan memilih tanggal/waktu yang tersedia jika konfigurasi jadwal telah disiapkan. Jika slot belum dikonfigurasi, sistem menggunakan instruksi kontak/jam operasional yang ditampilkan dan admin mengonfirmasi waktu melalui status order. Jangan menampilkan slot yang tidak benar-benar tersedia.

### 6.9 Status order

Gunakan pemisahan status order dan status payment.

| Status order | Arti | Transisi umum |
|---|---|---|
| `awaiting_payment` | Order online dibuat, menunggu pembayaran | `paid/processing` setelah pembayaran sah; `expired` jika tenggat lewat; `failed` jika pembayaran ditolak |
| `processing` | Order telah dibayar dan sedang disiapkan | `ready_for_pickup` atau `out_for_delivery` |
| `ready_for_pickup` | Pesanan siap diambil di toko | `completed` setelah diserahkan |
| `out_for_delivery` | Pesanan diserahkan untuk diantar | `completed` setelah pengantaran selesai |
| `completed` | Pesanan selesai | Status final normal |
| `expired` | Order unpaid kedaluwarsa | Status final normal; reservasi telah dilepas |
| `failed` | Pembayaran/order gagal sebelum dibayar | Status final/menunggu tindakan sesuai aturan gateway; reservasi dilepas bila tidak ada pembayaran sah |

Status payment yang disarankan: `pending`, `paid`, `failed`, `expired`, dan `cancelled` untuk merepresentasikan hasil gateway, dengan pemetaan yang mengutamakan status sah dari Midtrans. Penamaan enum final boleh menyesuaikan struktur yang sudah ada di project, tetapi maknanya harus tetap konsisten.

**Larangan transisi:** `paid` tidak boleh kembali menjadi `pending`, `expired`, atau `failed` hanya karena notifikasi lama/duplikat. `completed` tidak kembali ke status pemrosesan pada alur normal. Order paid tidak dapat dibatalkan melalui fitur MVP.

## 7. Kebutuhan Fungsional (Functional Requirements)

Prioritas: **P0 = wajib untuk MVP**, **P1 = penting jika waktu memungkinkan**, **P2 = pengembangan lanjutan**.

### FR-01 — Beranda dan profil usaha (P0)

- Sistem menampilkan identitas DONS MEAT, ringkasan usaha, kategori, beberapa produk pilihan, informasi pengiriman/pengambilan, jam operasional, dan kontak yang sudah diatur.
- Sistem menampilkan harga standar daging sapi per kg saat ini dari pengaturan aktif.
- Jika harga standar belum diatur, UI menampilkan pesan yang aman seperti “Hubungi admin untuk informasi harga” atau menyembunyikan angka; jangan menampilkan Rp0 secara menyesatkan.
- Admin/pemilik dapat mengubah nilai tanpa perubahan source code.

### FR-02 — Kategori dan produk (P0)

- Admin/pemilik dapat menambah, melihat, mengubah, mengaktifkan/nonaktifkan, dan menghapus secara aman kategori dan produk.
- Produk memuat nama, slug, SKU/kode unik (direkomendasikan), kategori, deskripsi, gambar, harga per kg, stok gram, stok cadangan gram, batas stok menipis, status aktif, dan flag unggulan.
- Produk nonaktif tidak dapat dipesan.
- Produk yang telah memiliki histori transaksi sebaiknya dinonaktifkan/soft delete, bukan dihapus fisik yang merusak laporan.
- Validasi harga >= 0, berat/stok >= 0, nama wajib, slug unik, format gambar yang didukung, serta ukuran upload yang dibatasi.

### FR-03 — Opsi berat produk (P0)

- Admin/pemilik mengatur opsi berat per produk dalam gram.
- Opsi memiliki label tampilan, nilai gram, status aktif, dan urutan.
- Berat harus bilangan bulat positif.
- Opsi berat tidak boleh dibeli jika jumlah total berat paket melebihi stok tersedia.
- Saat opsi berat dinonaktifkan, opsi itu tidak dapat dipilih pada checkout baru tetapi tetap tersimpan pada snapshot order lama.

### FR-04 — Katalog dan detail produk (P0)

- Pengunjung dapat menelusuri produk aktif, melihat kategori, mencari nama produk, melihat harga per kg, memilih berat, dan menambahkan ke keranjang.
- Hanya produk aktif dan berat aktif yang ditampilkan.
- UI memberi peringatan bila stok habis atau tidak cukup.
- Jangan mengandalkan status stok dari cache/browser untuk menyelesaikan order; periksa ulang pada server.

### FR-05 — Keranjang (P0)

- Keranjang menyimpan pilihan produk, opsi berat, dan kuantitas paket.
- Pelanggan dapat menambah/mengurangi jumlah dan menghapus item.
- Perubahan keranjang memicu penghitungan ulang subtotal.
- Checkout mengulang validasi harga, produk aktif, opsi berat, dan stok.
- Jika harga berubah sejak produk ditambahkan ke keranjang, tampilkan harga terbaru dan minta pelanggan menyetujui ringkasan baru sebelum membuat order.

### FR-06 — Guest checkout (P0)

- Checkout tidak memerlukan registrasi/login.
- Nama dan nomor telepon wajib; alamat wajib jika delivery; email opsional/rekomendasi.
- Validasi nomor telepon, panjang input, dan sanitasi data.
- Pelanggan memilih pickup/delivery; delivery meminta zona aktif serta alamat yang memadai.
- Sistem menampilkan rincian dan total yang dihitung server sebelum pelanggan melanjutkan.
- Sistem menyimpan snapshot kontak/alamat pada order, sehingga perubahan data di masa depan tidak mengubah pesanan lama.

### FR-07 — Pembuatan order dan reservasi stok (P0)

- Server membuat order code unik dan tracking token acak yang aman.
- Server mengambil harga dan biaya pengiriman dari database.
- Server memvalidasi seluruh produk, pilihan berat, kuantitas, stok tersedia, dan zona pengiriman.
- Pembuatan order dan reservasi stok berjalan atomik di dalam database transaction.
- Jika salah satu produk tidak tersedia, seluruh checkout ditolak dengan pesan yang jelas; jangan membuat order sebagian.
- Cegah submit ganda/double click dengan idempotency key/session protection atau mekanisme ekuivalen yang sesuai.

### FR-08 — Integrasi Midtrans Snap (P0)

- Backend menyediakan proses untuk membuat transaksi Snap dengan `order_id` unik per percobaan pembayaran dan total dari server.
- Frontend menerima token pembayaran yang diperlukan, bukan Server Key.
- Status berhasil didapat dari webhook tervalidasi/pengecekan status server-to-server; redirect frontend tidak menjadi sumber kebenaran.
- Webhook memverifikasi signature, order mapping, nominal, currency dan status.
- Webhook harus idempoten dan aman terhadap notifikasi berulang/berurutan salah.
- Rekam referensi gateway dan waktu status untuk audit.
- Gunakan Sandbox pada lingkungan lokal.

### FR-09 — Kedaluwarsa order unpaid (P0)

- Setiap order pending memiliki timestamp batas pembayaran.
- Scheduled job menjalankan pengecekan order yang lewat tenggat.
- Job melepaskan stok cadangan satu kali dan menyimpan histori status.
- Job tidak boleh mengubah order paid menjadi expired.
- Dashboard menampilkan status expired dengan benar.

### FR-10 — Dashboard pesanan (P0)

- Admin/pemilik melihat daftar order dengan filter status, kanal, tanggal, dan kode order.
- Pesanan baru menimbulkan indikator/notifikasi pada dashboard.
- Detail order menampilkan item, berat, harga snapshot, ongkir, total, kontak pelanggan secukupnya, metode fulfillment, status payment, dan histori status.
- Admin mengubah status fulfillment sesuai transisi yang diizinkan.
- Status pembayaran tidak boleh diedit manual menjadi paid tanpa verifikasi gateway yang sah.
- Pesanan paid tidak bisa dibatalkan melalui alur normal.

### FR-11 — Penjualan langsung di toko tanpa role kasir (P0)

- Menu **Penjualan Langsung** hanya dapat diakses admin/pemilik yang login.
- Admin/pemilik memilih produk dan opsi berat, memasukkan kuantitas paket, lalu sistem menghitung total dari data server.
- Pada MVP, pembayaran transaksi langsung direkomendasikan menggunakan pencatatan tunai/manual, dengan status paid hanya setelah admin/pemilik mengonfirmasi transaksi tersebut benar diterima. Jangan menggunakan Midtrans online flow untuk transaksi tunai.
- Saat transaksi langsung disimpan, stok fisik dikurangi sekali dan histori `stock_movements` dicatat.
- Transaksi langsung mempunyai `channel = in_store`, mencatat user internal pembuatnya, serta masuk ke laporan yang sama dengan order online.
- Tidak ada akun atau role bernama kasir.

### FR-12 — Pengelolaan stok (P0)

- Admin/pemilik dapat mencatat stok masuk, penyesuaian tambah/kurang, dan stok rusak dengan alasan.
- Form menampilkan stok fisik, stok cadangan, stok tersedia, dan ambang stok menipis.
- `available_stock_gram` dihitung dari stok fisik dikurangi stok cadangan; jangan mengizinkan stok tersedia negatif.
- Setiap perubahan menghasilkan histori siapa, kapan, produk, delta berat, jenis perubahan, referensi order jika ada, dan alasan.
- Bila stok tersedia <= ambang yang diatur, tampilkan notifikasi stok menipis.

### FR-13 — Pengaturan usaha (P0)

- Pemilik mengatur nama/identitas usaha, logo, alamat, kontak, jam operasional, harga standar daging sapi/kg, batas waktu pembayaran, dan pengaturan umum.
- Admin boleh mengubah pengaturan operasional yang diizinkan, tetapi tidak boleh melihat/mengubah rahasia gateway.
- Semua nilai konfigurasi publik tervalidasi sebelum ditampilkan.

### FR-14 — Zona dan ongkir (P0)

- Pemilik/admin yang berwenang dapat membuat, mengubah, mengaktifkan, dan menonaktifkan zona pengiriman.
- Zona memiliki nama, deskripsi/area, biaya tetap, dan status aktif.
- Checkout menghitung biaya dari zona yang dipilih di database.
- Jika zona dinonaktifkan setelah berada di keranjang, server wajib menolak delivery saat checkout dan meminta pelanggan memilih opsi lain.

### FR-15 — Laporan (P0)

- Filter tanggal awal/akhir.
- Omzet harian dan bulanan.
- Daftar transaksi yang memuat kode order, tanggal, kanal, status, metode pemenuhan, jumlah, total dan status payment.
- Laporan penjualan hanya menghitung transaksi dibayar sesuai definisi omzet yang ditetapkan (rekomendasi: jumlah order yang status pembayarannya `paid`, bukan order pending/expired).
- Tampilkan penjualan online dan langsung secara terpisah serta total gabungan.
- Tampilkan stok menipis.
- Angka laporan harus dihitung dari data transaksi, bukan dari hardcoded atau data contoh.
- Ekspor CSV boleh menjadi P1; jika dibuat, data harus mengikuti filter yang sama.

### FR-16 — User internal (P0)

- Login khusus admin/pemilik.
- Kata sandi disimpan menggunakan hashing Laravel.
- Pemilik dapat membuat, mengubah, menonaktifkan, dan mengatur role admin.
- Akun internal tidak boleh didaftarkan publik.
- Implementasikan rate limit pada login dan validasi input.

### FR-17 — Riwayat dan audit (P0)

- Catat histori perubahan status order.
- Catat semua perubahan stok.
- Catat aktor internal untuk perubahan manual.
- Untuk perubahan harga produk, rekomendasi: simpan nilai lama/baru, aktor, dan waktu dalam tabel histori harga atau audit log.
- Jangan menyimpan password, Server Key, token rahasia, atau data kartu dalam audit log.

## 8. Kebutuhan Nonfungsional

### NFR-01 — Keamanan

- Gunakan autentikasi dan middleware/authorization Laravel sesuai role.
- Semua perubahan administratif diverifikasi di sisi server.
- Gunakan validasi request dan proteksi CSRF pada form web.
- Gunakan ORM/query builder/parameter binding untuk mencegah SQL injection.
- Escape konten pada view; hindari menampilkan HTML yang tidak tepercaya.
- Kredensial disimpan di `.env`, tidak di source code, PRD, screenshot, atau log.
- `.env` tidak boleh masuk Git. Pastikan `.gitignore` benar.
- Lindungi halaman pelacakan tamu dengan token acak yang tidak mudah ditebak; jangan cukup mengandalkan ID numerik.
- Batasi format dan ukuran file gambar, validasi mime, dan jangan izinkan upload file executable.
- Jangan simpan data kartu pembayaran atau meminta pelanggan memasukkannya ke form buatan sendiri; proses pembayaran dijalankan melalui Midtrans Snap.

### NFR-02 — Integritas data

- Gunakan foreign key dan unique index yang diperlukan.
- Operasi create order, reserve stock, payment success, dan sale deduction dilakukan dalam database transaction.
- Webhook pembayaran dan pemrosesan order wajib idempoten.
- Nominal uang menggunakan tipe decimal.
- Berat menggunakan integer gram.
- Tanggal disimpan konsisten dan ditampilkan sesuai zona waktu usaha; rekomendasi awal `Asia/Makassar` karena lingkungan pemilik berada di zona tersebut, dapat diubah pada konfigurasi.

### NFR-03 — Responsivitas dan aksesibilitas

- Tidak ada horizontal overflow pada viewport 360 px atau lebih.
- Form bisa digunakan keyboard, label input jelas, fokus terlihat, teks cukup kontras, gambar mempunyai alt text yang relevan.
- Jangan hanya mengandalkan warna untuk status pembayaran atau stok.
- Tata letak dashboard dioptimalkan untuk layar laptop tetapi tetap berfungsi di tablet/ponsel.

### NFR-04 — Kinerja

- Gunakan pagination untuk katalog admin, pesanan, stok, dan laporan.
- Hindari N+1 query dengan eager loading yang relevan.
- Optimalkan gambar dan aset statis.
- Target awal yang direkomendasikan: halaman biasa merespons dalam sekitar 3 detik pada lingkungan uji yang wajar; target ini perlu diuji setelah data dan hosting ditentukan dan tidak menjadi jaminan untuk komputer lokal yang berbeda-beda.

### NFR-05 — Pemeliharaan

- Ikuti versi Laravel, PHP, Node/NPM, dan dependency yang sudah digunakan oleh project.
- Jangan meng-upgrade major framework tanpa alasan yang jelas.
- Struktur kode memakai pola yang sudah ada dan konvensi Laravel.
- Gunakan migrations, seeders development, form requests, policies/middleware, dan tests bila sesuai dengan struktur project.
- Berikan README instalasi serta konfigurasi `.env` contoh tanpa rahasia.

### NFR-06 — Backup dan operasi

- Dokumentasikan backup database manual untuk lingkungan lokal.
- Jangan menjalankan command destruktif seperti `migrate:fresh`, `db:wipe`, atau menghapus database tanpa persetujuan eksplisit.
- Buat migration baru untuk perubahan skema dan hindari mengubah data produksi secara langsung.

## 9. Rancangan Data dan Database

Skema berikut adalah model konseptual yang disarankan. AI agent harus memeriksa migrations/model yang sudah ada terlebih dahulu dan mengadaptasi dengan aman, bukan menggandakan tabel yang sudah tersedia.

### 9.1 `users`

| Kolom | Tipe/aturan yang disarankan | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID internal |
| `name` | VARCHAR(150) | Nama user |
| `email` | VARCHAR(190), unique | Login internal |
| `password` | VARCHAR | Hasil hashing Laravel |
| `role` | ENUM/string: `owner`, `admin` | Tidak ada role `cashier` |
| `is_active` | BOOLEAN | Akun aktif/nonaktif |
| `email_verified_at` | nullable timestamp bila relevan | Opsional |
| `timestamps` | Laravel timestamps | Audit waktu |

### 9.2 `categories`

| Kolom | Tipe/aturan yang disarankan | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID kategori |
| `name` | VARCHAR(120), required | Contoh: Daging Utuh, Potongan Khusus, Tulang |
| `slug` | VARCHAR(150), unique | URL katalog |
| `description` | TEXT nullable | Deskripsi |
| `sort_order` | INTEGER default 0 | Urutan tampilan |
| `is_active` | BOOLEAN default true | Status kategori |
| `timestamps` | timestamps | — |

### 9.3 `products`

| Kolom | Tipe/aturan yang disarankan | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID produk |
| `category_id` | FK ke categories | Kategori |
| `sku` | VARCHAR, unique | Kode internal produk |
| `name` | VARCHAR(180), required | Nama produk |
| `slug` | VARCHAR(200), unique | URL detail |
| `description` | TEXT nullable | Detail produk |
| `image_path` | VARCHAR nullable | Path relatif gambar |
| `price_per_kg` | DECIMAL(12,2), >= 0 | Harga jual aktif/kg |
| `stock_gram` | BIGINT unsigned default 0 | Berat fisik tercatat |
| `reserved_stock_gram` | BIGINT unsigned default 0 | Berat yang dicadangkan untuk pending order |
| `low_stock_threshold_gram` | BIGINT unsigned default 0 | Ambang peringatan |
| `is_active` | BOOLEAN default true | Bisa dibeli atau tidak |
| `is_featured` | BOOLEAN default false | Produk unggulan di beranda |
| `timestamps` | timestamps | — |
| `deleted_at` | nullable timestamp bila soft delete dipakai | Menjaga referensi histori |

Constraint bisnis: `reserved_stock_gram <= stock_gram`; `available_stock_gram` dihitung sebagai `stock_gram - reserved_stock_gram`. Jangan mengandalkan nilai yang dikirim browser.

### 9.4 `product_weight_options`

| Kolom | Tipe/aturan yang disarankan | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | — |
| `product_id` | FK ke products | Produk induk |
| `weight_gram` | INTEGER unsigned, > 0 | Berat per paket |
| `label` | VARCHAR(80) | Label display; dapat dihasilkan otomatis dari berat |
| `sort_order` | INTEGER default 0 | Urutan opsi |
| `is_active` | BOOLEAN default true | Opsi bisa dipilih |
| `timestamps` | timestamps | — |
| Unique | (`product_id`, `weight_gram`) | Mencegah opsi berat ganda |

Harga final dihitung dari `products.price_per_kg` dan total berat. Bila bisnis di masa depan perlu harga paket berbeda, ubah desain secara eksplisit dan simpan harga snapshot pada item order.

### 9.5 `store_settings`

Satu record pengaturan toko (atau model key-value bila lebih sesuai dengan aplikasi yang sudah ada).

| Kolom | Keterangan |
|---|---|
| `id` | Primary key; rekomendasi satu record aktif |
| `business_name` | DONS MEAT sebagai nilai awal |
| `logo_path` | Path relatif logo |
| `business_description` | Deskripsi usaha |
| `phone` | Kontak usaha |
| `email` | Email usaha bila ingin ditampilkan |
| `address` | Alamat usaha |
| `opening_hours` | JSON terstruktur atau tabel tersendiri untuk jadwal |
| `standard_beef_price_per_kg` | Harga standar daging sapi/kg di beranda/profil |
| `payment_expiry_minutes` | Default rekomendasi: 60 menit; dapat dikonfigurasi |
| `timezone` | Default rekomendasi: `Asia/Makassar` |
| `timestamps` | Waktu perubahan |

Jangan simpan Server Key/Client Key Midtrans sebagai kolom pengaturan yang bisa ditampilkan di dashboard. Kredensial gateway berada di environment variables.

### 9.6 `delivery_zones`

| Kolom | Keterangan |
|---|---|
| `id` | PK |
| `name` | Nama zona/area |
| `description` | Area yang dilayani, petunjuk atau batas wilayah |
| `fee` | Biaya tetap zona, DECIMAL |
| `minimum_order_amount` | Nullable, bila ada minimum order |
| `is_active` | Boolean |
| `timestamps` | — |

Validasi ongkir mengambil `fee` dari zona aktif di database. Jika pemilik belum menetapkan area, siapkan CRUD pengaturan tetapi jangan membuat zona layanan palsu yang terlihat publik.

### 9.7 `orders`

| Kolom | Tipe/aturan yang disarankan | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | ID internal |
| `order_code` | VARCHAR, unique | Kode yang mudah dibaca pelanggan |
| `public_tracking_token` | VARCHAR, unique, acak | Token privat untuk halaman tracking |
| `channel` | `online` / `in_store` | Kanal penjualan |
| `created_by_user_id` | FK users nullable | Pembuat transaksi langsung; null untuk guest order |
| `customer_name` | VARCHAR | Snapshot nama pelanggan |
| `customer_phone` | VARCHAR | Snapshot nomor telepon |
| `customer_email` | nullable VARCHAR | Opsional |
| `fulfillment_method` | `pickup` / `delivery` | Cara pemenuhan |
| `delivery_zone_id` | FK nullable | Zona yang dipilih untuk delivery |
| `delivery_address` | TEXT nullable | Snapshot alamat untuk pengiriman |
| `requested_fulfillment_at` | nullable timestamp | Waktu pilihan/permintaan jika fitur jadwal diaktifkan |
| `subtotal` | DECIMAL(12,2) | Total item sebelum ongkir |
| `delivery_fee` | DECIMAL(12,2) | Snapshot ongkir |
| `total_amount` | DECIMAL(12,2) | Total order |
| `order_status` | string/enum yang konsisten | Status pemenuhan/order |
| `payment_status` | string/enum yang konsisten | Status pembayaran |
| `customer_note` | TEXT nullable | Catatan dari pelanggan |
| `internal_note` | TEXT nullable | Catatan operasional, tidak terlihat publik |
| `payment_deadline_at` | nullable timestamp | Tenggat pembayaran |
| `paid_at` | nullable timestamp | Waktu dinyatakan dibayar |
| `expired_at` | nullable timestamp | Waktu kedaluwarsa |
| `completed_at` | nullable timestamp | Waktu selesai |
| `timestamps` | timestamps | — |

Data kontak dan alamat dalam order adalah snapshot. Jangan menampilkan `internal_note` di halaman pelanggan.

### 9.8 `order_items`

| Kolom | Keterangan |
|---|---|
| `id` | PK |
| `order_id` | FK orders |
| `product_id` | FK products nullable jika penghapusan/arsip produk diperlukan; pertahankan snapshot |
| `product_name_snapshot` | Nama produk pada saat order |
| `product_sku_snapshot` | SKU/kode saat order |
| `weight_per_package_gram` | Berat per paket yang dipilih |
| `quantity` | Jumlah paket, integer positif |
| `total_weight_gram` | Berat total item = berat paket × jumlah |
| `price_per_kg_snapshot` | Harga per kg saat order |
| `subtotal` | Subtotal Rupiah untuk item |
| `timestamps` | — |

Validasi konsistensi antara `weight_per_package_gram`, `quantity`, `total_weight_gram`, harga snapshot, dan subtotal. Nilai snapshot tidak berubah setelah order dibuat.

### 9.9 `payments`

| Kolom | Keterangan |
|---|---|
| `id` | PK |
| `order_id` | FK orders |
| `provider` | `midtrans` atau `manual` untuk transaksi langsung |
| `gateway_order_id` | Unique ID untuk percobaan transaksi Midtrans, nullable untuk manual |
| `gateway_transaction_id` | Nullable, ID dari gateway |
| `snap_token` | Nullable token Snap yang diperlukan untuk alur pelanggan; jangan pernah menyimpan Server Key di sini |
| `amount` | Nilai transaksi yang dikirim/diterima |
| `currency` | IDR |
| `payment_type` | Nullable, misalnya tipe metode dari Midtrans |
| `status` | Status payment yang konsisten |
| `paid_at` | Nullable timestamp |
| `expires_at` | Nullable timestamp |
| `gateway_status_code` | Nullable kode status |
| `last_notification_at` | Nullable timestamp |
| `metadata` | JSON terbatas untuk metadata non-sensitif bila benar-benar perlu |
| `timestamps` | — |

Satu order dapat memiliki beberapa percobaan pembayaran jika implementasi mengizinkan retry; setiap percobaan harus memakai `gateway_order_id` unik. Cegah percobaan pembayaran baru jika order sudah paid.

### 9.10 `stock_movements`

| Kolom | Keterangan |
|---|---|
| `id` | PK |
| `product_id` | FK products |
| `order_id` | FK nullable orders |
| `created_by_user_id` | FK nullable users |
| `movement_type` | `inbound`, `sale`, `adjustment_in`, `adjustment_out`, `damaged`, `reserve`, `release_reservation` |
| `stock_delta_gram` | Perubahan stok fisik bertanda positif/negatif bila relevan |
| `reserved_delta_gram` | Perubahan stok cadangan bertanda positif/negatif bila relevan |
| `stock_balance_after_gram` | Saldo fisik sesudah perubahan |
| `reserved_balance_after_gram` | Saldo cadangan sesudah perubahan |
| `reason` | Alasan yang terbaca |
| `created_at` | Waktu pencatatan |

Jenis movement harus dibedakan dengan jelas. Reserve/release mengubah cadangan, bukan stok fisik. Sale mengurangi stok fisik dan melepaskan reservasi jika berasal dari order online.

### 9.11 `order_status_histories`

Kolom minimum: `id`, `order_id`, `from_status`, `to_status`, `actor_user_id` nullable, `source` (admin/customer/system/midtrans), `note` nullable, dan `created_at`. Simpan setiap perubahan yang relevan, khususnya dari webhook, job kedaluwarsa, dan admin.

### 9.12 `product_price_histories` (rekomendasi)

Kolom minimum: `id`, `product_id`, `old_price_per_kg`, `new_price_per_kg`, `changed_by_user_id`, `reason` nullable, `created_at`. Harga standar pada `store_settings` juga boleh dicatat dalam audit log yang setara.

### 9.13 Relasi utama

- `categories` 1—N `products`.
- `products` 1—N `product_weight_options`.
- `orders` 1—N `order_items`.
- `products` 1—N `order_items` melalui produk saat ini, sementara nama/SKU/harga disimpan sebagai snapshot.
- `orders` 1—N `payments`.
- `products` 1—N `stock_movements`.
- `orders` 1—N `stock_movements`.
- `orders` 1—N `order_status_histories`.
- `users` 1—N `orders` yang dibuat melalui penjualan langsung dan 1—N perubahan stok/status yang dilakukan user.
- `delivery_zones` 1—N `orders` untuk metode delivery.

### 9.14 Prinsip migrasi

Jangan langsung membuat semua tabel tanpa memeriksa project. Audit tabel/model yang sudah ada; gunakan tabel existing jika fungsinya cocok. Perubahan wajib dibuat melalui migration yang aman dan tidak boleh menghapus data existing secara otomatis.

## 10. Alur Proses Sistem

### 10.1 Pemesanan online

1. Pengunjung membuka beranda/katalog.
2. Pengunjung memilih produk, opsi berat yang aktif, dan kuantitas paket.
3. Sistem menampilkan harga/subtotal per item.
4. Pelanggan membuka keranjang dan melanjutkan checkout sebagai tamu.
5. Pelanggan mengisi nama, nomor telepon, memilih pickup atau delivery, dan mengisi data yang diperlukan.
6. Backend memvalidasi semua item, harga, opsi berat, stok tersedia, dan biaya pengiriman.
7. Backend membuat order, item snapshot, tenggat pembayaran, dan reservasi stok secara atomik.
8. Backend meminta Snap token ke Midtrans menggunakan Server Key dari environment.
9. Pelanggan menyelesaikan pembayaran pada Snap.
10. Midtrans mengirim notifikasi server-to-server ke endpoint webhook aplikasi.
11. Backend memverifikasi signature, order ID, nominal, currency, dan status; lalu memproses update secara idempoten.
12. Jika paid sah, stok fisik dikurangi dan reservasi dilepas sekali, status order diperbarui menjadi processing, dan notifikasi dashboard bertambah.
13. Admin memproses pesanan dan mengubah status ke ready for pickup atau out for delivery.
14. Admin menandai selesai setelah barang diambil/dikirim; dashboard dan laporan diperbarui.
15. Jika payment belum selesai sampai tenggat, scheduled task menandai order expired dan melepaskan reservasi satu kali.

### 10.2 Pembelian langsung di toko

1. Admin/pemilik login ke dashboard.
2. Membuka menu Penjualan Langsung.
3. Memilih produk dan opsi berat; sistem memvalidasi stok terkini.
4. Sistem menghitung subtotal dan total.
5. Admin/pemilik menerima pembayaran langsung (misalnya tunai) dan mengonfirmasikannya.
6. Backend membuat order `channel = in_store`, payment provider `manual`, status paid setelah konfirmasi yang sah, mengurangi stok fisik, dan mencatat movement.
7. Sistem menampilkan nomor transaksi dan ringkasan transaksi yang dapat dicetak atau disimpan.
8. Transaksi masuk ke laporan gabungan serta laporan kanal langsung.

### 10.3 Penambahan dan penyesuaian stok

1. Admin/pemilik membuka detail produk/stok.
2. Memilih jenis perubahan (stok masuk, penyesuaian tambah/kurang, atau rusak).
3. Memasukkan berat gram dan alasan.
4. Backend menghitung saldo terbaru secara atomik dan menolak hasil stok fisik yang negatif atau lebih kecil dari reservasi yang masih aktif.
5. Sistem mencatat histori movement dengan aktor dan saldo setelah perubahan.
6. Ketersediaan katalog dan notifikasi stok menipis diperbarui.

## 11. Rancangan API/Route (Pedoman, Bukan Keharusan Nama Persis)

Project dapat menggunakan Laravel web routes dan controller untuk aplikasi Blade. Jangan membuat API terpisah jika tidak diperlukan.

### 11.1 Route publik yang disarankan

| Method | Path contoh | Fungsi |
|---|---|---|
| GET | `/` | Beranda |
| GET | `/produk` | Katalog |
| GET | `/produk/{slug}` | Detail produk |
| GET | `/keranjang` | Keranjang |
| POST | `/checkout` | Validasi dan buat order |
| POST | `/orders/{order}/payment` | Minta token Snap melalui backend |
| GET | `/pesanan/{publicToken}` | Lihat status order tamu menggunakan token privat |
| POST | `/midtrans/notification` | Webhook notifikasi Midtrans; dikecualikan dari CSRF dengan konfigurasi aman sesuai framework dan diverifikasi via signature |

Path publik tidak harus sama persis, tetapi harus jelas, konsisten, dan sesuai route convention project.

### 11.2 Route internal yang disarankan

- `/admin/dashboard`
- `/admin/orders` dan detail/update status order
- `/admin/products`, `/admin/categories`, `/admin/weight-options`
- `/admin/inventory`, `/admin/stock-movements`
- `/admin/direct-sales`
- `/admin/delivery-zones`
- `/admin/reports/sales`
- `/admin/settings`
- `/admin/users` (khusus pemilik)

Gunakan middleware/authentication dan authorization policy sesuai peran. Endpoint webhook harus memiliki proteksi khusus berbasis signature dan pemeriksaan payload, bukan autentikasi sesi pengguna.

## 12. Konfigurasi Environment dan Midtrans

### 12.1 Variabel environment yang disarankan

Nama dapat disesuaikan dengan pola project, tetapi konfigurasi harus terpisah dari source code.

```dotenv
APP_NAME="DONS MEAT"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Makassar

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dons_meat
DB_USERNAME=your_local_db_user
DB_PASSWORD=your_local_db_password

MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
```

- Nilai di atas adalah contoh placeholder, bukan kredensial yang dapat langsung digunakan.
- Jangan menyalin email atau password login akun ke variabel Midtrans.
- Ambil Client Key dan Server Key dari halaman Access Keys pada dashboard Midtrans untuk lingkungan yang sesuai.
- Jangan commit `.env`; `.env.example` hanya berisi nama variabel dan nilai kosong/contoh nonrahasia.
- Gunakan key Sandbox selama pengembangan lokal; key Production hanya digunakan saat benar-benar siap live dan setelah proses aktivasi/konfigurasi merchant selesai.

### 12.2 Pengujian webhook di komputer lokal

Midtrans perlu mengirim notifikasi HTTP ke server aplikasi. Alamat `localhost` atau `127.0.0.1` pada komputer pemilik tidak dapat diakses langsung dari layanan eksternal. Untuk menguji webhook pada local development, gunakan HTTPS tunnel yang disetujui pemilik (misalnya alat tunnel yang tepercaya) atau deploy ke staging publik. Jangan mengirim kredensial ke layanan tunnel; jangan mengekspos dashboard/admin tanpa proteksi.

Pastikan URL notifikasi dikonfigurasi pada dashboard Sandbox Midtrans. Jalankan scheduled task Laravel selama pengujian kedaluwarsa, atau jalankan mekanisme scheduler yang telah disediakan aplikasi. README harus menjelaskan langkah ini.

### 12.3 Implementasi SDK

Rekomendasi menggunakan library PHP resmi Midtrans melalui Composer (`midtrans/midtrans-php`) setelah AI agent memastikan kompatibilitas PHP dan Laravel yang telah terpasang. Jangan memperbarui major version dependency yang tidak terkait. Dokumentasi integrasi resmi: https://docs.midtrans.com/docs/snap-snap-integration-guide.

## 13. Notifikasi Dashboard

MVP hanya memerlukan notifikasi di dashboard internal, bukan push notification/email/WhatsApp.

- Ada indikator jumlah pesanan online baru yang belum dilihat/diproses.
- Daftar pesanan dapat menandai order baru berdasarkan status dan timestamp.
- Setelah admin membuka atau memproses order, indikator dapat diperbarui.
- Notifikasi tidak boleh mengubah status transaksi secara otomatis hanya karena admin melihatnya.
- Implementasi awal boleh menggunakan polling ringan atau refresh daftar jika project belum memiliki WebSocket/broadcasting. Jangan memasang infrastruktur realtime berat tanpa kebutuhan yang jelas.
- Jika memakai polling, berikan interval masuk akal dan hentikan/kurangi polling ketika tab tidak aktif.

## 14. Laporan dan Definisi Perhitungan

### 14.1 Definisi omzet

Rekomendasi untuk MVP: omzet dihitung dari total order yang telah dinyatakan dibayar secara sah. Order pending, failed, expired, dan order yang baru dibuat tidak masuk omzet dibayar. Untuk transaksi langsung, omzet dihitung setelah admin/pemilik mengonfirmasi pembayaran manual.

`omzet_harian = total total_amount dari order paid pada tanggal lokal yang dipilih`

`omzet_bulanan = total total_amount dari order paid pada bulan lokal yang dipilih`

Gunakan zona waktu konfigurasi usaha secara konsisten. Ongkir yang dibayar pelanggan termasuk dalam total penerimaan penjualan pada laporan transaksi; jika di masa depan perlu pelaporan pendapatan produk terpisah dari ongkir, tampilkan metrik tersebut secara eksplisit.

### 14.2 Laporan minimum

- Ringkasan omzet hari ini.
- Ringkasan omzet bulan ini.
- Jumlah order paid.
- Daftar transaksi dengan filter tanggal/status/kanal.
- Rekap online vs pembelian langsung.
- Daftar produk yang stok tersedia di bawah ambang.

Perhitungan laba belum menjadi requirement MVP karena biaya modal dan biaya operasional belum ditentukan. Jangan menampilkan laba atau margin sebagai angka akurat tanpa model biaya modal yang benar.

## 15. Pengujian dan Acceptance Criteria

Fitur hanya dianggap selesai setelah diuji di sisi server dan UI. Minimal jalankan unit/feature tests yang sesuai dengan project, lalu lakukan uji manual responsif.

| ID | Skenario | Hasil yang diharapkan |
|---|---|---|
| AC-01 | Pengunjung membuka beranda | Konten, harga standar saat ini, dan produk unggulan muncul dari database/pengaturan |
| AC-02 | Harga standar usaha diubah oleh pemilik | Angka pada beranda/profil berubah tanpa edit source code |
| AC-03 | Admin mengubah harga produk | Katalog memakai harga baru; order lama tetap memakai harga snapshot |
| AC-04 | Pelanggan membuka katalog | Hanya produk aktif dan opsi berat aktif terlihat |
| AC-05 | Pelanggan checkout tanpa akun | Order dapat dibuat tanpa registrasi/login setelah validasi sukses |
| AC-06 | Pelanggan memilih berat nonaktif atau mengirim ID manipulatif | Backend menolak pilihan yang tidak sah |
| AC-07 | Total order dimanipulasi dari browser | Backend mengabaikan total browser dan menghitung ulang dari database |
| AC-08 | Checkout meminta berat melebihi available stock | Order ditolak dan tidak ada reservasi parsial |
| AC-09 | Dua pelanggan checkout produk yang sama bersamaan | Tidak terjadi overselling atau stok negatif |
| AC-10 | Order online dibuat pending | Stok cadangan bertambah; stok fisik belum berkurang |
| AC-11 | Webhook Midtrans valid menandakan pembayaran berhasil | Payment menjadi paid dan stok fisik berkurang sekali; reservasi dilepas sekali |
| AC-12 | Webhook dikirim dua kali | Tidak ada pengurangan stok/omzet ganda |
| AC-13 | Signature webhook tidak valid | Webhook ditolak/tidak mengubah payment, stok atau status order |
| AC-14 | Nominal/order ID/currency webhook tidak cocok | Status tidak dianggap paid dan dicatat untuk investigasi |
| AC-15 | Pelanggan kembali dari halaman Snap dengan URL sukses tetapi webhook belum valid | UI tidak menganggap paid hanya dari redirect; status menunggu konfirmasi |
| AC-16 | Order belum dibayar melewati tenggat | Order expired, reservasi dilepas sekali, stok fisik tetap |
| AC-17 | Job kedaluwarsa berjalan pada order paid | Status paid tidak berubah dan stok tidak diubah lagi |
| AC-18 | Pelanggan mencoba membatalkan order paid | Tidak tersedia aksi; server menolak permintaan pembatalan |
| AC-19 | Admin membuat penjualan langsung | Order `in_store` tersimpan, payment manual dicatat, stok fisik turun sekali |
| AC-20 | Penjualan langsung dibuat tanpa role kasir | Hanya admin/pemilik yang memiliki izin bisa melakukannya |
| AC-21 | Admin menambah stok dengan alasan | Stok dan histori movement diperbarui secara konsisten |
| AC-22 | Stok available mencapai ambang | Dashboard menampilkan peringatan stok menipis |
| AC-23 | Pelanggan memilih delivery | Zona aktif dan ongkir dari database masuk ke total |
| AC-24 | Pelanggan memilih pickup | Ongkir menjadi nol dan tidak meminta alamat delivery wajib |
| AC-25 | Pelanggan memilih zona yang dinonaktifkan | Backend menolak delivery pada checkout dan meminta pilihan lain |
| AC-26 | Admin mencoba melihat/mengubah pengaturan khusus pemilik | Akses ditolak oleh server |
| AC-27 | Pengunjung mencoba membuka dashboard | Dialihkan ke login/ditolak |
| AC-28 | Ukuran layar 360 px, tablet, dan desktop | Halaman tidak overflow dan fungsi inti bisa digunakan |
| AC-29 | Laporan harian/bulanan difilter | Hanya transaksi yang sesuai periode/status ikut terhitung |
| AC-30 | Produk diarsipkan setelah memiliki transaksi | Histori transaksi tetap dapat ditampilkan dari snapshot |
| AC-31 | Penjualan yang sama dikirim berulang oleh browser | Server mencegah pembuatan/pemrosesan transaksi ganda sesuai mekanisme idempotensi |
| AC-32 | `.env` dan key diperiksa dalam commit | Tidak ada secret nyata yang masuk ke Git atau log |

## 16. Tahapan Implementasi yang Disarankan

AI agent harus membagi implementasi menjadi tahap kecil agar project mudah diperiksa dan masalah dapat dilokalisasi.

### Tahap 0 — Audit project (wajib sebelum coding)

- Periksa `composer.json`, versi PHP/Laravel, `package.json`, `.env.example`, `routes`, migrations, models, controllers, views, assets, testing setup, dan autentikasi yang sudah ada.
- Identifikasi pola UI dan komponen yang tersedia.
- Pastikan tidak membuat ulang Laravel karena project sudah dimiliki.
- Laporkan konflik atau prasyarat yang penting secara ringkas.

### Tahap 1 — Fondasi dan autentikasi internal

- Pastikan migrasi/database berjalan aman.
- Implementasikan login admin/pemilik dan middleware hak akses.
- Siapkan layout dashboard responsif.
- Seed data demo hanya di lingkungan development; jangan membuat kredensial admin produksi dengan password hardcoded.

### Tahap 2 — Katalog dan pengaturan usaha

- Kategori, produk, upload gambar, opsi berat, harga produk, harga standar/kg, dan halaman publik.
- Validasi responsif pada ponsel, tablet, desktop.

### Tahap 3 — Stok dan transaksi langsung

- Histori stok dan pencatatan stok masuk/penyesuaian.
- Menu Penjualan Langsung tanpa role kasir.
- Pengujian perhitungan berat/harga dan stok.

### Tahap 4 — Checkout online dan ongkir

- Keranjang, guest checkout, delivery zones, reservasi stok, order snapshot, token tracking.
- Pengujian race condition/overselling dan validasi server.

### Tahap 5 — Integrasi Midtrans Sandbox

- Konfigurasi env dan SDK.
- Pembuatan Snap token di backend.
- Webhook dengan signature verification, idempotensi, order/payment state mapping.
- Scheduled task kedaluwarsa dan pelepasan reservasi.
- Pengujian lokal menggunakan tunnel/staging untuk webhook.

### Tahap 6 — Dashboard order, notifikasi, dan laporan

- Pemrosesan order, status pickup/delivery, indikator pesanan baru.
- Omzet harian/bulanan, transaksi per kanal, stok menipis.

### Tahap 7 — Hardening dan dokumentasi

- Jalankan test suite, perbaiki bug, cek keamanan role dan webhook.
- Uji ukuran layar.
- Dokumentasikan instalasi, konfigurasi environment, seeding, scheduler, dan Midtrans Sandbox.
- Jangan mengaktifkan Production Midtrans sampai pemilik telah mengambil key Production dan menyetujui transisi ke pembayaran nyata.

## 17. Risiko dan Mitigasi

| Risiko | Mitigasi |
|---|---|
| Stok terjual ganda saat checkout bersamaan | Database transaction, row locking, validasi stok di server, dan idempotensi |
| Webhook palsu/manipulasi status paid | Verifikasi signature, validasi order ID, nominal, currency, dan status gateway |
| Notifikasi webhook berulang | Idempotensi dan pencatatan status/payment event |
| Harga berubah saat barang berada di keranjang | Hitung ulang server dan minta persetujuan ulang pada checkout |
| Stok terjebak karena order pending | Tenggat pembayaran, scheduled job, dan pelepasan reservasi idempoten |
| Harga beranda berbeda dari harga produk | Bedakan pengaturan harga standar/kg dari harga tiap produk secara eksplisit |
| Admin mengakses fungsi pemilik | Middleware/policy di backend, bukan hanya menyembunyikan tombol |
| Localhost tidak menerima webhook Midtrans | Gunakan HTTPS tunnel tepercaya atau staging publik, dengan Sandbox |
| Kunci rahasia masuk Git/log | Environment variables, `.gitignore`, redaksi log, dan review repository |
| UI referensi ditiru terlalu literal | Gunakan pola UX sebagai inspirasi; bangun komponen, konten, dan aset orisinal DONS MEAT |
| Klaim laba tidak akurat | Jangan menampilkan laba sebelum data harga modal/biaya tersedia |

## 18. Keputusan yang Masih Harus Dikonfirmasi Pemilik (Tidak Menghambat MVP Lokal)

Bagian berikut tidak boleh dianggap sebagai fakta operasional sampai dikonfirmasi. AI agent dapat menggunakan rekomendasi awal sambil menyediakan pengaturan yang mudah diubah.

| Topik | Rekomendasi awal | Dampak jika keputusan berubah |
|---|---|---|
| Durasi order pending | 60 menit | Mengubah konfigurasi expiration Midtrans/scheduled task |
| Berat paket yang tersedia | Opsi dapat dikelola admin per produk | Tidak perlu migration baru jika model opsi berat fleksibel |
| Ongkir | Biaya tetap per zona | Jika berdasarkan jarak/kurir, memerlukan integrasi tambahan |
| Waktu pickup | Instruksi/jadwal yang diatur admin; slot detail opsional | Dapat ditambah sistem ketersediaan slot jika dibutuhkan |
| Kontak/alamat/jam operasional | Diatur dari dashboard | Nilainya harus dilengkapi oleh pemilik sebelum go-live |
| Metode pembayaran transaksi langsung | Catat tunai/manual dan konfirmasi admin/pemilik | Jika perlu pembayaran QRIS/gateway di toko, alur pembayaran perlu diperluas |
| Riwayat batch/kedaluwarsa | Di luar MVP | Tambahkan tabel batch/FEFO jika diperlukan oleh cara penyimpanan aktual |
| Ekspor CSV | P1 | Dapat dibuat setelah laporan dasar berjalan |
| Identitas visual final | Belum ada aset yang diberikan di PRD ini | Logo/foto/warna final dimasukkan saat aset tersedia |
| Produksi/hosting | Belum dipilih; tahap ini lokal | Deployment, domain, HTTPS, backup rutin, dan webhook publik harus dipersiapkan sebelum live |

## 19. Definition of Done (DoD)

Implementasi MVP belum boleh dinyatakan selesai jika masih ada salah satu kondisi berikut:

- [ ] Project Laravel lama telah diaudit dan tetap dipertahankan; tidak dibuat ulang tanpa alasan.
- [ ] Semua migration baru dapat dijalankan pada database development tanpa menghapus data existing.
- [ ] Produk, kategori, pilihan berat, harga standar/kg, dan stok dapat diatur melalui dashboard.
- [ ] Katalog/beranda responsif, harga standar dari pengaturan database.
- [ ] Guest checkout dan pilihan pickup/delivery bekerja.
- [ ] Perhitungan harga berat menggunakan gram dan snapshot harga order.
- [ ] Stok sinkron antara order online dan transaksi langsung; tidak oversell.
- [ ] Menu Penjualan Langsung bisa digunakan oleh admin/pemilik tanpa role kasir.
- [ ] Midtrans Sandbox berjalan, signature webhook tervalidasi, dan status tidak bergantung pada redirect browser.
- [ ] Webhook idempoten; pembayaran ganda tidak mengurangi stok ganda.
- [ ] Order unpaid kedaluwarsa dan stok cadangan dilepas dengan benar.
- [ ] Order paid tidak dapat dibatalkan melalui alur normal.
- [ ] Dashboard menampilkan pesanan baru, laporan omzet, laporan penjualan, dan stok menipis.
- [ ] Hak akses diperiksa di server.
- [ ] Tidak ada kredensial nyata dalam kode, markdown, log, screenshot, atau repository.
- [ ] Test suite dan uji manual utama lulus atau kegagalan yang tersisa didokumentasikan.
- [ ] README menjelaskan setup lokal, `.env`, migration, seeding, scheduler, dan cara uji Midtrans Sandbox.

## 20. Instruksi Wajib untuk AI Coding Agent / AI IDE

Bagian ini merupakan kontrak kerja implementasi. Ikuti urutannya.

### 20.1 Sebelum mengubah kode

1. Baca dokumen ini sampai selesai.
2. Audit repository yang sedang terbuka. Pastikan root project adalah project Laravel yang sudah ada.
3. Periksa versi runtime, struktur folder, pola autentikasi, migration/model, routing, view, asset pipeline, komponen UI, dan test setup.
4. Catat apa yang sudah ada dan apa yang belum ada. Gunakan komponen/kode existing jika sesuai.
5. Buat rencana perubahan file dan tahapan implementasi singkat. Jangan mulai dengan mengganti seluruh struktur atau mendownload/membuat project Laravel baru.

### 20.2 Saat implementasi

- Ikuti versi Laravel/PHP dan konvensi repo yang ada.
- Implementasikan fitur secara bertahap dari fondasi hingga checkout/payment.
- Gunakan migration baru dan perubahan kompatibel; jangan menghapus data atau mengedit migration lama yang sudah digunakan tanpa alasan jelas.
- Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP TABLE`, atau reset destruktif.
- Gunakan validasi request, authorization middleware/policies, database transaction, foreign key, dan tes untuk aturan stok/pembayaran.
- Jangan mempercayai total harga, diskon, berat, ongkir, status pembayaran, atau role yang dikirim browser.
- Terapkan stok gram, opsi berat, harga snapshot, perhitungan server, reservasi, dan idempotensi sesuai PRD.
- Jangan menciptakan role `kasir`.
- Jangan membuat login pelanggan wajib.
- Jangan mengimplementasikan AI features hanya karena ada kata AI pada referensi desain Dribbble.
- Gunakan foto placeholder hanya jika belum ada aset, dan tandai sebagai placeholder; jangan mengklaim foto itu adalah produk nyata DONS MEAT.
- Jangan menyalin aset/teks website referensi secara persis.
- Jangan meng-hardcode data laporan, harga, stok, atau status order.

### 20.3 Aturan kredensial dan pembayaran

- Jangan meminta, mencatat, mengungkapkan, atau menyalin password login akun ke source code.
- Email/password dashboard bukan pengganti Midtrans `Client Key`/`Server Key`.
- Jangan menaruh secret ke Blade/frontend, file markdown lain, seeders, logs, atau commit.
- Gunakan variabel `.env` dan config service Laravel; perbarui `.env.example` dengan placeholder kosong saja.
- Gunakan Sandbox sampai pemilik secara eksplisit menyetujui Production.
- Webhook harus diverifikasi dan idempoten sebelum memperbarui order/payment/stock.
- Jangan menandai payment `paid` dari query string redirect atau JavaScript klien.
- Jangan mencoba melakukan transaksi live dengan kredensial yang muncul di percakapan.

### 20.4 Sebelum menyatakan selesai

1. Jalankan test yang relevan dan laporkan hasil nyata; jangan mengklaim test lulus jika tidak dijalankan.
2. Jalankan pemeriksaan migration dan asset build yang sesuai dengan project.
3. Pastikan tidak ada role kasir, tidak ada login wajib pelanggan, dan tidak ada credential rahasia dalam git diff.
4. Periksa status pesanan dan stok pada skenario sukses, gagal, expired, webhook duplikat, dan checkout bersamaan.
5. Periksa UI pada ponsel, tablet, dan desktop.
6. Perbarui README dengan langkah setup dan pengujian yang benar-benar bekerja.
7. Berikan ringkasan file yang dibuat/diubah, command yang dijalankan, hasil tes, konfigurasi yang masih harus diisi pemilik, dan blocker yang belum terselesaikan.

## 21. Referensi Produk dan Teknis

### 21.1 Referensi desain/produk

1. **Meat Tracker — Online ordering for your meat shop.** Referensi struktur website usaha daging, harga per berat, pemesanan online, pickup, pengiriman, dan area layanan. https://meattracker.app/
2. **UIX Maruf Hossen — AI Food Marketplace App UI/UX Design | Meat Shopping & Delivery.** Referensi visual katalog marketplace daging, discovery produk, pemilihan berat, dan pengalaman mobile-first. Inspirasi UI saja; fitur AI tidak termasuk MVP. https://dribbble.com/shots/27780857-AI-Food-Marketplace-App-UI-UX-Design-Meat-Shopping-Delivery

### 21.2 Dokumentasi integrasi Midtrans

3. **Midtrans — Snap Integration Guide.** Alur umum memperoleh token Snap di backend, menampilkan halaman pembayaran, dan menangani status pembayaran. https://docs.midtrans.com/docs/snap-snap-integration-guide
4. **Midtrans — Access Keys.** Penjelasan Client Key dan Server Key serta kewajiban menjaga Server Key tetap rahasia. https://docs.midtrans.com/docs/access-keys
5. **Midtrans — HTTP(S) Notification / Webhooks.** Struktur notifikasi dan verifikasi signature untuk memeriksa keaslian notifikasi. https://docs.midtrans.com/docs/https-notification-webhooks
6. **Midtrans — Switching to Production Mode.** Langkah umum penggunaan key dan environment Production setelah onboarding/konfigurasi selesai. https://docs.midtrans.com/docs/switching-to-production-mode

### 21.3 Referensi penelitian terdahulu (untuk konteks tambahan)

7. Ardiyansyah, R., & Wahyuddin, M. I. (2022). *Sistem Informasi Penjualan Daging Menerapkan Model User Centered Design Berbasis Web*. Jurnal Media Informatika Budidarma, 6(2). https://ejurnal.stmik-budidarma.ac.id/index.php/mib/article/view/3562
8. Kule, Y., Abby, S., La Wungo, S., Jeffry, & Supak, N. A. (2022). *Perancangan Sistem Informasi Penjualan Daging Sapi Berbasis Web pada UD. Sakinah*. Journal of System and Computer Engineering. https://journal.unpacti.ac.id/JSCE/article/view/360
9. Eugene, D. A., Darmanto, T., & Antonius. (2024). *Perancangan Sistem Informasi Penjualan Daging Ayam pada PT Ciomas Adisatwa Berbasis Website*. MASITIKA. https://journal.widyadharma.ac.id/index.php/masitika/article/view/10406
10. Syafitri, L., Susanto, & Handayani, T. (2026). *Implementasi Metode First Expired First Out (FEFO) pada Sistem Manajemen Inventaris Distributor Daging Berbasis Web*. Jurnal Teknologi Informasi dan Multimedia, 8(3). https://journal.sekawan-org.id/index.php/jtim/id/article/view/1040

Referensi penelitian digunakan sebagai konteks, bukan sebagai bukti bahwa proses bisnis DONS MEAT sama dengan objek penelitian tersebut. Sebelum dipakai pada skripsi, baca naskah lengkapnya dan pastikan format sitasi mengikuti pedoman kampus.

## 22. Glosarium

| Istilah | Definisi |
|---|---|
| Admin | Pengguna internal yang mengelola operasional sesuai hak akses |
| Pemilik | Pengguna internal dengan hak kontrol penuh yang ditentukan sistem |
| Guest checkout | Pemesanan tanpa mewajibkan pelanggan membuat akun/login |
| POS | Point of Sale; pada dokumen ini fungsi transaksi langsung, bukan role/akun tersendiri |
| Stok fisik | Berat persediaan tercatat dalam gram |
| Stok cadangan/reserved | Bagian stok yang ditahan untuk order online yang belum dibayar |
| Stok tersedia | Stok fisik dikurangi stok cadangan |
| Snapshot harga | Salinan harga yang berlaku saat transaksi dibuat |
| Midtrans Snap | Halaman/flow pembayaran Midtrans yang tokennya dibuat oleh backend |
| Webhook/notification | Notifikasi server-to-server mengenai status transaksi gateway |
| Idempoten | Pemrosesan berulang atas event yang sama tidak menggandakan efek |
| Acceptance criteria | Syarat yang harus terpenuhi agar fitur dinyatakan sesuai |
| MVP | Minimum Viable Product; versi pertama yang berisi fungsi wajib |

---

**Akhir dokumen PRD DONS MEAT versi 1.0.**
