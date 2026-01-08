@extends('layouts.authenticated')
@section('title', '- Detail Akses JIP PDNS')
@section('header-title', 'Detail Akses JIP PDNS')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan Akses JIP PDNS</h1>
        <a href="{{ route('admin.vpn.jip-pdns.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
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
    </div>

    <!-- Action Buttons -->
    @if($jipPdnsRequest->status === 'menunggu')
    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-3">Tindakan untuk Permohonan Ini</h3>
        <div class="flex gap-3">
            <button onclick="document.getElementById('formProses').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                Proses Permohonan
            </button>
            <button onclick="document.getElementById('formTolak').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">
                Tolak Permohonan
            </button>
        </div>

        <!-- Form Proses -->
        <form id="formProses" action="{{ route('admin.vpn.jip-pdns.process', $jipPdnsRequest->id) }}" method="POST" style="display:none;" class="mt-4">
            @csrf
            <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan (opsional):</label>
            <textarea name="admin_notes" rows="3" class="w-full px-3 py-2 border border-blue-300 rounded-lg"></textarea>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Konfirmasi Proses</button>
                <button type="button" onclick="this.closest('form').style.display='none'; this.closest('.bg-yellow-50').querySelector('.flex.gap-3').style.display='flex';"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">Batal</button>
            </div>
        </form>

        <!-- Form Tolak -->
        <form id="formTolak" action="{{ route('admin.vpn.jip-pdns.reject', $jipPdnsRequest->id) }}" method="POST" style="display:none;" class="mt-4">
            @csrf
            <label class="block text-sm font-semibold text-red-700 mb-1">Alasan Penolakan: <span class="text-red-500">*</span></label>
            <textarea name="admin_notes" rows="3" required class="w-full px-3 py-2 border border-red-300 rounded-lg" placeholder="Jelaskan alasan penolakan..."></textarea>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">Konfirmasi Tolak</button>
                <button type="button" onclick="this.closest('form').style.display='none'; this.closest('.bg-yellow-50').querySelector('.flex.gap-3').style.display='flex';"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">Batal</button>
            </div>
        </form>
    </div>
    @endif

    @if($jipPdnsRequest->status === 'proses')
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-blue-800 mb-3">Selesaikan Permohonan</h3>
        <form action="{{ route('admin.vpn.jip-pdns.complete', $jipPdnsRequest->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-blue-700 mb-1">Keterangan/Respon untuk User: <span class="text-red-500">*</span></label>
                <textarea name="keterangan_admin" rows="5" required
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Berikan informasi kepada user mengenai akses JIP PDNS atau informasi Segment IPSec yang diminta...">{{ $jipPdnsRequest->keterangan_admin }}</textarea>
                <p class="mt-1 text-xs text-blue-600">Berikan informasi lengkap mengenai akses JIP PDNS, Segment IPSec, atau detail teknis lainnya</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan Internal (opsional):</label>
                <textarea name="admin_notes" rows="2"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Catatan untuk admin">{{ $jipPdnsRequest->admin_notes }}</textarea>
            </div>
            <button type="submit" class="mt-3 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                Selesaikan Permohonan
            </button>
        </form>
    </div>
    @endif

    @if(in_array($jipPdnsRequest->status, ['proses', 'selesai']))
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Catatan Admin</h3>
        <form action="{{ route('admin.vpn.jip-pdns.update-notes', $jipPdnsRequest->id) }}" method="POST">
            @csrf
            <textarea name="admin_notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Tambahkan catatan...">{{ $jipPdnsRequest->admin_notes }}</textarea>
            <div class="mt-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">
                    Update Catatan
                </button>
            </div>
        </form>

        @if($jipPdnsRequest->processedBy)
        <div class="mt-4 pt-4 border-t">
            <p class="text-sm text-gray-600">Diproses oleh: <span class="font-semibold">{{ $jipPdnsRequest->processedBy->name }}</span></p>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
