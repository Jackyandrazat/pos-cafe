<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mode Pembayaran
    |--------------------------------------------------------------------------
    |
    | Pilih mode operasi pembayaran:
    |
    |  "manual"    → Kasir yang konfirmasi secara langsung.
    |                QRIS Statis, E-Wallet manual, Virtual Account manual, Cash.
    |                Tidak memerlukan integrasi API ke pihak ketiga.
    |
    |  "midtrans"  → Midtrans Payment Gateway (QRIS Dinamis, VA, E-Wallet).
    |                Konfirmasi otomatis via webhook dari Midtrans.
    |
    |  "doku"      → DOKU Payment Gateway (QRIS, VA, E-Wallet).
    |                Konfirmasi otomatis via webhook dari DOKU.
    |
    | Lihat docs/payment-gateway-setup.md untuk panduan setup gateway.
    |
    */
    'mode' => env('PAYMENT_MODE', 'manual'),

    /*
    |--------------------------------------------------------------------------
    | QRIS Statis Merchant
    |--------------------------------------------------------------------------
    |
    | String QRIS statis dari stiker/cetakan merchant Anda (format EMVCo).
    | Digunakan di mode manual untuk generate QR dinamis per transaksi.
    | Biarkan kosong jika tidak menggunakan QRIS statis.
    |
    */
    'qris_static_string' => env('QRIS_STATIC_STRING', ''),

    /*
    |--------------------------------------------------------------------------
    | Virtual Account Tersedia (Mode Manual)
    |--------------------------------------------------------------------------
    |
    | Daftar bank beserta nomor rekening untuk Virtual Account manual.
    | Kasir dapat memilih bank mana yang digunakan saat menerima transfer.
    |
    */
    'virtual_accounts' => [
        'bca'     => ['label' => 'BCA', 'account_number' => env('VA_BCA', '')],
        'mandiri' => ['label' => 'Mandiri', 'account_number' => env('VA_MANDIRI', '')],
        'bni'     => ['label' => 'BNI', 'account_number' => env('VA_BNI', '')],
        'bri'     => ['label' => 'BRI', 'account_number' => env('VA_BRI', '')],
        'permata' => ['label' => 'Permata', 'account_number' => env('VA_PERMATA', '')],
    ],

    /*
    |--------------------------------------------------------------------------
    | E-Wallet Tersedia (Mode Manual)
    |--------------------------------------------------------------------------
    |
    | Daftar e-wallet yang diterima kasir secara manual.
    | Kasir memilih, lalu konfirmasi setelah pelanggan transfer.
    |
    */
    'ewallets' => [
        'gopay'  => ['label' => 'GoPay',  'phone' => env('EWALLET_GOPAY_PHONE', '')],
        'ovo'    => ['label' => 'OVO',    'phone' => env('EWALLET_OVO_PHONE', '')],
        'dana'   => ['label' => 'DANA',   'phone' => env('EWALLET_DANA_PHONE', '')],
        'shopeepay' => ['label' => 'ShopeePay', 'phone' => env('EWALLET_SHOPEEPAY_PHONE', '')],
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Gateway (aktif sesuai 'mode')
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'midtrans' => [
            'server_key'    => env('MIDTRANS_SERVER_KEY', ''),
            'client_key'    => env('MIDTRANS_CLIENT_KEY', ''),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
            'merchant_id'   => env('MIDTRANS_MERCHANT_ID', ''),
        ],

        'doku' => [
            'client_id'     => env('DOKU_CLIENT_ID', ''),
            'secret_key'    => env('DOKU_SECRET_KEY', ''),
            'is_production' => env('DOKU_IS_PRODUCTION', false),
        ],
    ],
];
