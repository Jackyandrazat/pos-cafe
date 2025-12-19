## Gift Card & Corporate Account Support

Dokumentasi singkat modul gift card / corporate balance.

### Struktur Baru

- `gift_cards` table: kode, tipe (gift card / corporate), status, nilai awal, saldo, masa berlaku, info penerima/perusahaan.
- `gift_card_transactions` table: log issue/reload/redeem/refund/adjustment beserta saldo setelah transaksi.
- Kolom tambahan di `orders`: `gift_card_id`, `gift_card_code`, `gift_card_amount` untuk mencatat pemakaian.

### Komponen Aplikasi

- **Model & Service**: `GiftCard`, `GiftCardTransaction`, `GiftCardService`, dan `GiftCardException` untuk validasi saldo & error handling.
- **Filament Resource**: menu "Gift Card & Corporate" (grup Marketing) dengan form penerbitan, status, expiry, detail penerima/korporat, serta aksi "Reload Saldo" plus riwayat transaksi.
- **Order Workflow**: form order kini punya input kode gift card & nominal. Setelah diskon manual + promo dihitung, `GiftCardService` mengecek saldo dan menyimpan referensi order.

### Alur Redeem

1. Kasir mengisi kode & nominal gift card saat membuat order.
2. Sistem memvalidasi: kode aktif, belum kadaluarsa, saldo cukup, nominal tidak melebihi tagihan sisa.
3. Order tersimpan dengan `gift_card_id` dan total bayar langsung dikurangi nominal tersebut.
4. `GiftCardService` memotong saldo dan menulis transaksi `redeem` (dengan referensi order) agar audit trail jelas.
5. Jika terjadi race condition (saldo habis), sistem membatalkan relasi gift card pada order dan memberi notifikasi.

### Pengelolaan Saldo

- Admin dapat reload saldo kartu kapan pun via action Filament; transaksi `reload` otomatis tercatat.
- Status akan berubah menjadi `exhausted` jika saldo mencapai nol, dan kembali `active` setelah reload.
- Field expiry dicek setiap kali redeem; kartu kadaluarsa tidak dapat digunakan.

### Manfaat Bisnis

- Memungkinkan penjualan bundel gift card tanpa integrasi pembayaran baru.
- Mendukung kerjasama korporat (saldo bersama untuk kantor) dengan pelacakan saldo real-time.
- Laporan order kini menyertakan kode & nominal gift card sehingga mudah menghitung liability outstanding.