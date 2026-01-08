<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts (bisa tetap pakai bunny.net atau Google Fonts) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('kaltara.svg') }}">
    <!-- Fallback untuk browser lama -->
    <link rel="alternate icon" href="{{ asset('kaltara.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .tech-bg {
            background: linear-gradient(135deg, #0d9488 0%, #059669 50%, #10b981 100%);
            position: relative;
            overflow: hidden;
        }

        /* Matrix Digital Rain Effect */
        .matrix-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .matrix-column {
            position: absolute;
            top: -100%;
            width: 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #10b981;
            text-shadow: 0 0 8px #10b981;
            animation: matrixRain linear infinite;
            white-space: nowrap;
            opacity: 0.8;
        }

        @keyframes matrixRain {
            0% { top: -100%; }
            100% { top: 100%; }
        }

        .matrix-column:nth-child(1) { left: 5%; animation-duration: 8s; animation-delay: 0s; }
        .matrix-column:nth-child(2) { left: 10%; animation-duration: 10s; animation-delay: 1s; }
        .matrix-column:nth-child(3) { left: 15%; animation-duration: 7s; animation-delay: 2s; }
        .matrix-column:nth-child(4) { left: 20%; animation-duration: 9s; animation-delay: 0.5s; }
        .matrix-column:nth-child(5) { left: 25%; animation-duration: 11s; animation-delay: 1.5s; }
        .matrix-column:nth-child(6) { left: 30%; animation-duration: 8.5s; animation-delay: 2.5s; }
        .matrix-column:nth-child(7) { left: 35%; animation-duration: 10.5s; animation-delay: 0.8s; }
        .matrix-column:nth-child(8) { left: 40%; animation-duration: 9.5s; animation-delay: 1.8s; }
        .matrix-column:nth-child(9) { left: 45%; animation-duration: 7.5s; animation-delay: 0.3s; }
        .matrix-column:nth-child(10) { left: 50%; animation-duration: 11.5s; animation-delay: 2.3s; }
        .matrix-column:nth-child(11) { left: 55%; animation-duration: 8.8s; animation-delay: 1.3s; }
        .matrix-column:nth-child(12) { left: 60%; animation-duration: 10.2s; animation-delay: 0.6s; }
        .matrix-column:nth-child(13) { left: 65%; animation-duration: 9.2s; animation-delay: 2.1s; }
        .matrix-column:nth-child(14) { left: 70%; animation-duration: 7.8s; animation-delay: 1.1s; }
        .matrix-column:nth-child(15) { left: 75%; animation-duration: 11.2s; animation-delay: 0.2s; }
        .matrix-column:nth-child(16) { left: 80%; animation-duration: 8.3s; animation-delay: 1.6s; }
        .matrix-column:nth-child(17) { left: 85%; animation-duration: 10.8s; animation-delay: 2.6s; }
        .matrix-column:nth-child(18) { left: 90%; animation-duration: 9.8s; animation-delay: 0.9s; }
        .matrix-column:nth-child(19) { left: 95%; animation-duration: 7.3s; animation-delay: 1.9s; }
        .matrix-column:nth-child(20) { left: 98%; animation-duration: 11.8s; animation-delay: 2.9s; }

        /* Glowing Grid */
        .tech-bg::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background-image:
                linear-gradient(90deg, rgba(16, 185, 129, 0.05) 1px, transparent 1px),
                linear-gradient(0deg, rgba(16, 185, 129, 0.05) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: gridPulse 4s ease-in-out infinite;
        }

        @keyframes gridPulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }

        /* Hexagon Pattern */
        .hex-pattern {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0.1;
        }

        .hexagon {
            position: absolute;
            width: 60px;
            height: 35px;
            background: #10b981;
            clip-path: polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%);
            animation: hexPulse 3s ease-in-out infinite;
        }

        .hexagon:nth-child(1) { left: 10%; top: 15%; animation-delay: 0s; }
        .hexagon:nth-child(2) { left: 70%; top: 25%; animation-delay: 0.5s; }
        .hexagon:nth-child(3) { left: 30%; top: 65%; animation-delay: 1s; }
        .hexagon:nth-child(4) { left: 85%; top: 75%; animation-delay: 1.5s; }
        .hexagon:nth-child(5) { left: 50%; top: 45%; animation-delay: 2s; }

        @keyframes hexPulse {
            0%, 100% { opacity: 0.1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(1.2); }
        }

        /* Scanning Line Effect */
        .scan-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
            box-shadow: 0 0 10px #10b981;
            animation: scan 4s linear infinite;
        }

        @keyframes scan {
            0% { top: 0%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }

        .login-box {
            background: rgba(220, 252, 231, 0.95) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(16, 185, 129, 0.3);
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.2);
        }
    </style>
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 tech-bg">
        <!-- Matrix Digital Rain -->
        <div class="matrix-bg">
            <div class="matrix-column">10011010<br>01101001<br>11010110<br>00110101<br>10101100</div>
            <div class="matrix-column">01110100<br>10010111<br>11001010<br>01010011<br>10110010</div>
            <div class="matrix-column">11010101<br>00101101<br>10011100<br>01101010<br>11010011</div>
            <div class="matrix-column">00110110<br>11010010<br>01011101<br>10101001<br>01110100</div>
            <div class="matrix-column">10101011<br>01001110<br>11010101<br>00110011<br>10101001</div>
            <div class="matrix-column">01101100<br>10010011<br>01110101<br>11001010<br>00101101</div>
            <div class="matrix-column">11001001<br>00110101<br>10101110<br>01011001<br>11010100</div>
            <div class="matrix-column">01010110<br>10011010<br>00111001<br>11010011<br>01101010</div>
            <div class="matrix-column">10110011<br>01101001<br>11010010<br>00101110<br>10011101</div>
            <div class="matrix-column">00101101<br>11010110<br>01011010<br>10101001<br>01110011</div>
            <div class="matrix-column">11010011<br>00110101<br>10101101<br>01001110<br>11010010</div>
            <div class="matrix-column">01101010<br>10010110<br>00111010<br>11010101<br>01010011</div>
            <div class="matrix-column">10011101<br>01101011<br>11010010<br>00110101<br>10101100</div>
            <div class="matrix-column">00110110<br>11010101<br>01011001<br>10101010<br>01110101</div>
            <div class="matrix-column">11010010<br>00101101<br>10011110<br>01101001<br>11010011</div>
            <div class="matrix-column">01011010<br>10010101<br>00111001<br>11010110<br>01101001</div>
            <div class="matrix-column">10101101<br>01001011<br>11010101<br>00110010<br>10101110</div>
            <div class="matrix-column">00110101<br>11010011<br>01011010<br>10101001<br>01110010</div>
            <div class="matrix-column">11001010<br>00101110<br>10011101<br>01101011<br>11010100</div>
            <div class="matrix-column">01010101<br>10011001<br>00111010<br>11010101<br>01010110</div>
        </div>

        <!-- Hexagon Pattern -->
        <div class="hex-pattern">
            <div class="hexagon"></div>
            <div class="hexagon"></div>
            <div class="hexagon"></div>
            <div class="hexagon"></div>
            <div class="hexagon"></div>
        </div>

        <!-- Scanning Line -->
        <div class="scan-line"></div>

        <div style="position: relative; z-index: 10;">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-white" />
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 login-box shadow-xl overflow-hidden sm:rounded-lg" style="position: relative; z-index: 10;">
            {{ $slot }}

            <!-- Copyright & Developer Info -->
            <div class="mt-6 pt-4 border-t border-gray-300 text-center">
                <p class="text-xs text-gray-600 mb-2">
                    © {{ date('Y') }} DKISP Kalimantan Utara - Bidang Aplikasi Informatika. All rights reserved.
                </p>
                <p class="text-xs text-gray-500 flex items-center justify-center gap-2">
                    Dikembangkan oleh : Bayu Adi H.
                    <a href="https://www.linkedin.com/in/noclaire/" target="_blank" rel="noopener noreferrer" class="inline-block hover:opacity-70">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                    <a href="https://github.com/skivanoclaire" target="_blank" rel="noopener noreferrer" class="inline-block hover:opacity-70">
                        <svg class="w-4 h-4 text-gray-700" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>
