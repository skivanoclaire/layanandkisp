@extends('layouts.authenticated')
@section('title', '- Detail Pasang Baru Internet')
@section('header-title', 'Detail Pasang Baru Internet')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('user.internet.pasang-baru.index') }}" class="text-purple-600 hover:text-purple-900 font-medium">&larr; Kembali ke daftar</a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6 border-b pb-4">
            <div>
                <p class="text-sm text-gray-500">No. Tiket</p>
                <p class="text-xl font-bold text-purple-700">{{ $installation->ticket_no }}</p>
            </div>
            @switch($installation->status)
                @case('menunggu')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                    @break
                @case('proses')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                    @break
                @case('selesai')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Terpasang</span>
                    @break
                @default
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
            @endswitch
        </div>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500">Nama Pemohon</dt>
                <dd class="font-medium text-gray-900">{{ $installation->nama }} ({{ $installation->nip }})</dd>
            </div>
            <div>
                <dt class="text-gray-500">Unit Kerja</dt>
                <dd class="font-medium text-gray-900">{{ $installation->unitKerja->nama ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">No. HP/WhatsApp</dt>
                <dd class="font-medium text-gray-900">{{ $installation->no_hp }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tanggal Pengajuan</dt>
                <dd class="font-medium text-gray-900">{{ $installation->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-gray-500">Lokasi Pemasangan</dt>
                <dd class="font-medium text-gray-900 whitespace-pre-line">{{ $installation->lokasi_pemasangan }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Ruangan / Lantai</dt>
                <dd class="font-medium text-gray-900">{{ $installation->ruangan ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">PIC di Lokasi</dt>
                <dd class="font-medium text-gray-900">{{ $installation->pic_lokasi ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Jumlah Titik</dt>
                <dd class="font-medium text-gray-900">{{ $installation->jumlah_titik }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-gray-500">Uraian Kebutuhan</dt>
                <dd class="text-gray-900 whitespace-pre-line">{{ $installation->uraian_kebutuhan }}</dd>
            </div>
        </dl>

        @if($installation->admin_notes)
            <div class="mt-6 rounded-lg border p-4 {{ $installation->status === 'ditolak' ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-200' }}">
                <p class="text-sm font-semibold text-gray-800 mb-1">Catatan Admin</p>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $installation->admin_notes }}</p>
                @if($installation->processedBy)
                    <p class="text-xs text-gray-500 mt-2">Oleh {{ $installation->processedBy->name }}</p>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
