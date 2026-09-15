{{--
    Timeline tracking permohonan DTSEN (Fitur Lintas-Tahapan).
    Butuh: $item (App\Models\DtsenDataRequest)
--}}
@php
    $steps = \App\Models\DtsenDataRequest::timelineSteps();
    $stamps = $item->timelineTimestamps();

    // Tahap BAST hanya berlaku untuk permohonan level 4 (BNBA).
    if (! $item->requiresBast()) {
        unset($steps[\App\Models\DtsenDataRequest::STATUS_MENUNGGU_BAST]);
    }

    $keys = array_keys($steps);
    $currentIndex = array_search($item->status, $keys, true);

    // Status cabang tidak ada di daftar linier; posisikan pada tahap terkait.
    $branchAnchor = [
        \App\Models\DtsenDataRequest::STATUS_PERLU_PERBAIKAN => \App\Models\DtsenDataRequest::STATUS_VERIF_ADMIN,
        \App\Models\DtsenDataRequest::STATUS_KLARIFIKASI => \App\Models\DtsenDataRequest::STATUS_VERIF_SUBSTANSI,
        \App\Models\DtsenDataRequest::STATUS_DITOLAK => \App\Models\DtsenDataRequest::STATUS_VERIF_SUBSTANSI,
        \App\Models\DtsenDataRequest::STATUS_KEDALUWARSA => \App\Models\DtsenDataRequest::STATUS_DATA_TERSEDIA,
    ];
    if ($currentIndex === false && isset($branchAnchor[$item->status])) {
        $currentIndex = array_search($branchAnchor[$item->status], $keys, true);
    }
    $currentIndex = $currentIndex === false ? -1 : $currentIndex;

    $isRejected = in_array($item->status, [
        \App\Models\DtsenDataRequest::STATUS_DITOLAK,
        \App\Models\DtsenDataRequest::STATUS_PERLU_PERBAIKAN,
    ], true);
@endphp

<div class="bg-white rounded-lg shadow-sm border p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-gray-800">Tahapan Permohonan</h3>
        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
    </div>

    <ol class="space-y-3">
        @foreach ($steps as $key => $label)
            @php
                $index = array_search($key, $keys, true);
                $done = $stamps[$key] !== null;
                $current = $index === $currentIndex;
            @endphp
            <li class="flex items-start gap-3">
                <span @class([
                    'mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                    'bg-green-600 text-white' => $done,
                    'bg-amber-500 text-white' => ! $done && $current && ! $isRejected,
                    'bg-red-600 text-white' => ! $done && $current && $isRejected,
                    'bg-gray-200 text-gray-500' => ! $done && ! $current,
                ])>
                    {{ $done ? '✓' : $index + 1 }}
                </span>
                <div class="min-w-0">
                    <p @class([
                        'text-sm',
                        'font-semibold text-gray-800' => $done || $current,
                        'text-gray-500' => ! $done && ! $current,
                    ])>{{ $label }}</p>
                    @if ($done)
                        <p class="text-xs text-gray-500">{{ $stamps[$key]->format('d/m/Y H:i') }}</p>
                    @elseif ($current)
                        <p class="text-xs {{ $isRejected ? 'text-red-600' : 'text-amber-600' }}">Sedang berjalan — {{ $item->status_label }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>

    @if ($item->status === \App\Models\DtsenDataRequest::STATUS_DITOLAK)
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            <p class="font-semibold">Permohonan ditolak pada verifikasi substansi.</p>
            <p class="mt-1">{{ $item->sub_alasan_penolakan }}</p>
            <p class="mt-2 text-xs">Penolakan bersifat final; proses tidak dapat dilanjutkan. Anda dapat mengajukan permohonan baru dengan perbaikan substansi.</p>
        </div>
    @endif
</div>
