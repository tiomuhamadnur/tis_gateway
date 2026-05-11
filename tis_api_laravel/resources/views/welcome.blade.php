<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TIS Gateway — MRT Jakarta</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            min-height: 100vh;
            line-height: 1.6;
        }

        a { text-decoration: none; color: inherit; }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.1rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            position: sticky;
            top: 0;
            background: rgba(15,23,42,0.92);
            backdrop-filter: blur(12px);
            z-index: 100;
        }

        .nav-brand { display: flex; align-items: center; gap: 0.75rem; }

        .nav-logo {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(59,130,246,0.35);
            flex-shrink: 0;
        }
        .nav-logo svg { width: 20px; height: 20px; stroke: white; fill: none; }

        .nav-title { font-size: 0.95rem; font-weight: 700; letter-spacing: -0.02em; }
        .nav-subtitle { font-size: 0.65rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: #60a5fa; }

        .nav-actions { display: flex; gap: 0.75rem; }

        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            font-size: 0.875rem; font-weight: 500;
            transition: all 0.15s ease;
            border: none; cursor: pointer;
        }
        .btn-ghost {
            color: #94a3b8; background: transparent;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-ghost:hover { background: rgba(255,255,255,0.06); color: #f1f5f9; }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 4px 14px rgba(59,130,246,0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 4px 18px rgba(59,130,246,0.5);
            transform: translateY(-1px);
        }
        .btn-lg { padding: 0.85rem 2rem; font-size: 1rem; font-weight: 600; border-radius: 12px; }
        .btn-lg svg { width: 18px; height: 18px; }

        /* Hero */
        .hero {
            text-align: center;
            padding: 6rem 2rem 5rem;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; top: -180px; left: 50%;
            transform: translateX(-50%);
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(59,130,246,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .badge {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.35rem 1rem;
            background: rgba(59,130,246,0.12);
            border: 1px solid rgba(59,130,246,0.28);
            border-radius: 100px;
            font-size: 0.78rem; font-weight: 500; color: #60a5fa;
            margin-bottom: 1.5rem;
        }
        .badge svg { width: 12px; height: 12px; stroke: #60a5fa; fill: none; }

        .hero h1 {
            font-size: clamp(2.4rem, 5vw, 3.75rem);
            font-weight: 700; letter-spacing: -0.04em; line-height: 1.1;
            margin-bottom: 1.25rem;
            background: linear-gradient(160deg, #f1f5f9 40%, #64748b);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero p {
            font-size: 1.15rem; color: #94a3b8;
            max-width: 540px; margin: 0 auto 2.5rem;
            line-height: 1.7;
        }
        .hero-actions { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }

        /* Stats bar */
        .stats-bar {
            display: flex; justify-content: center; gap: 0;
            border-top: 1px solid rgba(255,255,255,0.06);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .stat-item {
            flex: 1; max-width: 200px;
            text-align: center; padding: 2.25rem 1rem;
            border-right: 1px solid rgba(255,255,255,0.06);
        }
        .stat-item:last-child { border-right: none; }
        .stat-number { font-size: 1.9rem; font-weight: 700; color: #3b82f6; letter-spacing: -0.04em; }
        .stat-label { font-size: 0.8rem; color: #64748b; margin-top: 0.2rem; }

        /* Features section */
        .section { max-width: 1100px; margin: 0 auto; padding: 5rem 2rem; }
        .section-header { text-align: center; margin-bottom: 3.5rem; }
        .section-label {
            font-size: 0.75rem; font-weight: 600; letter-spacing: 0.12em;
            text-transform: uppercase; color: #3b82f6; margin-bottom: 0.75rem;
        }
        .section-title {
            font-size: 2.1rem; font-weight: 700; letter-spacing: -0.03em;
            color: #f1f5f9; margin-bottom: 0.75rem;
        }
        .section-desc { font-size: 0.95rem; color: #64748b; max-width: 460px; margin: 0 auto; }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.25rem;
        }
        .feature-card {
            background: rgba(255,255,255,0.025);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px; padding: 1.6rem;
            transition: all 0.2s ease;
        }
        .feature-card:hover {
            background: rgba(255,255,255,0.045);
            border-color: rgba(59,130,246,0.25);
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.3);
        }
        .feature-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
        }
        .feature-icon svg { width: 22px; height: 22px; stroke: currentColor; fill: none; stroke-width: 1.8; }

        .icon-blue  { background: rgba(59,130,246,0.15);  color: #60a5fa; }
        .icon-red   { background: rgba(239,68,68,0.15);   color: #f87171; }
        .icon-green { background: rgba(34,197,94,0.15);   color: #4ade80; }
        .icon-purple{ background: rgba(168,85,247,0.15);  color: #c084fc; }
        .icon-orange{ background: rgba(249,115,22,0.15);  color: #fb923c; }
        .icon-cyan  { background: rgba(6,182,212,0.15);   color: #22d3ee; }

        .feature-title { font-size: 1rem; font-weight: 600; color: #f1f5f9; margin-bottom: 0.45rem; }
        .feature-desc { font-size: 0.875rem; color: #64748b; line-height: 1.65; }

        /* CTA section */
        .cta {
            text-align: center; padding: 5rem 2rem;
            background: linear-gradient(180deg, rgba(59,130,246,0.05) 0%, transparent 100%);
            border-top: 1px solid rgba(59,130,246,0.1);
        }
        .cta h2 { font-size: 2rem; font-weight: 700; letter-spacing: -0.03em; margin-bottom: 0.75rem; }
        .cta p { color: #64748b; margin-bottom: 2rem; }

        /* Footer */
        footer {
            text-align: center; padding: 1.75rem 2rem;
            color: #334155; font-size: 0.8rem;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        @media (max-width: 640px) {
            nav { padding: 1rem 1.25rem; }
            .nav-subtitle { display: none; }
            .hero { padding: 4rem 1.25rem 3rem; }
            .stats-bar { flex-wrap: wrap; }
            .stat-item { border-right: none; border-bottom: 1px solid rgba(255,255,255,0.06); }
            .section { padding: 3.5rem 1.25rem; }
            .features-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav>
        <a href="/" class="nav-brand">
            <div class="nav-logo">
                <svg viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                </svg>
            </div>
            <div>
                <div class="nav-title">TIS Gateway</div>
                <div class="nav-subtitle">MRT Jakarta</div>
            </div>
        </a>

        @if (Route::has('login'))
            <div class="nav-actions">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost">Masuk</a>
                @endauth
            </div>
        @endif
    </nav>

    <section class="hero">
        <div class="badge">
            <svg viewBox="0 0 24 24" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Platform Monitoring Sistem Traksi MRT Jakarta
        </div>
        <h1>Traction Information<br>System Gateway</h1>
        <p>Platform terpusat untuk pemantauan, pencatatan, dan analisis data kegagalan sistem traksi pada trainset MRT Jakarta secara real-time.</p>
        <div class="hero-actions">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-lg">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Buka Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                    Masuk ke Sistem
                </a>
            @endauth
        </div>
    </section>

    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-number">16</div>
            <div class="stat-label">Trainset Aktif</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">3</div>
            <div class="stat-label">Klasifikasi Fault</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">REST</div>
            <div class="stat-label">API Gateway</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">24/7</div>
            <div class="stat-label">Monitoring</div>
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <div class="section-label">Fitur Sistem</div>
            <h2 class="section-title">Semua yang Anda butuhkan</h2>
            <p class="section-desc">Dari monitoring real-time hingga analitik mendalam, TIS Gateway menyediakan tools lengkap untuk operasional MRT Jakarta.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon icon-blue">
                    <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M18.4 9a9 9 0 11-14.5 9"/></svg>
                </div>
                <div class="feature-title">Dashboard Analitik</div>
                <div class="feature-desc">Visualisasi data kegagalan per trainset, distribusi klasifikasi fault, dan top equipment bermasalah dengan chart interaktif dan filter rentang tanggal.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon icon-red">
                    <svg viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="feature-title">Failure Records</div>
                <div class="feature-desc">Pencatatan detail setiap kejadian fault: kode, deskripsi, gerbong, waktu terjadi, durasi pemulihan, dan klasifikasi Heavy / Light / Info.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon icon-green">
                    <svg viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div class="feature-title">Analitik Pareto</div>
                <div class="feature-desc">Identifikasi equipment dengan angka kegagalan tertinggi menggunakan analisis Pareto untuk prioritas pemeliharaan dan perbaikan armada.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon icon-purple">
                    <svg viewBox="0 0 24 24"><path d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="feature-title">REST API Gateway</div>
                <div class="feature-desc">API endpoint terproteksi dengan API key untuk submit data dari sistem onboard, query sessions, upload file, dan akses statistik real-time.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon icon-orange">
                    <svg viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="feature-title">Export Laporan</div>
                <div class="feature-desc">Ekspor data failure records ke format Excel dan PDF dengan filter tanggal, trainset, dan klasifikasi yang fleksibel sesuai kebutuhan laporan operasional.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon icon-cyan">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/></svg>
                </div>
                <div class="feature-title">Manajemen Pengguna</div>
                <div class="feature-desc">Kontrol akses berbasis peran — superadmin, operator, dan viewer — dengan manajemen izin granular untuk keamanan data operasional.</div>
            </div>
        </div>
    </div>

    <div class="cta">
        <h2>Siap memulai?</h2>
        <p>Masuk untuk mengakses dashboard dan monitoring sistem traksi MRT Jakarta.</p>
        @auth
            <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-lg">Buka Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Masuk ke Sistem</a>
        @endauth
    </div>

    <footer>
        © {{ date('Y') }} PT MRT Jakarta — Traction Information System Gateway
    </footer>

</body>
</html>
