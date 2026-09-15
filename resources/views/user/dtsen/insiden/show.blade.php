@extends('layouts.authenticated')

@section('title', '- Detail Insiden DTSEN')
@section('header-title', 'Detail Laporan Insiden')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <a href="{{ route('user.dtsen.insiden.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>

    <div class="bg-white rounded-lg shadow-sm border p-6 mt-4 mb-6">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $item->jenis_label }}</h1>
                <p class="font-mono text-sm text-gray-500">{{ $item->ticket_no }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
        </div>

        @if ($item->terlambat)
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-900">
                Laporan disampaikan melewati batas 3x24 jam hari kerja
                (batas: {{ $item->batas_pelaporan?->format('d/m/Y H:i') ?? '-' }})
                @if ($item->escalated_at) dan telah dieskalasi pada {{ $item->escalated_at->format('d/m/Y H:i') }} @endif.
            </div>
        @endif

        <dl class="space-y-3 text-sm">
            <div><dt class="text-gray-500">Waktu Diketahui</dt><dd class="font-medium">{{ $item->waktu_diketahui->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-gray-500">Batas Pelaporan</dt><dd class="font-medium">{{ $item->batas_pelaporan?->format('d/m/Y H:i') ?? '-' }}</dd></div>
            <div><dt class="text-gray-500">Permohonan Terkait</dt><dd class="font-medium">{{ $item->dataRequest->ticket_no ?? '-' }}</dd></div>
            <div><dt class="text-gray-500">Kronologi</dt><dd class="whitespace-pre-line">{{ $item->kronologi }}</dd></div>
            <div><dt class="text-gray-500">Dampak</dt><dd class="whitespace-pre-line">{{ $item->dampak }}</dd></div>
            <div><dt class="text-gray-500">Tindakan Awal</dt><dd class="whitespace-pre-line">{{ $item->tindakan_awal }}</dd></div>
        </dl>

        @if ($item->file_path)
            <p class="mt-4 text-sm"><a href="{{ Storage::url($item->file_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 Lampiran</a></p>
        @endif

        @if ($item->tindak_lanjut)
            <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                <p class="font-semibold">Tindak lanjut Prosesor DTSEN</p>
                <p class="mt-1 whitespace-pre-line">{{ $item->tindak_lanjut }}</p>
                @if ($item->handledBy)
                    <p class="text-xs mt-2">{{ $item->handledBy->name }} — {{ $item->handled_at?->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="font-bold text-gray-800 mb-3">Riwayat</h3>
        <ul class="space-y-2 text-sm">
            @forelse ($logs as $log)
                <li class="flex flex-wrap gap-x-3 border-l-2 border-gray-200 pl-3 py-1">
                    <span class="text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                    <span class="font-medium">{{ $log->action }}</span>
                    <span class="text-gray-600">{{ $log->note }}</span>
                </li>
            @empty
                <li class="text-gray-500">Belum ada aktivitas.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
