# Laporan Persediaan & Waste + Manajemen Customer/Loyalty

Dokumentasi ini merangkum implementasi dua modul baru yang menambah visibilitas operasional sekaligus mendukung program loyalitas pelanggan.

## 1. Laporan Persediaan & Waste

### Komponen yang Dibuat
- **Tabel baru** `ingredient_wastes` beserta model `IngredientWaste` untuk mencatat setiap bahan terbuang (qty, alasan, shift, user).
- **Relasi & penyesuaian stok otomatis**: setiap create/update/delete waste menyesuaikan `ingredients.stock_qty` agar stok riil sinkron.
- **Filament Resource** `IngredientWasteResource` untuk CRUD waste dengan form terstruktur (bahan, qty, alasan, catatan, waktu, shift).
- **Service** `InventoryWasteReportService` yang menghitung agregasi stok masuk (purchase), pemakaian (order + komposisi), dan waste lengkap dengan biaya & persentase waste.
- **Filament Page** `InventoryWasteDashboard` + Blade view khusus yang menampilkan:
  - Filter tanggal + tombol export CSV
  - KPI cards (stok masuk, pemakaian, total waste, biaya & % waste)
  - Tabel detail per bahan: stok masuk vs penggunaan vs waste, variance, stok saat ini, biaya waste.
  - Quick action “Catat Waste” menuju form resource

### Alur Utama
1. **Pencatatan Waste**: pengguna membuka menu Waste Bahan, pilih bahan + qty + alasan. Setelah disimpan, stok bahan otomatis berkurang.
2. **Pengumpulan Data**: service menggabungkan data purchase item (stok masuk), order item + product ingredients (pemakaian), dan waste untuk rentang tanggal tertentu.
3. **Dashboard**: owner membuka Laporan Persediaan & Waste, memilih rentang waktu → KPI dan tabel terisi. Bisa ekspor CSV untuk analisis lebih lanjut.

### Skenario Penggunaan
- **Kontrol margin**: Owner memfilter 7 hari terakhir untuk melihat bahan mana yang paling banyak waste serta biaya kerugiannya.
- **Investigasi variansi stok**: Bandingkan stok masuk vs konsumsi; jika variance besar, bisa menelusuri apakah ada waste yang belum dicatat.
- **Audit shift**: Dengan mencatat shift pada waste, bisa mengevaluasi tim/shift mana yang paling sering menyebabkan waste.

## 2. Manajemen Customer / Loyalty

### Komponen yang Dibuat
- **Tabel baru** `customers`, `customer_point_transactions`, dan kolom `customer_id` pada `orders`.
- **Model** `Customer` + `CustomerPointTransaction` dengan factory untuk testing.
- **Service** `LoyaltyService` yang menghitung poin otomatis (default 1% dari nilai order), menyimpan riwayat poin, serta memperbarui lifetime value & last order date.
- **Order Form Enhancements**: pada form order (Filament) sekarang ada select Customer yang bisa mencari atau langsung buat customer baru dari modal kecil.
- **Filament Resource** `CustomerResource` lengkap dengan:
  - List view (nama, email, phone, poin, lifetime value, order terakhir, filter high value)
  - Form profil termasuk preferensi (tag)
  - Relation managers: riwayat order & ledger poin
- **Unit Test** `LoyaltyServiceTest` memastikan perhitungan poin + pembaruan metrik berjalan benar.

### Alur Utama
1. **Pembuatan Customer**: Admin bisa mendaftarkan customer manual atau langsung dari dropdown saat membuat order.
2. **Order + Poin**: Saat order baru dibuat dan customer dipilih, `LoyaltyService` otomatis menambah poin berdasarkan total order, mencatat transaksi poin, dan update lifetime value.
3. **Monitoring Customer**: Melalui CustomerResource, owner dapat melihat riwayat order dan ledger poin tiap customer, termasuk preferensi untuk kampanye targeted.

### Skenario Penggunaan
- **Program Loyalitas Sederhana**: Setiap Rp100.000 order → 1.000 poin. Owner dapat memantau top spender berdasarkan lifetime value.
- **Kampanye Personal**: Filter customer dengan preferensi tertentu (misal “less sugar”) lalu export/email manual menggunakan data preferensi.
- **Pengaduan atau Kompensasi**: Jika ada komplain, admin bisa menambah poin manual lewat ledger atau membuat order dengan redeem poin (siap dikembangkan karena ada ledger dan saldo poin).

## Catatan Implementasi
- **Migrasi**: jalankan `php artisan migrate` untuk membuat tabel/tambah kolom baru.
- **Pengujian**: seluruh suite `php artisan test` memastikan modul waste + loyalty berjalan stabil.
- **Ekspor**: Dashboard persediaan mendukung ekspor CSV; CustomerResource dapat diekspor lewat action standar Filament bila dibutuhkan.

Dengan kombinasi dua modul ini, owner memiliki visibilitas terhadap margin bahan serta alat untuk mempertahankan customer melalui program poin sederhana.
