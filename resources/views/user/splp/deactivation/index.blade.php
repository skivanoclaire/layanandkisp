@extends('layouts.authenticated')

@section('title', '- Penonaktifan/Pencabutan SPLP')
@section('header-title', 'Penonaktifan / Pencabutan Endpoint (SPLP)')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Penonaktifan / Pencabutan</h1>
            <p class="text-gray-600 mt-1">Permohonan penonaktifan/pencabutan SPLP Anda.</p>
        </div>
        <a href="{{ route('user.splp.deactivation.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Ajukan Baru</a>
    </div>

    @if (session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">No. Tiket</th>
                    <th class="px-4 py-3 text-left">Objek</th>
                    <th class="px-4 py-3 text-left">Tindakan</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono">{{ $item->ticket_no }}</td>
                        <td class="px-4 py-3">
                            @if ($item->target_type === 'service')
                                {{ $item->service->nama_layanan ?? 'Layanan' }}
                            @else
                                {{ $item->consumer->nama_konsumen ?? 'Konsumen' }}
                            @endif
                            @if ($item->is_darurat)<span class="ml-1 text-xs text-red-600 font-semibold">[Darurat]</span>@endif
                        </td>
                        <td class="px-4 py-3">{{ ucfirst($item->jenis_tindakan) }}</td>
                        <td class="px-4 py-3">{{ optional($item->submitted_at ?? $item->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span></td>
                        <td class="px-4 py-3 text-center">
                            @if ($item->isEditableByOwner())
                                <a href="{{ route('user.splp.deactivation.edit', $item->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('user.splp.deactivation.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus permohonan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline ml-2">Hapus</button>
                                </form>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada permohonan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
