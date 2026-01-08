<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>e-Layanan DKISP • Maintenance</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root {
            --bg1: #0f172a;
            /* slate-900 */
            --bg2: #111827;
            /* gray-900 */
            --accent: #22d3ee;
            /* cyan-400 */
            --accent2: #a78bfa;
            /* violet-400 */
            --text: #e5e7eb;
            /* gray-200 */
            --muted: #9ca3af;
            /* gray-400 */
            --card: rgba(255, 255, 255, 0.06);
            --border: rgba(255, 255, 255, 0.12);
            --success: #10b981;
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%
        }

        body {
            margin: 0;
            color: var(--text);
            font: 16px/1.6 system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, "Noto Sans", "Helvetica Neue", Arial, "Apple Color Emoji", "Segoe UI Emoji";
            background:
                radial-gradient(60vw 60vw at 10% 10%, rgba(34, 211, 238, .15), transparent 60%),
                radial-gradient(50vw 50vw at 90% 20%, rgba(167, 139, 250, .12), transparent 60%),
                linear-gradient(135deg, var(--bg1), var(--bg2));
            background-size: 200% 200%;
            animation: bgShift 18s ease-in-out infinite alternate;
        }

        @keyframes bgShift {
            0% {
                background-position: 0% 0%, 100% 0%, 0 0
            }

            100% {
                background-position: 100% 100%, 0% 100%, 100% 100%
            }
        }

        .wrap {
            min-height: 100%;
            display: grid;
            place-items: center;
            padding: 32px;
        }

        .card {
            position: relative;
            width: min(820px, 100%);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: clamp(24px, 5vw, 40px);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 20px 80px rgba(0, 0, 0, .35);
            overflow: hidden;
        }

        .badge {
            display: inline-flex;
            gap: 10px;
            align-items: center;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(34, 211, 238, .12);
            border: 1px solid rgba(34, 211, 238, .35);
            color: var(--text);
            font-weight: 600;
            letter-spacing: .2px;
        }

        .row {
            display: flex;
            gap: 24px;
            align-items: center;
            flex-wrap: wrap
        }

        h1 {
            margin: 16px 0 6px;
            font-size: clamp(28px, 3.4vw, 40px);
            line-height: 1.15;
            letter-spacing: .2px;
        }

        p.lead {
            margin: 0 0 8px;
            color: var(--muted);
            font-size: clamp(15px, 2.2vw, 18px)
        }

        .foot {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px dashed var(--border);
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            color: var(--muted);
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.04);
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            transition: transform .15s ease, background .15s ease, border-color .15s ease;
            will-change: transform;
        }

        .btn:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(255, 255, 255, 0.25)
        }

        .btn:active {
            transform: translateY(0)
        }

        .btn .icon {
            width: 18px;
            height: 18px;
            display: inline-block
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(16, 185, 129, .12);
            color: #d1fae5;
            border: 1px solid rgba(16, 185, 129, .35);
            font-weight: 600;
        }

        .progress {
            position: relative;
            margin: 16px 0 4px;
            height: 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .08);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .bar {
            position: absolute;
            inset: 0 auto 0 0;
            width: 30%;
            background: linear-gradient(90deg, var(--accent), var(--accent2));
            background-size: 200% 100%;
            animation: load 2.8s ease-in-out infinite;
            border-radius: inherit;
        }

        @keyframes load {
            0% {
                transform: translateX(-30%);
                background-position: 0% 50%
            }

            50% {
                transform: translateX(40%);
                background-position: 100% 50%
            }

            100% {
                transform: translateX(110%);
                background-position: 0% 50%
            }
        }

        .gears {
            position: absolute;
            right: -40px;
            top: -40px;
            opacity: .12;
            display: flex;
            gap: 14px;
            transform: rotate(12deg);
            pointer-events: none;
            user-select: none;
        }

        .gear svg {
            width: 120px;
            height: 120px;
        }

        .gear.small svg {
            width: 80px;
            height: 80px;
        }

        .spin-slow {
            animation: spin 18s linear infinite
        }

        .spin-rev {
            animation: spin 22s linear infinite reverse
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: .95em
        }

        .hr {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .15), transparent);
            margin: 20px 0
        }

        /* Respect reduced motion */
        @media (prefers-reduced-motion: reduce) {

            body,
            .spin-slow,
            .spin-rev,
            .bar {
                animation: none !important
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <main class="card" role="main" aria-labelledby="title">
            <!-- decorative gears -->
            <div class="gears" aria-hidden="true">
                <div class="gear spin-slow">
                    <svg viewBox="0 0 100 100" fill="none">
                        <path fill="url(#g1)"
                            d="M58.2 3.5a6 6 0 0 1 5.7 1.8l3.8 4.1 6.9-1.3a6 6 0 0 1 6.2 3.2l3.2 6.2 6.9 1.2a6 6 0 0 1 4.8 5.7l.3 7 6 3.5a6 6 0 0 1 2.6 7.6l-3 6.3 3.9 5.8a6 6 0 0 1-1 7.6l-5 5 1.3 6.9a6 6 0 0 1-4 6.8l-6.6 2.4-1.2 6.9a6 6 0 0 1-5.7 4.9l-7-.3-3.5 6a6 6 0 0 1-7.6 2.6l-6.3-3-5.8 3.9a6 6 0 0 1-7.6-1l-5-5-6.9 1.3a6 6 0 0 1-6.8-4l-2.4-6.6-6.9-1.2a6 6 0 0 1-4.9-5.7l.3-7-6-3.5a6 6 0 0 1-2.6-7.6l3-6.3-3.9-5.8a6 6 0 0 1 1-7.6l5-5-1.3-6.9a6 6 0 0 1 4-6.8l6.6-2.4 1.2-6.9a6 6 0 0 1 5.7-4.9l7 .3 3.5-6A6 6 0 0 1 58.2 3.5ZM50 69a19 19 0 1 0 0-38 19 19 0 0 0 0 38Z" />
                        <defs>
                            <linearGradient id="g1" x1="0" y1="0" x2="100" y2="100">
                                <stop stop-color="#22d3ee" />
                                <stop offset="1" stop-color="#a78bfa" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="gear small spin-rev">
                    <svg viewBox="0 0 100 100" fill="none">
                        <path fill="url(#g2)"
                            d="M58.2 3.5a6 6 0 0 1 5.7 1.8l3.8 4.1 6.9-1.3a6 6 0 0 1 6.2 3.2l3.2 6.2 6.9 1.2a6 6 0 0 1 4.8 5.7l.3 7 6 3.5a6 6 0 0 1 2.6 7.6l-3 6.3 3.9 5.8a6 6 0 0 1-1 7.6l-5 5 1.3 6.9a6 6 0 0 1-4 6.8l-6.6 2.4-1.2 6.9a6 6 0 0 1-5.7 4.9l-7-.3-3.5 6a6 6 0 0 1-7.6 2.6l-6.3-3-5.8 3.9a6 6 0 0 1-7.6-1l-5-5-6.9 1.3a6 6 0 0 1-6.8-4l-2.4-6.6-6.9-1.2a6 6 0 0 1-4.9-5.7l.3-7-6-3.5a6 6 0 0 1-2.6-7.6l3-6.3-3.9-5.8a6 6 0 0 1 1-7.6l5-5-1.3-6.9a6 6 0 0 1 4-6.8l6.6-2.4 1.2-6.9a6 6 0 0 1 5.7-4.9l7 .3 3.5-6A6 6 0 0 1 58.2 3.5ZM50 69a19 19 0 1 0 0-38 19 19 0 0 0 0 38Z" />
                        <defs>
                            <linearGradient id="g2" x1="0" y1="0" x2="100" y2="100">
                                <stop stop-color="#a78bfa" />
                                <stop offset="1" stop-color="#22d3ee" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>

            <span class="badge" aria-label="Status maintenance">
                <!-- wrench icon -->
                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor"
                        d="M22 19.59 19.59 22l-6.83-6.83a6.5 6.5 0 1 1 2.41-2.41zM7 10.5A3.5 3.5 0 1 0 10.5 7 3.5 3.5 0 0 0 7 10.5" />
                </svg>
                Maintenance Berjalan
            </span>

            <h1 id="title">Website e-Layanan DKISP sedang dalam perawatan</h1>
            <p class="lead">Kami sedang melakukan <strong>penambahan fitur</strong> dan penyempurnaan sistem agar
                layanan makin nyaman digunakan.</p>

            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                aria-label="Proses pembaruan">
                <div class="bar"></div>
            </div>
            <div class="row">
                <span class="pill" title="Status sistem">Status: Online kembali secepatnya</span>
                <span class="mono" aria-live="polite" id="heartbeat">Mengoptimalkan modul…</span>
            </div>

            <div class="hr" aria-hidden="true"></div>

            <div class="foot">
                <div>
                    <strong>Bidang Aplikasi Informatika</strong><br>
                    DKISP Provinsi Kalimantan Utara
                </div>
                <div class="actions" aria-label="Profil pengembang">
                    <span>Dikembangkan oleh: <strong>Bayu Adi Hartanto</strong></span>
                    <a class="btn" href="https://github.com/skivanoclaire" target="_blank" rel="noopener">
                        <span class="icon" aria-hidden="true">
                            <!-- GitHub -->
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 .5a12 12 0 0 0-3.79 23.4c.6.11.82-.26.82-.58 0-.29-.01-1.05-.02-2.07-3.34.73-4.04-1.61-4.04-1.61-.55-1.4-1.33-1.77-1.33-1.77-1.09-.75.08-.74.08-.74 1.21.09 1.85 1.25 1.85 1.25 1.07 1.84 2.8 1.31 3.48 1 .11-.78.42-1.31.76-1.61-2.67-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.12-.3-.54-1.52.12-3.17 0 0 1.01-.32 3.3 1.23a11.5 11.5 0 0 1 6.01 0c2.28-1.55 3.29-1.23 3.29-1.23.67 1.65.25 2.87.12 3.17.77.84 1.23 1.91 1.23 3.22 0 4.61-2.8 5.62-5.48 5.92.43.37.81 1.1.81 2.22 0 1.6-.01 2.89-.01 3.28 0 .32.21.69.82.57A12 12 0 0 0 12 .5z" />
                            </svg>
                        </span>
                        GitHub
                    </a>
                    <a class="btn" href="https://www.linkedin.com/in/noclaire/" target="_blank" rel="noopener">
                        <span class="icon" aria-hidden="true">
                            <!-- LinkedIn -->
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M4.98 3.5C4.98 4.88 3.86 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.5 8.5h4V23h-4zM8.5 8.5h3.8v2h.05c.53-1 1.82-2.05 3.75-2.05 4.01 0 4.75 2.64 4.75 6.07V23h-4v-5.9c0-1.41-.02-3.22-1.96-3.22-1.96 0-2.26 1.53-2.26 3.11V23h-4z" />
                            </svg>
                        </span>
                        LinkedIn
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Pesan "heartbeat" kecil agar terasa hidup, tanpa eksternal dependency
        (function() {
            const el = document.getElementById('heartbeat');
            if (!el) return;
            const msgs = [
                'Mengoptimalkan modul…',
                'Menyusun komponen antarmuka…',
                'Menjalankan pengujian otomatis…',
                'Membersihkan cache & berkas sementara…',
                'Menyiapkan fitur baru…'
            ];
            let i = 0;
            setInterval(() => {
                i = (i + 1) % msgs.length;
                el.textContent = msgs[i];
            }, 2400);
        })();
    </script>
</body>

</html>
