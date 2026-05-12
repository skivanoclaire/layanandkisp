@extends('layouts.authenticated')
@section('title', '- Admin: Pemendek Tautan')
@section('header-title', 'Admin: Pemendek Tautan')

@section('content')
@php
    $badge = fn($s) => match($s) {
        'menunggu' => '<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>',
        'proses'   => '<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>',
        'selesai'  => '<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>',
        default    => '<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>',
    };
@endphp
<div class="container mx-auto px-4 max-w-7xl">
    <h1 class="text-2xl font-bold text-purple-700 mb-4">Kelola Permohonan Pemendek Tautan</h1>

    @if(session('success'))<div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>@endif

    <form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
        <div>
            <label class="block text-xs text-gray-600 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="">Semua</option>
                @foreach(['menunggu'=>'Menunggu','proses'=>'Diproses','selesai'=>'Selesai','ditolak'=>'Ditolak'] as $k=>$v)
                    <option value="{{ $k }}" @selected($status===$k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="tiket / nama / NIP / URL / kode" class="border border-gray-300 rounded px-3 py-2 text-sm w-64">
        </div>
        <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm font-semibold">Filter</button>
        @if(request('status') || request('search'))<a href="{{ route('admin.shortlink.index') }}" class="px-3 py-2 text-sm text-gray-600 hover:underline">Reset</a>@endif
    </form>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tiket</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Pemohon</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">URL Tujuan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Short Link</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Klik</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($requests as $req)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap font-mono text-purple-600 font-semibold text-sm">{{ $req->ticket_no }}</td>
                            <td class="px-4 py-3 text-sm">{{ $req->nama }}<br><span class="text-gray-400 text-xs">{{ $req->instansi ?: ($req->nip ?: '—') }}</span></td>
                            <td class="px-4 py-3 max-w-[14rem] truncate text-sm" title="{{ $req->long_url }}">{{ $req->long_url }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @if($req->short_url)
                                    <a href="{{ $req->short_url }}" target="_blank" class="text-blue-600 hover:underline font-mono">/{{ $req->keyword }}</a>
                                    @unless($req->is_active)<span class="ml-1 text-xs text-red-600">(off)</span>@endunless
                                @elseif($req->requested_keyword)
                                    <span class="text-gray-400 text-xs">usulan: /{{ $req->requested_keyword }}</span>
                                @else <span class="text-gray-400">—</span>@endif
                            </td>
                            <td class="px-4 py-3 text-sm">{{ number_format($req->clicks) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs">{{ optional($req->submitted_at ?? $req->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{!! $badge($req->status) !!}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm"><a href="{{ route('admin.shortlink.show', $req->id) }}" class="text-purple-600 hover:text-purple-900 font-semibold">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())<div class="px-4 py-3 border-t border-gray-200">{{ $requests->links() }}</div>@endif
    </div>
</div>
@endsection
