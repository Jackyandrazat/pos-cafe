# LakuPOS — Cloud-Native Cafe Point of Sale & Self-Order Ecosystem

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-F59E0B?style=for-the-badge&logo=filament&logoColor=white)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![React](https://img.shields.io/badge/React-18.x-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://react.dev)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com)
[![Database](https://img.shields.io/badge/TiDB%20Cloud-MySQL-00758F?style=for-the-badge&logo=mysql&logoColor=white)](https://tidbcloud.com)

**LakuPOS** adalah ekosistem Point of Sale (POS) dan Self-Order modern berbasis cloud yang dirancang khusus untuk industri Food & Beverage (F&B / Kafe & Restoran). Sistem ini memadukan **Backoffice & Kasir Admin (Laravel 11 + Filament v3)** dengan **Customer Self-Order Web PWA (React 18 + Vite)** yang saling terintegrasi secara real-time.

---

## 🌐 Live Infrastructure & Deployment

| Komponen | Platform | Region | Status | URL Akses |
|---|---|---|---|---|
| **Backend & KDS Panel** | Render Cloud (Docker) | Singapore / US | 🟢 Live | `https://pos-cafe-f38k.onrender.com` |
| **Self-Order Hub PWA** | Vercel | Global CDN | 🟢 Live | `https://github.com/Jackyandrazat/cafe-order-hub` |
| **Primary Database** | TiDB Cloud Serverless (MySQL) | Singapore (`ap-southeast-1`) | 🟢 Connected | Low Latency (<25ms) |

---

## 🏛️ Arsitektur Ekosistem

```
                                  ┌────────────────────────┐
                                  │   Pelanggan di Meja    │
                                  │  (Smartphone / Browser)│
                                  └───────────┬────────────┘
                                              │ Scan QR Meja
                                              ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                    Customer Self-Order Hub (cafe-order-hub)                 │
│  - React 18 + Vite + TypeScript + Tailwind CSS + shadcn/ui                 │
│  - Geofencing GPS Verification (Anti-Fraud Luar Area Kafe)                  │
│  - Guest Session Lifecycle (Auto-Expire 4 Jam, Escape Hatch Reset Tamu)     │
│  - Bottom Sheet Customization (Ukuran, Topping, Ice/Sugar Level)            │
│  - Real-Time Order Tracking & Digital Sound Chime saat Pesanan Siap        │
│  - Integrasi Digital Payments (QRIS, Midtrans, Xendit, Bayar Kasir)         │
└─────────────────────────────────────┬───────────────────────────────────────┘
                                      │ REST API / Bearer Token
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                    Backend Core & Backoffice (pos-cafe)                     │
│  - Laravel 11 + Filament v3 + Livewire + Alpine.js                          │
│  - Dockerized Alpine Linux + PHP 8.3-FPM + Nginx + OPcache + Auto DB Sync   │
│  - Robust API Controllers with Idempotency Key & Transaction Guards         │
│                                                                             │
│  ┌───────────────────────┐ ┌───────────────────────┐ ┌───────────────────┐  │
│  │   Kitchen Display     │ │   Kasir & Transaksi   │ │  Inventori Resep  │  │
│  │   (KDS Realtime)      │ │   (POS Dine-in/ToGo)  │ │  & Waste Tracking │  │
│  └───────────────────────┘ └───────────────────────┘ └───────────────────┘  │
│  ┌───────────────────────┐ ┌───────────────────────┐ ┌───────────────────┐  │
│  │   Dynamic Pricing &   │ │   Gamified Loyalty &  │ │  Payment Gateway  │  │
│  │   Promo Engine (BOGO) │ │   Tiering Challenge   │ │  Webhook Idempot. │  │
│  └───────────────────────┘ └───────────────────────┘ └───────────────────┘  │
└─────────────────────────────────────┬───────────────────────────────────────┘
                                      │
                                      ▼
                       ┌─────────────────────────────┐
                       │  TiDB Cloud Serverless DB   │
                       │  (MySQL Compatible, SG)     │
                       └─────────────────────────────┘
```

---

## 🚀 Fitur Utama & Keunggulan Bisnis

### 1. Kitchen Display System (KDS) & Audio Chime
- Tampilan interaktif khusus barista dan koki dapur untuk memantau tiket pesanan masuk secara real-time.
- **Strict State Machine:** Transisi status pesanan (`submitted` ➔ `confirmed` ➔ `preparing` ➔ `ready` ➔ `completed`) divalidasi ketat sehingga status tidak dapat melompat secara keliru.
- **Digital Sound Chime:** Web Audio API membunyikan nada digital di smartphone pelanggan tepat saat barista menandai pesanan menjadi `ready`.

### 2. QR Code Meja & Anti-Fraud Geofencing (GPS)
- Setiap meja kafe memiliki QR Code unik yang langsung membuka halaman pesanan dengan nomor meja terikat otomatis.
- **Geofence Protection:** Menggunakan formula *Haversine* untuk menghitung jarak akurat pembeli ke koordinat kafe.
- **Dynamic Payment Gating:** Jika pembeli berada di luar area kafe (misal orang jahil mencoba order dari luar kota), opsi **Bayar Tunai di Kasir otomatis dinonaktifkan**, mewajibkan pembayaran digital (QRIS/E-Wallet/Transfer) di muka agar tidak merugikan dapur.

### 3. Siklus Hidup Sesi Tamu (Guest Session Lifecycle)
- **4-Hour TTL Auto-Expiration:** Sesi tamu non-member otomatis kadaluarsa setelah 4 jam tanpa aktivitas untuk menjaga higienitas perangkat dan keamanan data.
- **Escape Hatch saat Scan Ulang:** Pelanggan baru yang men-scan QR meja pada HP yang masih menyimpan sesi tamu sebelumnya dapat langsung menekan tombol *"Bukan [Nama]? Masuk sebagai Tamu Baru"* tanpa perlu clear cache browser secara manual.
- **Selesai & Keluar Sesi:** Tombol konfirmasi penyelesaian sesi pada halaman pesanan selesai (`completed`) untuk mengakhiri kunjungan dan membersihkan keranjang.

### 4. Dynamic Pricing & Promosi Fleksibel
- **BOGO (Buy One Get One):** Beli produk X dapat produk Y gratis.
- **Happy Hour:** Diskon persentase atau potongan nominal otomatis pada jam dan hari tertentu.
- **Min Spend Promo:** Potongan harga otomatis saat pesanan mencapai nilai minimum transaksi.
- **Product Bundling:** Paket kombo makanan + minuman dengan harga spesial.

### 5. Gamified Loyalty Program & Member Tiers
- Sistem tingkatan member bertingkat: **Bronze**, **Silver**, **Gold**, dan **Platinum**.
- Multiplier perolehan poin belanja sesuai level member.
- **Gamification Challenges:** Misi berhadiah poin (misal: "Beli 5 Kopi Minggu Ini", "Weekend Spender").
- Guard deduplikasi transaksi untuk menjamin poin hanya dikreditkan 1 kali saat pesanan berstatus `completed`.

### 6. Multi-Payment Gateway dengan Idempotency Guard
- Mendukung berbagai kanal pembayaran:
  - **Bayar di Kasir (Cash / Tunai)**
  - **QRIS Statis / Manual Upload Bukti**
  - **Transfer Virtual Account**
  - **Payment Gateway Midtrans (Snap/Core API)**
  - **Payment Gateway Xendit (Invoice/E-Wallet)**
- Dilengkapi `idempotency_key` dan database row-locking (`lockForUpdate`) untuk mencegah duplikasi pemrosesan webhook pembayaran.

### 7. Manajemen Bahan Baku, Resep Topping & Waste Tracking
- Komposisi bahan baku otomatis berkurang saat menu terjual.
- **Pivot Topping Ingredients:** Konsumsi bahan baku untuk tambahan topping (misal: extra shot espresso, oat milk) dipotong secara akurat melalui tabel relasi `topping_ingredients`.
- **Atomic Stock Check:** Mencegah stok menjadi minus dengan conditional decrement (`WHERE stock_qty >= needed`).
- **Waste Management:** Pencatatan bahan baku yang tumpah, basi, atau kadaluarsa lengkap dengan alasan (*spoilage, expired, damage*) dan pelacakan cost kerugian.

### 8. Pembersihan Otomatis Pesanan Terbengkalai (Order Auto-Expiration)
- Perintah Artisan otomatis:
  ```bash
  php artisan orders:expire-pending --minutes=15
  ```
- Pesanan online yang tidak dibayar kasir dalam batas waktu wajar otomatis dibatalkan dan kuota stok bahan bakunya dikembalikan seketika.

### 9. Modern UI: Smart Responsive Sidebar
- Sidebar Filament responsif yang dapat di-**Pin (📌)** agar tetap terbuka atau di-**Unpin** agar menutup otomatis saat navigasi.
- **Ribbon Tab:** Tombol tab melayang di tepi layar untuk membuka/menutup sidebar dengan 1 klik.
- **Keyboard Shortcut:** Tekan tombol `[` pada keyboard untuk toggle sidebar secara instan.

### 10. Kustomisasi Struk Thermal & WhatsApp
- Pengaturan logo kafe, header, catatan kaki, nomor WhatsApp kasir, dan integrasi tombol *"Kirim Struk via WhatsApp"* langsung ke nomor pelanggan.

---

## 💻 Panduan Instalasi Lokal

### Prasyarat
- PHP 8.2 atau 8.3 (ekstensi: `pdo_mysql`, `bcmath`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`)
- Composer 2.x
- Node.js 18+ & npm
- MySQL / MariaDB

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/Jackyandrazat/pos-cafe.git
   cd pos-cafe
   ```

2. **Instal Dependensi PHP & Frontend**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Konfigurasi Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Sesuaikan parameter database dan gateway pada file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pos_cafe
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Jalankan Migrasi & Database Seeder**
   ```bash
   php artisan migrate --seed
   ```

5. **Symlink Storage Media**
   ```bash
   php artisan storage:link
   ```

6. **Jalankan Server Lokal**
   ```bash
   php artisan serve
   ```
   Akses panel admin di: `http://127.0.0.1:8000/admin`

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

Semua fungsionalitas inti telah dilengkapi dengan Feature Tests:

```bash
# Menjalankan seluruh test suite
php artisan test

# Menjalankan test spesifik
php artisan test tests/Feature/Api/StoreConfigTest.php
php artisan test tests/Feature/KitchenDisplayTest.php
php artisan test tests/Feature/ReceiptSettingsTest.php
```

---

## 📂 Struktur Direktori Utama

```
pos-cafe/
├── app/
│   ├── Console/Commands/       # Cron jobs (orders:expire-pending)
│   ├── Filament/               # Resources, Pages (KDS, Settings, Shift)
│   ├── Http/Controllers/Api/   # REST API V1 Controllers
│   ├── Models/                 # Eloquent Models & Relationships
│   ├── Observers/              # Model Observers (OrderObserver, Stock)
│   └── Services/               # Business Logic Services (Payments, Stock, Loyalty)
├── database/
│   ├── migrations/             # Database Schema Migrations
│   └── seeders/                # Sample Data Seeders
├── docker/                     # Dockerfile, entrypoint.sh, php.ini, nginx
├── docs/                       # Dokumentasi Teknis & UAT Checklist
└── resources/
    ├── css/                    # Custom Styling & Filament Overrides
    └── js/                     # Smart Sidebar & Client Scripts
```

---

## 📄 Lisensi
Didistribusikan di bawah Lisensi **MIT**. Hak Cipta © 2026 LakuPOS by kodeeweb.
