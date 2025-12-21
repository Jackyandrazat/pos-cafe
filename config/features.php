<?php

return [
    'modules' => [
        'promotions' => [
            'label' => 'Promotions & Dynamic Pricing',
            'description' => 'Voucher, promo, dan jadwal happy hour.',
            'default' => true,
        ],
        'gift_cards' => [
            'label' => 'Gift Card & Corporate Balance',
            'description' => 'Voucher saldo digital dan akun perusahaan.',
            'default' => true,
        ],
        'table_management' => [
            'label' => 'Table & Queue Management',
            'description' => 'Dashboard floor plan dan antrean tamu.',
            'default' => true,
        ],
        'inventory_waste' => [
            'label' => 'Inventory & Waste Tracking',
            'description' => 'Pencatatan stok/waste dan dashboard bahan baku.',
            'default' => true,
        ],
        'loyalty' => [
            'label' => 'Customer Loyalty Program',
            'description' => 'Manajemen customer dan poin reward otomatis.',
            'default' => true,
        ],
        'kitchen_display' => [
            'label' => 'Kitchen Display System',
            'description' => 'Monitor pesanan dapur dan status produksi.',
            'default' => true,
        ],
    ],
];
