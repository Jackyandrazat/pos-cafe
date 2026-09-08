# LakuPOS — Comprehensive User Acceptance Testing (UAT) Checklist

Dokumen ini memuat skenario pengujian penerimaan pengguna (UAT) menyeluruh untuk memastikan seluruh modul **Backoffice/Kasir (Laravel 11 + Filament v3)** dan **Customer Self-Order Hub (React 18 + Vite PWA)** berfungsi sesuai spesifikasi bisnis industri Food & Beverage (F&B).

---

## 📋 Informasi Pengujian
- **Nama Sistem:** LakuPOS Cafe Ecosystem
- **Target Lingkungan:** Staging / Production (`pos-cafe-f38k.onrender.com` & `cafe-order-hub`)
- **Database:** TiDB Cloud Serverless MySQL (Singapore)
- **Aktor Pengujian:**
  1. **Owner / Super Admin:** Akses penuh ke seluruh konfigurasi, laporan keuangan, dan inventori.
  2. **Kasir / Barista:** Akses kasir POS, pembukaan/penutupan shift, dan KDS dapur.
  3. **Pelanggan Tamu (Guest):** Pelanggan meja yang memesan melalui scan QR code tanpa akun tetap.
  4. **Pelanggan Member:** Pelanggan tetap dengan nomor HP terdaftar, akumulasi poin, dan level tiering.

---

## 🧪 Matriks Skenario UAT

### Bagian 1: Master Data, Resep & Inventori Bahan Baku

| ID | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|:---:|
| **UAT-01** | CRUD Produk Menu dengan Varian Ukuran & Topping | 1. Masuk ke **Menu Products**.<br>2. Tambah produk baru (misal: *Caramel Macchiato*).<br>3. Tentukan harga dasar, kategori, varian ukuran (Regular, Large), dan opsi topping yang diizinkan.<br>4. Simpan. | Produk tersimpan di database dan langsung muncul di daftar menu kasir serta web self-order pelanggan. | [ ] |
| **UAT-02** | Manajemen Resep & Pengurangan Stok Otomatis | 1. Masuk ke **Ingredients** dan tentukan stok bahan baku (misal: *Biji Kopi*: 1.000g, *Susu UHT*: 2.000ml).<br>2. Pada produk *Caramel Macchiato*, tentukan resep: 18g biji kopi + 150ml susu.<br>3. Buat pesanan produk tersebut dan selesaikan transaksi. | Stok bahan baku berkurang secara atomik (1.000 - 18 = 982g, 2.000 - 150 = 1.850ml) tanpa selisih. | [ ] |
| **UAT-03** | Resep Topping Bahan Baku (`topping_ingredients`) | 1. Buat topping *Extra Oat Milk* dengan konsumsi 100ml susu oat.<br>2. Pelanggan memesan kopi dengan topping tersebut.<br>3. Transaksi diselesaikan. | Bahan baku susu oat ikut terpotong secara otomatis melalui tabel pivot `topping_ingredients`. | [ ] |
| **UAT-04** | Validasi Atomic Stok Habis (Out of Stock Guard) | 1. Set stok bahan baku ke angka 0 atau kurang dari takaran resep.<br>2. Coba buat pesanan di kasir atau self-order. | Sistem menolak pembuatan order dengan notifikasi error *"Stok bahan baku tidak mencukupi"*, mencegah stok minus. | [ ] |
| **UAT-05** | Penataan Meja & Unduh QR Code Meja | 1. Buka menu **Tables**.<br>2. Buat meja baru (misal: *Meja 08*).<br>3. Klik aksi unduh / generate QR Code. | QR Code valid ter-generate dengan URL mengarah ke `/table/08` pada aplikasi Self-Order. | [ ] |

---

### Bagian 2: Operasional Kasir (Cashier POS & Shift Management)

