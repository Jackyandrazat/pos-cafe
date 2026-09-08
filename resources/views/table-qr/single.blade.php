<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Meja {{ $card['table']->table_number }} - {{ $card['cafe_name'] }}</title>
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
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        /* Action Toolbar (Hidden during print) */
        .toolbar {
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
            align-items: center;
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

        .btn-outline:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        /* Table Card (Acrylic Tent / Stand Design) */
        .table-card {
            width: 100%;
            max-width: 360px;
            background: #ffffff;
            border-radius: 24px;
            padding: 28px 24px;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08), 0 0 1px 1px rgba(0, 0, 0, 0.05);
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .card-header-accent {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #8B5A2B, #D4A574, #8B5A2B);
        }

        .cafe-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 4px;
            margin-bottom: 16px;
        }

        .cafe-logo-icon {
            width: 32px;
            height: 32px;
            background: #8B5A2B;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .cafe-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: -0.3px;
        }

        .table-badge-container {
            background: #fdf8f4;
            border: 2px dashed #D4A574;
            border-radius: 16px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .table-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: #8B5A2B;
            letter-spacing: 1.5px;
            margin-bottom: 2px;
        }

        .table-number {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.1;
        }

        .table-meta {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            margin-top: 4px;
        }

        .qr-wrapper {
            background: #ffffff;
            padding: 16px;
            border-radius: 20px;
            border: 2px solid #f1f5f9;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            display: inline-block;
            margin-bottom: 16px;
        }

        .qr-wrapper svg {
            display: block;
            width: 220px;
            height: 220px;
        }

        .scan-instruction {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .scan-subtext {
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
            margin-bottom: 18px;
        }

        .steps-container {
            background: #f8fafc;
            border-radius: 14px;
            padding: 12px 14px;
            text-align: left;
            margin-bottom: 14px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: #334155;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .step-item:last-child {
            margin-bottom: 0;
        }

        .step-num {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #8B5A2B;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .waiter-note {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }

        .url-preview {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
            font-family: monospace;
            font-size: 10px;
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

            .table-card {
                box-shadow: none;
                border: 1px dashed #cbd5e1;
                margin: 0 auto;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button class="btn btn-primary" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Cetak Kartu Meja
        </button>

        <a href="{{ route('tables.qr.download', $card['table']->id) }}" class="btn btn-outline">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Download SVG
        </a>

        <button class="btn btn-outline" onclick="window.close()">
            Tutup
        </button>
    </div>

    <div class="table-card">
        <div class="card-header-accent"></div>

        <div class="cafe-brand">
            <div class="cafe-logo-icon"></div>
            <div class="cafe-title">{{ $card['cafe_name'] }}</div>
        </div>

        <div class="table-badge-container">
            <div class="table-label">Nomor Meja</div>
            <div class="table-number">{{ $card['table']->table_number }}</div>
            <div class="table-meta">{{ $card['area_name'] }} • Kapasitas {{ $card['table']->capacity ?? 2 }} Pax</div>
        </div>

        <div class="qr-wrapper">
            {!! $card['qr_svg'] !!}
        </div>

        <div class="scan-instruction">Scan QR untuk Pesan & Bayar</div>
        <p class="scan-subtext">Arahkan kamera HP Anda ke kode QR di atas untuk membuka menu pemesanan mandiri.</p>

        <div class="steps-container">
            <div class="step-item">
                <span class="step-num">1</span>
                <span>Scan QR dengan kamera HP / browser</span>
            </div>
            <div class="step-item">
                <span class="step-num">2</span>
                <span>Pilih menu favorit Anda</span>
            </div>
            <div class="step-item">
                <span class="step-num">3</span>
                <span>Bayar mandiri & pesanan diantar ke meja</span>
            </div>
        </div>

        <div class="waiter-note">
            💡 Butuh bantuan pelayan? Panggil pelayan langsung dari aplikasi.
        </div>

        <div class="url-preview">
            {{ $card['target_url'] }}
        </div>
    </div>

</body>
</html>
