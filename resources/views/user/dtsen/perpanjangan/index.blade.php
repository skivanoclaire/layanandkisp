@extends('layouts.authenticated')

@section('title', '- Perpanjangan Akses DTSEN')
@section('header-title', 'Perpanjangan Masa Akses DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Perpanjangan Masa Akses</h1>
        <p class="text-gray-600 mt-1">Riwayat permohonan perpanjangan token/tautan unduh data DTSEN (Form 4.4).</p>
    </div>

    @include('partials.dtsen.errors')

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Permohonan</th>
                    <th class="px-4 py-3 text-left">Alasan</th>
                    <th class="px-4 py-3 text-center">Durasi</th>
                    <th class="px-4 py-3 text-left">Diajukan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('user.dtsen.permohonan.show', $item->dtsen_data_request_id) }}"
                               class="font-mono text-blue-600 hover:underline">{{ $item->dataRequest->ticket_no ?? '-' }}</a>
                            <p class="text-xs text-gray-500">{{ $item->dataRequest->nama_program ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 max-w-xs">{{ $item->alasan }}</td>
                        <td class="px-4 py-3 text-center">{{ $item->durasi_hari }} hari</td>
                        <td class="px-4 py-3">{{ $item->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $item->catatan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada permohonan perpanjangan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
