# Panduan Setup Payment Gateway

Dokumen ini menjelaskan cara mengaktifkan integrasi dengan payment gateway populer untuk POS Cafe.

---

## Mode Pembayaran

Atur `PAYMENT_MODE` di file `.env`:

| Mode | Keterangan |
|------|-----------|
| `manual` | **(Default)** Kasir konfirmasi pembayaran secara langsung. Tidak perlu integrasi API. |
| `midtrans` | Otomatis via notifikasi Midtrans webhook. |
| `doku` | Otomatis via notifikasi DOKU webhook. |
| `sandbox` | Untuk testing — generate payload simulasi. |

---

## Mode Manual (Default)

Mode ini tidak memerlukan integrasi API. Semua konfirmasi dilakukan oleh kasir.

### Setup QRIS Statis

1. Dapatkan string QRIS EMVCo dari stiker/cetakan merchant Anda (biasanya dari bank atau PJSP)
2. Isi di `.env`:
   ```
   QRIS_STATIC_STRING=00020101021126...
   ```
3. String ini akan digunakan oleh **QRIS Generator** di menu Pembayaran untuk membuat QRIS dinamis per transaksi.

### Setup Virtual Account Manual

Isi nomor rekening merchant Anda:
```env
VA_BCA=1234567890
VA_MANDIRI=0987654321
VA_BNI=1122334455
VA_BRI=5544332211
```

### Setup E-Wallet Manual

Isi nomor HP yang terdaftar di setiap e-wallet:
```env
EWALLET_GOPAY_PHONE=081234567890
EWALLET_OVO_PHONE=081234567890
EWALLET_DANA_PHONE=081234567890
```

### Alur Kasir (Mode Manual)

```
1. Kasir buat pembayaran (pilih metode + jumlah)
2. Sistem simpan payment dengan status "Menunggu" (pending)
3. Kasir tampilkan instruksi ke pelanggan (nomor VA / QR / nomor e-wallet)
4. Pelanggan bayar
5. Kasir verifikasi pembayaran masuk
6. Kasir klik tombol "✅ Konfirmasi" di tabel Pembayaran
7. Status berubah ke "Berhasil" → order diproses → stok berkurang
```

---

## Mode Midtrans

### Langkah Setup

1. **Daftar akun Midtrans**: https://dashboard.midtrans.com
2. **Aktifkan metode pembayaran** yang diinginkan di dashboard (QRIS, VA, GoPay, dll)
3. **Ambil kredensial** dari menu: Settings → Access Keys
4. **Isi di `.env`**:
   ```env
   PAYMENT_MODE=midtrans
   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxxxxxxxxx
   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxxxxxxxxx
   MIDTRANS_MERCHANT_ID=Gxxxxxxxx
   MIDTRANS_IS_PRODUCTION=false
   ```
5. **Setup Webhook URL** di Midtrans Dashboard:
   - Buka: Settings → Configuration → Payment → Notification URL
   - Isi: `https://yourdomain.com/api/v1/webhooks/midtrans`
   - Pastikan server Anda dapat diakses dari internet (gunakan ngrok untuk development)
6. **Implementasi Gateway**: Buka [`MidtransGateway.php`](../app/Services/Payments/MidtransGateway.php) dan ikuti petunjuk di komentar `// TODO`

### Implementasi createCharge()

```php
// Contoh untuk QRIS via Midtrans Charge API
$response = Http::withBasicAuth($this->serverKey, '')
    ->post('https://api.sandbox.midtrans.com/v2/charge', [
        'payment_type' => 'qris',
        'transaction_details' => [
            'order_id'    => $reference,
            'gross_amount' => (int) $amount,
        ],
        'qris' => [
            'acquirer' => 'gopay',
        ],
    ]);

$result = $response->json();
// $result['qr_string'] berisi QR code yang perlu ditampilkan
```

---

## Mode DOKU

### Langkah Setup

1. **Daftar akun DOKU**: https://dashboard.doku.com
2. **Ambil kredensial** dari: My Account → Integration
3. **Isi di `.env`**:
   ```env
   PAYMENT_MODE=doku
   DOKU_CLIENT_ID=MCN-xxxxxxxx
   DOKU_SECRET_KEY=SK-xxxxxxxxxxxxxxxxxxxxxxxx
   DOKU_IS_PRODUCTION=false
   ```
4. **Setup Webhook** di DOKU Dashboard:
   - Configuration → Notification URL: `https://yourdomain.com/api/v1/webhooks/doku`
5. **Implementasi Gateway**: Buka [`DokuGateway.php`](../app/Services/Payments/DokuGateway.php) dan ikuti petunjuk di komentar `// TODO`
6. **Referensi API**: https://jokul.doku.com/docs

---

## Arsitektur Kode

```
config/payment.php              ← konfigurasi mode dan kredensial
app/Services/Payments/
  ├── PaymentGatewayInterface.php  ← kontrak yang harus diimplementasikan gateway
  ├── PaymentGatewayManager.php    ← resolver driver berdasarkan config
  ├── PaymentService.php           ← logika bisnis pembayaran (process, confirm, webhook)
  ├── ManualGateway.php            ← implementasi mode manual
  ├── MidtransGateway.php          ← skeleton Midtrans (TODO: lengkapi)
  └── DokuGateway.php              ← skeleton DOKU (TODO: lengkapi)

app/Http/Controllers/Api/V1/
  ├── PaymentController.php        ← CRUD + confirm
  └── PaymentWebhookController.php ← terima notifikasi gateway

routes/api.php
  ├── POST /orders/{order}/payments         ← buat pembayaran
  ├── PATCH /orders/{order}/payments/{id}/confirm  ← kasir konfirmasi
  ├── POST /webhooks/midtrans               ← webhook Midtrans
  └── POST /webhooks/doku                  ← webhook DOKU
```

---

## Menambahkan Gateway Baru

Untuk menambahkan gateway payment baru (misal: Xendit, Nicepay):

1. Buat class baru: `app/Services/Payments/XenditGateway.php`
2. Implement `PaymentGatewayInterface`
3. Daftarkan di `PaymentGatewayManager.php`:
   ```php
   'xendit' => new XenditGateway(),
   ```
4. Tambahkan konfigurasi di `config/payment.php` dan `.env.example`
5. Tambahkan webhook route di `routes/api.php` jika diperlukan
