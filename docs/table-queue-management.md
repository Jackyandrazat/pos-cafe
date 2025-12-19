# Table & Queue Management Dashboard

Dokumentasi singkat atas implementasi integrasi Floor/Table management dan antrean pelanggan pada POS Café.

## Komponen yang Ditambahkan

1. **Migrasi Struktur** `2025_12_19_150000_add_table_management_support.php`
   - Menambahkan kolom `capacity`, `x_position`, `y_position`, `notes` pada tabel `tables`.
   - Menambahkan status tambahan `cleaning` pada enum status meja (untuk siklus selesai order → bersih).
   - Membuat tabel baru `table_queue_entries` untuk menyimpan data tamu waiting list beserta meja yang ditugaskan.

2. **Model & Relasi**
   - `TableQueueEntry` model baru dengan relasi ke `CafeTable`.
   - `CafeTable` kini memiliki relasi `queueEntries` dan cast untuk field baru.
   - `Order` meng-override `booted()` untuk otomatis mengubah status meja saat order dine-in dibuat/completed/cancelled.

3. **Filament Resources & Page**
   - `TableQueueEntryResource` (CRUD antrean lengkap dengan status, estimasi, catatan).
   - Update `TableResource` agar form/table mendukung kapasitas, status cleaning, posisi, catatan.
   - Halaman kustom `TableStatusBoard` + Blade `table-status-board.blade.php` sebagai dashboard floor plan + antrean.

4. **Testing**
   - `TableStatusTest` memastikan status meja mengikuti lifecycle order dine-in (open → occupied, completed → cleaning).

## Alur Operasional

1. **Kasir/PIC menambahkan antrean** melalui menu Antrean Pelanggan:
   - Input nama tamu, jumlah orang, kontak opsional.
   - Sistem mencatat `check_in_at` otomatis.

2. **Monitoring di Table Status Board**:
   - Pilih Area untuk melihat seluruh meja beserta status (warna-coded).
   - Aksi cepat (Available, Reserved, Occupied, Cleaning) langsung mengubah status di DB.
   - Panel antrean menampilkan tamu waiting dengan dropdown untuk memilih meja available.

3. **Seating Flow**:
   - Petugas pilih meja dari dropdown lalu klik `Seat` → antrean berubah `seated`, `assigned_table_id` terisi, `seated_at` tercatat, status meja otomatis `occupied`.
   - Opsi `Call` untuk memberi tanda tamu sedang dipanggil, `Cancel` jika tamu batal.

4. **Order Lifecycle**:
   - Saat order dine-in dibuat dengan `table_id` terisi, hook `Order::created` men-set meja **occupied**.
   - Ketika status order berubah `completed`, meja otomatis **cleaning**; jika `cancelled`, kembali **available**.

## Skenario Contoh

1. **Peak Hour**: Kasir menginput tamu waiting list. Begitu meja kosong (status di dashboard berubah available), kasir assign tamu → klik Seat → order dine-in dibuat → status meja occupied.
2. **After Service**: Waiter menandai order `completed`, otomatis meja masuk status cleaning sampai cleaning done; kasir bisa manual set ke available lagi setelah meja siap.
3. **Antrian Penuh**: Owner melihat daftar queue, dapat memperkirakan estimasi tunggu (field `estimated_wait_minutes`) dan memutuskan membuka area tambahan jika perlu.

## Catatan

- Dashboard tidak bergantung hardware tambahan; cukup menggunakan Filament Page.
- Jika ingin floor plan visual (drag-drop), field `x_position` & `y_position` siap dipakai untuk rendering grid/canvas.
- Pencataan queue dan status meja sudah terintegrasi dengan order sehingga laporan penjualan tetap konsisten.
