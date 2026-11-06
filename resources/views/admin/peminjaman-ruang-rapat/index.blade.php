@extends('layouts.authenticated')
@section('title', '- Kelola Peminjaman Ruang Rapat')
@section('header-title', 'Kelola Peminjaman Ruang Rapat')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h1 class="text-3xl font-bold text-teal-700">Permohonan Peminjaman Ruang Rapat</h1>
        <a href="{{ route('admin.ruang-rapat.index') }}"
           class="px-4 py-2 rounded-lg font-semibold bg-gray-200 hover:bg-gray-300 text-gray-800 text-center">
            Kelola Master Ruang Rapat
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="mb-4 flex gap-2 flex-wrap">
        @foreach($tabs as $key => $tab)
            <a href="{{ route('admin.peminjaman-ruang-rapat.index', $key ? ['status' => $key] : []) }}"
               class="px-4 py-2 rounded-lg font-semibold {{ ($status ?? '') === $key ? $tab['active'] : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                {{ $tab['label'] }} ({{ $counts[$key] ?? 0 }})
            </a>
        @endforeach
    </div>

    @if($bookings->isEmpty())
        <div class="bg-white rounded shadow p-8 text-center">
            <p class="text-gray-600">Tidak ada permohonan peminjaman ruang rapat{{ $status ? ' dengan status ' . $status : '' }}.</p>
        </div>
    @else
        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-teal-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">No. Tiket</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Pemohon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Ruang</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Tanggal & Jam</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($bookings as $booking)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-teal-600">{{ $booking->ticket_no }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium">{{ $booking->nama }}</div>
                            <div class="text-gray-500 text-xs">{{ $booking->nip }} · {{ $booking->unitKerja->nama ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $booking->meetingRoom->nama ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            {{ $booking->tanggal->format('d/m/Y') }},
                            {{ substr($booking->jam_mulai, 0, 5) }} - {{ substr($booking->jam_selesai, 0, 5) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @switch($booking->status)
                                @case('menunggu')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                                    @break
                                @case('disetujui')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                                    @break
                                @case('ditolak')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                    @break
                                @default
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-700">Dibatalkan</span>
                            @endswitch
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.peminjaman-ruang-rapat.show', $booking->id) }}"
                               class="text-teal-600 hover:text-teal-900 font-medium">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection
