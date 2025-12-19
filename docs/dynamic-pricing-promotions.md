# Dynamic Pricing & Promotions Engine

Dokumen ini menjelaskan perluasan modul promo untuk mendukung jadwal fleksibel / happy hour tanpa infrastruktur baru.

## Perubahan Teknis

1. **Database** – Migrasi `2025_12_19_000001_create_promotions_tables.php` ditambahkan kolom:
   - `schedule_days` (JSON): daftar hari (1=Senin ... 7=Minggu).
   - `schedule_start_time` & `schedule_end_time` (time): jendela jam promo, mendukung rentang melewati tengah malam.

2. **Model** `Promotion`:
   - Menandai field baru sebagai `fillable` & `casts`.
   - `isCurrentlyValid()` sekarang mengecek hari & jam; jika start > end dianggap overnight (contoh 21:00–02:00).

3. **Form Admin (Filament)** `PromotionResource`:
   - Seksi “Jadwal Dinamis” dengan CheckboxList hari dan dua TimePicker.
   - Helper text menjelaskan perilaku opsional & overnight window.

4. **Service** `PromotionService`:
   - Pesan error diganti menjadi “Promo belum aktif atau berada di luar jam yang ditentukan.” sehingga kasir tahu alasan penolakan kode.

5. **Testing** `PromotionServiceTest`:
   - Test `promotion_respects_schedule_days_and_time_window` memverifikasi promo hanya berlaku di hari/jam yang ditentukan dan menolak di luar window.

## Alur Penggunaan

1. **Marketing / Admin** membuat promo baru di Filament:
   - Tentukan info standard (nama, kode, tipe, diskon, minimum, kuota).
   - Aktifkan jadwal dinamis bila ingin promo hanya muncul di hari/jam tertentu (mis. Senin–Jumat 14:00–17:00).

2. **Kasir** memasukkan kode promo saat membuat order:
   - `PromotionService` otomatis menormalisasi kode, mengecek status global, minimum subtotal, kuota pengguna, lalu mengevaluasi jadwal dinamis.
   - Jika berada di luar jadwal, kasir menerima pesan error baru tanpa perlu tracking manual.

3. **Penjualan** tercatat seperti biasa:
   - Order yang valid menyimpan `promotion_id` & `promotion_discount` dan tercatat di `promotion_usages` via `syncUsage()`.

## Contoh Skenario

| Skenario | Konfigurasi | Hasil |
| --- | --- | --- |
| Happy Hour Weekday | Day: 1-5, Jam: 13:00–15:00 | Order Jumat 14:00 diterima, Jumat 16:00 ditolak. |
| Late Night Promo | Day: 5-7, Jam: 21:00–02:00 | Order Sabtu 23:00 diterima. Order Sabtu 03:00 (masih Sabtu) ditolak karena di luar window. |
| Tanpa Jadwal | Field kosong | Promo berlaku 24/7 selama tanggal aktif & kondisi lain terpenuhi. |

## Catatan

- Jadwal dinamis bersifat opsional; jika tidak diisi sistem bertingkah seperti sebelumnya.
- Overnight window dihitung dengan perbandingan string HH:MM:SS, cukup untuk kebutuhan happy hour sederhana tanpa konversi timezone tambahan (aplikasi menggunakan `Asia/Jakarta`).
- Penambahan ini tidak memerlukan job scheduler atau struktur baru sehingga marketing bisa membuat campaign cepat langsung dari dashboard.