| ID | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|:---:|
| **UAT-06** | Pembukaan Shift Kasir (Starting Cash) | 1. Kasir login ke panel Filament.<br>2. Masuk ke **Cashier Shifts** ➔ Klik *Buka Shift*.<br>3. Masukkan modal awal laci kasir (misal: Rp 200.000). | Shift berstatus aktif. Waktu pembukaan dan kasir tercatat. Transaksi kasir hanya dapat diproses saat shift aktif. | [ ] |
| **UAT-07** | Transaksi POS Kasir (Dine-In & Takeaway) | 1. Buka antarmuka POS Kasir.<br>2. Pilih tipe pesanan (*Dine In* / *Take Away*), nomor meja, dan pilih item menu beserta variannya.<br>3. Lakukan pembayaran Tunai. | Pesanan tersimpan, status ter-update ke *completed*, dan laci kas bertambah sesuai nilai bayar. | [ ] |
| **UAT-08** | Cetak Struk Thermal & Kirim via WhatsApp | 1. Setelah pembayaran selesai, klik tombol *Cetak Struk*.<br>2. Klik tombol *Kirim WhatsApp* dengan memasukkan nomor HP pelanggan. | Format struk thermal tercetak rapi dengan logo kafe dan detail pesanan; pesan WhatsApp terkirim otomatis berisi rincian struk belanja. | [ ] |
| **UAT-09** | Penutupan Shift Kasir (Reconciliation & Z-Report) | 1. Di akhir jam kerja, kasir membuka menu *Tutup Shift*.<br>2. Masukkan uang fisik yang ada di laci kas.<br>3. Submit penutupan. | Sistem menghitung selisih uang fisik vs kalkulasi sistem, mencatat catatan kasir, dan mengunci shift. | [ ] |

---

### Bagian 3: Kitchen Display System (KDS) & Real-Time Transitions

| ID | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|:---:|
| **UAT-10** | Tiket Pesanan Masuk Realtime di Layar Dapur | 1. Pelanggan melakukan order melalui Self-Order atau kasir memasukkan pesanan baru.<br>2. Buka layar **Kitchen Display** di browser dapur/barista. | Tiket pesanan langsung muncul di kolom *Antrean* lengkap dengan nomor meja, item, catatan, dan timer waktu tunggu. | [ ] |
| **UAT-11** | Transisi State Machine Pesanan Dapur | 1. Barista mengklik tombol transisi pesanan: `confirmed` ➔ `preparing` ➔ `ready` ➔ `completed`.<br>2. Coba paksa status melompat (misal: dari draft langsung completed). | Transisi berjalan halus sesuai urutan workflow yang valid; upaya lompatan status tidak valid diblokir sistem. | [ ] |
| **UAT-12** | Pembatalan Pesanan Dapur & Rollback Stok Bahan Baku | 1. Pesanan yang sudah diproses dibatalkan melalui modal pembatalan KDS/Kasir.<br>2. Cek stok bahan baku di master data. | Status pesanan berubah menjadi `cancelled` dan seluruh bahan baku yang sempat terpotong dikembalikan otomatis ke inventori. | [ ] |

---

### Bagian 4: Customer Self-Order Hub (Web PWA)

