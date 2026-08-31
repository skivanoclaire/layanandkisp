@php
    $runningTexts = \App\Models\RunningText::untukNavbar();

    // Durasi disesuaikan panjang teks supaya kecepatan geser terasa sama
    // untuk pengumuman pendek maupun panjang.
    $totalKarakter = $runningTexts->sum(fn ($rt) => mb_strlen($rt->isi));
    $durasi = max(20, min(120, (int) round($totalKarakter * 0.28)));
@endphp

@if ($runningTexts->isNotEmpty())
    <style>
        .rt-bar { background:#065f46; color:#ecfdf5; }
        .rt-viewport { overflow:hidden; }
        .rt-track {
            display:inline-flex; white-space:nowrap; will-change:transform;
            animation: rt-scroll var(--rt-duration, 40s) linear infinite;
        }
        .rt-viewport:hover .rt-track,
        .rt-viewport:focus-within .rt-track { animation-play-state:paused; }
        .rt-group { display:inline-flex; align-items:center; padding-right:2rem; }
        .rt-item { display:inline-flex; align-items:center; }
        .rt-item + .rt-item::before {
            content:"\2022"; margin:0 .75rem; opacity:.6;
        }
        .rt-item a { text-decoration:underline; text-underline-offset:2px; }
        @keyframes rt-scroll {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }
        /* Hormati preferensi pengguna: tanpa animasi, teks bisa digeser manual. */
        @media (prefers-reduced-motion: reduce) {
            .rt-track { animation:none; }
            .rt-viewport { overflow-x:auto; }
        }
    </style>

    <div class="rt-bar text-sm max-w-full" role="region" aria-label="Informasi berjalan">
        <div class="flex items-center max-w-full">
            <span class="flex-shrink-0 bg-green-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide">
                Info
            </span>
            <div class="rt-viewport flex-1 py-2 min-w-0">
                <div class="rt-track" style="--rt-duration: {{ $durasi }}s">
                    @for ($salinan = 0; $salinan < 2; $salinan++)
                        {{-- Salinan kedua membuat perulangan terlihat mulus, dan
                             disembunyikan dari pembaca layar agar tidak dibaca dua kali. --}}
                        <span class="rt-group" @if ($salinan === 1) aria-hidden="true" @endif>
                            @foreach ($runningTexts as $rt)
                                <span class="rt-item">
                                    @if ($rt->tautan)
                                        <a href="{{ $rt->tautan }}" target="_blank" rel="noopener noreferrer nofollow">{{ $rt->isi }}</a>
                                    @else
                                        {{ $rt->isi }}
                                    @endif
                                </span>
                            @endforeach
                        </span>
                    @endfor
                </div>
            </div>
        </div>
    </div>
@endif
