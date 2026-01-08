@extends('layouts.authenticated')

@section('title', '- Jadwal Video Konferensi')
@section('header-title', 'Jadwal Video Konferensi')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Jadwal Video Konferensi</h1>

        {{-- Month/Year Filter --}}
        <form method="GET" class="flex gap-2">
            <select name="month" class="border-gray-300 rounded-md shadow-sm">
                @foreach(['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $num => $name)
                    <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select name="year" class="border-gray-300 rounded-md shadow-sm">
                @for($y = 2024; $y <= 2026; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filter</button>
        </form>
    </div>

    {{-- Upcoming Schedule Card --}}
    @if($upcomingSchedules->count() > 0)
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <h2 class="text-lg font-semibold text-yellow-800 mb-3">Jadwal Mendatang (7 Hari ke Depan)</h2>
        <div class="space-y-2">
            @foreach($upcomingSchedules as $upcoming)
            <div class="bg-white p-3 rounded shadow-sm">
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900">{{ $upcoming->judul_kegiatan }}</p>
                        <p class="text-sm text-gray-600">{{ $upcoming->nama_instansi }}</p>
                    </div>
                    <div class="text-right mr-3">
                        <p class="text-sm font-semibold text-blue-600">
                            {{ $upcoming->tanggal_mulai->format('d M Y') }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $upcoming->jam_mulai ? $upcoming->jam_mulai->format('H:i') : '-' }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('op.tik.schedule.show', $upcoming->id) }}"
                           class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Schedule Table --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instansi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul Kegiatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Platform</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operator</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="font-medium">{{ $schedule->tanggal_mulai->format('d M') }}</div>
                            <div class="text-xs text-gray-500">{{ $schedule->tanggal_mulai->format('Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($schedule->jam_mulai)
                                <div>{{ $schedule->jam_mulai->format('H:i') }}</div>
                                @if($schedule->jam_selesai)
                                    <div class="text-xs text-gray-500">s/d {{ $schedule->jam_selesai->format('H:i') }}</div>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="font-medium text-gray-900">{{ $schedule->nama_instansi }}</div>
                            @if($schedule->nomor_surat)
                                <div class="text-xs text-gray-500">{{ $schedule->nomor_surat }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="max-w-md">{{ $schedule->judul_kegiatan }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $schedule->lokasi ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($schedule->platform)
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ str_contains(strtolower($schedule->platform), 'zoom') ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ str_contains(strtolower($schedule->platform), 'youtube') ? 'bg-red-100 text-red-800' : '' }}
                                    {{ !str_contains(strtolower($schedule->platform), 'zoom') && !str_contains(strtolower($schedule->platform), 'youtube') ? 'bg-gray-100 text-gray-800' : '' }}
                                ">
                                    {{ $schedule->platform }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($schedule->operators->count() > 0)
                                {{ $schedule->operators->pluck('name')->join(', ') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <a href="{{ route('op.tik.schedule.show', $schedule->id) }}"
                               class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <div class="text-lg">Tidak ada jadwal untuk bulan ini</div>
                            <p class="text-sm mt-2">Silakan pilih bulan lain atau tambahkan data baru</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Summary --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total Jadwal Bulan Ini</div>
            <div class="text-2xl font-bold text-blue-600">{{ $schedules->count() }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Platform Terbanyak</div>
            <div class="text-lg font-semibold text-gray-800">
                {{ $schedules->groupBy('platform')->sortByDesc(function($group) { return $group->count(); })->keys()->first() ?? 'N/A' }}
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Instansi Terbanyak</div>
            <div class="text-lg font-semibold text-gray-800">
                {{ Str::limit($schedules->groupBy('nama_instansi')->sortByDesc(function($group) { return $group->count(); })->keys()->first() ?? 'N/A', 30) }}
            </div>
        </div>
    </div>
</div>
@endsection
