@extends('layouts.authenticated')

@section('title', '- Dashboard DTSEN')
@section('header-title', 'Dashboard Monitoring DTSEN')

@section('content')
@php
    $maxBulan = max(1, max($perBulan));
    $namaBulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
@endphp

<div class="container mx-auto p-6">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Monitoring Berbagi Pakai Data DTSEN</h1>
            <p class="text-gray-600 mt-1">Rekap permohonan, kepatuhan SLA, pemanfaatan, dan insiden — bahan evaluasi tahunan (Bab VIII huruf C).</p>
        </div>
        <form method="GET" class="flex items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                <select name="tahun" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @foreach ($tahunOptions as $t)
                        <option value="{{ $t }}" @selected($tahun === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Tampilkan</button>
        </form>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        @foreach ([
            ['label' => 'Permohonan ' . $tahun, 'value' => $ringkasan['permohonan'], 'class' => 'text-gray-800'],
            ['label' => 'Sedang Berjalan', 'value' => $ringkasan['berjalan'], 'class' => 'text-blue-600'],
            ['label' => 'Ditolak', 'value' => $ringkasan['ditolak'], 'class' => 'text-red-600'],
            ['label' => 'Akun Aktif', 'value' => $ringkasan['akun_aktif'], 'class' => 'text-green-600'],
            ['label' => 'Token Aktif', 'value' => $ringkasan['token_aktif'], 'class' => 'text-teal-600'],
            ['label' => 'Insiden Terlambat', 'value' => $ringkasan['insiden_terlambat'], 'class' => 'text-red-600'],
        ] as $card)
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <p class="text-xs text-gray-500">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold mt-1 {{ $card['class'] }}">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Kepatuhan SLA --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="font-bold text-gray-800 mb-1">Kepatuhan SLA per Tahapan</h2>
            <p class="text-xs text-gray-500 mb-4">Target end-to-end {{ $slaTotalTarget }} hari kerja.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Tahapan</th>
                            <th class="px-3 py-2 text-center">Target</th>
                            <th class="px-3 py-2 text-center">Selesai</th>
                            <th class="px-3 py-2 text-center">Tepat</th>
                            <th class="px-3 py-2 text-center">Lewat</th>
                            <th class="px-3 py-2 text-center">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($slaSummary as $row)
                            <tr>
                                <td class="px-3 py-2">{{ $row['label'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $row['target'] }} hk</td>
                                <td class="px-3 py-2 text-center">{{ $row['selesai'] }}</td>
                                <td class="px-3 py-2 text-center text-green-700 font-semibold">{{ $row['tepat'] }}</td>
                                <td class="px-3 py-2 text-center {{ $row['lewat'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">{{ $row['lewat'] }}</td>
                                <td class="px-3 py-2 text-center {{ $row['rata2'] > $row['target'] ? 'text-red-600' : 'text-gray-700' }}">{{ $row['rata2'] }} hk</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sebaran status --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="font-bold text-gray-800 mb-4">Sebaran Status Permohonan</h2>
            @php($totalStatus = max(1, array_sum($perStatus)))
            <div class="space-y-2">
                @foreach ($perStatus as $label => $count)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700">{{ $label }}</span>
                            <span class="font-semibold">{{ $count }}</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded mt-1">
                            <div class="h-2 bg-green-500 rounded" style="width: {{ round($count / $totalStatus * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Permohonan per bulan --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border p-6">
            <h2 class="font-bold text-gray-800 mb-4">Permohonan per Bulan ({{ $tahun }})</h2>
            <div class="flex items-end gap-2 h-40">
                @foreach ($perBulan as $bulan => $jumlah)
                    <div class="flex-1 flex flex-col items-center justify-end h-full">
                        <span class="text-xs text-gray-600 mb-1">{{ $jumlah ?: '' }}</span>
                        <div class="w-full bg-green-500 rounded-t" style="height: {{ $jumlah > 0 ? max(4, round($jumlah / $maxBulan * 120)) : 2 }}px"></div>
                        <span class="text-[10px] text-gray-500 mt-1">{{ $namaBulan[$bulan] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Sebaran level --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="font-bold text-gray-800 mb-4">Sebaran Level Hak Akses</h2>
            <ul class="space-y-3 text-sm">
                @foreach ($perLevel as $label => $count)
                    <li class="flex justify-between gap-2">
                        <span class="text-gray-700">{{ $label }}</span>
                        <span class="font-bold">{{ $count }}</span>
                    </li>
                @endforeach
            </ul>

            <h3 class="font-bold text-gray-800 mt-6 mb-3">Pelaporan Tahap 5</h3>
            <ul class="space-y-2 text-sm">
                <li class="flex justify-between"><span class="text-gray-700">Laporan pemanfaatan</span><span class="font-bold">{{ $ringkasan['laporan_pemanfaatan'] }}</span></li>
                <li class="flex justify-between"><span class="text-gray-700">Berita acara pemusnahan</span><span class="font-bold">{{ $ringkasan['berita_pemusnahan'] }}</span></li>
                <li class="flex justify-between"><span class="text-gray-700">Insiden keamanan</span><span class="font-bold">{{ $ringkasan['insiden'] }}</span></li>
                <li class="flex justify-between"><span class="text-gray-700">Pengaduan baru</span><span class="font-bold">{{ $ringkasan['pengaduan_baru'] }}</span></li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- OPD terbanyak --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="font-bold text-gray-800 mb-4">OPD Pemohon Terbanyak</h2>
            <ol class="space-y-2 text-sm">
                @forelse ($perOpd as $row)
                    <li class="flex justify-between gap-2">
                        <span class="text-gray-700 truncate">{{ $row->unitKerja->nama ?? '-' }}</span>
                        <span class="font-bold">{{ $row->total }}</span>
                    </li>
                @empty
                    <li class="text-gray-500">Belum ada data.</li>
                @endforelse
            </ol>
        </div>

        {{-- Token menjelang kedaluwarsa --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="font-bold text-gray-800 mb-1">Token Segera Kedaluwarsa</h2>
            <p class="text-xs text-gray-500 mb-3">Dalam {{ \App\Models\DtsenAccessToken::WARN_BEFORE_DAYS }} hari ke depan.</p>
            <ul class="space-y-2 text-sm">
                @forelse ($tokenSegeraKedaluwarsa as $token)
                    <li class="border-l-2 border-orange-300 pl-2">
                        <a href="{{ route('admin.dtsen.permohonan.show', $token->dtsen_data_request_id) }}"
                           class="font-mono text-blue-600 hover:underline">{{ $token->dataRequest->ticket_no ?? '-' }}</a>
                        <p class="text-xs text-gray-500">
                            {{ $token->dataRequest->unitKerja->nama ?? '-' }} — berakhir {{ $token->expires_at?->format('d/m/Y') }}
                        </p>
                    </li>
                @empty
                    <li class="text-gray-500">Tidak ada.</li>
                @endforelse
            </ul>
        </div>

        {{-- Pemusnahan terlambat --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="font-bold text-gray-800 mb-1">Pemusnahan Belum Disampaikan</h2>
            <p class="text-xs text-gray-500 mb-3">Melewati batas {{ \App\Models\DtsenDestructionReport::BATAS_PENYAMPAIAN_HARI }} hari kalender.</p>
            <ul class="space-y-2 text-sm">
                @forelse ($pemusnahanTerlambat as $report)
                    <li class="border-l-2 border-red-300 pl-2">
                        <span class="font-mono">{{ $report->dataRequest->ticket_no ?? '-' }}</span>
                        <p class="text-xs text-gray-500">Batas {{ $report->batas_penyampaian?->format('d/m/Y') }}</p>
                    </li>
                @empty
                    <li class="text-gray-500">Tidak ada.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
