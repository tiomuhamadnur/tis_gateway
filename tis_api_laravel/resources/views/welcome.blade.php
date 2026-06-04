<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TIS Gateway - MRT Jakarta</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|dm-sans:400,500,700" rel="stylesheet" />
    <style>
        :root {
            --bg: #06131a;
            --bg-soft: #0a1c24;
            --panel: rgba(7, 25, 34, 0.72);
            --panel-strong: rgba(10, 31, 43, 0.92);
            --line: rgba(120, 177, 198, 0.16);
            --line-strong: rgba(120, 177, 198, 0.3);
            --text: #e7f6fb;
            --muted: #8da8b3;
            --cyan: #47d7ff;
            --teal: #5af2c8;
            --amber: #ffbc58;
            --danger: #ff7272;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            min-height: 100vh;
            font-family: 'DM Sans', system-ui, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 15% 20%, rgba(71, 215, 255, 0.18), transparent 28%),
                radial-gradient(circle at 85% 18%, rgba(90, 242, 200, 0.12), transparent 24%),
                radial-gradient(circle at 50% 100%, rgba(38, 111, 140, 0.16), transparent 35%),
                linear-gradient(180deg, #071118 0%, #06131a 48%, #041017 100%);
            line-height: 1.5;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(115, 169, 189, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(115, 169, 189, 0.06) 1px, transparent 1px);
            background-size: 72px 72px;
            mask-image: radial-gradient(circle at center, black 42%, transparent 88%);
            pointer-events: none;
            z-index: 0;
        }

        a { color: inherit; text-decoration: none; }

        .page-shell {
            position: relative;
            z-index: 1;
            width: min(1240px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 48px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 18px;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: rgba(5, 18, 25, 0.72);
            backdrop-filter: blur(22px);
            box-shadow: 0 22px 60px rgba(0, 0, 0, 0.26);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(71, 215, 255, 0.18), rgba(90, 242, 200, 0.24));
            border: 1px solid rgba(90, 242, 200, 0.26);
            box-shadow: inset 0 0 28px rgba(71, 215, 255, 0.14);
        }

        .brand-mark svg {
            width: 24px;
            height: 24px;
            stroke: var(--cyan);
            fill: none;
        }

        .brand-title {
            font: 700 1rem/1 'Space Grotesk', sans-serif;
            letter-spacing: -0.03em;
        }

        .brand-subtitle,
        .status-pill,
        .eyebrow,
        .panel-label,
        .metric-label,
        .rail-label,
        .feature-kicker {
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.68rem;
        }

        .brand-subtitle { color: var(--muted); margin-top: 4px; }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            color: #b8dce8;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.02);
        }

        .status-pill::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--teal);
            box-shadow: 0 0 12px rgba(90, 242, 200, 0.8);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 14px;
            border: 1px solid transparent;
            font-weight: 700;
            transition: transform 160ms ease, border-color 160ms ease, background 160ms ease, color 160ms ease, box-shadow 160ms ease;
        }

        .btn:hover { transform: translateY(-1px); }

        .btn-outline {
            color: #d8eef6;
            border-color: var(--line);
            background: rgba(255, 255, 255, 0.02);
        }

        .btn-outline:hover {
            border-color: var(--line-strong);
            background: rgba(71, 215, 255, 0.08);
        }

        .btn-primary {
            color: #031017;
            background: linear-gradient(135deg, var(--teal), var(--cyan));
            box-shadow: 0 16px 38px rgba(71, 215, 255, 0.22);
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(330px, 0.85fr);
            gap: 28px;
            padding: 32px 0 0;
            align-items: stretch;
        }

        .hero-copy,
        .hero-visual,
        .insight-card,
        .feature-card,
        .cta {
            border: 1px solid var(--line);
            background: var(--panel);
            backdrop-filter: blur(20px);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.22);
        }

        .hero-copy {
            border-radius: 32px;
            padding: 34px;
            position: relative;
            overflow: hidden;
        }

        .hero-copy::after {
            content: '';
            position: absolute;
            inset: auto -8% -28% 36%;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(71, 215, 255, 0.18), transparent 64%);
            pointer-events: none;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            margin-bottom: 20px;
            color: #bfe9f6;
            border-radius: 999px;
            border: 1px solid rgba(90, 242, 200, 0.2);
            background: rgba(90, 242, 200, 0.08);
        }

        .eyebrow::before {
            content: '';
            width: 26px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--teal));
        }

        h1,
        h2,
        h3 {
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: -0.04em;
        }

        h1 {
            max-width: 9.5ch;
            font-size: clamp(3.2rem, 7vw, 6rem);
            line-height: 0.94;
        }

        .hero-copy p {
            max-width: 620px;
            margin-top: 20px;
            color: var(--muted);
            font-size: 1.04rem;
            line-height: 1.8;
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 28px;
        }

        .hero-meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 32px;
            position: relative;
            z-index: 1;
        }

        .metric {
            padding: 16px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.025);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .metric-value {
            font: 700 clamp(1.6rem, 3vw, 2.25rem)/1 'Space Grotesk', sans-serif;
            color: #f2feff;
        }

        .metric-label {
            color: var(--muted);
            margin-top: 10px;
        }

        .hero-visual {
            border-radius: 32px;
            padding: 22px;
            display: grid;
            gap: 16px;
        }

        .panel {
            border-radius: 24px;
            background: var(--panel-strong);
            border: 1px solid rgba(120, 177, 198, 0.12);
            padding: 18px;
        }

        .panel-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .panel-label { color: var(--muted); }

        .panel-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 999px;
            background: rgba(71, 215, 255, 0.08);
            border: 1px solid rgba(71, 215, 255, 0.18);
            color: #bcefff;
            font-size: 0.78rem;
        }

        .panel-chip::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--cyan);
            box-shadow: 0 0 10px rgba(71, 215, 255, 0.7);
        }

        .signal-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .signal-card {
            padding: 14px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.04);
        }

        .signal-card strong {
            display: block;
            font: 700 1.25rem/1 'Space Grotesk', sans-serif;
        }

        .signal-card span {
            display: block;
            margin-top: 8px;
            color: var(--muted);
            font-size: 0.82rem;
        }

        .signal-track {
            height: 170px;
            margin-top: 16px;
            border-radius: 22px;
            padding: 18px;
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.05), transparent),
                linear-gradient(180deg, rgba(71, 215, 255, 0.08), rgba(71, 215, 255, 0));
        }

        .signal-track::before,
        .signal-track::after {
            content: '';
            position: absolute;
            inset: 18px;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .signal-track::after {
            inset: auto 18px 36px;
            height: 1px;
            border: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
        }

        .trace {
            position: absolute;
            inset: 32px 22px 30px;
            display: flex;
            align-items: flex-end;
            gap: 12px;
        }

        .trace span {
            flex: 1;
            border-radius: 999px 999px 12px 12px;
            background: linear-gradient(180deg, rgba(71, 215, 255, 0.92), rgba(71, 215, 255, 0.08));
            box-shadow: 0 0 18px rgba(71, 215, 255, 0.2);
        }

        .trace span:nth-child(1) { height: 42%; }
        .trace span:nth-child(2) { height: 58%; }
        .trace span:nth-child(3) { height: 34%; }
        .trace span:nth-child(4) { height: 74%; }
        .trace span:nth-child(5) { height: 49%; }
        .trace span:nth-child(6) { height: 86%; background: linear-gradient(180deg, rgba(90, 242, 200, 0.95), rgba(90, 242, 200, 0.08)); }

        .rail {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .rail-card {
            padding: 16px;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(255, 255, 255, 0.025);
        }

        .rail-value {
            font: 700 1.2rem/1 'Space Grotesk', sans-serif;
            margin-top: 10px;
        }

        .rail-label { color: var(--muted); }

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
            gap: 28px;
            padding-top: 28px;
        }

        .insight-stack {
            display: grid;
            gap: 20px;
        }

        .insight-card {
            border-radius: 28px;
            padding: 26px;
        }

        .section-heading {
            margin-bottom: 18px;
        }

        .section-heading p {
            max-width: 560px;
            color: var(--muted);
            margin-top: 10px;
        }

        .timeline {
            display: grid;
            gap: 14px;
        }

        .timeline-item {
            display: grid;
            grid-template-columns: 110px minmax(0, 1fr) auto;
            gap: 14px;
            align-items: center;
            padding: 16px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .timeline-item:first-child { border-top: 0; padding-top: 0; }

        .timeline-time {
            color: #bfe9f6;
            font: 700 0.92rem/1 'Space Grotesk', sans-serif;
        }

        .timeline-title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .timeline-text {
            color: var(--muted);
            font-size: 0.92rem;
        }

        .severity {
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .severity-critical {
            color: #ffd1d1;
            background: rgba(255, 114, 114, 0.14);
            border: 1px solid rgba(255, 114, 114, 0.2);
        }

        .severity-watch {
            color: #ffe3ba;
            background: rgba(255, 188, 88, 0.14);
            border: 1px solid rgba(255, 188, 88, 0.18);
        }

        .severity-stable {
            color: #c9f8ee;
            background: rgba(90, 242, 200, 0.12);
            border: 1px solid rgba(90, 242, 200, 0.16);
        }

        .feature-list {
            display: grid;
            gap: 14px;
        }

        .feature-card {
            border-radius: 22px;
            padding: 18px;
        }

        .feature-kicker {
            color: var(--cyan);
            margin-bottom: 10px;
        }

        .feature-card p {
            color: var(--muted);
            margin-top: 8px;
            font-size: 0.94rem;
        }

        .cta {
            margin-top: 28px;
            border-radius: 28px;
            padding: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .cta p {
            color: var(--muted);
            margin-top: 10px;
            max-width: 560px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            color: var(--muted);
            font-size: 0.84rem;
            padding: 20px 6px 0;
        }

        @media (max-width: 1080px) {
            .hero,
            .content-grid {
                grid-template-columns: 1fr;
            }

            h1 { max-width: none; }
        }

        @media (max-width: 760px) {
            .page-shell {
                width: min(100% - 20px, 1240px);
                padding-top: 10px;
            }

            .topbar,
            .cta,
            .footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .hero-copy,
            .hero-visual,
            .insight-card,
            .feature-card,
            .cta {
                border-radius: 24px;
            }

            .hero-copy,
            .hero-visual,
            .insight-card,
            .cta {
                padding: 22px;
            }

            .hero-meta,
            .rail,
            .signal-grid {
                grid-template-columns: 1fr;
            }

            .timeline-item {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: clamp(2.6rem, 14vw, 4.2rem);
                line-height: 0.98;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <nav class="topbar">
            <a href="/" class="brand">
                <div class="brand-mark">
                    <svg viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 14.75h5.25L12 5l3.75 14 2.25-6H21" />
                    </svg>
                </div>
                <div>
                    <div class="brand-title">TIS Gateway</div>
                    <div class="brand-subtitle">Traction Intelligence Layer • MRT Jakarta</div>
                </div>
            </a>

            <div class="topbar-right">
                <div class="status-pill">Live telemetry active</div>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Open dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline">Masuk</a>
                    @endauth
                @endif
            </div>
        </nav>

        <section class="hero">
            <div class="hero-copy">
                <div class="eyebrow">Traction monitoring platform</div>
                <h1>Operasi yang terlihat sebelum gangguan terasa.</h1>
                <p>
                    TIS Gateway menyatukan fault telemetry, histori session, dan insight pemeliharaan ke dalam satu
                    layer operasional yang lebih presisi. Tampilan dibuat untuk kontrol cepat, bukan sekadar landing page generik.
                </p>

                <div class="hero-actions">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Masuk ke dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">Masuk ke sistem</a>
                    @endauth
                    <a href="#insight" class="btn btn-outline">Lihat capability</a>
                </div>

                <div class="hero-meta">
                    <div class="metric">
                        <div class="metric-value">16</div>
                        <div class="metric-label">Active trainset profile</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">24/7</div>
                        <div class="metric-label">Continuous stream capture</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">REST</div>
                        <div class="metric-label">Gateway for ingest and export</div>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="panel">
                    <div class="panel-top">
                        <div>
                            <div class="panel-label">Realtime supervision</div>
                            <h3>Network signal overview</h3>
                        </div>
                        <div class="panel-chip">Synced</div>
                    </div>

                    <div class="signal-grid">
                        <div class="signal-card">
                            <strong>03</strong>
                            <span>Faults flagged for review</span>
                        </div>
                        <div class="signal-card">
                            <strong>11s</strong>
                            <span>Median response visibility</span>
                        </div>
                        <div class="signal-card">
                            <strong>98.4%</strong>
                            <span>Capture integrity</span>
                        </div>
                    </div>

                    <div class="signal-track">
                        <div class="trace">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>

                <div class="rail">
                    <div class="rail-card">
                        <div class="rail-label">Focus area</div>
                        <div class="rail-value">TCMS faults</div>
                    </div>
                    <div class="rail-card">
                        <div class="rail-label">Export mode</div>
                        <div class="rail-value">Excel / PDF</div>
                    </div>
                    <div class="rail-card">
                        <div class="rail-label">User access</div>
                        <div class="rail-value">Role-based</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="content-grid" id="insight">
            <div class="insight-stack">
                <div class="insight-card">
                    <div class="section-heading">
                        <div class="eyebrow">Operational flow</div>
                        <h2>Dari event mentah ke keputusan maintenance.</h2>
                        <p>
                            Fokus utamanya bukan dekorasi, tapi rasa kontrol. Setiap blok dirancang seperti panel operasi:
                            status cepat, event kritis, dan jalur tindakan yang langsung terbaca.
                        </p>
                    </div>

                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-time">00:00-06:00</div>
                            <div>
                                <div class="timeline-title">Session ingest dan sinkronisasi parser</div>
                                <div class="timeline-text">Data onboard diterima, dipetakan ke equipment, lalu dipersiapkan untuk analitik fault.</div>
                            </div>
                            <div class="severity severity-stable">Stable</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-time">06:00-18:00</div>
                            <div>
                                <div class="timeline-title">Monitoring fault aktif per trainset</div>
                                <div class="timeline-text">Operator bisa membaca fault code, gerbong, timestamp, dan recovery path dari satu antarmuka.</div>
                            </div>
                            <div class="severity severity-watch">Watch</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-time">18:00-24:00</div>
                            <div>
                                <div class="timeline-title">Export dan evaluasi tren harian</div>
                                <div class="timeline-text">Rekap Excel/PDF dan pareto equipment dipakai untuk prioritas inspeksi berikutnya.</div>
                            </div>
                            <div class="severity severity-critical">Priority</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="feature-list">
                <div class="feature-card">
                    <div class="feature-kicker">01 • Analytics</div>
                    <h3>Dashboard yang terasa seperti command surface.</h3>
                    <p>Distribusi fault, tren session, dan equipment paling bermasalah ditampilkan sebagai insight operasional, bukan widget tempelan.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-kicker">02 • Traceability</div>
                    <h3>Failure records yang lebih mudah diaudit.</h3>
                    <p>Kode fault, deskripsi, gerbong, durasi, dan klasifikasi tetap jadi inti, tapi disajikan dengan hierarki visual yang lebih tegas.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-kicker">03 • Secure exchange</div>
                    <h3>REST gateway untuk alur data yang disiplin.</h3>
                    <p>API key, upload file, dan akses statistik diposisikan sebagai bagian dari arsitektur kontrol data, bukan fitur sampingan.</p>
                </div>
            </div>
        </section>

        <section class="cta">
            <div>
                <div class="eyebrow">Ready for operations</div>
                <h2>Buka workspace monitoring dan lanjutkan ke fault intelligence.</h2>
                <p>Landing page ini sekarang lebih tajam, lebih modern, dan lebih nyambung ke sistem rail operations dibanding gaya template AI yang generik.</p>
            </div>
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-primary">Open dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Masuk sekarang</a>
            @endauth
        </section>

        <footer class="footer">
            <span>&copy; {{ date('Y') }} PT MRT Jakarta</span>
            <span>Traction Information System Gateway</span>
        </footer>
    </div>
</body>
</html>
