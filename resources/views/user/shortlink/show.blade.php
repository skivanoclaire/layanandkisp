@extends('layouts.authenticated')
@section('title', '- Detail Permohonan Pemendek Tautan')
@section('header-title', 'Pemendek Tautan')

@section('content')
@php
    $badge = fn($s) => match($s) {
        'menunggu' => '<span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>',
        'proses'   => '<span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>',
        'selesai'  => '<span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>',
        default    => '<span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>',
    };
@endphp
<div class="container mx-auto px-4 max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Permohonan {{ $item->ticket_no }}</h1>
        <a href="{{ route('user.shortlink.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">← Kembali</a>
    </div>

    @if(session('success'))<div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>@endif

    <div class="bg-white rounded-lg shadow-md p-6 space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-gray-500 text-sm">Status</span>
            {!! $badge($item->status) !!}
        </div>

        @if($item->short_url)
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="text-xs text-gray-500 mb-1">Short Link</div>
            <a href="{{ $item->short_url }}" target="_blank" class="text-lg font-mono font-semibold text-purple-700 hover:underline break-all">{{ $item->short_url }}</a>
            @unless($item->is_active)<div class="mt-1 text-sm text-red-600">Link ini sedang dinonaktifkan.</div>@endunless
            @if($item->stats_synced_at)<div class="mt-1 text-xs text-gray-500">{{ number_format($item->clicks) }} klik · per {{ $item->stats_synced_at->format('d/m/Y H:i') }}</div>@endif
        </div>
        @endif

        <dl class="divide-y divide-gray-100 text-sm">
            <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">URL Tujuan</dt><dd class="col-span-2 break-all">{{ $item->long_url }}</dd></div>
            <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Judul</dt><dd class="col-span-2">{{ $item->title ?: '—' }}</dd></div>
            <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Kode Diusulkan</dt><dd class="col-span-2 font-mono">{{ $item->requested_keyword ?: '—' }}</dd></div>
            <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Keperluan</dt><dd class="col-span-2 whitespace-pre-line">{{ $item->keperluan }}</dd></div>
            <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Pemohon</dt><dd class="col-span-2">{{ $item->nama }} @if($item->nip)· {{ $item->nip }}@endif @if($item->instansi)<br>{{ $item->instansi }}@endif</dd></div>
            <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Diajukan</dt><dd class="col-span-2">{{ optional($item->submitted_at ?? $item->created_at)->format('d/m/Y H:i') }} WITA</dd></div>
            @if($item->admin_note)<div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Catatan Admin</dt><dd class="col-span-2 whitespace-pre-line">{{ $item->admin_note }}</dd></div>@endif
        </dl>

        @if($item->status === 'menunggu')
        <div class="flex gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route('user.shortlink.edit', $item->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold text-sm">Ubah Permohonan</a>
            <form action="{{ route('user.shortlink.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Batalkan permohonan ini?')">
                @csrf @method('DELETE')
                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold text-sm">Batalkan</button>
            </form>
        </div>
        @endif
    </div>

    @if($item->logs->isNotEmpty())
    <div class="mt-6 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase mb-3">Riwayat</h2>
        <ol class="space-y-2 text-sm">
            @foreach($item->logs->sortByDesc('created_at') as $log)
            <li class="flex gap-3">
                <span class="text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m H:i') }}</span>
                <span><span class="font-medium">{{ $log->action }}</span>@if($log->note) — {{ $log->note }}@endif<span class="text-gray-400"> ({{ $log->actor->name ?? 'sistem' }})</span></span>
            </li>
            @endforeach
        </ol>
    </div>
    @endif
</div>
@endsection
