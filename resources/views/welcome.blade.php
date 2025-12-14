<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>POS by Kodeweb | Sistem Kasir Kuliner</title>
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
                max-width: 1200px;
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
            .brand-copy small { color: var(--text-muted); font-weight: 500; }
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
                transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
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
            .hero { padding: 4rem 0 3rem; }
            .hero-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 2.5rem;
                align-items: center;
            }
            .hero-eyebrow {
                text-transform: uppercase;
                font-size: 0.9rem;
                letter-spacing: 0.15em;
                color: var(--orange-dark);
                font-weight: 600;
            }
            .hero-copy h1 {
                font-size: clamp(2.3rem, 4vw, 3.8rem);
                line-height: 1.1;
                margin: 0.8rem 0;
            }
            .hero-copy .lead { color: var(--text-muted); font-size: 1.05rem; margin-bottom: 1.5rem; }
            .hero-cta { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; }
            .hero-benefits {
                list-style: none;
                padding: 0;
                margin: 0 0 1.2rem;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 0.8rem;
            }
            .hero-benefits li {
                display: flex;
                gap: 0.6rem;
                align-items: flex-start;
                background: rgba(255, 255, 255, 0.8);
                border-radius: 16px;
                padding: 0.85rem 1rem;
                border: 1px solid var(--border);
                font-weight: 500;
            }
            .hero-benefits span { font-size: 1.2rem; }
            .hero-badges { display: flex; flex-wrap: wrap; gap: 1rem; }
            .hero-badge {
                background: #fff;
                border-radius: 18px;
                padding: 0.9rem 1.1rem;
                border: 1px solid var(--border);
                min-width: 140px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            }
            .hero-badge strong { display: block; font-size: 1.2rem; }
            .hero-badge small { color: var(--text-muted); font-size: 0.85rem; }
            .hero-card {
                background: var(--card);
                border-radius: 30px;
                border: 1px solid rgba(255, 255, 255, 0.8);
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
                background: radial-gradient(circle at 80% -10%, rgba(255, 209, 102, 0.4), transparent 50%);
                z-index: 0;
            }
            .hero-card-content { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 1.25rem; }
            .ticket-header { display: flex; justify-content: space-between; gap: 1rem; align-items: center; }
            .ticket-title { margin: 0; font-weight: 600; font-size: 1.05rem; }
            .ticket-meta { margin: 0; color: var(--text-muted); font-size: 0.9rem; }
            .status {
                background: rgba(52, 198, 134, 0.12);
                color: var(--green);
                padding: 0.25rem 0.85rem;
                border-radius: 999px;
                font-size: 0.85rem;
                font-weight: 600;
            }
            .ticket-items { display: flex; flex-direction: column; gap: 1rem; }
            .ticket-items article { display: flex; justify-content: space-between; gap: 1rem; }
            .ticket-items h4 { margin: 0 0 0.15rem; font-size: 1rem; }
            .ticket-items span { color: var(--text-muted); font-size: 0.9rem; }
            .ticket-items strong { font-size: 1rem; }
            .ticket-total {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding-top: 1rem;
                border-top: 1px dashed rgba(34, 28, 24, 0.15);
            }
            .ticket-total strong { font-size: 1.5rem; }
            .ticket-total button {
                border: none;
                background: var(--green);
                color: #fff;
                padding: 0.75rem 1.3rem;
                border-radius: 16px;
                font-weight: 600;
                cursor: pointer;
                box-shadow: 0 15px 30px rgba(0, 168, 120, 0.25);
            }
            .ticket-total button:hover { background: #009368; }
            .section-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 1.5rem;
                position: relative;
                z-index: 1;
            }
            .main { position: relative; z-index: 1; }
            .stats { padding: 1rem 0 3rem; }
            .stats-grid {
                background: #fff;
                border-radius: 28px;
                border: 1px solid var(--border);
                box-shadow: var(--shadow);
                padding: 2rem;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 1.5rem;
            }
            .stat-card strong { display: block; font-size: 2.2rem; }
            .stat-card span { color: var(--text-muted); font-size: 0.95rem; }
            .features { padding: 3rem 0; }
            .section-head {
                text-align: center;
                max-width: 640px;
                margin: 0 auto 2.5rem;
            }
            .section-head p {
                color: var(--orange-dark);
                font-weight: 600;
                letter-spacing: 0.18em;
                font-size: 0.85rem;
                text-transform: uppercase;
            }
            .section-head h2 { font-size: 2.2rem; margin: 0.5rem 0 0.8rem; }
            .section-head span { color: var(--text-muted); }
            .feature-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 1.5rem;
            }
            .feature-card {
                background: #fff;
                border-radius: 24px;
                padding: 1.75rem;
                border: 1px solid var(--border);
                box-shadow: 0 18px 45px rgba(20, 16, 11, 0.05);
                display: flex;
                flex-direction: column;
                gap: 0.6rem;
            }
            .feature-icon {
                width: 56px;
                height: 56px;
                border-radius: 16px;
                background: rgba(255, 123, 84, 0.12);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.4rem;
            }
            .feature-card p { color: var(--text-muted); margin: 0; font-size: 0.95rem; }
            .menu-showcase { padding: 3.5rem 0; }
            .menu-wrapper {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 2rem;
                align-items: center;
                background: #fff7ec;
                border-radius: 32px;
                padding: 2.5rem;
                border: 1px solid rgba(255, 123, 84, 0.2);
                box-shadow: 0 30px 60px rgba(255, 123, 84, 0.12);
            }
            .menu-copy h3 { font-size: 2rem; margin-bottom: 0.75rem; }
            .menu-copy p { color: var(--text-muted); margin-bottom: 1.25rem; }
            .menu-pills { display: flex; flex-wrap: wrap; gap: 0.6rem; margin-bottom: 1.5rem; }
            .menu-pills span {
                padding: 0.4rem 0.9rem;
                border-radius: 999px;
                background: #fff;
                border: 1px solid rgba(255, 123, 84, 0.25);
                font-weight: 500;
            }
            .menu-card {
                background: #fff;
                border-radius: 28px;
                padding: 1.75rem;
                border: 1px solid rgba(34, 28, 24, 0.08);
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
                position: relative;
                overflow: hidden;
            }
            .menu-card::after {
                content: "";
                position: absolute;
                inset: 0;
                background-image: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80');
                background-size: cover;
                background-position: center;
                opacity: 0.08;
                z-index: 0;
            }
            .menu-card-content { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 1rem; }
            .menu-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
            .menu-header h4 { margin: 0; }
            .menu-header span { color: var(--text-muted); font-size: 0.9rem; }
            .menu-item {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                border-bottom: 1px dashed rgba(34, 28, 24, 0.15);
                padding-bottom: 0.75rem;
            }
            .menu-item:last-child { border-bottom: none; padding-bottom: 0; }
            .menu-item span { color: var(--text-muted); font-size: 0.9rem; }
            .menu-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-weight: 600;
            }
            .menu-footer small { color: var(--text-muted); font-weight: 500; }
            .workflow { padding: 3.5rem 0; }
            .workflow-steps {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 1.5rem;
            }
            .step {
                background: #fff;
                border-radius: 24px;
                padding: 1.5rem;
                border: 1px solid var(--border);
                box-shadow: 0 18px 40px rgba(0, 0, 0, 0.05);
            }
            .step-number {
                width: 40px;
                height: 40px;
                border-radius: 12px;
                background: var(--orange);
                color: #fff;
                font-weight: 600;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 0.75rem;
                box-shadow: 0 12px 25px rgba(255, 123, 84, 0.35);
            }
            .step p { color: var(--text-muted); margin: 0; }
            .cta-banner { padding: 3rem 1.5rem 4rem; }
            .cta-inner {
                background: linear-gradient(135deg, var(--orange) 0%, var(--orange-dark) 100%);
                border-radius: 32px;
                color: #fff;
                padding: 3rem;
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                box-shadow: 0 30px 60px rgba(255, 123, 84, 0.35);
            }
            .cta-inner h3 { margin: 0; font-size: 2.2rem; }
            .cta-inner p { margin: 0; font-size: 1.05rem; }
            .cta-actions { display: flex; flex-wrap: wrap; gap: 0.8rem; }
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
            .footer {
                text-align: center;
                padding: 2rem 1.5rem 3rem;
                color: var(--text-muted);
                font-size: 0.95rem;
            }
            @media (max-width: 768px) {
                .nav-links { width: 100%; justify-content: center; }
                .auth-links { width: 100%; justify-content: flex-start; }
                .hero-cta { flex-direction: column; align-items: flex-start; }
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
                            <span>POS by Kodeweb</span>
                            <small>Kitchen-to-cashier lebih cepat</small>
                        </div>
                    </div>
                    <div class="nav-links">
                        <a href="#fitur">Fitur</a>
                        <a href="#menu">Menu Digital</a>
                        <a href="#alur">Alur</a>
                        <a href="#kontak">Kontak</a>
                    </div>
                    @if (Route::has('login'))
                        <div class="auth-links">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn ghost">Buka Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="btn ghost">Masuk</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn solid">Coba Gratis</a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </nav>

                <header class="hero" id="beranda">
                    <div class="hero-grid">
                        <div class="hero-copy">
                            <span class="hero-eyebrow">Solusi POS Restoran &amp; Cafe</span>
                            <h1>Semua alur dapur, kasir, dan delivery jadi lebih manis untuk tim makananmu.</h1>
                            <p class="lead">
                                Kodeweb POS menghubungkan meja, dapur, dan pembayaran dalam satu platform ramah kru.
                                Cocok untuk coffee shop, kedai cepat saji, hingga resto multi-outlet.
                            </p>
                            <div class="hero-cta">
                                <a href="#kontak" class="btn solid">Jadwalkan Demo</a>
                                <a href="#fitur" class="btn ghost">Lihat Fitur</a>
                            </div>
                            <ul class="hero-benefits">
                                <li><span>🍜</span>Sinkron kitchen display &amp; kasir real-time.</li>
                                <li><span>🍹</span>Atur promo bundling &amp; happy hour hitungan detik.</li>
                                <li><span>🛵</span>Kelola pickup, delivery, dan marketplace dalam satu layar.</li>
                            </ul>
                            <div class="hero-badges">
                                <div class="hero-badge">
                                    <strong>+38%</strong>
                                    <small>Lebih cepat menyajikan pesanan dine-in</small>
                                </div>
                                <div class="hero-badge">
                                    <strong>3x</strong>
                                    <small>Percepatan tutup kas harian</small>
                                </div>
                            </div>
                        </div>
                        <div class="hero-card">
                            <div class="hero-card-content">
                                <div class="ticket-header">
                                    <div>
                                        <p class="ticket-title">Order #A082 - Meja 12</p>
                                        <p class="ticket-meta">Chef Nina • 3 menit lalu</p>
                                    </div>
                                    <span class="status">Sedang dimasak</span>
                                </div>
                                <div class="ticket-items">
                                    <article>
                                        <div>
                                            <h4>Rice Bowl Sambal Matah</h4>
                                            <span>+ Telur mata sapi</span>
                                        </div>
                                        <strong>Rp45K</strong>
                                    </article>
                                    <article>
                                        <div>
                                            <h4>Matcha Latte Oat</h4>
                                            <span>Dingin • less sugar</span>
                                        </div>
                                        <strong>Rp32K</strong>
                                    </article>
                                    <article>
                                        <div>
                                            <h4>Waffle Choco Berry</h4>
                                            <span>Extra sauce stroberi</span>
                                        </div>
                                        <strong>Rp38K</strong>
                                    </article>
                                </div>
                                <div class="ticket-total">
                                    <div>
                                        <p class="ticket-meta">Estimasi siap</p>
                                        <strong>12:14</strong>
                                    </div>
                                    <button type="button">Mark as Served</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            </div>

            <main class="main">
                <section class="stats" aria-label="Statistik utama Kodeweb POS">
                    <div class="section-container stats-grid">
                        <div class="stat-card">
                            <p>Brand kuliner aktif</p>
                            <strong>850+</strong>
                            <span>Resto, cafe, kedai, cloud kitchen</span>
                        </div>
                        <div class="stat-card">
                            <p>Transaksi tercatat</p>
                            <strong>12.5M</strong>
                            <span>Order per tahun terintegrasi</span>
                        </div>
                        <div class="stat-card">
                            <p>Uptime sistem</p>
                            <strong>99.9%</strong>
                            <span>Dijaga 24/7 cloud Kodeweb</span>
                        </div>
                        <div class="stat-card">
                            <p>Modul siap pakai</p>
                            <strong>30+</strong>
                            <span>Kasir, inventori, loyalty, delivery</span>
                        </div>
                    </div>
                </section>

                <section class="features" id="fitur">
                    <div class="section-container">
                        <div class="section-head">
                            <p>Kenapa Kodeweb POS</p>
                            <h2>Rancang khusus untuk alur makanan &amp; minuman</h2>
                            <span>Mulai dari dapur, barista, hingga owner—semua mendapatkan insight yang dibutuhkan.</span>
                        </div>
                        <div class="feature-grid">
                            <article class="feature-card">
                                <div class="feature-icon">👩🏽‍🍳</div>
                                <h3>Kitchen Display &amp; Bump Screen</h3>
                                <p>Pantau antrean masak tanpa kertas. Notifikasi otomatis saat pesanan siap.</p>
                            </article>
                            <article class="feature-card">
                                <div class="feature-icon">🧋</div>
                                <h3>Menu &amp; Promo Dinamis</h3>
                                <p>Update harga seasonal, paket sharing, hingga add-on topping langsung tampil di kasir.</p>
                            </article>
                            <article class="feature-card">
                                <div class="feature-icon">📦</div>
                                <h3>Inventori Bahan Baku</h3>
                                <p>Kurangi waste lewat auto-deduction per resep dan alert stok kritis.</p>
                            </article>
                            <article class="feature-card">
                                <div class="feature-icon">📊</div>
                                <h3>Insight Penjualan Real-time</h3>
                                <p>Lihat menu terlaris, performa shift, dan margin outlet kapan pun dari dashboard owner.</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="menu-showcase" id="menu">
                    <div class="section-container menu-wrapper">
                        <div class="menu-copy">
                            <h3>Menu digital yang bikin pelanggan lapar mata</h3>
                            <p>
                                Tampilkan foto menggoda, highlight rekomendasi chef, hingga upsell topping favorit.
                                Kasir tinggal ketuk dan order langsung terbaca di dapur.
                            </p>
                            <div class="menu-pills">
                                <span>#digitalmenu</span>
                                <span>#touchlessorder</span>
                                <span>#foodphotography</span>
                            </div>
                            <a href="#kontak" class="btn solid">Aktifkan Menu QR</a>
                        </div>
                        <div class="menu-card">
                            <div class="menu-card-content">
                                <div class="menu-header">
                                    <div>
                                        <h4>Menu Hari Ini</h4>
                                        <span>Chef special</span>
                                    </div>
                                    <span class="status">QR 028</span>
                                </div>
                                <div class="menu-item">
                                    <div>
                                        <strong>Truffle Fries</strong>
                                        <span>Rosemary • parmesan</span>
                                    </div>
                                    <strong>Rp32K</strong>
                                </div>
                                <div class="menu-item">
                                    <div>
                                        <strong>Chicken Katsu Ramen</strong>
                                        <span>Choice spicy • original</span>
                                    </div>
                                    <strong>Rp58K</strong>
                                </div>
                                <div class="menu-item">
                                    <div>
                                        <strong>Berry Breeze Mocktail</strong>
                                        <span>Blueberry • soda</span>
                                    </div>
                                    <strong>Rp36K</strong>
                                </div>
                                <div class="menu-footer">
                                    <small>Dilihat 124x hari ini</small>
                                    <strong>Swipe untuk tambah</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="workflow" id="alur">
                    <div class="section-container">
                        <div class="section-head">
                            <p>Alur Implementasi</p>
                            <h2>Go-live POS dalam 3 langkah</h2>
                            <span>Tim Kodeweb dampingi dari training crew sampai monitoring hari pertama.</span>
                        </div>
                        <div class="workflow-steps">
                            <article class="step">
                                <div class="step-number">1</div>
                                <h3>Audit outlet &amp; menu</h3>
                                <p>Mapping device, varian menu, resep, hingga channel penjualan.</p>
                            </article>
                            <article class="step">
                                <div class="step-number">2</div>
                                <h3>Setup &amp; training</h3>
                                <p>Input menu, stok awal, user role, lalu sesi latihan kasir + kitchen.</p>
                            </article>
                            <article class="step">
                                <div class="step-number">3</div>
                                <h3>Support &amp; eskalasi</h3>
                                <p>Monitoring live order, hotline WhatsApp, serta laporan performa mingguan.</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="cta-banner" id="kontak">
                    <div class="section-container cta-inner">
                        <div>
                            <h3>Siap bikin antrean makin cepat?</h3>
                            <p>Hubungi tim Kodeweb untuk demo langsung POS yang dirancang khusus bisnis kuliner.</p>
                        </div>
                        <div class="cta-actions">
                            <a class="btn solid" href="mailto:sales@kodeweb.id">Email Sales</a>
                            <a class="btn ghost" href="https://wa.me/6281234567800" target="_blank" rel="noopener">Chat WhatsApp</a>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="footer">
                <div>© {{ now()->year }} Kodeweb • POS untuk pelaku kuliner Indonesia</div>
                <div>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</div>
            </footer>
        </div>
    </body>
</html>