| ID | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|:---:|
| **UAT-13** | Scan QR Meja & Auto-Binding Meja | 1. Pelanggan men-scan QR meja dengan kamera HP atau membuka `/table/05`. | Halaman `TableEntryPage` terbuka menyambut pelanggan dan otomatis mengunci nomor Meja 05 di perangkat. | [ ] |
| **UAT-14** | Verifikasi Lokasi GPS Kafe (Geofencing) | 1. Buka aplikasi di smartphone dan izinkan akses lokasi GPS.<br>2. Bandingkan saat berada di dalam kafe vs di luar radius kafe. | Saat di dalam radius, muncul badge `🟢 Lokasi Terverifikasi`. Saat di luar radius, muncul indikator jarak estimasi. | [ ] |
| **UAT-15** | Anti-Fraud Gating: Nonaktifkan Tunai di Luar Kafe | 1. Simulasikan pelanggan memesan dari luar kafe (`!isInsideGeofence`).<br>2. Masuk ke halaman checkout pembayaran. | Opsi *Bayar Tunai di Kasir* otomatis disembunyikan/dinonaktifkan; pelanggan wajib menggunakan metode digital (prepaid). | [ ] |
| **UAT-16** | Kustomisasi Menu & Bottom Sheet Mobile Gesture | 1. Klik salah satu menu kopi di halaman menu.<br>2. Modal kustomisasi muncul dari bawah (*bottom sheet*).<br>3. Coba lakukan gesture swipe-down pada handle bar modal. | Modal tertutup mulus dengan gesture swipe-down tanpa me-refresh atau kembali ke halaman sebelumnya. | [ ] |
| **UAT-17** | Browser History Back Trap pada Modal Kustomisasi | 1. Buka modal kustomisasi menu.<br>2. Tekan tombol back fisik / gesture swipe back di HP Android/iOS. | Modal kustomisasi tertutup secara aman tanpa menyebabkan halaman browser terpental ke halaman sebelumnya. | [ ] |
| **UAT-18** | Panggil Pelayan ke Meja (Call Waiter) | 1. Klik ikon lonceng *Panggil Pelayan* di header.<br>2. Pilih alasan (minta bill / sendok / air mineral).<br>3. Kirim panggilan. | Notifikasi panggil pelayan terkirim dengan mencantumkan nomor meja yang benar. | [ ] |
| **UAT-19** | Digital Audio Chime saat Pesanan `ready` | 1. Buka halaman status pesanan di HP pelanggan.<br>2. Barista di panel dapur menandai status pesanan menjadi `ready`. | Smartphone pelanggan otomatis membunyikan nada chime dua nada (*tone chime*) memberitahu pesanan siap diambil di bar. | [ ] |
| **UAT-20** | Auto-Expiration Sesi Tamu (4-Hour TTL) | 1. Masuk sebagai tamu dan biarkan sesi lebih dari 4 jam (atau simulasikan timestamp lawas).<br>2. Buka kembali aplikasi. | Token tamu kadaluarsa otomatis dibersihkan; pengguna kembali ke halaman awal tanpa token kadaluarsa tersisa. | [ ] |
| **UAT-21** | Tombol Escape Hatch: *"Bukan [Nama]? Masuk sebagai Tamu Baru"* | 1. Buka `/table/05` pada perangkat yang masih menyimpan sesi tamu sebelumnya.<br>2. Tekan tombol *"Bukan [Nama]? Masuk sebagai Tamu Baru"*. | Sesi tamu lama langsung di-logout, keranjang dibersihkan, dan form input nama baru langsung terbuka dengan meja tetap Meja 05. | [ ] |
| **UAT-22** | Tombol *"Selesai & Keluar Sesi"* saat Pesanan Selesai | 1. Pada status pesanan `completed`, periksa bagian bawah layar.<br>2. Klik tombol *"Selesai & Keluar Sesi"*. | Sesi tamu dan nomor meja dibersihkan, lalu browser diarahkan kembali ke halaman beranda kafe secara bersih. | [ ] |
| **UAT-23** | Tombol Cepat `(Ganti)` di Header Menu Mobile | 1. Buka halaman menu pada layar HP.<br>2. Klik tautan `(Ganti)` di samping nama pengguna di header. | Sesi langsung di-reset dan navigasi kembali ke halaman entry meja untuk mengganti identitas tamu. | [ ] |

---

### Bagian 5: Member Loyalty, Promosi & Gamifikasi

