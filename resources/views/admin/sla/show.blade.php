@extends('layouts.authenticated')

@section('title', '- SLA ' . $summary['label'])
@section('header-title', 'Manajemen SLA')

@section('content')
    <div class="max-w-full overflow-hidden">
        <div class="flex items-center gap-2 text-sm mb-4">
            <a href="{{ route('admin.sla.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="text-blue-600 hover:underline">
                &larr; Kembali ke Dashboard SLA
            </a>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $summary['label'] }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $summary['group'] }} &middot; Periode {{ $bulan }}/{{ $tahun }}</p>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg text-center shadow">
                <p class="text-xs text-gray-600">Total</p>
                <p class="text-xl font-bold text-gray-800">{{ $summary['total'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg text-center shadow">
                <p class="text-xs text-gray-600">Menunggu</p>
                <p class="text-xl font-bold text-yellow-700">{{ $summary['menunggu'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg text-center shadow">
                <p class="text-xs text-gray-600">Proses</p>
                <p class="text-xl font-bold text-blue-700">{{ $summary['proses'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg text-center shadow">
                <p class="text-xs text-gray-600">Selesai</p>
                <p class="text-xl font-bold text-emerald-700">{{ $summary['selesai'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg text-center shadow">
                <p class="text-xs text-gray-600">Ditolak</p>
                <p class="text-xl font-bold text-red-700">{{ $summary['ditolak'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg text-center shadow">
                <p class="text-xs text-gray-600">Capaian SLA</p>
                <p class="text-xl font-bold text-green-700">
                    {{ $summary['achieved_pct'] !== null ? $summary['achieved_pct'] . '%' : '—' }}
                </p>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm rounded px-4 py-3 mb-6">
            Target SLA:
            <strong>
                @if ($summary['target_active'])
                    {{ $summary['target_value'] }} {{ $summary['target_unit'] === 'jam' ? 'jam kerja' : 'hari kerja' }}
                @else
                    Nonaktif (capaian tidak dihitung)
                @endif
            </strong>
            &middot; Rata-rata durasi penyelesaian:
            <strong>{{ $summary['avg_duration_hours'] !== null ? $summary['avg_duration_hours'] . ' jam kerja' : '—' }}</strong>
            <a href="{{ route('admin.sla.pengaturan') }}" class="underline ml-2">Ubah target</a>
        </div>

        {{-- Daftar permohonan --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b bg-gray-50">
                            <th class="py-2 px-4 font-semibold">#</th>
                            <th class="py-2 px-4 font-semibold">Diajukan</th>
                            <th class="py-2 px-4 font-semibold">Status</th>
                            <th class="py-2 px-4 font-semibold">Selesai/Ditolak</th>
                            <th class="py-2 px-4 font-semibold text-center">Durasi Kerja</th>
                            <th class="py-2 px-4 font-semibold text-center">Capaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($rows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 text-gray-600">
                                    {{ $row->ticket_no ?? $row->ticket_number ?? ('#' . $row->id) }}
                                </td>
                                <td class="py-2 px-4 whitespace-nowrap">
                                    {{ $row->start_at?->format('d M Y H:i') ?? '—' }}
                                </td>
                                <td class="py-2 px-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @class([
                                            'bg-yellow-100 text-yellow-800' => $row->bucket === 'menunggu',
                                            'bg-blue-100 text-blue-800' => $row->bucket === 'proses',
                                            'bg-emerald-100 text-emerald-800' => $row->bucket === 'selesai',
                                            'bg-red-100 text-red-800' => $row->bucket === 'ditolak',
                                        ])">
                                        {{ ucfirst($row->bucket) }}
                                    </span>
                                    @if ($row->overdue)
                                        <span class="ml-1 px-2 py-1 rounded text-xs font-semibold bg-orange-100 text-orange-800">
                                            Berpotensi Terlambat
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 px-4 whitespace-nowrap">
                                    {{ $row->end_at?->format('d M Y H:i') ?? '—' }}
                                </td>
                                <td class="py-2 px-4 text-center whitespace-nowrap">{{ $row->duration_hours }} jam</td>
                                <td class="py-2 px-4 text-center">
                                    @if ($row->achieved === null)
                                        <span class="text-gray-400">—</span>
                                    @elseif ($row->achieved)
                                        <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Tercapai</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800">Terlambat</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 px-4 text-center text-gray-400">
                                    Tidak ada permohonan pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t">
                {{ $rows->links() }}
            </div>
        </div>
    </div>
@endsection
