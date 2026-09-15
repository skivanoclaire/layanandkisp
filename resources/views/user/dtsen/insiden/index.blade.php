@extends('layouts.authenticated')

@section('title', '- Insiden Keamanan DTSEN')
@section('header-title', 'Insiden Keamanan Data DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Laporan Insiden Keamanan Data</h1>
            <p class="text-gray-600 mt-1">Wajib dilaporkan maksimal 3x24 jam hari kerja sejak insiden diketahui.</p>
        </div>
        <a href="{{ route('user.dtsen.insiden.create') }}"
           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">+ Lapor Insiden</a>
    </div>

    @include('partials.dtsen.errors')

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">No. Tiket</th>
                    <th class="px-4 py-3 text-left">Jenis</th>
                    <th class="px-4 py-3 text-left">Diketahui</th>
                    <th class="px-4 py-3 text-left">Permohonan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono">{{ $item->ticket_no }}</td>
                        <td class="px-4 py-3">{{ $item->jenis_label }}</td>
                        <td class="px-4 py-3">
                            {{ $item->waktu_diketahui->format('d/m/Y H:i') }}
                            @if ($item->terlambat)
                                <span class="block text-xs text-red-600 font-semibold">Dilaporkan melewati batas</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $item->dataRequest->ticket_no ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('user.dtsen.insiden.show', $item->id) }}" class="text-blue-600 hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada laporan insiden.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
