{{-- Header evaluasi + navigasi tahapan --}}
@include('user.kuesioner-portal._flash')

@if ($errors->any())
    <div class="mb-4 p-4 rounded-lg border bg-red-50 border-red-200 text-red-800 text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="flex flex-wrap items-start justify-between gap-3 mb-4">
    <div>
        <a href="{{ route('admin.tata-kelola-keamanan.index') }}" class="text-sm text-green-700 hover:underline">&larr; Semua evaluasi</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-1">{{ $evaluasi->nama }}</h1>
        <p class="text-sm text-gray-600">{{ $evaluasi->instansi }}
            @if ($evaluasi->is_simulasi)
                <span class="ml-2 px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-800 font-semibold">DATA SIMULASI — hanya untuk latihan</span>
            @endif
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.tata-kelola-keamanan.panduan') }}" class="px-4 py-2 text-sm font-medium rounded-lg border border-blue-300 text-blue-700 hover:bg-blue-50">Panduan</a>
        <a href="{{ route('admin.tata-kelola-keamanan.laporan', $evaluasi) }}" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Laporan</a>
        <a href="{{ route('admin.tata-kelola-keamanan.export', $evaluasi) }}" class="px-4 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">Ekspor Excel</a>
    </div>
</div>

@php
    $menu = [
        'admin.tata-kelola-keamanan.show' => 'Ringkasan',
        'admin.tata-kelola-keamanan.responden' => 'Responden',
        'admin.tata-kelola-keamanan.design-factors' => '1. Design Factors',
        'admin.tata-kelola-keamanan.objektif' => '2. Prioritas Objektif',
        'admin.tata-kelola-keamanan.pemetaan' => '3. Pemetaan',
        'admin.tata-kelola-keamanan.validasi' => '4. Validasi Pakar',
        'admin.tata-kelola-keamanan.asesmen' => '5a. Asesmen Kapabilitas',
        'admin.tata-kelola-keamanan.kontrol' => '5b. Status Kontrol',
        'admin.tata-kelola-keamanan.rekomendasi' => '6. Kesenjangan & Rekomendasi',
    ];
@endphp
<nav class="flex flex-wrap gap-1 border-b border-gray-200 mb-6">
    @foreach ($menu as $route => $label)
        <a href="{{ route($route, $evaluasi) }}"
           class="px-3 py-2 text-sm rounded-t-lg {{ request()->routeIs($route) ? 'bg-white border border-b-0 border-gray-200 text-green-700 font-semibold' : 'text-gray-600 hover:text-green-700' }}">
            {{ $label }}
        </a>
    @endforeach
</nav>
