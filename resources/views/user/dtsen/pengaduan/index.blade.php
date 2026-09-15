@extends('layouts.authenticated')

@section('title', '- Pengaduan Layanan DTSEN')
@section('header-title', 'Pengaduan, Saran & Masukan DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Pengaduan, Saran &amp; Masukan</h1>
            <p class="text-gray-600 mt-1">Sampaikan keluhan atau usulan perbaikan layanan berbagi pakai data DTSEN (Bab IX Juknis).</p>
        </div>
        <a href="{{ route('user.dtsen.pengaduan.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Sampaikan</a>
    </div>

    @include('partials.dtsen.errors')

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">No. Tiket</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-left">Disampaikan</th>
                    <th class="px-4 py-3 text-left">Identitas</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono">{{ $item->ticket_no }}</td>
                        <td class="px-4 py-3">{{ $item->kategori_label }}</td>
                        <td class="px-4 py-3">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $item->is_anonim ? 'Anonim' : 'Terbuka' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('user.dtsen.pengaduan.show', $item->id) }}" class="text-blue-600 hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada pengaduan/saran/masukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
