@extends('layouts.authenticated')
@section('title', '- Pemendek Tautan')
@section('header-title', 'Pemendek Tautan')

@section('content')
@php
    $badge = fn($s) => match($s) {
        'menunggu' => '<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>',
        'proses'   => '<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>',
        'selesai'  => '<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>',
        default    => '<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>',
    };
@endphp
<div class="container mx-auto px-4 max-w-6xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Permohonan Pemendek Tautan</h1>
        <a href="{{ route('user.shortlink.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">+ Ajukan Permohonan Baru</a>
    </div>

    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded text-sm text-blue-800">
        Layanan ini memendekkan tautan menjadi <strong>link.kaltaraprov.go.id/&lt;kode&gt;</strong>. Permohonan diperiksa admin Diskominfo; tautan dibuat setelah disetujui.
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">No. Tiket</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">URL Tujuan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Short Link</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tanggal Ajuan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($requests as $req)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap"><span class="font-mono text-purple-600 font-semibold">{{ $req->ticket_no }}</span></td>
                            <td class="px-4 py-3 max-w-xs truncate" title="{{ $req->long_url }}">{{ $req->long_url }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($req->short_url)
                                    <a href="{{ $req->short_url }}" target="_blank" class="text-blue-600 hover:underline font-mono text-sm">{{ $req->short_url }}</a>
                                    @unless($req->is_active)<span class="ml-1 text-xs text-red-600">(nonaktif)</span>@endunless
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">{{ optional($req->submitted_at ?? $req->created_at)->format('d/m/Y H:i') }} WITA</td>
                            <td class="px-4 py-3 whitespace-nowrap">{!! $badge($req->status) !!}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <a href="{{ route('user.shortlink.show', $req->id) }}" class="text-purple-600 hover:text-purple-900 font-semibold">Detail</a>
                                @if($req->status === 'menunggu')
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('user.shortlink.edit', $req->id) }}" class="text-blue-600 hover:text-blue-900 font-semibold">Ubah</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada permohonan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $requests->links() }}</div>
        @endif
    </div>
</div>
@endsection
