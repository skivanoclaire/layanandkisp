@extends('layouts.authenticated')
@section('title', '- Admin: Detail Pemendek Tautan')
@section('header-title', 'Admin: Pemendek Tautan')

@section('content')
@php
    $badge = fn($s) => match($s) {
        'menunggu' => '<span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>',
        'proses'   => '<span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>',
        'selesai'  => '<span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>',
        default    => '<span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>',
    };
    $inp = 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500';
@endphp
<div class="container mx-auto px-4 max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Permohonan {{ $item->ticket_no }}</h1>
        <a href="{{ route('admin.shortlink.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">← Kembali</a>
    </div>

    @if(session('success'))<div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="grid md:grid-cols-3 gap-6">
        {{-- Detail --}}
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-md p-6 space-y-3">
                <div class="flex items-center justify-between"><span class="text-gray-500 text-sm">Status</span>{!! $badge($item->status) !!}</div>
                @if($item->short_url)
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="text-xs text-gray-500 mb-1">Short Link {{ $item->is_active ? '' : '(NONAKTIF)' }}</div>
                    <a href="{{ $item->short_url }}" target="_blank" class="text-lg font-mono font-semibold text-purple-700 hover:underline break-all">{{ $item->short_url }}</a>
                    <div class="mt-1 text-xs text-gray-500">{{ number_format($item->clicks) }} klik @if($item->stats_synced_at)· per {{ $item->stats_synced_at->format('d/m/Y H:i') }}@endif</div>
                </div>
                @endif
                <dl class="divide-y divide-gray-100 text-sm">
                    <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">URL Tujuan</dt><dd class="col-span-2 break-all">{{ $item->long_url }}</dd></div>
                    <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Judul</dt><dd class="col-span-2">{{ $item->title ?: '—' }}</dd></div>
                    <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Kode Diusulkan</dt><dd class="col-span-2 font-mono">{{ $item->requested_keyword ?: '—' }}</dd></div>
                    <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Keperluan</dt><dd class="col-span-2 whitespace-pre-line">{{ $item->keperluan }}</dd></div>
                    <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Pemohon</dt><dd class="col-span-2">{{ $item->nama }} @if($item->nip)· {{ $item->nip }}@endif @if($item->instansi)<br>{{ $item->instansi }}@endif @if($item->user)<br><span class="text-gray-400 text-xs">{{ $item->user->email }}</span>@endif</dd></div>
                    <div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Diajukan</dt><dd class="col-span-2">{{ optional($item->submitted_at ?? $item->created_at)->format('d/m/Y H:i') }} WITA</dd></div>
                    @if($item->processedBy)<div class="py-2 grid grid-cols-3 gap-2"><dt class="text-gray-500">Diproses oleh</dt><dd class="col-span-2">{{ $item->processedBy->name }}</dd></div>@endif
                </dl>
            </div>

            {{-- Catatan admin --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-sm font-semibold text-gray-700 uppercase mb-3">Catatan Admin</h2>
                <form action="{{ route('admin.shortlink.update-note', $item->id) }}" method="POST">
                    @csrf
                    <textarea name="admin_note" rows="3" class="{{ $inp }}">{{ old('admin_note', $item->admin_note) }}</textarea>
                    <button class="mt-2 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm font-semibold">Simpan Catatan</button>
                </form>
            </div>

            {{-- Riwayat --}}
            @if($item->logs->isNotEmpty())
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-sm font-semibold text-gray-700 uppercase mb-3">Riwayat</h2>
                <ol class="space-y-2 text-sm">
                    @foreach($item->logs->sortByDesc('created_at') as $log)
                    <li class="flex gap-3"><span class="text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m H:i') }}</span><span><span class="font-medium">{{ $log->action }}</span>@if($log->note) — {{ $log->note }}@endif<span class="text-gray-400"> ({{ $log->actor->name ?? 'sistem' }})</span></span></li>
                    @endforeach
                </ol>
            </div>
            @endif
        </div>

        {{-- Aksi --}}
        <div class="space-y-6">
            @if(in_array($item->status, ['menunggu','proses']))
                @if($item->status === 'menunggu')
                <div class="bg-white rounded-lg shadow-md p-5">
                    <h3 class="font-semibold text-gray-700 mb-2 text-sm">Tandai Diproses</h3>
                    <form action="{{ route('admin.shortlink.process', $item->id) }}" method="POST">
                        @csrf
                        <input type="text" name="note" placeholder="Catatan (opsional)" class="{{ $inp }} mb-2">
                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm font-semibold">Proses</button>
                    </form>
                </div>
                @endif

                <div class="bg-white rounded-lg shadow-md p-5 border-2 border-green-200">
                    <h3 class="font-semibold text-gray-700 mb-2 text-sm">Setujui &amp; Buat Short Link</h3>
                    <form action="{{ route('admin.shortlink.approve', $item->id) }}" method="POST">
                        @csrf
                        <label class="block text-xs text-gray-600 mb-1">Kode pendek (opsional — kosongkan untuk pakai usulan/otomatis)</label>
                        <div class="flex items-center mb-2">
                            <span class="px-2 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l text-gray-500 text-xs font-mono">/</span>
                            <input type="text" name="keyword" value="{{ old('keyword', $item->requested_keyword) }}" class="flex-1 px-2 py-2 border border-gray-300 rounded-r text-sm font-mono">
                        </div>
                        <input type="text" name="note" placeholder="Catatan (opsional)" class="{{ $inp }} mb-2">
                        <button class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-semibold">Setujui</button>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow-md p-5 border-2 border-red-200">
                    <h3 class="font-semibold text-gray-700 mb-2 text-sm">Tolak Permohonan</h3>
                    <form action="{{ route('admin.shortlink.reject', $item->id) }}" method="POST">
                        @csrf
                        <textarea name="note" rows="3" required placeholder="Alasan penolakan (wajib)" class="{{ $inp }} mb-2">{{ old('note') }}</textarea>
                        <button class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-sm font-semibold" onclick="return confirm('Tolak permohonan ini?')">Tolak</button>
                    </form>
                </div>
            @endif

            @if($item->status === 'selesai' && $item->keyword)
                <div class="bg-white rounded-lg shadow-md p-5">
                    <h3 class="font-semibold text-gray-700 mb-3 text-sm">Kelola Link</h3>

                    <form action="{{ route('admin.shortlink.refresh-stats', $item->id) }}" method="POST" class="mb-4">
                        @csrf
                        <button class="w-full bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm font-semibold">Segarkan Statistik Klik</button>
                    </form>

                    @if($item->is_active)
                    <form action="{{ route('admin.shortlink.update-destination', $item->id) }}" method="POST" class="mb-4 border-t pt-4">
                        @csrf
                        <label class="block text-xs text-gray-600 mb-1">Ubah URL Tujuan</label>
                        <input type="url" name="new_url" value="{{ old('new_url', $item->long_url) }}" required class="{{ $inp }} mb-2">
                        <input type="text" name="note" placeholder="Catatan (opsional)" class="{{ $inp }} mb-2">
                        <button class="w-full bg-amber-600 hover:bg-amber-700 text-white px-3 py-2 rounded text-sm font-semibold">Simpan Tujuan Baru</button>
                    </form>

                    <form action="{{ route('admin.shortlink.disable', $item->id) }}" method="POST" class="border-t pt-4">
                        @csrf
                        <input type="text" name="note" placeholder="Alasan (opsional)" class="{{ $inp }} mb-2">
                        <button class="w-full bg-red-700 hover:bg-red-800 text-white px-3 py-2 rounded text-sm font-semibold" onclick="return confirm('Nonaktifkan (hapus) short link ini dari YOURLS?')">Nonaktifkan / Hapus Link</button>
                    </form>
                    @else
                    <form action="{{ route('admin.shortlink.enable', $item->id) }}" method="POST" class="border-t pt-4">
                        @csrf
                        <p class="text-xs text-gray-500 mb-2">Link sedang nonaktif. Aktifkan kembali akan membuat ulang <code>/{{ $item->keyword }}</code> di YOURLS.</p>
                        <button class="w-full bg-green-700 hover:bg-green-800 text-white px-3 py-2 rounded text-sm font-semibold">Aktifkan Kembali</button>
                    </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
