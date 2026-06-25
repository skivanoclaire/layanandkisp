@extends('layouts.authenticated')

@section('title', '- Kelola Penonaktifan/Pencabutan SPLP')
@section('header-title', 'Kelola Permohonan — Penonaktifan/Pencabutan (SPLP)')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Penonaktifan / Pencabutan (V5)</h1>
    <p class="text-gray-600 mb-6">Nilai dampak & eksekusi penonaktifan/pencabutan objek SPLP.</p>

    @if (session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('status') }}</div>
    @endif

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\SplpDeactivationRequest::statusLabels() as $val => $label)
                    @continue($val === 'draft')
                    <option value="{{ $val }}" @selected($status === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Dari Tanggal</label>
            <input type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Sampai Tanggal</label>
            <input type="date" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Filter</button>
        <a href="{{ route('admin.splp.deactivation.index') }}" class="px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">Reset</a>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">No. Tiket</th>
                    <th class="px-4 py-3 text-left">Objek</th>
                    <th class="px-4 py-3 text-left">Tindakan</th>
                    <th class="px-4 py-3 text-left">Pemohon</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50 {{ $item->is_darurat ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 font-mono">{{ $item->ticket_no }}</td>
                        <td class="px-4 py-3">
                            {{ $item->target_type === 'service' ? ($item->service->nama_layanan ?? 'Layanan') : ($item->consumer->nama_konsumen ?? 'Konsumen') }}
                            @if ($item->is_darurat)<span class="ml-1 text-xs text-red-600 font-semibold">[Darurat]</span>@endif
                        </td>
                        <td class="px-4 py-3">{{ ucfirst($item->jenis_tindakan) }}</td>
                        <td class="px-4 py-3">{{ $item->nama }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span></td>
                        <td class="px-4 py-3 text-center"><a href="{{ route('admin.splp.deactivation.show', $item->id) }}" class="text-blue-600 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada permohonan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
