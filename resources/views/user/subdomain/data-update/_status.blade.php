@php
    $map = [
        'pending'   => ['Menunggu', 'bg-yellow-100 text-yellow-800'],
        'revisi'    => ['Perlu Revisi', 'bg-orange-100 text-orange-800'],
        'disetujui' => ['Disetujui', 'bg-green-100 text-green-800'],
        'ditolak'   => ['Ditolak', 'bg-red-100 text-red-800'],
    ];
    [$label, $cls] = $map[$status] ?? [ucfirst($status), 'bg-gray-100 text-gray-800'];
@endphp
<span class="px-2 py-1 rounded-full text-xs font-medium {{ $cls }}">{{ $label }}</span>
