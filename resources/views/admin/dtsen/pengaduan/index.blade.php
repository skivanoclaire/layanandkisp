@extends('layouts.authenticated')

@section('title', '- Pengaduan DTSEN')
@section('header-title', 'Pengaduan, Saran & Masukan DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pengaduan, Saran &amp; Masukan</h1>
        <p class="text-gray-600 mt-1">Form G.1 — tindak lanjut oleh Prosesor DTSEN (Bab IX Juknis).</p>
    </div>

    @include('partials.dtsen.errors')

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\DtsenComplaint::statusLabels() as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
            <select name="kategori" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\DtsenComplaint::kategoriLabels() as $val => $label)
                    <option value="{{ $val }}" @selected(request('kategori') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Terapkan</button>
        <a href="{{ route('admin.dtsen.pengaduan.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Reset</a>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">No. Tiket</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-left">Pelapor</th>
                    <th class="px-4 py-3 text-left">Disampaikan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono">{{ $item->ticket_no }}</td>
                        <td class="px-4 py-3">{{ $item->kategori_label }}</td>
                        <td class="px-4 py-3">
                            {{ $item->displayName() }}
                            @if ($item->is_anonim)
                                <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-200 text-gray-600">identitas dirahasiakan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.dtsen.pengaduan.show', $item->id) }}" class="text-blue-600 hover:underline">Tindak Lanjut</a>
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
