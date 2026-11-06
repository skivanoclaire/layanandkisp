@extends('layouts.authenticated')
@section('title', '- Detail Peminjaman Ruang Rapat')
@section('header-title', 'Detail Peminjaman Ruang Rapat')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6">
        <a href="{{ route('admin.peminjaman-ruang-rapat.index') }}" class="text-teal-600 hover:text-teal-900 font-medium">&larr; Kembali ke daftar</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-6 border-b pb-4">
            <div>
                <p class="text-sm text-gray-500">No. Tiket</p>
                <p class="text-xl font-bold text-teal-700">{{ $booking->ticket_no }}</p>
            </div>
            @switch($booking->status)
                @case('menunggu')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                    @break
                @case('disetujui')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                    @break
                @case('ditolak')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                    @break
                @default
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-200 text-gray-700">Dibatalkan</span>
            @endswitch
        </div>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500">Pemohon</dt>
                <dd class="font-medium text-gray-900">{{ $booking->nama }} ({{ $booking->nip }})</dd>
            </div>
            <div>
                <dt class="text-gray-500">Unit Kerja</dt>
                <dd class="font-medium text-gray-900">{{ $booking->unitKerja->nama ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">No. HP/WhatsApp</dt>
                <dd class="font-medium text-gray-900">{{ $booking->no_hp }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Ruang Rapat</dt>
                <dd class="font-medium text-gray-900">{{ $booking->meetingRoom->nama ?? '-' }}{{ $booking->meetingRoom?->lokasi ? ' · ' . $booking->meetingRoom->lokasi : '' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tanggal</dt>
                <dd class="font-medium text-gray-900">{{ $booking->tanggal->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Waktu</dt>
                <dd class="font-medium text-gray-900">{{ substr($booking->jam_mulai, 0, 5) }} - {{ substr($booking->jam_selesai, 0, 5) }} WITA</dd>
            </div>
            <div>
                <dt class="text-gray-500">Jumlah Peserta</dt>
                <dd class="font-medium text-gray-900">{{ $booking->jumlah_peserta }} orang (kapasitas {{ $booking->meetingRoom->kapasitas ?? '-' }})</dd>
            </div>
            <div>
                <dt class="text-gray-500">Diajukan pada</dt>
                <dd class="font-medium text-gray-900">{{ $booking->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-gray-500">Keperluan</dt>
                <dd class="text-gray-900 whitespace-pre-line">{{ $booking->keperluan }}</dd>
            </div>
        </dl>

        @if($booking->admin_notes)
            <div class="mt-6 rounded-lg border bg-blue-50 border-blue-200 p-4">
                <p class="text-sm font-semibold text-gray-800 mb-1">Catatan Admin</p>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $booking->admin_notes }}</p>
                @if($booking->processedBy)
                    <p class="text-xs text-gray-500 mt-2">Oleh {{ $booking->processedBy->name }}{{ $booking->processed_at ? ' pada ' . $booking->processed_at->format('d/m/Y H:i') : '' }}</p>
                @endif
            </div>
        @endif
    </div>

    @if($booking->status === 'menunggu')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-green-700 mb-3">Setujui Peminjaman</h2>
            <form action="{{ route('admin.peminjaman-ruang-rapat.approve', $booking->id) }}" method="POST">
                @csrf
                <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan (opsional)</label>
                <textarea name="admin_notes" rows="3" maxlength="1000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 mb-4"
                          placeholder="Contoh: Ruang sudah disiapkan, harap kembalikan kondisi ruang seperti semula"></textarea>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                    Setujui
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-red-700 mb-3">Tolak Peminjaman</h2>
            <form action="{{ route('admin.peminjaman-ruang-rapat.reject', $booking->id) }}" method="POST">
                @csrf
                <label class="block text-sm font-semibold text-gray-700 mb-2">Alasan penolakan <span class="text-red-500">*</span></label>
                <textarea name="admin_notes" rows="3" required maxlength="1000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 mb-4"
                          placeholder="Contoh: Ruang sedang digunakan untuk kegiatan internal"></textarea>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">
                    Tolak
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
