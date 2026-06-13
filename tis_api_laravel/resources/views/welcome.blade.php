<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TIS Gateway — MRT Jakarta</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #0b0e14;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
        }

        .page {
            width: min(1080px, calc(100% - 40px));
            margin: 0 auto;
            padding: 20px 0 40px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-icon svg {
            width: 20px;
            height: 20px;
            stroke: white;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .brand-name {
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: -0.02em;
        }

        .brand-sub {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-top: 1px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            padding: 0 20px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.15s, box-shadow 0.15s;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-outline {
            border: 1px solid #1e293b;
            color: #94a3b8;
        }

        .btn-outline:hover {
            border-color: #334155;
            color: #e2e8f0;
        }

        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 0;
        }

        .hero {
            max-width: 720px;
        }

        .hero h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1.1;
            color: #f1f5f9;
        }

        .hero h1 span {
            color: #60a5fa;
        }

        .hero p {
            margin-top: 20px;
            font-size: 1.1rem;
            color: #64748b;
            max-width: 560px;
            line-height: 1.7;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .capabilities {
            display: flex;
            gap: 32px;
            margin-top: 64px;
            padding-top: 32px;
            border-top: 1px solid #1e293b;
        }

        .capability {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #94a3b8;
            font-size: 0.875rem;
        }

        .capability-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .capability-dot.blue { background: #3b82f6; }
        .capability-dot.green { background: #22c55e; }
        .capability-dot.purple { background: #a855f7; }

        footer {
            display: flex;
            justify-content: space-between;
            padding-top: 24px;
            font-size: 0.8rem;
            color: #475569;
        }

        @media (max-width: 640px) {
            .page { width: min(100% - 32px, 1080px); }
            .capabilities { flex-direction: column; gap: 16px; }
            .hero-actions { flex-direction: column; }
            .hero-actions .btn { width: 100%; }
            footer { flex-direction: column; gap: 4px; }
        }
    </style>
</head>
<body>
    <div class="page">
        <nav>
            <a href="/" class="brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                    </svg>
                </div>
                <div>
                    <div class="brand-name">TIS Gateway</div>
                    <div class="brand-sub">MRT Jakarta</div>
                </div>
            </a>

            <div>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    @endauth
                @endif
            </div>
        </nav>

        <main>
            <div class="hero">
                <h1>Train fault intelligence,<br><span>built for operations</span></h1>
                <p>
                    TIS Gateway captures, monitors, and analyses fault telemetry from MRT Jakarta trainsets.
                    A focused operational tool, not a generic dashboard.
                </p>
                <div class="hero-actions">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Open Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">Login to System</a>
                    @endauth
                </div>
            </div>

            <div class="capabilities">
                <div class="capability">
                    <span class="capability-dot blue"></span>
                    Real-time fault monitoring
                </div>
                <div class="capability">
                    <span class="capability-dot green"></span>
                    Session recording & export
                </div>
                <div class="capability">
                    <span class="capability-dot purple"></span>
                    REST API integration
                </div>
            </div>
        </main>

        <footer>
            <span>&copy; {{ date('Y') }} PT MRT Jakarta</span>
            <span>Train Information System Gateway</span>
        </footer>
    </div>
</body>
</html>