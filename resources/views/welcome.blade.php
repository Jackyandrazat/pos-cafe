<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Labo By kodeeweb | Solusi Ekosistem POS &amp; Self-Order Kafe</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --bg: #fff7f0;
                --orange: #ff7b54;
                --orange-dark: #e96334;
                --yellow: #ffd166;
                --green: #34c686;
                --text-dark: #221c18;
                --text-muted: #6f655c;
                --card: #ffffff;
                --border: rgba(34, 28, 24, 0.08);
                --shadow: 0 25px 60px rgba(255, 123, 84, 0.18);
            }
            *, *::before, *::after { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: 'Poppins','Segoe UI',system-ui,-apple-system,sans-serif;
                background: linear-gradient(180deg, var(--bg) 0%, #fff 65%);
                color: var(--text-dark);
                line-height: 1.6;
                min-height: 100vh;
            }
            a { text-decoration: none; color: inherit; }
            .page { position: relative; min-height: 100vh; overflow: hidden; }
            .overlay-pattern {
                position: absolute;
                inset: -20% -10% 0 -10%;
                background-image:
                    radial-gradient(circle at 20% 20%, rgba(255, 123, 84, 0.15), transparent 50%),
                    radial-gradient(circle at 80% 0%, rgba(255, 209, 102, 0.25), transparent 45%),
                    radial-gradient(circle at 50% 80%, rgba(52, 198, 134, 0.2), transparent 45%);
                opacity: 0.8;
                pointer-events: none;
                z-index: 0;
            }
            .container {
                position: relative;
                z-index: 1;
                max-width: 1240px;
                margin: 0 auto;
                padding: 2rem 1.5rem 1rem;
            }
            .nav {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 1rem 0;
            }
            .brand { display: flex; align-items: center; gap: 0.875rem; }
            .logo-badge {
                width: 48px;
                height: 48px;
                border-radius: 14px;
                background: linear-gradient(135deg, var(--orange), var(--orange-dark));
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                color: #fff;
                font-size: 1.1rem;
                box-shadow: 0 10px 25px rgba(233, 99, 52, 0.4);
            }
            .brand-copy span { display: block; font-weight: 600; font-size: 1.05rem; }
            .brand-copy small { color: var(--text-muted); font-weight: 500; font-size: 0.85rem; }
            .nav-links { display: flex; flex-wrap: wrap; gap: 1rem; font-weight: 500; color: var(--text-muted); }
            .nav-links a {
                padding: 0.25rem 0.65rem;
                border-radius: 999px;
                transition: color 0.2s ease, background 0.2s ease;
            }
            .nav-links a:hover { color: var(--orange-dark); background: rgba(255, 123, 84, 0.08); }
            .auth-links { display: flex; flex-wrap: wrap; gap: 0.75rem; }
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 0.55rem 1.35rem;
                font-weight: 600;
                font-size: 0.95rem;
                transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
                cursor: pointer;
            }
            .btn:hover { transform: translateY(-1px); }
            .btn.solid {
                background: var(--orange);
                color: #fff;
                box-shadow: 0 15px 30px rgba(255, 123, 84, 0.3);
            }
            .btn.solid:hover { background: var(--orange-dark); }
            .btn.ghost {
                border: 1px solid rgba(255, 123, 84, 0.35);
                color: var(--orange-dark);
                background: rgba(255, 123, 84, 0.05);
            }
            .btn.ghost:hover { background: rgba(255, 123, 84, 0.15); }
            .hero { padding: 3.5rem 0 2.5rem; }
            .hero-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: 2.5rem;
                align-items: center;
            }
            .hero-eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                text-transform: uppercase;
                font-size: 0.85rem;
                letter-spacing: 0.14em;
                color: var(--orange-dark);
                font-weight: 600;
                background: rgba(255, 123, 84, 0.1);
                padding: 0.3rem 0.8rem;
                border-radius: 999px;
                margin-bottom: 0.8rem;
            }
            .hero-copy h1 {
                font-size: clamp(2.2rem, 3.8vw, 3.4rem);
                line-height: 1.15;
                margin: 0.5rem 0 1rem;
                font-weight: 700;
                color: var(--text-dark);
            }
            .hero-copy .lead {
                color: var(--text-muted);
                font-size: 1.05rem;
                margin-bottom: 1.5rem;
                line-height: 1.7;
            }
            .hero-cta { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
            .hero-benefits {
                list-style: none;
                padding: 0;
                margin: 0 0 1.5rem;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 0.75rem;
            }
            .hero-benefits li {
                display: flex;
                gap: 0.75rem;
                align-items: flex-start;
                background: rgba(255, 255, 255, 0.85);
                border-radius: 16px;
                padding: 0.85rem 1rem;
                border: 1px solid var(--border);
                font-size: 0.92rem;
                font-weight: 500;
            }
            .hero-benefits span { font-size: 1.25rem; line-height: 1; }
            .hero-card {
                background: var(--card);
                border-radius: 28px;
                border: 1px solid rgba(255, 255, 255, 0.9);
                box-shadow: var(--shadow);
                padding: 2rem;
                position: relative;
                overflow: hidden;
            }
            .hero-card::after {
                content: "";
                position: absolute;
                inset: 0;
                border-radius: inherit;
                background: radial-gradient(circle at 80% -10%, rgba(255, 209, 102, 0.35), transparent 50%);
                z-index: 0;
            }
            .hero-card-content { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 1.25rem; }
            .ticket-header { display: flex; justify-content: space-between; gap: 1rem; align-items: center; }
            .ticket-title { margin: 0; font-weight: 600; font-size: 1.05rem; }
            .ticket-meta { margin: 0; color: var(--text-muted); font-size: 0.85rem; }
            .status {
                background: rgba(52, 198, 134, 0.15);
                color: #0d8a57;
                padding: 0.3rem 0.85rem;
                border-radius: 999px;
                font-size: 0.82rem;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
            }
            .status-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #0d8a57;
            }
            .ticket-items { display: flex; flex-direction: column; gap: 0.9rem; }
            .ticket-items article { display: flex; justify-content: space-between; gap: 1rem; }
            .ticket-items h4 { margin: 0 0 0.15rem; font-size: 0.98rem; font-weight: 600; }
            .ticket-items span { color: var(--text-muted); font-size: 0.85rem; }
            .ticket-items strong { font-size: 0.98rem; font-weight: 600; }
            .ticket-total {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding-top: 1rem;
                border-top: 1px dashed rgba(34, 28, 24, 0.15);
            }
            .ticket-total strong { font-size: 1.35rem; font-weight: 700; color: var(--orange-dark); }
            .ticket-total button {
                border: none;
                background: var(--green);
                color: #fff;
                padding: 0.65rem 1.2rem;
                border-radius: 14px;
                font-weight: 600;
                font-size: 0.9rem;
                cursor: pointer;
                box-shadow: 0 10px 25px rgba(52, 198, 134, 0.3);
                transition: background 0.2s ease;
            }
            .ticket-total button:hover { background: #2bb075; }
            .section-container {
                max-width: 1240px;
                margin: 0 auto;
                padding: 0 1.5rem;
                position: relative;
                z-index: 1;
            }
            .main { position: relative; z-index: 1; }
            .values { padding: 1.5rem 0 3rem; }
            .values-grid {
                background: #fff;
                border-radius: 28px;
                border: 1px solid var(--border);
                box-shadow: 0 18px 45px rgba(20, 16, 11, 0.05);
                padding: 2.2rem 2rem;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 1.8rem;
            }
            .value-card {
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
            }
            .value-icon {
                font-size: 1.6rem;
                margin-bottom: 0.2rem;
            }
            .value-card h4 {
                margin: 0;
                font-size: 1.1rem;
                font-weight: 600;
                color: var(--text-dark);
            }
            .value-card p {
                margin: 0;
                color: var(--text-muted);
                font-size: 0.9rem;
                line-height: 1.55;
            }
            .features { padding: 4rem 0 3.5rem; }
            .section-head {
                text-align: center;
                max-width: 740px;
                margin: 0 auto 3rem;
            }
            .section-head p {
                color: var(--orange-dark);
                font-weight: 600;
                letter-spacing: 0.16em;
                font-size: 0.85rem;
                text-transform: uppercase;
                margin-bottom: 0.4rem;
            }
            .section-head h2 { font-size: 2.3rem; margin: 0 0 0.8rem; font-weight: 700; line-height: 1.25; }
            .section-head span { color: var(--text-muted); font-size: 1.02rem; line-height: 1.6; }
            .ecosystem-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                background: #fff;
                border: 1px solid var(--border);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
                padding: 0.45rem 1.1rem;
                border-radius: 999px;
                font-size: 0.85rem;
                font-weight: 600;
                color: var(--orange-dark);
                margin-top: 0.8rem;
            }

            /* Modules Grid */
            .modules-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
                gap: 1.8rem;
            }
            .module-card {
                background: #fff;
                border-radius: 26px;
                padding: 2rem;
                border: 1px solid var(--border);
                box-shadow: 0 16px 40px rgba(20, 16, 11, 0.04);
                display: flex;
                flex-direction: column;
                gap: 1rem;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            .module-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 22px 55px rgba(255, 123, 84, 0.12);
            }
            .module-header {
                display: flex;
                align-items: center;
                gap: 1rem;
                border-bottom: 1px solid rgba(34, 28, 24, 0.06);
                padding-bottom: 1rem;
            }
            .module-icon {
                width: 50px;
                height: 50px;
                border-radius: 16px;
                background: rgba(255, 123, 84, 0.12);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                flex-shrink: 0;
            }
            .module-title {
                display: flex;
                flex-direction: column;
            }
            .module-title span {
                font-size: 0.75rem;
                color: var(--orange-dark);
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            .module-title h3 {
                margin: 0.1rem 0 0;
                font-size: 1.18rem;
                font-weight: 700;
                color: var(--text-dark);
            }
            .module-items {
                list-style: none;
                padding: 0;
                margin: 0;
                display: flex;
                flex-direction: column;
                gap: 0.65rem;
            }
            .module-items li {
                display: flex;
                align-items: flex-start;
                gap: 0.55rem;
                font-size: 0.88rem;
                color: var(--text-muted);
                line-height: 1.5;
            }
            .module-items li::before {
                content: "•";
                color: var(--orange);
                font-weight: bold;
                font-size: 1.1rem;
                line-height: 1.2;
            }
            .module-items li strong {
                color: var(--text-dark);
                font-weight: 600;
            }
            .module-badge-list {
                display: flex;
                flex-wrap: wrap;
                gap: 0.4rem;
                margin-top: auto;
                padding-top: 0.9rem;
                border-top: 1px dashed rgba(34, 28, 24, 0.08);
            }
            .badge-pill {
                font-size: 0.74rem;
                font-weight: 600;
                padding: 0.22rem 0.65rem;
                border-radius: 999px;
                background: rgba(255, 123, 84, 0.08);
                color: var(--orange-dark);
            }

            .menu-showcase { padding: 3.5rem 0; }
            .menu-wrapper {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
                gap: 2.5rem;
                align-items: center;
                background: #fff7ec;
                border-radius: 32px;
                padding: 2.8rem;
                border: 1px solid rgba(255, 123, 84, 0.2);
                box-shadow: 0 30px 60px rgba(255, 123, 84, 0.1);
            }
            .menu-copy h3 { font-size: 2rem; margin: 0 0 0.85rem; font-weight: 700; line-height: 1.25; }
            .menu-copy p { color: var(--text-muted); margin: 0 0 1.5rem; line-height: 1.65; font-size: 0.98rem; }
            .menu-pills { display: flex; flex-wrap: wrap; gap: 0.6rem; margin-bottom: 1.75rem; }
            .menu-pills span {
                padding: 0.4rem 0.95rem;
                border-radius: 999px;
                background: #fff;
                border: 1px solid rgba(255, 123, 84, 0.25);
                font-size: 0.88rem;
                font-weight: 500;
                color: var(--text-dark);
            }
            .menu-card {
                background: #fff;
                border-radius: 28px;
                padding: 1.85rem;
                border: 1px solid rgba(34, 28, 24, 0.08);
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
                position: relative;
                overflow: hidden;
            }
            .menu-card-content { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 1.1rem; }
            .menu-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
            .menu-header h4 { margin: 0; font-size: 1.05rem; }
            .menu-header span { color: var(--text-muted); font-size: 0.85rem; }
            .menu-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                border-bottom: 1px dashed rgba(34, 28, 24, 0.12);
                padding-bottom: 0.85rem;
            }
            .menu-item:last-child { border-bottom: none; padding-bottom: 0; }
            .menu-item strong { display: block; font-size: 0.95rem; }
            .menu-item span { color: var(--text-muted); font-size: 0.85rem; }
            .menu-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-weight: 600;
                padding-top: 0.5rem;
                border-top: 1px solid rgba(34, 28, 24, 0.06);
            }
            .menu-footer small { color: var(--text-muted); font-weight: 500; font-size: 0.82rem; }
            .workflow { padding: 3.5rem 0; }
            .workflow-steps {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 1.5rem;
            }
            .step {
                background: #fff;
                border-radius: 24px;
                padding: 1.75rem;
                border: 1px solid var(--border);
                box-shadow: 0 16px 35px rgba(0, 0, 0, 0.04);
                display: flex;
                flex-direction: column;
            }
            .step-number {
                width: 42px;
                height: 42px;
                border-radius: 12px;
                background: var(--orange);
                color: #fff;
                font-weight: 700;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 1rem;
                box-shadow: 0 10px 22px rgba(255, 123, 84, 0.35);
            }
            .step h3 { margin: 0 0 0.5rem; font-size: 1.15rem; font-weight: 600; }
            .step p { color: var(--text-muted); margin: 0; font-size: 0.92rem; line-height: 1.6; }
            .cta-banner { padding: 3rem 1.5rem 4rem; }
            .cta-inner {
                background: linear-gradient(135deg, var(--orange) 0%, var(--orange-dark) 100%);
                border-radius: 32px;
                color: #fff;
                padding: 3.2rem 2.8rem;
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                box-shadow: 0 30px 60px rgba(255, 123, 84, 0.35);
            }
            .cta-inner h3 { margin: 0; font-size: 2.2rem; font-weight: 700; }
            .cta-inner p { margin: 0; font-size: 1.05rem; opacity: 0.95; line-height: 1.6; max-width: 700px; }
            .cta-actions { display: flex; flex-wrap: wrap; gap: 0.85rem; margin-top: 0.5rem; }
            .cta-actions .btn.ghost {
                border-color: rgba(255, 255, 255, 0.7);
                color: #fff;
                background: transparent;
            }
            .cta-actions .btn.ghost:hover { background: rgba(255, 255, 255, 0.2); }
            .cta-actions .btn.solid {
                background: #fff;
                color: var(--orange-dark);
                box-shadow: none;
            }
            .cta-actions .btn.solid:hover { background: #fff7f0; }
            .footer {
                text-align: center;
                padding: 2.5rem 1.5rem 3rem;
                color: var(--text-muted);
                font-size: 0.92rem;
                border-top: 1px solid var(--border);
            }
            .footer-copy { margin-bottom: 0.4rem; font-weight: 500; }
            .footer-meta { font-size: 0.82rem; color: #8e8378; }
            @media (max-width: 768px) {
                .nav-links { width: 100%; justify-content: center; }
                .auth-links { width: 100%; justify-content: center; }
                .hero-cta { flex-direction: column; align-items: stretch; }
                .modules-grid { grid-template-columns: 1fr; }
                .cta-actions { flex-direction: column; }
                .cta-actions .btn { width: 100%; text-align: center; }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="overlay-pattern" aria-hidden="true"></div>
            <div class="container">
                <nav class="nav">
                    <div class="brand">
                        <div class="logo-badge">KW</div>
                        <div class="brand-copy">
                            <span>Labo By kodeeweb</span>
                            <small>Sistem Kasir &amp; Operasional Kafe</small>
                        </div>
                    </div>
                    <div class="nav-links">
                        <a href="#fitur">Ekosistem Fitur</a>
                        <a href="#menu">Self-Order QR</a>
                        <a href="#kolaborasi">Tumbuh Bersama</a>
                        <a href="#kontak">Hubungi Kami</a>
                    </div>
                    @if (Route::has('login'))
                        <div class="auth-links">
                            @auth
                                <a href="{{ url('/admin') }}" class="btn ghost">Buka Panel Kasir &amp; Admin</a>
                            @else
                                <a href="{{ url('/admin/login') }}" class="btn solid">Masuk Kasir / Admin</a>
                            @endauth
                        </div>
                    @endif
                </nav>

                <header class="hero" id="beranda">
                    <div class="hero-grid">
                        <div class="hero-copy">
                            <span class="hero-eyebrow">☕ Solusi Lengkap Kafe &amp; Kuliner</span>
                            <h1>Hubungkan meja, dapur, dan kasir dalam satu ekosistem yang terpercaya.</h1>
                            <p class="lead">
                                Labo By kodeeweb hadir sebagai partner operasional kafe Anda. Mengintegrasikan Self-Order QR meja pelanggan, Kitchen Display Barista, stok resep bahan baku, manajemen kasir, hingga program loyalitas dalam satu platform terpadu.
                            </p>
                            <div class="hero-cta">
                                <a href="#fitur" class="btn solid">Lihat Semua Modul Fitur</a>
                                <a href="#kontak" class="btn ghost">Diskusikan Kebutuhan Kafe</a>
                            </div>
                            <ul class="hero-benefits">
                                <li><span>📱</span><strong>Self-Order Meja:</strong> Tamu scan QR, bebas lihat menu &amp; topping dari tempat duduk.</li>
                                <li><span>👨‍🍳</span><strong>Kitchen Display:</strong> Barista &amp; dapur memantau antrean real-time tanpa kertas bon hilang.</li>
                                <li><span>📦</span><strong>Bahan Baku Presisi:</strong> Resep menu &amp; topping memotong stok otomatis, lengkap dengan kontrol waste.</li>
                                <li><span>🛡️</span><strong>Anti-Fraud Geofencing:</strong> Deteksi GPS radius kafe untuk mencegah order tunai fiktif dari luar area.</li>
                            </ul>
                        </div>
                        <div class="hero-card">
                            <div class="hero-card-content">
                                <div class="ticket-header">
                                    <div>
                                        <p class="ticket-title">Pesanan #A018 • Meja 05</p>
                                        <p class="ticket-meta">Barista Nina • Baru saja diterima</p>
                                    </div>
                                    <span class="status">
                                        <span class="status-dot"></span>
                                        Sedang Disiapkan
                                    </span>
                                </div>
                                <div class="ticket-items">
                                    <article>
                                        <div>
                                            <h4>Aren Latte Signature</h4>
                                            <span>Dingin • Less Sweet • Oat Milk</span>
                                        </div>
                                        <strong>Rp28.000</strong>
                                    </article>
                                    <article>
                                        <div>
                                            <h4>Matcha Macchiato Cream</h4>
                                            <span>Large • Extra Shot Espresso</span>
                                        </div>
                                        <strong>Rp34.000</strong>
                                    </article>
                                    <article>
                                        <div>
                                            <h4>Butter Croissant Warm</h4>
                                            <span>Hangatkan • Extra Butter</span>
                                        </div>
                                        <strong>Rp24.000</strong>
                                    </article>
                                </div>
                                <div class="ticket-total">
                                    <div>
                                        <p class="ticket-meta">Total Tagihan (Meja 05)</p>
                                        <strong>Rp86.000</strong>
                                    </div>
                                    <button type="button">Tandai Siap Saji ✓</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            </div>

            <main class="main">
                <section class="values" aria-label="Prinsip Kolaborasi dan Kepercayaan">
                    <div class="section-container values-grid">
                        <div class="value-card">
                            <div class="value-icon">🤝</div>
                            <h4>Bertumbuh Bersama</h4>
                            <p>Bukan sekadar software lepas. Kami mendengarkan masukan nyata dari tim kafe Anda untuk terus menyempurnakan alur aplikasi.</p>
                        </div>
                        <div class="value-card">
                            <div class="value-icon">⚖️</div>
                            <h4>Transparan &amp; Akurat</h4>
                            <p>Pencatatan kas per shift, pemotongan stok bahan baku per resep, hingga struk digital yang jelas tanpa selisih membingungkan.</p>
                        </div>
                        <div class="value-card">
                            <div class="value-icon">📱</div>
                            <h4>Ramah untuk Kru &amp; Tamu</h4>
                            <p>Tampilan kasir dan self-order intuitif sehingga barista baru cepat terbiasa dan tamu tidak bingung saat memesan.</p>
                        </div>
                        <div class="value-card">
                            <div class="value-icon">🔒</div>
                            <h4>Keamanan &amp; Validasi</h4>
                            <p>Dilindungi idempotency guard untuk mencegah order ganda dan validasi radius GPS kafe untuk metode bayar kasir.</p>
                        </div>
                    </div>
                </section>

                <section class="features" id="fitur">
                    <div class="section-container">
                        <div class="section-head">
                            <p>Ekosistem Lengkap &amp; Terintegrasi</p>
                            <h2>8 Pilar Modul Operasional Kafe yang Siap Pakai</h2>
                            <span>Dirancang menyeluruh untuk menghubungkan tamu di meja, barista di bar, kasir di counter, hingga pengawasan owner.</span>
                            <div>
                                <div class="ecosystem-badge">
                                    <span>✨</span> 100% Fitur Aktif &amp; Teruji di Lapangan
                                </div>
                            </div>
                        </div>

                        <div class="modules-grid">
                            <!-- 1. Self-Order PWA -->
                            <article class="module-card">
                                <div class="module-header">
                                    <div class="module-icon">📱</div>
                                    <div class="module-title">
                                        <span>Customer Experience</span>
                                        <h3>1. Self-Order QR Meja (PWA)</h3>
                                    </div>
                                </div>
                                <ul class="module-items">
                                    <li><strong>Scan QR Meja Mandiri:</strong> Buka menu langsung di ponsel tamu tanpa unduh aplikasi.</li>
                                    <li><strong>Progressive Guest Checkout:</strong> Bebas lihat menu &amp; tambah keranjang, isi nama saat siap pesan.</li>
                                    <li><strong>Kustomisasi Menu Lengkap:</strong> Varian ukuran, pilihan level gula/es, dan topping tambahan.</li>
                                    <li><strong>Panggil Pelayan (Call Waiter):</strong> Tombol bantuan cepat langsung memanggil staf ke nomor meja.</li>
                                    <li><strong>Digital Sound Chime:</strong> Nada lonceng digital berbunyi otomatis saat pesanan siap diambil.</li>
                                    <li><strong>Siklus Sesi Tamu:</strong> Auto-expire 4 jam dan tombol ganti tamu jika meja dipakai pelanggan baru.</li>
                                </ul>
                                <div class="module-badge-list">
                                    <span class="badge-pill">QR Dine-In</span>
                                    <span class="badge-pill">PWA Mobile</span>
                                    <span class="badge-pill">Call Waiter</span>
                                    <span class="badge-pill">Audio Chime</span>
                                </div>
                            </article>

                            <!-- 2. KDS -->
                            <article class="module-card">
                                <div class="module-header">
                                    <div class="module-icon">👨‍🍳</div>
                                    <div class="module-title">
                                        <span>Barista &amp; Dapur</span>
                                        <h3>2. Kitchen Display System (KDS)</h3>
                                    </div>
                                </div>
                                <ul class="module-items">
                                    <li><strong>Tiket Pesanan Real-Time:</strong> Pesanan meja &amp; kasir langsung muncul di layar dapur/bar.</li>
                                    <li><strong>Validated State Machine:</strong> Alur status ketat (Diterima ➔ Dimasak ➔ Siap Saji ➔ Selesai).</li>
                                    <li><strong>Auto Rollback Bahan Baku:</strong> Jika order dibatalkan di dapur, stok bahan otomatis kembali.</li>
                                    <li><strong>Cetak Kitchen Docket:</strong> Cetak kertas bon dapur termal untuk workstation terpisah.</li>
                                    <li><strong>Auto-Refresh Polling:</strong> Layar dapur selalu terbarukan tanpa ketergantungan koneksi berat.</li>
                                </ul>
                                <div class="module-badge-list">
                                    <span class="badge-pill">Real-Time Queue</span>
                                    <span class="badge-pill">Strict State Machine</span>
                                    <span class="badge-pill">Stock Rollback</span>
                                    <span class="badge-pill">Thermal Docket</span>
                                </div>
                            </article>

                            <!-- 3. POS & Shift -->
                            <article class="module-card">
                                <div class="module-header">
                                    <div class="module-icon">💵</div>
                                    <div class="module-title">
                                        <span>Front Office &amp; Kasir</span>
                                        <h3>3. Kasir &amp; Manajemen Shift</h3>
                                    </div>
                                </div>
                                <ul class="module-items">
                                    <li><strong>Fast POS Interface:</strong> Layanan cepat untuk pesanan Dine-In, Take Away, maupun delivery.</li>
                                    <li><strong>Buka &amp; Tutup Shift Kasir:</strong> Pencatatan modal awal (starting cash) dan penutupan harian.</li>
                                    <li><strong>Rekonsiliasi Laci Kasir:</strong> Pantau selisih uang fisik vs catatan sistem dengan transparan.</li>
                                    <li><strong>Struk Termal &amp; WhatsApp:</strong> Cetak struk kasir termal atau kirim struk digital ke WhatsApp tamu.</li>
                                    <li><strong>Shift Protection:</strong> Hanya kasir dengan shift aktif yang diizinkan memproses transaksi.</li>
                                </ul>
                                <div class="module-badge-list">
                                    <span class="badge-pill">Fast POS</span>
                                    <span class="badge-pill">Shift Management</span>
                                    <span class="badge-pill">Cash Drawer Audit</span>
                                    <span class="badge-pill">WA Receipt</span>
                                </div>
                            </article>

                            <!-- 4. Resep & Inventory -->
                            <article class="module-card">
                                <div class="module-header">
                                    <div class="module-icon">📦</div>
                                    <div class="module-title">
                                        <span>Gudang &amp; Food Cost</span>
                                        <h3>4. Bahan Baku, Resep &amp; Waste</h3>
                                    </div>
                                </div>
                                <ul class="module-items">
                                    <li><strong>Resep Menu Otomatis:</strong> Komposisi bahan baku per porsi langsung terpotong saat pesanan dibuat.</li>
                                    <li><strong>Resep Topping Presisi:</strong> Bahan baku ekstra (oat milk, extra shot) ikut terpotong akurat.</li>
                                    <li><strong>Atomic Stock Deduction:</strong> Pengurangan bersyarat mencegah stok minus akibat order bersamaan.</li>
                                    <li><strong>Restok &amp; Pembelian Supplier:</strong> Pencatatan order pembelian dengan auto-tambah stok fisik.</li>
                                    <li><strong>Waste Management:</strong> Catat bahan tumpah, basi, atau kedaluwarsa lengkap dengan nilai kerugiannya.</li>
                                </ul>
                                <div class="module-badge-list">
                                    <span class="badge-pill">Atomic Deduction</span>
                                    <span class="badge-pill">Topping Pivot</span>
                                    <span class="badge-pill">Waste Tracking</span>
                                    <span class="badge-pill">Purchase Restock</span>
                                </div>
                            </article>

                            <!-- 5. Pembayaran & Anti-Fraud -->
                            <article class="module-card">
                                <div class="module-header">
                                    <div class="module-icon">💳</div>
                                    <div class="module-title">
                                        <span>Transaksi &amp; Keamanan</span>
                                        <h3>5. Pembayaran &amp; Anti-Fraud</h3>
                                    </div>
                                </div>
                                <ul class="module-items">
                                    <li><strong>Verifikasi Kasir Fleksibel (Aktif):</strong> Pembayaran Tunai, QRIS kafe, Transfer Bank, &amp; E-Wallet diverifikasi kasir dengan pencatatan shift rapi.</li>
                                    <li><strong>Payment Gateway Ready:</strong> Arsitektur modular siap dihubungkan ke penyedia payment gateway terkini kapan pun kafe Anda siap berkembang.</li>
                                    <li><strong>Anti-Fraud Geofencing GPS:</strong> Kunci opsi bayar tunai di meja jika pemesan terdeteksi di luar radius area kafe.</li>
                                    <li><strong>Proteksi Transaksi Dobel:</strong> Database locking (idempotency guard) &amp; pembersihan otomatis pesanan terbengkalai.</li>
                                </ul>
                                <div class="module-badge-list">
                                    <span class="badge-pill">Verifikasi Kasir</span>
                                    <span class="badge-pill">QRIS &amp; E-Wallet</span>
                                    <span class="badge-pill">Payment Gateway Ready</span>
                                    <span class="badge-pill">GPS Geofencing</span>
                                </div>
                            </article>

                            <!-- 6. Member & Loyalty -->
                            <article class="module-card">
                                <div class="module-header">
                                    <div class="module-icon">🎁</div>
                                    <div class="module-title">
                                        <span>Retensi Pelanggan</span>
                                        <h3>6. Member, Poin &amp; Promosi</h3>
                                    </div>
                                </div>
                                <ul class="module-items">
                                    <li><strong>Member Berbasis WhatsApp:</strong> Login cepat tanpa kartu fisik, kumpulkan poin setiap kali memesan.</li>
                                    <li><strong>Tingkatan Tier Member:</strong> Level Bronze, Silver, Gold, Platinum dengan benefit bertingkat.</li>
                                    <li><strong>Tantangan Belanja (Challenges):</strong> Misi belanja interaktif berhadiah bonus poin bagi pelanggan setia.</li>
                                    <li><strong>Promo &amp; Happy Hour Dinamis:</strong> Diskon nominal/persen, kuota per user, dan jadwal jam tertentu.</li>
                                    <li><strong>Gift Card Digital:</strong> Penerbitan voucher kartu hadiah dengan pelacakan saldo transaksi.</li>
                                </ul>
                                <div class="module-badge-list">
                                    <span class="badge-pill">Loyalty Points</span>
                                    <span class="badge-pill">Member Tiers</span>
                                    <span class="badge-pill">Happy Hour Promo</span>
                                    <span class="badge-pill">Gift Card</span>
                                </div>
                            </article>

                            <!-- 7. Analytics & Reporting -->
                            <article class="module-card">
                                <div class="module-header">
                                    <div class="module-icon">📊</div>
                                    <div class="module-title">
                                        <span>Insight Bisnis</span>
                                        <h3>7. Laporan Penjualan &amp; Analytics</h3>
                                    </div>
                                </div>
                                <ul class="module-items">
                                    <li><strong>Grafik Tren Penjualan:</strong> Visualisasi omzet harian, mingguan, dan bulanan (Chart.js).</li>
                                    <li><strong>Top Selling Menu:</strong> Identifikasi menu terlaris dan produk paling menghasilkan margin.</li>
                                    <li><strong>Performa Kasir &amp; Shift:</strong> Evaluasi akurasi penerimaan pembayaran per kasir yang bertugas.</li>
                                    <li><strong>Laporan Waste &amp; Varians:</strong> Pantau efisiensi penggunaan bahan baku terhadap food cost.</li>
                                    <li><strong>Ekspor CSV Streaming:</strong> Unduh data transaksi tanpa membebani memori server.</li>
                                </ul>
                                <div class="module-badge-list">
                                    <span class="badge-pill">Sales Charts</span>
                                    <span class="badge-pill">Daily Top Orders</span>
                                    <span class="badge-pill">Cashier Recap</span>
                                    <span class="badge-pill">CSV Export</span>
                                </div>
                            </article>

                            <!-- 8. Table & Role RBAC -->
                            <article class="module-card">
                                <div class="module-header">
                                    <div class="module-icon">🏢</div>
                                    <div class="module-title">
                                        <span>Organisasi Kafe</span>
                                        <h3>8. Denah Meja &amp; Hak Akses (RBAC)</h3>
                                    </div>
                                </div>
                                <ul class="module-items">
                                    <li><strong>Manajemen Area &amp; Meja:</strong> Tata letak meja (Indoor, Outdoor, Lantai 1/2) dengan status meja.</li>
                                    <li><strong>Antrean Meja (Table Queue):</strong> Kelola daftar tunggu tamu dengan estimasi waktu meja tersedia.</li>
                                    <li><strong>Cetak QR Meja Massal:</strong> Fitur download SVG &amp; cetak barcode QR code untuk setiap nomor meja.</li>
                                    <li><strong>Role Granular:</strong> Pembagian akses aman untuk Owner, Admin, Kasir, dan Kru Dapur/Barista.</li>
                                    <li><strong>Feature Toggle System:</strong> Fleksibel menyalakan atau mematikan modul sesuai kebutuhan kafe.</li>
                                </ul>
                                <div class="module-badge-list">
                                    <span class="badge-pill">Table Layout</span>
                                    <span class="badge-pill">Table Queue</span>
                                    <span class="badge-pill">QR Generator</span>
                                    <span class="badge-pill">RBAC Roles</span>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="menu-showcase" id="menu">
                    <div class="section-container menu-wrapper">
                        <div class="menu-copy">
                            <h3>Beri kebebasan tamu memilih menu dari meja mereka</h3>
                            <p>
                                Tidak perlu menunggu pelayan membawakan buku menu fisik. Pengunjung kafe dapat santai melihat foto minuman, memilih tingkat kemanisan, varian susu, hingga topping tambahan sesuai selera mereka.
                            </p>
                            <div class="menu-pills">
                                <span>#QRMejaOtomatis</span>
                                <span>#KustomisasiTopping</span>
                                <span>#PanggilPelayan</span>
                                <span>#GeofenceProtection</span>
                                <span>#ProgressiveCheckout</span>
                            </div>
                            <a href="#kontak" class="btn solid">Konsultasikan Implementasi Meja</a>
                        </div>
                        <div class="menu-card">
                            <div class="menu-card-content">
                                <div class="menu-header">
                                    <div>
                                        <h4>Tampilan Menu Tamu</h4>
                                        <span>Terhubung: Meja 05</span>
                                    </div>
                                    <span class="status">
                                        <span class="status-dot"></span>
                                        Area Kafe
                                    </span>
                                </div>
                                <div class="menu-item">
                                    <div>
                                        <strong>Signature Aren Latte</strong>
                                        <span>Espresso • Aren • Fresh Milk</span>
                                    </div>
                                    <strong>Rp22.000</strong>
                                </div>
                                <div class="menu-item">
                                    <div>
                                        <strong>Ice Creamy Matcha</strong>
                                        <span>Pure Uji Matcha • Oat Milk</span>
                                    </div>
                                    <strong>Rp26.000</strong>
                                </div>
                                <div class="menu-item">
                                    <div>
                                        <strong>Almond Cinnamon Roll</strong>
                                        <span>Warm flaky pastry</span>
                                    </div>
                                    <strong>Rp25.000</strong>
                                </div>
                                <div class="menu-footer">
                                    <small>Tamu bebas melihat menu &amp; tambah keranjang</small>
                                    <strong>Isi data saat pesan</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="workflow" id="kolaborasi">
                    <div class="section-container">
                        <div class="section-head">
                            <p>Pendekatan Kolaboratif</p>
                            <h2>3 Tahap Menerapkan Sistem di Kafe Anda</h2>
                            <span>Kami mendampingi proses implementasi agar kru kafe nyaman dan sistem benar-benar bekerja sesuai ritme harian Anda.</span>
                        </div>
                        <div class="workflow-steps">
                            <article class="step">
                                <div class="step-number">1</div>
                                <h3>Diskusi Alur &amp; Kebutuhan</h3>
                                <p>Kami memahami jumlah meja, layout area kafe, kategori menu, serta alur kerja yang sudah berjalan antara kasir dan barista Anda.</p>
                            </article>
                            <article class="step">
                                <div class="step-number">2</div>
                                <h3>Setup Menu, Resep &amp; Uji Coba</h3>
                                <p>Input master menu, komposisi bahan baku, nomor meja, hingga role akun staf. Kami adakan simulasi alur order bersama tim kafe.</p>
                            </article>
                            <article class="step">
                                <div class="step-number">3</div>
                                <h3>Go-Live &amp; Evaluasi Berkala</h3>
                                <p>Sistem mulai digunakan melayani pelanggan dengan pendampingan. Masukan kru kafe menjadi dasar perbaikan dan pembaruan berkala.</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="cta-banner" id="kontak">
                    <div class="section-container cta-inner">
                        <div>
                            <h3>Mari ngobrol dan bertumbuh bersama.</h3>
                            <p>Kami percaya teknologi terbaik adalah yang lahir dari empati terhadap kerepotan operasional sehari-hari. Diskusikan kebutuhan kafe Anda atau jadwalkan sesi coba langsung sistem ini.</p>
                        </div>
                        <div class="cta-actions">
                            <a class="btn solid" href="https://wa.me/6281234567800" target="_blank" rel="noopener">Chat via WhatsApp</a>
                            <a class="btn ghost" href="mailto:info@kodeeweb.id">Kirim Email Diskusi</a>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="footer">
                <div class="footer-copy">© {{ now()->year }} Labo By kodeeweb • Solusi Kasir &amp; Operasional Kafe yang Bertumbuh Bersama Mitra</div>
                <div class="footer-meta">Dibangun dengan Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }}) &amp; Filament POS</div>
            </footer>
        </div>
    </body>
</html>
