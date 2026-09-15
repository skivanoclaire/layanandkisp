@extends('layouts.authenticated')

@section('title', '- Detail Pengaduan DTSEN')
@section('header-title', 'Detail Pengaduan/Saran/Masukan')

@section('content')
<div class="container mx-auto p-6 max-w-3xl">
    <a href="{{ route('user.dtsen.pengaduan.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>

    <div class="bg-white rounded-lg shadow-sm border p-6 mt-4">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $item->kategori_label }}</h1>
                <p class="font-mono text-sm text-gray-500">{{ $item->ticket_no }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
        </div>

        <dl class="space-y-3 text-sm">
            <div><dt class="text-gray-500">Disampaikan</dt><dd class="font-medium">{{ $item->created_at->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-gray-500">Identitas</dt><dd class="font-medium">{{ $item->is_anonim ? 'Anonim (dirahasiakan)' : $item->displayName(true) }}</dd></div>
            <div><dt class="text-gray-500">Uraian</dt><dd class="whitespace-pre-line">{{ $item->uraian }}</dd></div>
        </dl>

        @if ($item->file_path)
            <p class="mt-4 text-sm"><a href="{{ Storage::url($item->file_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 Lampiran</a></p>
        @endif

        @if ($item->tindak_lanjut)
            <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                <p class="font-semibold">Tindak lanjut Prosesor DTSEN</p>
                <p class="mt-1 whitespace-pre-line">{{ $item->tindak_lanjut }}</p>
                <p class="text-xs mt-2">{{ $item->handled_at?->format('d/m/Y H:i') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
