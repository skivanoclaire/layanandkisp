@extends('layouts.authenticated')

@section('title', '- Manajemen SLA')
@section('header-title', 'Manajemen SLA')

@section('content')
    <div class="max-w-full overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard Capaian SLA</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Capaian SLA seluruh layanan digital, dihitung berdasarkan jam kerja (exclude akhir pekan & libur).
                </p>
            </div>
            <a href="{{ route('admin.sla.pengaturan') }}"
                class="bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Pengaturan SLA
            </a>
        </div>

        {{-- Filter periode --}}
        <form method="GET" class="flex flex-wrap items-end gap-3 mb-6 bg-white p-4 rounded-lg shadow">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Bulan</label>
                <select name="bulan" class="border border-gray-300 rounded px-3 py-2 text-sm">
                    @foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $nama)
                        <option value="{{ $i + 1 }}" {{ $bulan == $i + 1 ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun</label>
                <input type="number" name="tahun" value="{{ $tahun }}" min="2020" max="2100"
                    class="border border-gray-300 rounded px-3 py-2 text-sm w-24">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded">
                Tampilkan
            </button>
        </form>

        {{-- Kartu Ringkasan --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg text-center shadow border-l-4 border-gray-400">
                <p class="text-sm text-gray-600">Total Permohonan</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totals['total'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg text-center shadow border-l-4 border-emerald-500">
                <p class="text-sm text-gray-600">Selesai</p>
                <p class="text-2xl font-bold text-emerald-700">{{ $totals['selesai'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg text-center shadow border-l-4 border-red-500">
                <p class="text-sm text-gray-600">Ditolak</p>
                <p class="text-2xl font-bold text-red-700">{{ $totals['ditolak'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg text-center shadow border-l-4 border-blue-500">
                <p class="text-sm text-gray-600">Dalam Proses</p>
                <p class="text-2xl font-bold text-blue-700">{{ $totals['proses'] + $totals['menunggu'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg text-center shadow border-l-4 border-green-600">
                <p class="text-sm text-gray-600">Capaian SLA</p>
                <p class="text-2xl font-bold text-green-700">
                    {{ $totals['achieved_pct'] !== null ? $totals['achieved_pct'] . '%' : '—' }}
                </p>
            </div>
        </div>

        {{-- Chart per layanan --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Capaian SLA per Layanan (%)</h2>
            <div style="height: 340px;">
                <canvas id="chartSlaAchievement"></canvas>
            </div>
        </div>

        {{-- Tabel per layanan, dikelompokkan per kategori --}}
        @foreach ($groups as $groupName => $keys)
            @php $groupServices = collect($services)->whereIn('service_key', $keys); @endphp
            @if ($groupServices->count())
                <div class="bg-white rounded-lg shadow mb-6 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b">
                        <h3 class="font-semibold text-gray-700">{{ $groupName }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 border-b bg-gray-50">
                                    <th class="py-2 px-4 font-semibold">Layanan</th>
                                    <th class="py-2 px-4 font-semibold text-center">Total</th>
                                    <th class="py-2 px-4 font-semibold text-center">Menunggu</th>
                                    <th class="py-2 px-4 font-semibold text-center">Proses</th>
                                    <th class="py-2 px-4 font-semibold text-center">Selesai</th>
                                    <th class="py-2 px-4 font-semibold text-center">Ditolak</th>
                                    <th class="py-2 px-4 font-semibold text-center">Target</th>
                                    <th class="py-2 px-4 font-semibold text-center">Rata-rata</th>
                                    <th class="py-2 px-4 font-semibold text-center">Capaian SLA</th>
                                    <th class="py-2 px-4 font-semibold text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($groupServices as $s)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 font-medium text-gray-700">{{ $s['label'] }}</td>
                                        <td class="py-2 px-4 text-center">{{ $s['total'] }}</td>
                                        <td class="py-2 px-4 text-center">{{ $s['menunggu'] }}</td>
                                        <td class="py-2 px-4 text-center">{{ $s['proses'] }}</td>
                                        <td class="py-2 px-4 text-center">{{ $s['selesai'] }}</td>
                                        <td class="py-2 px-4 text-center">{{ $s['ditolak'] }}</td>
                                        <td class="py-2 px-4 text-center whitespace-nowrap">
                                            @if ($s['target_active'])
                                                {{ $s['target_value'] }} {{ $s['target_unit'] === 'jam' ? 'jam' : 'hari kerja' }}
                                            @else
                                                <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-500">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 text-center whitespace-nowrap">
                                            {{ $s['avg_duration_hours'] !== null ? $s['avg_duration_hours'] . ' jam' : '—' }}
                                        </td>
                                        <td class="py-2 px-4 text-center">
                                            @if ($s['achieved_pct'] === null)
                                                <span class="text-gray-400">—</span>
                                            @else
                                                <span class="px-2 py-1 rounded text-xs font-semibold
                                                    {{ $s['achieved_pct'] >= 90 ? 'bg-green-100 text-green-800' : ($s['achieved_pct'] >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ $s['achieved_pct'] }}%
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 text-center">
                                            <a href="{{ route('admin.sla.show', ['serviceKey' => $s['service_key'], 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                                                class="text-blue-600 hover:underline text-xs font-semibold">Lihat</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctxSla = document.getElementById('chartSlaAchievement').getContext('2d');
        const slaLabels = {!! json_encode(collect($services)->pluck('label')) !!};
        const slaAchieved = {!! json_encode(collect($services)->pluck('achieved_pct')->map(fn($v) => $v ?? 0)) !!};

        new Chart(ctxSla, {
            type: 'bar',
            data: {
                labels: slaLabels,
                datasets: [{
                    label: 'Capaian SLA (%)',
                    data: slaAchieved,
                    backgroundColor: slaAchieved.map(v => v >= 90 ? '#16a34a' : (v >= 70 ? '#eab308' : '#dc2626')),
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 100 } }
            }
        });
    </script>
@endpush
