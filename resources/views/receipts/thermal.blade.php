<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'kitchen' ? 'TIKET DAPUR #' . $order->id : 'STRUK #' . $order->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.35;
            color: #000000;
            background: #f1f5f9;
            padding: 20px 10px;
        }
        @php
            $paperWidth = setting('receipt_paper_width', '58mm');
            $containerWidth = $paperWidth === '80mm' ? '380px' : '300px';
        @endphp
        .receipt-container {
            width: 100%;
            max-width: {{ $containerWidth }};
            margin: 0 auto;
            background: #ffffff;
            padding: 16px 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000000;
            margin: 8px 0;
        }
        .divider-double {
            border-top: 2px solid #000000;
            margin: 8px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .item-row {
            margin-bottom: 6px;
        }
        .item-main {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
        }
        .item-sub {
            padding-left: 14px;
            font-size: 11px;
            color: #333333;
        }
        .item-note {
            padding-left: 14px;
            font-size: 11px;
            font-style: italic;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border: 1px solid #000000;
            font-size: 10px;
            font-weight: bold;
        }
        .screen-toolbar {
            max-width: {{ $containerWidth }};
            margin: 0 auto 15px;
            display: flex;
            gap: 8px;
        }
        .btn-print {
            flex: 1;
            padding: 8px 12px;
            background: #0f172a;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
        }
        .btn-close {
            padding: 8px 12px;
            background: #e2e8f0;
            color: #334155;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .screen-toolbar {
                display: none !important;
            }
            .receipt-container {
                max-width: 100% !important;
                width: 100% !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            @page {
                margin: 0;
                size: auto;
            }
        }
    </style>
</head>
<body>
    {{-- On-Screen Floating Toolbar --}}
    <div class="screen-toolbar">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk</button>
        <button class="btn-close" onclick="window.close()">Tutup</button>
    </div>

    <div class="receipt-container">
        @if ($type === 'kitchen')
            {{-- KITCHEN DOCKET (TIKET DAPUR / BAR) --}}
            <div class="text-center">
                <h2 style="font-size: 16px; font-weight: 900; letter-spacing: 1px;">*** TIKET DAPUR ***</h2>
                <div class="divider"></div>
            </div>

            <div class="row">
                <span>No. Order :</span>
                <span class="font-bold" style="font-size: 15px;">#{{ $order->id }}</span>
            </div>
            <div class="row">
                <span>Waktu     :</span>
                <span>{{ $order->created_at->format('d/m/y H:i') }}</span>
            </div>
            <div class="row">
                <span>Tipe/Meja :</span>
                <span class="font-bold" style="font-size: 14px;">
                    @if ($order->table)
                        MEJA {{ $order->table->table_number }}
                    @else
                        {{ strtoupper(str_replace('_', ' ', $order->order_type)) }}
                    @endif
                </span>
            </div>
            @if ($order->customer_name)
                <div class="row">
                    <span>Pelanggan :</span>
                    <span>{{ $order->customer_name }}</span>
                </div>
            @endif

            <div class="divider-double"></div>

            {{-- Kitchen Order Items --}}
            <div style="margin: 10px 0;">
                @foreach ($order->items as $item)
                    <div class="item-row" style="margin-bottom: 8px;">
                        <div class="item-main" style="font-size: 13px;">
                            <span>{{ $item->qty }}x {{ $item->product?->name ?? 'Menu' }}</span>
                            @if ($item->size)
                                <span class="badge">[{{ strtoupper($item->size->name) }}]</span>
                            @endif
                        </div>
                        @if ($item->toppings && $item->toppings->count() > 0)
                            <div class="item-sub">
                                + {{ $item->toppings->pluck('name')->join(', ') }}
                            </div>
                        @endif
                        @if (!empty($item->notes))
                            <div class="item-note" style="font-weight: bold;">
                                ⚠️ {{ $item->notes }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if (!empty($order->notes))
                <div class="divider"></div>
                <div style="padding: 4px; border: 1px dashed #000; margin-top: 6px;">
                    <strong>CATATAN KHUSUS:</strong><br>
                    {{ $order->notes }}
                </div>
            @endif

            <div class="divider-double"></div>
            <div class="text-center" style="font-size: 10px;">
                Dicetak: {{ now()->format('H:i:s') }}
            </div>

        @else
            {{-- CUSTOMER RECEIPT (STRUK PEMBELIAN PELANGGAN) --}}
            <div class="text-center">
                <h1 style="font-size: 16px; font-weight: 900; letter-spacing: 0.5px;">{{ setting('receipt_cafe_name', 'KAFE DIGITAL POS') }}</h1>
                @php
                    $cafeAddress = setting('receipt_cafe_address', 'Jl. Kopi Harapan No. 12, Jakarta');
                    $cafePhone = setting('receipt_cafe_phone', '0812-3456-7890');
                @endphp
                @if (filled($cafeAddress))
                    <p style="font-size: 10px; margin-top: 2px;">{{ $cafeAddress }}</p>
                @endif
                @if (filled($cafePhone))
                    <p style="font-size: 10px;">Telp / WA: {{ $cafePhone }}</p>
                @endif
                <div class="divider"></div>
            </div>

            <div class="row">
                <span>No. Struk :</span>
                <span class="font-bold">#{{ $order->id }}</span>
            </div>
            <div class="row">
                <span>Tanggal   :</span>
                <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="row">
                <span>Kasir     :</span>
                <span>{{ $order->user?->name ?? 'Kasir Self-Order' }}</span>
            </div>
            <div class="row">
                <span>Meja/Tipe :</span>
                <span class="font-bold">
                    @if ($order->table)
                        Meja {{ $order->table->table_number }}
                    @else
                        {{ strtoupper(str_replace('_', ' ', $order->order_type)) }}
                    @endif
                </span>
            </div>
            @if ($order->customer_name)
                <div class="row">
                    <span>Pelanggan :</span>
                    <span>{{ $order->customer_name }}</span>
                </div>
            @endif

            <div class="divider"></div>

            {{-- Customer Items List --}}
            @php
                $subtotalCalc = 0;
            @endphp
            @foreach ($order->items as $item)
                @php
                    $itemSubtotal = $item->subtotal ?? ($item->qty * $item->price);
                    $subtotalCalc += $itemSubtotal;
                @endphp
                <div class="item-row">
                    <div class="item-main">
                        <span>{{ $item->qty }}x {{ $item->product?->name ?? 'Menu' }}</span>
                        <span>{{ number_format($itemSubtotal, 0, ',', '.') }}</span>
                    </div>
                    @if ($item->size)
                        <div class="item-sub">
                            Size: {{ $item->size->name }}
                        </div>
                    @endif
                    @if ($item->toppings && $item->toppings->count() > 0)
                        <div class="item-sub">
                            + {{ $item->toppings->pluck('name')->join(', ') }}
                        </div>
                    @endif
                    @if (!empty($item->notes))
                        <div class="item-note">
                            * {{ $item->notes }}
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="divider"></div>

            {{-- Summary Details --}}
            @php
                $subtotal = $order->subtotal_order ?? $subtotalCalc;
                $discount = (float) ($order->discount_order ?? 0);
                $promoDiscount = (float) ($order->promotion_discount ?? 0);
                $giftCard = (float) ($order->gift_card_amount ?? 0);
                $serviceFee = (float) ($order->service_fee_order ?? 0);
                $grandTotal = $order->total_order ?? ($subtotal - $discount - $promoDiscount - $giftCard + $serviceFee);
            @endphp

            <div class="row">
                <span>Subtotal</span>
                <span>{{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>

            @if ($discount > 0)
                <div class="row">
                    <span>Diskon Manual</span>
                    <span>-{{ number_format($discount, 0, ',', '.') }}</span>
                </div>
            @endif

            @if ($promoDiscount > 0)
                <div class="row">
                    <span>Promo ({{ $order->promotion_code }})</span>
                    <span>-{{ number_format($promoDiscount, 0, ',', '.') }}</span>
                </div>
            @endif

            @if ($giftCard > 0)
                <div class="row">
                    <span>Gift Card</span>
                    <span>-{{ number_format($giftCard, 0, ',', '.') }}</span>
                </div>
            @endif

            @if ($serviceFee > 0)
                <div class="row">
                    <span>Biaya Layanan</span>
                    <span>{{ number_format($serviceFee, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="divider-double"></div>

            <div class="row font-bold" style="font-size: 14px;">
                <span>TOTAL</span>
                <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>

            <div class="divider"></div>

            {{-- Payment Information --}}
            @php
                $latestPayment = $order->payments->first();
                $paymentMethodName = $latestPayment ? strtoupper(str_replace('_', ' ', $latestPayment->payment_method)) : strtoupper($order->status);
                $paidAmount = $latestPayment ? (float) $latestPayment->amount : $grandTotal;
                $change = max($paidAmount - $grandTotal, 0);
            @endphp

            <div class="row">
                <span>Metode Bayar :</span>
                <span class="font-bold">{{ $paymentMethodName }}</span>
            </div>
            <div class="row">
                <span>Status       :</span>
                <span class="font-bold">LUNAS</span>
            </div>
            @if ($latestPayment && $latestPayment->payment_method === 'cash')
                <div class="row">
                    <span>Bayar (Cash) :</span>
                    <span>Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                </div>
                <div class="row">
                    <span>Kembalian    :</span>
                    <span>Rp {{ number_format($change, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="divider"></div>

            {{-- Footer Notes --}}
            <div class="text-center" style="margin-top: 10px; font-size: 10px;">
                @php
                    $footerText = setting('receipt_footer_text', 'TERIMA KASIH ATAS KUNJUNGAN ANDA!');
                    $wifiSsid = setting('receipt_wifi_ssid', 'KafeDigital');
                    $wifiPass = setting('receipt_wifi_password', 'kopiEnak2026');
                    $feedbackInfo = setting('receipt_feedback_info', '@kafedigital');
                    $footerSubtext = setting('receipt_footer_subtext', '* Struk ini sah sebagai bukti pembayaran *');
                @endphp

                @if (filled($footerText))
                    <p class="font-bold">{{ $footerText }}</p>
                @endif

                @if (filled($wifiSsid))
                    <p style="margin-top: 2px;">WiFi: {{ $wifiSsid }}@if(filled($wifiPass)) | Pass: {{ $wifiPass }}@endif</p>
                @endif

                @if (filled($feedbackInfo))
                    <p style="margin-top: 4px; color: #555;">Kritik & Saran: {{ $feedbackInfo }}</p>
                @endif

                @if (filled($footerSubtext))
                    <p style="margin-top: 6px; font-size: 9px; color: #777;">{{ $footerSubtext }}</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Auto-print trigger on page load --}}
    <script>
        window.addEventListener('load', function () {
            // Auto open print dialog
            setTimeout(function () {
                window.print();
            }, 350);
        });
    </script>
</body>
</html>
