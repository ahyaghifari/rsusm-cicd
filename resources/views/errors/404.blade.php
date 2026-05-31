<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8f9fb;
            color: #111827;
            padding: 2rem;
        }

        .card {
            background: white;
            border-radius: 24px;
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.04), 0 2px 4px -1px rgba(0,0,0,.04);
            border: 1px solid #f0f0f5;
        }

        /* — Illustration — */
        .illustration {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.75rem;
            height: 140px;
        }

        .number {
            font-size: clamp(5.5rem, 18vw, 8.5rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
            background: linear-gradient(135deg, #d606b0 30%, #ff8fd6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 1;
        }

        .bg-cross {
            position: absolute;
            opacity: 0.05;
        }

        /* — ECG line — */
        .ecg-wrap {
            margin: 0 auto 1.75rem;
            width: 100%;
            max-width: 320px;
            overflow: hidden;
        }

        .ecg-line {
            stroke-dasharray: 400;
            stroke-dashoffset: 400;
            animation: draw-ecg 2s ease forwards, fade-ecg 0.4s ease 2.4s forwards;
        }

        @keyframes draw-ecg {
            to { stroke-dashoffset: 0; }
        }

        @keyframes fade-ecg {
            to { opacity: 0.35; }
        }

        /* — Text — */
        .heading {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0.6rem;
            color: #111827;
        }

        .description {
            font-size: 0.925rem;
            color: #6b7280;
            line-height: 1.65;
            margin-bottom: 2rem;
        }

        /* — Buttons — */
        .actions {
            display: flex;
            gap: 0.65rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.6rem 1.4rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.18s ease;
            border: none;
        }

        .btn-primary {
            background: #d606b0;
            color: white;
        }

        .btn-primary:hover {
            background: #b5049a;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(214, 6, 176, 0.32);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e9eaec;
            transform: translateY(-1px);
        }

        /* — Footer tag — */
        .tag {
            margin-top: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.775rem;
            color: #9ca3af;
        }

        .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #d606b0;
            animation: blink 1.8s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.25; }
        }
    </style>
</head>
<body>
    <div class="card">

        <div class="illustration">
            <svg class="bg-cross" width="160" height="160" viewBox="0 0 160 160" fill="none" aria-hidden="true">
                <rect x="60" y="10" width="40" height="140" rx="10" fill="#d606b0"/>
                <rect x="10" y="60" width="140" height="40" rx="10" fill="#d606b0"/>
            </svg>
            <span class="number">404</span>
        </div>

        <!-- ECG / heartbeat line -->
        <div class="ecg-wrap" aria-hidden="true">
            <svg viewBox="0 0 320 48" fill="none" xmlns="http://www.w3.org/2000/svg" width="100%">
                <path class="ecg-line"
                    d="M0 24 H60 L72 24 L80 8 L88 40 L96 8 L104 32 L112 24 H160 L172 24 L180 8 L188 40 L196 8 L204 32 L212 24 H320"
                    stroke="#d606b0" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h1 class="heading">Halaman Tidak Ditemukan</h1>
        <p class="description">
            Maaf, halaman yang Anda cari tidak tersedia atau mungkin telah dipindahkan ke alamat lain.
        </p>

        <div class="actions">
            <a href="javascript:history.back()" class="btn btn-secondary">
                ← Kembali
            </a>
            <a href="/" class="btn btn-primary">
                Beranda
            </a>
        </div>

        <div class="tag">
            <span class="dot"></span>
            RSU SYIFA MEDIKA
        </div>

    </div>
</body>
</html>