| ID | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|:---:|
| **UAT-24** | Login Member via Nomor HP | 1. Pada halaman awal, pilih *Masuk sebagai Member*.<br>2. Masukkan nomor HP terdaftar (misal: `08123456789`). | Berhasil masuk; nama member, badge level (Gold/Platinum), dan akumulasi saldo poin ditampilkan. | [ ] |
| **UAT-25** | Validasi Poin Belanja Hanya Diberikan 1 Kali | 1. Member melakukan pemesanan.<br>2. Pantau saldo poin saat status pesanan masih draft, confirmed, dan preparing.<br>3. Selesaikan pesanan ke status `completed`. | Poin bertambah tepat 1 kali hanya saat pesanan `completed`. Mengubah status kembali tidak menambah poin ganda. | [ ] |
| **UAT-26** | Penerapan Promosi Otomatis (BOGO / Min Spend) | 1. Tambahkan menu yang memenuhi syarat promo BOGO atau minimum belanja ke keranjang.<br>2. Buka halaman keranjang belanja. | Diskon atau item gratis otomatis tertera pada kalkulasi ringkasan biaya tanpa perlu input manual. | [ ] |
| **UAT-27** | Misi Gamifikasi & Reward Tantangan | 1. Buka tab Loyalti & Misi di aplikasi Self-Order.<br>2. Selesaikan tantangan (misal: order 3 kopi dalam 1 minggu). | Progress bar misi bertambah dan poin bonus otomatis dikreditkan ke akun member setelah misi tercapai. | [ ] |

---

### Bagian 6: Payment Gateway & Keamanan Finansial

| ID | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|:---:|
| **UAT-28** | Transaksi via Midtrans / Xendit Gateway | 1. Pilih metode pembayaran digital (Midtrans / Xendit).<br>2. Selesaikan pembayaran simulasi sandbox. | Webhook diterima backend; transaksi diproses dengan `idempotency_key` dan status pesanan otomatis berubah ke `payment`. | [ ] |
| **UAT-29** | Pencegahan Duplikasi Webhook (Idempotency Guard) | 1. Simulasikan pengiriman webhook pembayaran yang sama sebanyak dua kali berturut-turut. | Webhook kedua dikenali sebagai duplikat dan diabaikan tanpa memotong stok atau menambah pembayaran ganda. | [ ] |
| **UAT-30** | Penukaran Saldo Gift Card / Corporate Voucher | 1. Masukkan kode Gift Card yang valid saat checkout.<br>2. Periksa pengurangan total tagihan. | Total tagihan berkurang sesuai nominal saldo voucher dan sisa saldo voucher ter-update di database. | [ ] |

---

### Bagian 7: Waste Management & System Automation

| ID | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|:---:|
| **UAT-31** | Pencatatan Bahan Baku Rusak / Basi (Waste Tracking) | 1. Buka menu **Waste Logs** di admin panel.<br>2. Catat bahan baku yang tumpah atau kadaluarsa lengkap dengan alasan.<br>3. Simpan. | Stok bahan baku langsung terpotong dan estimasi nilai kerugian (cost) tercatat dalam laporan pembukuan. | [ ] |
| **UAT-32** | Auto-Expiration Pesanan Terbengkalai (`orders:expire-pending`) | 1. Buat pesanan status `pending` (belum dibayar kasir) dan biarkan melewati batas waktu TTL (15 menit).<br>2. Jalankan perintah `php artisan orders:expire-pending`. | Pesanan otomatis dibatalkan menjadi `cancelled` dan kuota bahan bakunya dikembalikan ke stok. | [ ] |
| **UAT-33** | Smart Sidebar Ribbon Toggle & Shortcut `[` | 1. Masuk ke admin Filament.<br>2. Tekan tombol Pin (📌), Ribbon Tab (🔖), atau keyboard shortcut `[`. | Sidebar responsif membuka/menutup dengan mulus dan mengingat preferensi pin pada navigasi berikutnya. | [ ] |

---

## ✍️ Lembar Pengesahan UAT (Sign-off Sheet)

| Parameter | Keterangan |
|---|---|
| **Tanggal Pengujian:** | ___________________________ |
| **Lead QA / Tester:** | ___________________________ |
| **Product Owner / Client:** | ___________________________ |
| **Hasil Akhir:** | **[ &nbsp; ] LULUS (PASS)** &nbsp;&nbsp;&nbsp;&nbsp; **[ &nbsp; ] BERSYARAT (CONDITIONAL)** &nbsp;&nbsp;&nbsp;&nbsp; **[ &nbsp; ] GAGAL (FAIL)** |
| **Catatan Rekomendasi:** | __________________________________________________________________ |
