@props([
    'value'     => null,
    'label'     => 'data',
    'visible'   => 4,
    'textClass' => 'text-gray-700 font-mono text-xs',
    'empty'     => '-',
])

@php
    $raw = trim((string) ($value ?? ''));
    $len = mb_strlen($raw);
    // Nilai pendek disamarkan penuh supaya tidak mudah ditebak.
    $show   = $len > ((int) $visible + 2) ? (int) $visible : 0;
    $tail   = $show > 0 ? mb_substr($raw, -$show) : '';
    $masked = str_repeat('•', max($len - $show, 4)) . $tail;
@endphp

@if ($raw === '')
    <span class="{{ $textClass }}">{{ $empty }}</span>
@else
    <span class="js-masked inline-flex items-center gap-1"
          data-masked="{{ $masked }}"
          data-revealed="{{ $raw }}">
        <span class="js-masked-text {{ $textClass }} select-none">{{ $masked }}</span>
        <button type="button"
                class="js-masked-toggle text-gray-400 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 rounded"
                aria-pressed="false"
                aria-label="Tampilkan {{ $label }}"
                title="Tampilkan {{ $label }}">
            {{-- ikon mata (tertutup = sedang tersembunyi) --}}
            <svg class="js-masked-icon-show w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
            <svg class="js-masked-icon-hide w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.22A10.48 10.48 0 0 0 2.25 12s3 6.75 9.75 6.75c1.35 0 2.56-.27 3.63-.71M6.53 6.53A10.2 10.2 0 0 1 12 5.25c6.75 0 9.75 6.75 9.75 6.75a10.9 10.9 0 0 1-3.53 4.47M9.88 9.88a3 3 0 1 0 4.24 4.24M3 3l18 18"/>
            </svg>
        </button>
    </span>
@endif

@once
    @push('scripts')
    <script>
    (function () {
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-masked-toggle');
            if (!btn) return;

            const wrap = btn.closest('.js-masked');
            const text = wrap?.querySelector('.js-masked-text');
            if (!wrap || !text) return;

            const revealed = btn.getAttribute('aria-pressed') === 'true';
            const label    = (btn.getAttribute('aria-label') || '').replace(/^(Tampilkan|Sembunyikan)\s*/i, '');

            text.textContent = revealed ? wrap.dataset.masked : wrap.dataset.revealed;
            text.classList.toggle('select-none', revealed);
            btn.setAttribute('aria-pressed', revealed ? 'false' : 'true');
            btn.setAttribute('aria-label', (revealed ? 'Tampilkan ' : 'Sembunyikan ') + label);
            btn.setAttribute('title', (revealed ? 'Tampilkan ' : 'Sembunyikan ') + label);
            btn.querySelector('.js-masked-icon-show')?.classList.toggle('hidden', !revealed);
            btn.querySelector('.js-masked-icon-hide')?.classList.toggle('hidden', revealed);
        });
    })();
    </script>
    @endpush
@endonce
