@extends('layouts.authenticated')
@section('title', '- Detail Akses JIP PDNS')
@section('header-title', 'Detail Akses JIP PDNS')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan Akses JIP PDNS</h1>
        <a href="{{ route('user.vpn.jip-pdns.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <!-- Ticket & Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">No. Tiket:</label>
                <p class="text-gray-800 font-mono text-purple-600 font-bold">{{ $jipPdnsRequest->ticket_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status:</label>
                @if($jipPdnsRequest->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                @elseif($jipPdnsRequest->status === 'proses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                @elseif($jipPdnsRequest->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Diajukan:</label>
                <p class="text-gray-800">{{ $jipPdnsRequest->created_at->format('d/m/Y H:i') }} WITA</p>
            </div>
        </div>

        <!-- Informasi Pemohon -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama:</label>
                    <p class="text-gray-800">{{ $jipPdnsRequest->nama }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP:</label>
                    <p class="text-gray-800">{{ $jipPdnsRequest->nip }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Pemohon:</label>
                    @if($jipPdnsRequest->is_kabupaten_kota)
                        <p class="text-gray-800">
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm font-semibold">Kabupaten/Kota</span>
                        </p>
                    @else
                        <p class="text-gray-800">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm font-semibold">Provinsi</span>
                        </p>
                    @endif
                </div>
                @if($jipPdnsRequest->is_kabupaten_kota)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kabupaten/Kota:</label>
                        <p class="text-gray-800">{{ $jipPdnsRequest->kabupaten_kota }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                        <p class="text-gray-800">{{ $jipPdnsRequest->unit_kerja_manual }}</p>
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                        <p class="text-gray-800">{{ $jipPdnsRequest->unitKerja->nama ?? '-' }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Detail Permohonan -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Permohonan</h2>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Uraian Permohonan:</label>
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $jipPdnsRequest->uraian_permohonan }}</p>
                </div>
            </div>
            @if($jipPdnsRequest->keterangan)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan / Informasi Tambahan:</label>
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $jipPdnsRequest->keterangan }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Respon Admin (jika sudah selesai) -->
        @if($jipPdnsRequest->status === 'selesai' && $jipPdnsRequest->keterangan_admin)
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <h3 class="text-lg font-semibold text-green-800 mb-3">Respon Admin</h3>
            <div class="bg-white p-3 rounded border border-green-300">
                <p class="text-green-900 whitespace-pre-wrap">{{ $jipPdnsRequest->keterangan_admin }}</p>
            </div>
        </div>
        @endif

        <!-- Admin Notes -->
        @if($jipPdnsRequest->admin_notes)
        <div class="bg-gray-50 p-4 rounded border">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Catatan Admin:</h3>
            <p class="text-gray-800 whitespace-pre-wrap">{{ $jipPdnsRequest->admin_notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
