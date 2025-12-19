# Feature Toggle & Modular Module Activation

Panduan ini menjelaskan arsitektur feature toggle baru dan cara menambahkan modul tambahan agar dapat diaktif/nonaktifkan sesuai kebutuhan rollout.

## Komponen Utama

1. **Konfigurasi**: `config/features.php` berisi daftar modul (`modules`) dengan `label`, `description`, dan `default` state.
2. **Penyimpanan Status**: Tabel `feature_flags` menyimpan state aktual (override dari default). Bila baris tidak ada, sistem memakai nilai bawaan.
3. **Helper**: `App\Support\Feature` menyediakan API `Feature::enabled('key')`, `Feature::set('key', bool)`, dan caching.
4. **Console Command**: `php artisan feature:toggle {key} --enable|--disable` untuk mengubah state via CLI.
5. **Admin UI**: Halaman Filament “Feature Toggle” memungkinkan super-admin menyalakan/mematikan modul lewat antarmuka.
6. **Integrasi Aplikasi**: Semua resource, service, page, hook, dan form memakai `Feature::enabled()` untuk menentukan apakah logika/komponen harus berjalan.

## Alur Aktivasi Modul

1. **Default State** – Saat deploy, sistem membaca daftar modul dari `config/features.php`. Jika tabel `feature_flags` kosong, status default digunakan.
2. **Override** – Admin membuka halaman Feature Toggle atau menjalankan command CLI untuk mengubah status; nilai tersimpan di tabel dan cache dibersihkan.
3. **Guarding** – Setiap modul (Promo, Gift Card, Table Management, Inventory Waste, Loyalty) memeriksa helper:
   - Resource Filament (`shouldRegisterNavigation`, `canViewAny`, dst)
   - Page `mount()` (abort 403 bila nonaktif)
   - Domain services (`PromotionService`, `GiftCardService`, order hooks, dsb.)
4. **Result** – Jika modul OFF, UI elemen hilang, service tidak dieksekusi, dan data lama tetap aman (kolom tetap ada namun tidak dipakai).

## Menambahkan Modul Baru ke Feature Toggle

1. **Konfigurasi**
   - Tambahkan entri baru di `config/features.php` → `modules` array:
     ```php
     'kds' => [
         'label' => 'Kitchen Display System',
         'description' => 'Dashboard produksi dapur',
         'default' => false,
     ],
     ```
2. **Guard Helper**
   - Import `App\Support\Feature` pada resource/page/service terkait.
   - Bungkus logika dengan `if (Feature::enabled('kds')) { ... }` atau `->visible(fn () => Feature::enabled('kds'))` untuk form/table column.
   - Untuk resource Filament, override `shouldRegisterNavigation`, `canViewAny`, dll. agar benar-benar hilang dari menu.
3. **Routes / Service Providers**
   - Jika modul punya route khusus, registrasikan bersyarat (misal di service provider: `if (Feature::enabled('kds')) { Route::middleware(...)->group(...); }`).
4. **Database / Jobs (Opsional)**
   - Schema tetap terpasang; modul OFF berarti UI/service tidak menyentuhnya.
   - Untuk job/antrian modul, periksa feature flag sebelum dispatch.
5. **UI Toggle**
   - Tidak perlu perubahan di halaman Feature Toggle; field otomatis muncul karena dibangkitkan dari `config/features.php`.
6. **Testing**
   - Tambahkan test ON/OFF scenario untuk memastikan modul tidak bocor saat dimatikan.

## Operasional

- **Melihat Status**: `php artisan feature:toggle gift_cards` (tanpa flag) akan menampilkan state saat ini.
- **Mengaktifkan**: `php artisan feature:toggle table_management --enable` atau lewat halaman Feature Toggle.
- **Menonaktifkan**: `php artisan feature:toggle promotions --disable` → otomatis menghapus cache.
- **Fallback**: Jika tabel `feature_flags` belum dimigrasikan, helper otomatis memakai default sehingga aplikasi tetap berjalan.

Dengan mekanisme ini setiap modul dapat dipaketkan reuse antar outlet/klien tanpa menghapus kode, cukup mengontrol flip sesuai kebutuhan proyek.
