<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Semua QR Meja - {{ $cafe_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            padding: 24px 16px;
        }

        .toolbar {
            max-width: 1000px;
            margin: 0 auto 24px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 16px 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .toolbar-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        .toolbar-desc {
            font-size: 12px;
            color: #64748b;
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: #8B5A2B;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(139, 90, 43, 0.25);
        }

        .btn-primary:hover {
            background: #70441e;
        }

        .btn-outline {
            background: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .grid-container {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        .table-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 24px 20px;
            border: 1.5px dashed #cbd5e1;
            text-align: center;
            position: relative;
            page-break-inside: avoid;
        }

        .cafe-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-bottom: 12px;
        }

        .cafe-logo-icon {
            width: 26px;
            height: 26px;
            background: #8B5A2B;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        .cafe-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
        }

        .table-badge {
            background: #fdf8f4;
            border: 1.5px dashed #D4A574;
            border-radius: 12px;
            padding: 8px 12px;
            margin-bottom: 14px;
        }

        .table-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 700;
            color: #8B5A2B;
            letter-spacing: 1.5px;
        }

        .table-number {
            font-size: 26px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.1;
        }

        .table-meta {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
            margin-top: 2px;
        }

        .qr-wrapper {
            background: #ffffff;
            padding: 12px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            display: inline-block;
            margin-bottom: 12px;
        }

        .qr-wrapper svg {
            display: block;
            width: 170px;
            height: 170px;
        }

        .scan-instruction {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .scan-subtext {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .steps-container {
            background: #f8fafc;
            border-radius: 10px;
            padding: 8px 12px;
            text-align: left;
            margin-bottom: 10px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: #334155;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .step-item:last-child {
            margin-bottom: 0;
        }

        .step-num {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #8B5A2B;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .url-preview {
            font-family: monospace;
            font-size: 9px;
            color: #94a3b8;
            word-break: break-all;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
                margin: 0;
            }

            .toolbar {
                display: none !important;
            }

            .grid-container {
                max-width: 100%;
                gap: 16px;
            }

            .table-card {
                border: 1px dashed #94a3b8;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <div>
            <div class="toolbar-title">Lembar Cetak QR Code Meja (Bulk)</div>
            <div class="toolbar-desc">Total {{ count($cards) }} kartu meja siap dicetak dan dilaminasi</div>
        </div>

        <div class="toolbar-actions">
            <button class="btn btn-primary" onclick="window.print()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Semua Sekarang
            </button>
            <button class="btn btn-outline" onclick="window.close()">
                Tutup
            </button>
        </div>
    </div>

    <div class="grid-container">
        @foreach($cards as $card)
            <div class="table-card">
                <div class="cafe-brand">
                    <div class="cafe-logo-icon"></div>
                    <div class="cafe-title">{{ $card['cafe_name'] }}</div>
                </div>

                <div class="table-badge">
                    <div class="table-label">Nomor Meja</div>
                    <div class="table-number">{{ $card['table']->table_number }}</div>
                    <div class="table-meta">{{ $card['area_name'] }} • {{ $card['table']->capacity ?? 2 }} Pax</div>
                </div>

                <div class="qr-wrapper">
                    {!! $card['qr_svg'] !!}
                </div>

                <div class="scan-instruction">Scan QR untuk Pesan & Bayar</div>
                <p class="scan-subtext">Arahkan kamera HP ke kode QR di atas</p>

                <div class="steps-container">
                    <div class="step-item">
                        <span class="step-num">1</span>
                        <span>Scan QR dengan kamera HP</span>
                    </div>
                    <div class="step-item">
                        <span class="step-num">2</span>
                        <span>Pilih menu favorit</span>
                    </div>
                    <div class="step-item">
                        <span class="step-num">3</span>
                        <span>Bayar & pesanan langsung diantar</span>
                    </div>
                </div>

                <div class="url-preview">
                    {{ $card['target_url'] }}
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
