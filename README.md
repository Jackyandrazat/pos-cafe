# Labo By kodeeweb System

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-F59E0B?style=for-the-badge&logo=filament&logoColor=white)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![React PWA](https://img.shields.io/badge/React%20PWA-Self--Order-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://react.dev)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com)
[![Database](https://img.shields.io/badge/TiDB%20Cloud-MySQL-00758F?style=for-the-badge&logo=mysql&logoColor=white)](https://tidbcloud.com)

Sistem POS (Point of Sale) untuk sebuah kafe yang dibuat menggunakan **Laravel 11** dan **Filament**. Proyek ini mencakup manajemen produk, bahan baku, transaksi, pembelian bahan baku, shift kasir, laporan penjualan, dan lebih banyak lagi. Sistem ini dirancang untuk mempermudah pengelolaan operasional sehari-hari di kafe dengan fokus pada kemudahan penggunaan dan integrasi data.

Kini sistem telah berkembang menjadi ekosistem kafe modern yang terintegrasi secara cloud dengan **Kitchen Display System (KDS)** dan **Customer Self-Order Web Hub (PWA)** berbasis QR Code per meja.

---

## 🌐 Live Deployment & Lingkungan Cloud

| Komponen | Platform / Host | Region | Status | URL Akses |
|---|---|---|---|---|
| **Backend & Panel Admin POS** | Render Cloud (Docker) | Singapore / US | 🟢 Live | `https://pos-cafe-f38k.onrender.com` |
| **Customer Self-Order Hub** | Vercel (PWA) | Global CDN | 🟢 Live | `https://github.com/Jackyandrazat/cafe-order-hub` |
| **Database Utama** | TiDB Cloud Serverless (MySQL) | Singapore (`ap-southeast-1`) | 🟢 Connected | Latensi rendah (<25ms) |

---

## 🛠 Teknologi yang Digunakan

* **Laravel 11**: Framework PHP yang powerful untuk pengembangan web.
* **Filament 3**: Admin panel dan dashboard yang elegan untuk Laravel.
* **MySQL / TiDB Cloud**: Sistem manajemen database relasional yang digunakan untuk penyimpanan data (kompatibel penuh MySQL dengan autoscaling).
* **Chart.js**: Library JavaScript untuk menampilkan grafik data penjualan secara interaktif.
* **Livewire 3 & Alpine.js**: Reactive state management pada antarmuka admin dan kasir.
* **React 18 + Vite + Tailwind CSS**: Frontend PWA mobile-first untuk Customer Self-Order Hub.
* **Docker & Nginx**: Multi-stage containerization berbasis Alpine Linux dengan PHP 8.3-FPM dan OPcache aktif.

---

## 📋 Fitur Utama

Sistem Labo By kodeeweb ini dilengkapi dengan berbagai fitur lengkap untuk mendukung operasional kafe Anda:

* **Manajemen Produk**:
    * Fungsionalitas CRUD (Create, Read, Update, Delete) untuk produk menu.
    * Pengelolaan kategori produk untuk organisasi yang lebih baik.
    * Pengaturan harga, stok, varian ukuran (Regular, Large), dan deskripsi detail untuk setiap produk.
* **Manajemen Bahan Baku**:
    * Fungsionalitas CRUD untuk bahan baku (ingredient).
    * Input komposisi bahan baku untuk setiap produk, memungkinkan pengurangan stok otomatis saat produk terjual.
    * **Resep Topping (`topping_ingredients`)**: Konsumsi bahan baku tambahan untuk topping (misal: extra shot espresso, oat milk) terpotong secara otomatis dan akurat.
    * **Atomic Stock Deduction**: Conditional decrement (`WHERE stock_qty >= needed`) untuk mencegah stok menjadi minus akibat pesanan bersamaan.
    * Sistem notifikasi untuk bahan baku yang hampir kedaluwarsa atau menipis.
* **Transaksi & Pembayaran**:
    * Membuat order baru yang intuitif untuk pelanggan (Dine In & Take Away).
    * Mengelola berbagai jenis pembayaran dan status order.
    * Opsi untuk mengirim struk via WhatsApp kepada pelanggan.
    * **Multi-Payment Gateway**: Mendukung Bayar di Kasir (Tunai), QRIS Statis / Manual, Transfer Bank, Midtrans, dan Xendit.
    * **Idempotency Guard**: Pengamanan webhook pembayaran dengan database locking (`lockForUpdate`) untuk mencegah duplikasi order.
* **Shift Kasir**:
    * Fitur untuk membuka dan menutup shift kasir dengan pencatatan modal awal (*starting cash*).
    * Rekapitulasi transaksi per shift untuk pelacakan performa dan rekonsiliasi selisih uang fisik laci kas.
* **Modul Pembelian (Restok)**:
    * Mengelola proses pembelian bahan baku dari supplier.
    * Penambahan stok bahan baku secara otomatis setelah pembelian divalidasi.
* **Laporan & Grafik**:
    * Grafik penjualan harian dan identifikasi produk terlaris (*Daily Top Orders*).
    * Laporan transaksi mendetail berdasarkan hari, shift, atau kasir.
* **Role & Permission**:
    * Pengaturan role pengguna yang fleksibel seperti **Admin**, **Kasir**, **Barista / Dapur**, dan **Owner**.
    * Akses granular ke berbagai bagian sistem sesuai dengan role pengguna.

### 🌟 Fitur Tambahan & Ekosistem Terkini (Phase 2)

* **Kitchen Display System (KDS)**:
    * Antarmuka khusus dapur dan barista untuk memantau tiket antrean pesanan secara real-time.
    * *Strict State Machine*: Transisi status pesanan (`submitted` ➔ `confirmed` ➔ `preparing` ➔ `ready` ➔ `completed`) tervalidasi agar tidak dapat melompat secara salah.
    * Pembatalan pesanan di dapur otomatis mengembalikan (*rollback*) kuota bahan baku ke gudang.
* **Customer Self-Order Hub (QR Meja)**:
    * Pelanggan men-scan QR code di meja untuk membuka menu dan langsung memesan mandiri tanpa antre di kasir.
    * Nomor meja terikat otomatis ke pesanan pelanggan.
    * Modal kustomisasi menu ramah ponsel dengan gesture *swipe-down* dan pengaman tombol *Back* HP (*popstate trap*).
    * Fitur *Panggil Pelayan* (Call Waiter) langsung dari meja.
    * *Digital Audio Chime*: Smartphone pelanggan otomatis membunyikan nada lonceng digital ganda saat pesanan ditandai `ready`.
* **Anti-Fraud Geofencing (GPS Lokasi Kafe)**:
    * Formula matematika *Haversine* mendeteksi jarak pelanggan ke titik koordinat kafe.
    * Opsi *Bayar Tunai di Kasir* otomatis dinonaktifkan jika pelanggan memesan dari luar radius kafe, mewajibkan pembayaran digital di muka (prepaid) untuk mencegah pesanan fiktif.
* **Siklus Hidup Sesi Tamu (Guest Session Lifecycle)**:
    * *Auto-Expiration 4 Jam (TTL)*: Sesi tamu non-member otomatis kadaluarsa setelah 4 jam tanpa aktivitas.
    * Tombol *"Bukan [Nama]? Masuk sebagai Tamu Baru"* saat scan ulang QR meja agar pelanggan baru di meja yang sama tidak terjebak di akun tamu sebelumnya.
    * Tombol *"Selesai & Keluar Sesi"* saat pesanan berstatus `completed`.
    * Tautan cepat `(Ganti)` di samping nama pengguna pada header menu mobile.
* **Dynamic Pricing & Promosi**:
    * Mendukung promosi BOGO (*Buy One Get One*), diskon Happy Hour, diskon nominal/persentase, dan potongan minimum belanja.
* **Program Loyalitas Member & Gamifikasi**:
    * Pendaftaran member via nomor WhatsApp dengan perolehan poin belanja.
    * Tingkatan level member (Bronze, Silver, Gold, Platinum).
    * Misi tantangan belanja (*Gamification Challenges*) berhadiah poin tambahan.
    * Poin dijamin hanya bertambah 1 kali saat pesanan berstatus `completed`.
* **Waste Management (Pencatatan Bahan Rusak/Basi)**:
    * Modul pencatatan bahan baku yang tumpah, basi, atau kedaluwarsa lengkap dengan alasan (*spoilage, damage, expired*) dan estimasi nilai kerugian.
* **Pembersihan Otomatis Pesanan Terbengkalai**:
    * Perintah Artisan terjadwal:
      ```bash
      php artisan orders:expire-pending --minutes=15
      ```
      Membatalkan pesanan unpaid yang ditinggalkan dan mengembalikan stok secara otomatis.
* **Smart Responsive Sidebar**:
    * Tombol Pin (📌) di header sidebar untuk mengunci sidebar agar tetap terbuka.
    * Tombol Ribbon Tab (🔖) melayang di samping layar untuk toggle sidebar.
    * Keyboard Shortcut: Tekan tombol `[` pada keyboard untuk membuka/menutup sidebar secara instan.
* **Kustomisasi Struk Thermal**:
    * Pengaturan logo kafe, header, footer kustom, dan integrasi pengiriman struk digital via WhatsApp.

---

## 💻 Prasyarat

Sebelum memulai instalasi, pastikan Anda telah menginstal beberapa software berikut di sistem Anda:

* **PHP 8.2+** atau **PHP 8.3** (dengan ekstensi `pdo_mysql`, `bcmath`, `mbstring`, `openssl`, `tokenizer`, `xml`, `curl`)
* **Composer 2.x**
* **MySQL 8.0+**, **MariaDB**, atau **TiDB Cloud**
* **Node.js 18+** & **npm**

---

## 🚀 Instalasi

Ikuti langkah-langkah di bawah ini untuk menginstal dan menjalankan aplikasi di lingkungan lokal Anda:

1.  **Clone Repository**

    Buka terminal atau command prompt Anda dan jalankan perintah berikut untuk mengkloning proyek:
    ```bash
    git clone https://github.com/Jackyandrazat/pos-cafe.git
    cd pos-cafe
    ```

2.  **Instal Dependensi**

    Setelah masuk ke direktori proyek, instal semua dependensi PHP menggunakan Composer dan aset frontend dengan npm:
    ```bash
    composer install
    npm install && npm run build
    ```

3.  **Konfigurasi `.env`**

    Salin file contoh konfigurasi `.env.example` menjadi `.env`:
    ```bash
    cp .env.example .env
    ```
    Kemudian, buka file `.env` dan sesuaikan konfigurasi database (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) serta layanan lain sesuai kebutuhan Anda.

4.  **Generate Key Aplikasi**

    Jalankan perintah berikut untuk menghasilkan kunci aplikasi Laravel:
    ```bash
    php artisan key:generate
    ```

5.  **Migrasi Database**

    Jalankan migrasi untuk membuat semua tabel yang diperlukan di database Anda:
    ```bash
    php artisan migrate
    ```

6.  **Seed Data (Opsional)**

    Jika Anda ingin menambahkan data sampel (seperti user, role, produk menu, meja, dll.) untuk pengujian, jalankan seeder:
    ```bash
    php artisan db:seed
    ```

7.  **Symlink Storage**

    Jalankan pembuatan symlink agar gambar produk dan aset dapat diakses publik:
    ```bash
    php artisan storage:link
    ```

8.  **Jalankan Aplikasi**

    Untuk menjalankan aplikasi, gunakan perintah Artisan:
    ```bash
    php artisan serve
    ```
    Aplikasi Anda sekarang dapat diakses melalui browser di: `http://localhost:8000/admin`.

---

## 🐳 Menjalankan dengan Docker

Proyek ini telah dilengkapi dengan konfigurasi Docker multi-stage (PHP 8.3-FPM + Nginx + OPcache):

```bash
# Build dan jalankan container
docker compose up -d --build

# Pantau log container
docker compose logs -f
```

---

## 🧪 Pengujian Otomatis (Automated Tests)

Semua fungsionalitas inti telah dilengkapi dengan automated tests untuk menjamin stabilitas:

```bash
# Menjalankan seluruh test suite
php artisan test

# Menjalankan test modul spesifik
php artisan test tests/Feature/Api/StoreConfigTest.php
php artisan test tests/Feature/KitchenDisplayTest.php
php artisan test tests/Feature/ReceiptSettingsTest.php
```

Daftar skenario UAT lengkap dapat dilihat di [docs/uat-checklist.md](docs/uat-checklist.md).

---

## 🔐 Role & Akses Pengguna

Sistem ini memiliki beberapa peran pengguna dengan tingkat akses yang berbeda:

* **Owner**: Memiliki akses penuh ke seluruh aplikasi, termasuk laporan laba rugi, konfigurasi sistem, dan manajemen user.
* **Admin**: Memiliki akses manajemen produk, bahan baku, pembelian, transaksi, laporan, dan pengaturan toko.
* **Kasir**: Dapat melakukan transaksi POS, membuka/menutup shift kasir, dan mengelola pembayaran pelanggan.
* **Barista / Dapur**: Mengoperasikan Kitchen Display System (KDS) untuk memproses tiket pesanan makanan dan minuman.

---

## 🧩 Struktur Folder

Berikut adalah gambaran singkat tentang struktur folder utama dalam proyek ini:

* `app/Console/Commands`: Berisi script cron job otomatis (seperti `orders:expire-pending`).
* `app/Filament/Resources`: Lokasi untuk semua Filament Resources yang menangani fungsionalitas CRUD dan tampilan admin panel.
* `app/Filament/Pages`: Halaman kustom Filament seperti Kitchen Display System (KDS) dan Pengaturan Struk.
* `app/Http/Controllers/Api`: Controller REST API V1 untuk melayani frontend Self-Order Hub.
* `app/Models`: Berisi semua model Eloquent yang digunakan dalam aplikasi untuk berinteraksi dengan database.
* `app/Observers`: Observer untuk menangani side-effects seperti alokasi poin loyalty dan stok otomatis.
* `app/Services`: Berisi *service helper* atau *business logic* (misalnya `StockService`, `PaymentService`, `GamifiedLoyaltyService`).
* `database/migrations`: Skrip migrasi database yang mendefinisikan struktur tabel.
* `docker/`: Berisi `Dockerfile`, `entrypoint.sh`, `php.ini`, dan konfigurasi web server untuk deployment.
* `docs/`: Dokumentasi teknis sistem, arsitektur, dan matriks UAT checklist.
* `resources/views/filament`: Berisi view Blade yang digunakan untuk kustomisasi tampilan admin panel Filament.
* `resources/js & css`: Script kustom seperti smart sidebar manager, ribbon tab, dan gaya visual.

---

## 🚧 Fitur yang Sedang Dikembangkan

Beberapa fitur sedang dalam pengembangan untuk terus meningkatkan fungsionalitas sistem:

* **Export Laporan ke PDF/Excel**: Menambahkan kemampuan untuk mengekspor laporan transaksi dan pembelian ke format PDF atau Excel.
* **Multi-Outlet Support**: Pengelolaan banyak cabang kafe dalam satu dashboard terpusat.
* **Integrasi Printer Thermal Bluetooth Langsung**: Dukungan Web Bluetooth API untuk mencetak langsung dari browser HP kasir.

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah **MIT License**. Lihat file `LICENSE` di repository untuk detail lebih lanjut.

---

## 📞 Kontak

Jika Anda memiliki pertanyaan, saran, atau menemukan masalah, jangan ragu untuk menghubungi:

- **Repository**: [https://github.com/Jackyandrazat/pos-cafe](https://github.com/Jackyandrazat/pos-cafe)
- **Email / Issue**: Silakan buka issue baru pada tab Issues di repository GitHub.

---

Terima kasih telah menggunakan **POS Cafe System**! 🚀
