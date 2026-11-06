@extends('layouts.authenticated')
@section('title', '- Detail Pasang Baru Internet')
@section('header-title', 'Detail Pasang Baru Internet')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6">
        <a href="{{ route('admin.internet.pasang-baru.index') }}" class="text-purple-600 hover:text-purple-900 font-medium">&larr; Kembali ke daftar</a>
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
                <p class="text-xl font-bold text-purple-700">{{ $installation->ticket_no }}</p>
            </div>
            @switch($installation->status)
                @case('menunggu')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                    @break
                @case('proses')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>
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
                <dt class="text-gray-500">Pemohon</dt>
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

        @if($installation->processedBy)
            <p class="text-xs text-gray-500 mt-6">Terakhir ditangani oleh {{ $installation->processedBy->name }}</p>
        @endif
    </div>

    {{-- Tindakan sesuai alur status: menunggu → proses → selesai, atau ditolak --}}
    @if($installation->status === 'menunggu')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-blue-700 mb-3">Mulai Proses</h2>
            <form action="{{ route('admin.internet.pasang-baru.process', $installation->id) }}" method="POST">
                @csrf
                <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan (opsional)</label>
                <textarea name="admin_notes" rows="3" maxlength="1000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-4"
                          placeholder="Contoh: Jadwal survei dengan PIC lokasi pada Senin"></textarea>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">Proses</button>
            </form>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-red-700 mb-3">Tolak Permohonan</h2>
            <form action="{{ route('admin.internet.pasang-baru.reject', $installation->id) }}" method="POST">
                @csrf
                <label class="block text-sm font-semibold text-gray-700 mb-2">Alasan penolakan <span class="text-red-500">*</span></label>
                <textarea name="admin_notes" rows="3" required maxlength="1000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 mb-4"
                          placeholder="Contoh: Lokasi belum dapat dijangkau jaringan fiber"></textarea>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">Tolak</button>
            </form>
        </div>
    </div>
    @elseif($installation->status === 'proses')
    <div class="bg-white rounded-lg shadow p-6 mb-6 space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-green-700 mb-3">Tandai Terpasang</h2>
            <form action="{{ route('admin.internet.pasang-baru.complete', $installation->id) }}" method="POST">
                @csrf
                <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan (opsional)</label>
                <textarea name="admin_notes" rows="2" maxlength="1000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 mb-4"
                          placeholder="Contoh: Terpasang di 2 titik, IP sudah diuji"></textarea>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">Selesaikan</button>
            </form>
        </div>
        <div class="border-t pt-6">
            <h2 class="text-lg font-semibold text-red-700 mb-3">Tolak Permohonan</h2>
            <form action="{{ route('admin.internet.pasang-baru.reject', $installation->id) }}" method="POST">
                @csrf
                <label class="block text-sm font-semibold text-gray-700 mb-2">Alasan penolakan <span class="text-red-500">*</span></label>
                <textarea name="admin_notes" rows="2" required maxlength="1000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 mb-4"></textarea>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">Tolak</button>
            </form>
        </div>
    </div>
    @endif

    @if(in_array($installation->status, ['proses', 'selesai']))
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Perbarui Catatan Admin</h2>
        <form action="{{ route('admin.internet.pasang-baru.update-notes', $installation->id) }}" method="POST">
            @csrf
            <textarea name="admin_notes" rows="3" maxlength="1000"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 mb-4">{{ $installation->admin_notes }}</textarea>
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold">Simpan Catatan</button>
        </form>
    </div>
    @endif
</div>
@endsection
