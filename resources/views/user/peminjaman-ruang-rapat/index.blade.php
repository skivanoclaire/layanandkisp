@extends('layouts.authenticated')
@section('title', '- Peminjaman Ruang Rapat')
@section('header-title', 'Peminjaman Ruang Rapat')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-teal-700 mb-2">Peminjaman Ruang Rapat</h1>
            <p class="text-gray-600">Ajukan peminjaman ruang rapat untuk kegiatan kantor. Permohonan ditinjau oleh admin sebelum disetujui.</p>
        </div>
        <a href="{{ route('user.peminjaman-ruang-rapat.create') }}"
           class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Peminjaman
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-300 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
    @endif

    @if($bookings->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-600 mb-4">Belum ada permohonan peminjaman ruang rapat.</p>
            <a href="{{ route('user.peminjaman-ruang-rapat.create') }}"
               class="inline-block bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg font-semibold">
                Ajukan Permohonan Pertama
            </a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-teal-700 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">No. Tiket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Ruang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Tanggal & Jam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($bookings as $booking)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-teal-600">{{ $booking->ticket_no }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->meetingRoom->nama ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $booking->tanggal->format('d/m/Y') }},
                            {{ substr($booking->jam_mulai, 0, 5) }} - {{ substr($booking->jam_selesai, 0, 5) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($booking->status)
                                @case('menunggu')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                                    @break
                                @case('disetujui')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                                    @break
                                @case('ditolak')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                    @break
                                @default
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-700">Dibatalkan</span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('user.peminjaman-ruang-rapat.show', $booking->id) }}"
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
