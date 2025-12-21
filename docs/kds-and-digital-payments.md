# Kitchen Display System & Digital Payment Integrations

Dokumen ini menjelaskan cara kerja KDS (Kitchen Display System) serta integrasi pembayaran non-tunai (QRIS / e-wallet) yang baru ditambahkan.

## Kitchen Display System (KDS)

- **Lokasi**: Filament page `KitchenDisplay` (menu Operasional). Dapat dinonaktifkan melalui feature toggle `kitchen_display`.
- **Data sumber**: order dengan status `pending`, `confirmed`, `preparing`, `ready`, `completed` termasuk item detail & meja.
- **Fitur utama**:
  - Filter status (Aktif/Siap/Selesai) + auto refresh 15 detik.
  - Tombol aksi “Mulai Masak”, “Tandai Siap”, “Selesai” yang langsung mengubah status order & mencatat log.
  - Realtime notifikasi Filament setelah update status.
- **Implementasi teknis**: `app/Filament/Pages/KitchenDisplay.php` memuat data via Livewire, view berada di `resources/views/filament/pages/kitchen-display.blade.php`.
- **Best practice**: batasi akses ke role dapur (atur via Filament auth/policy) dan pasangkan dengan modul antrean/meja bila butuh info lokasi.

## Integrasi Pembayaran Digital (QRIS / E-Wallet)

- **Schema Update**: Kolom baru pada tabel `payments`: `provider`, `external_reference`, `status`, `meta` (JSON), `paid_at`.
- **Gateway Manager**: `App\Services\Payments\PaymentGatewayManager` + `QrisGateway` membuat sandbox charge dengan reference dan instruksi QR.
- **API Flow**:
  - Endpoint `POST /api/v1/orders/{order}/payments` menerima `payment_method` (`cash`, `transfer`, `qris`, `ewallet`) dan `amount`.
  - Untuk QRIS/e-wallet, sistem menyimpan status `pending`, payload QR string, deeplink, dan masa berlaku di kolom `meta`.
  - Cash/transfer langsung dicatat sebagai `captured`; jika lunas, order otomatis `completed`.
- **Filament UI**:
  - Payment form sekarang punya field provider, reference, status, meta, timestamp.
  - Tabel menampilkan provider/status/reference serta action modal “Instruksi” yang menampilkan QR/deeplink.
  - WhatsApp receipt kini menggunakan relasi `order->items` sehingga daftar item akurat.
- **Testing**: `tests/Feature/Api/PaymentGatewayTest` memastikan alur QRIS pending charge berjalan.

## Langkah Penggunaan

1. Jalankan `php artisan migrate` untuk menambahkan kolom payment baru.
2. Pastikan feature toggle `kitchen_display` ON jika ingin menampilkan dashboard dapur.
3. Untuk outlet yang memakai QRIS/e-wallet nyata, gantikan `QrisGateway` dengan implementasi provider sesungguhnya (endpoint base URL, signature, dsb.).
4. Konfigurasikan role/akses Filament agar tim dapur hanya melihat KDS, sementara kasir dapat mengelola pembayaran.

Dengan kedua modul ini, tim dapur mendapat visual order real-time dan kasir dapat menerima pembayaran digital dengan status terpantau langsung di sistem POS.
