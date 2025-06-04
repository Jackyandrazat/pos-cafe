# POS Cafe System

Sistem POS (Point of Sale) untuk sebuah kafe yang dibuat menggunakan **Laravel 11** dan **Filament**. Proyek ini mencakup manajemen produk, bahan baku, transaksi, pembelian bahan baku, shift kasir, laporan penjualan, dan lebih banyak lagi. Sistem ini dirancang untuk mempermudah pengelolaan operasional sehari-hari di kafe dengan fokus pada kemudahan penggunaan dan integrasi data.

---

## 🛠 Teknologi yang Digunakan

* **Laravel 11**: Framework PHP yang powerful untuk pengembangan web.
* **Filament**: Admin panel dan dashboard yang elegan untuk Laravel.
* **MySQL**: Sistem manajemen database relasional yang digunakan untuk penyimpanan data.
* **Chart.js**: Library JavaScript untuk menampilkan grafik data penjualan secara interaktif.

---

## 📋 Fitur Utama

Sistem POS Cafe ini dilengkapi dengan berbagai fitur untuk mendukung operasional kafe Anda:

* **Manajemen Produk**:
    * Fungsionalitas CRUD (Create, Read, Update, Delete) untuk produk menu.
    * Pengelolaan kategori produk untuk organisasi yang lebih baik.
    * Pengaturan harga, stok, dan deskripsi detail untuk setiap produk.
* **Manajemen Bahan Baku**:
    * Fungsionalitas CRUD untuk bahan baku (ingredient).
    * Input komposisi bahan baku untuk setiap produk, memungkinkan pengurangan stok otomatis saat produk terjual.
    * Sistem notifikasi untuk bahan baku yang hampir kedaluwarsa.
* **Transaksi & Pembayaran**:
    * Membuat order baru yang intuitif untuk pelanggan.
    * Mengelola berbagai jenis pembayaran dan status order.
    * Opsi untuk mengirim struk via WhatsApp kepada pelanggan.
* **Shift Kasir**:
    * Fitur untuk membuka dan menutup shift kasir.
    * Rekapitulasi transaksi per shift untuk pelacakan performa.
* **Modul Pembelian (Restok)**:
    * Mengelola proses pembelian bahan baku dari supplier.
    * Penambahan stok bahan baku secara otomatis setelah pembelian.
* **Laporan & Grafik**:
    * Grafik penjualan harian dan identifikasi produk terlaris.
    * Laporan transaksi mendetail berdasarkan hari, shift, atau kasir.
* **Role & Permission**:
    * Pengaturan role pengguna yang fleksibel seperti **Admin**, **Kasir**, dan **Owner**.
    * Akses granular ke berbagai bagian sistem sesuai dengan role pengguna.

---

## 💻 Prasyarat

Sebelum memulai instalasi, pastikan Anda telah menginstal beberapa software berikut di sistem Anda:

* **PHP 8.0+**
* **Composer**
* **MySQL** atau **MariaDB**

---

## 🚀 Instalasi

Ikuti langkah-langkah di bawah ini untuk menginstal dan menjalankan aplikasi di lingkungan lokal Anda:

1.  **Clone Repository**

    Buka terminal atau command prompt Anda dan jalankan perintah berikut untuk mengkloning proyek:
    ```bash
    git clone https://github.com/Jackyandrazat/pos-cafe.git
    cd pos-cafe
    ```
    *(Ganti `username/pos-cafe.git` dengan URL repositori sebenarnya)*

2.  **Instal Dependensi**

    Setelah masuk ke direktori proyek, instal semua dependensi PHP menggunakan Composer:
    ```bash
    composer install
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

    Jika Anda ingin menambahkan data sampel (seperti user, role, dll.) untuk pengujian, jalankan seeder:
    ```bash
    php artisan db:seed
    ```

7.  **Jalankan Aplikasi**

    Untuk menjalankan aplikasi, gunakan perintah Artisan:
    ```bash
    php artisan serve
    ```
    Aplikasi Anda sekarang dapat diakses melalui browser di: `http://localhost:8000`.

---

## 🔐 Role & Akses Pengguna

Sistem ini memiliki beberapa peran pengguna dengan tingkat akses yang berbeda:

* **Admin**: Memiliki akses penuh untuk manajemen produk, bahan baku, transaksi, laporan, dan pengaturan sistem.
* **Kasir**: Dapat melakukan transaksi, membuat order, dan mengelola pembayaran.
* **Owner**: Memiliki akses penuh ke seluruh aplikasi, termasuk fitur-fitur administratif dan laporan.

---

## 🧩 Struktur Folder

Berikut adalah gambaran singkat tentang struktur folder utama dalam proyek ini:

* `app/Models`: Berisi semua model Eloquent yang digunakan dalam aplikasi untuk berinteraksi dengan database.
* `app/Filament/Resources`: Lokasi untuk semua Filament Resources yang menangani fungsionalitas CRUD dan tampilan admin panel.
* `app/Services`: Berisi *service helper* atau *business logic* yang dapat digunakan kembali, misalnya untuk pengurangan stok otomatis.
* `database/migrations`: Skrip migrasi database yang mendefinisikan struktur tabel.
* `resources/views/filament`: Berisi view Blade yang digunakan untuk kustomisasi tampilan admin panel Filament.

---

## 🚧 Fitur yang Sedang Dikembangkan

Beberapa fitur sedang dalam pengembangan untuk meningkatkan fungsionalitas sistem:

* **Export Laporan ke PDF/Excel**: Menambahkan kemampuan untuk mengekspor laporan transaksi dan pembelian ke format PDF atau Excel.

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah **MIT License**. Lihat file `LICENSE` di repository untuk detail lebih lanjut.

---

## 📞 Kontak

Jika Anda memiliki pertanyaan, saran, atau menemukan masalah, jangan ragu untuk menghubungi saya di: [jackyandrazat@gmail.com](mailto:jackyandrazat@gmail.com).

---

Terima kasih telah menggunakan **POS Cafe System**! 🚀
