@extends('layouts.authenticated')

@section('title', '- Tindak Lanjut Pengaduan DTSEN')
@section('header-title', 'Tindak Lanjut Pengaduan DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <a href="{{ route('admin.dtsen.pengaduan.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>

    <div class="mt-4">@include('partials.dtsen.errors')</div>

    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $item->kategori_label }}</h1>
                <p class="font-mono text-sm text-gray-500">{{ $item->ticket_no }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
        </div>

        @if ($item->is_anonim)
            <div class="mb-4 rounded-lg border border-gray-300 bg-gray-50 p-3 text-xs text-gray-700">
                Pelapor memilih anonim. Identitas di bawah ini hanya dibuka untuk keperluan tindak lanjut oleh
                Prosesor DTSEN dan tidak ditampilkan pada rekapitulasi.
            </div>
        @endif

        <dl class="space-y-3 text-sm">
            <div><dt class="text-gray-500">Disampaikan</dt><dd class="font-medium">{{ $item->created_at->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-gray-500">Pelapor</dt><dd class="font-medium">{{ $item->displayName(true) }}</dd></div>
            <div><dt class="text-gray-500">Kontak</dt><dd class="font-medium">{{ $item->kontak_pelapor ?: '-' }}</dd></div>
            <div><dt class="text-gray-500">Uraian</dt><dd class="whitespace-pre-line">{{ $item->uraian }}</dd></div>
        </dl>

        @if ($item->file_path)
            <p class="mt-4 text-sm"><a href="{{ Storage::url($item->file_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 Lampiran</a></p>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h3 class="font-bold text-gray-800 mb-4">Tindak Lanjut</h3>
        <form action="{{ route('admin.dtsen.pengaduan.update', $item->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Uraian Tindak Lanjut <span class="text-red-500">*</span></label>
                <textarea name="tindak_lanjut" rows="5" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $item->tindak_lanjut }}</textarea>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        @foreach (\App\Models\DtsenComplaint::statusLabels() as $val => $label)
                            <option value="{{ $val }}" @selected($item->status === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold text-sm">Simpan</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="font-bold text-gray-800 mb-3">Riwayat Penanganan</h3>
        <ul class="space-y-2 text-sm">
            @forelse ($logs as $log)
                <li class="flex flex-wrap gap-x-3 border-l-2 border-gray-200 pl-3 py-1">
                    <span class="text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                    <span class="font-medium">{{ $log->action }}</span>
                    <span class="text-gray-600">{{ $log->note }}@if($log->actor) — {{ $log->actor->name }}@endif</span>
                </li>
            @empty
                <li class="text-gray-500">Belum ada riwayat.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
