# Dokumentasi Fitur pada Branch `feat/feature-image-spatie`

## Deskripsi Branch
Branch ini menambahkan fitur untuk mengelola gambar produk menggunakan library Spatie Laravel Media Library. Fitur ini memungkinkan pengguna untuk mengunggah, menyimpan, dan menampilkan gambar produk dengan berbagai ukuran dan format.

## Fitur yang Ditambahkan

### 1. **Integrasi Spatie Laravel Media Library**
   - Menambahkan dependensi `spatie/laravel-medialibrary` dan `filament/spatie-laravel-media-library-plugin` pada file `composer.json`.
   - Konfigurasi Media Library pada file `config/media-library.php`.

### 2. **Model `Product`**
   - Implementasi interface `HasMedia` pada model `Product`.
   - Penambahan fungsi `registerMediaCollections` untuk mendefinisikan koleksi media.
   - Penambahan fungsi `registerMediaConversions` untuk membuat thumbnail gambar.

### 3. **Resource `ProductResource`**
   - Penambahan komponen `SpatieMediaLibraryFileUpload` pada form untuk mengunggah gambar produk.
   - Penambahan kolom `SpatieMediaLibraryImageColumn` pada tabel untuk menampilkan gambar produk.

### 4. **Migrasi Database**
   - Menambahkan migrasi untuk tabel `media` yang digunakan oleh Spatie Media Library.

### 5. **Blade View**
   - Menambahkan beberapa file Blade untuk menampilkan gambar:
     - `image.blade.php`
     - `placeholderSvg.blade.php`
     - `responsiveImage.blade.php`
     - `responsiveImageWithPlaceholder.blade.php`

## File yang Diubah/Ditambahkan

### File yang Diubah
1. `app/Filament/Resources/ProductResource.php`
2. `app/Models/Product.php`
3. `composer.json`
4. `composer.lock`

### File yang Ditambahkan
1. `config/media-library.php`
2. `database/migrations/2026_05_11_072748_create_media_table.php`
3. `resources/views/vendor/media-library/image.blade.php`
4. `resources/views/vendor/media-library/placeholderSvg.blade.php`
5. `resources/views/vendor/media-library/responsiveImage.blade.php`
6. `resources/views/vendor/media-library/responsiveImageWithPlaceholder.blade.php`

## Cara Menggunakan

1. **Unggah Gambar Produk**
   - Pada halaman form produk, pengguna dapat mengunggah gambar melalui komponen unggah gambar yang telah disediakan.

2. **Tampilkan Gambar Produk**
   - Gambar produk akan ditampilkan pada tabel produk menggunakan kolom gambar.

3. **Konfigurasi Tambahan**
   - Konfigurasi tambahan dapat dilakukan pada file `config/media-library.php` untuk mengatur disk penyimpanan, ukuran file maksimum, dan lainnya.

## Catatan
- Pastikan untuk menjalankan perintah migrasi database:
  ```bash
  php artisan migrate
  ```
- Pastikan dependensi telah terpasang dengan menjalankan perintah:
  ```bash
  composer install
  ```
