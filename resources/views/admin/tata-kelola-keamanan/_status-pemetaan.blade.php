@php
    $warna = [
        'draf' => 'bg-gray-100 text-gray-700',
        'disepakati' => 'bg-green-100 text-green-800',
        'dikeluarkan' => 'bg-red-100 text-red-700',
    ][$status] ?? 'bg-gray-100 text-gray-700';
@endphp
<span class="px-2 py-0.5 text-xs rounded {{ $warna }}">{{ \App\Http\Controllers\Admin\TataKelolaKeamanan\EvaluasiController::labelStatusPemetaan($status) }}@if (! empty($putaran)) (putaran {{ $putaran }})@endif</span>
