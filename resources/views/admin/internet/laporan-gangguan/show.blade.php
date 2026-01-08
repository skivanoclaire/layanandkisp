@extends('layouts.authenticated')
@section('title', '- Detail Laporan Gangguan')
@section('header-title', 'Detail Laporan Gangguan')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Laporan Gangguan</h1>
        <a href="{{ route('admin.internet.laporan-gangguan.index') }}"
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
                <p class="text-gray-800 font-mono text-purple-600 font-bold">{{ $laporan->ticket_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status:</label>
                @if($laporan->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                @elseif($laporan->status === 'proses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                @elseif($laporan->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Dilaporkan:</label>
                <p class="text-gray-800">{{ $laporan->created_at->format('d/m/Y H:i') }} WITA</p>
            </div>
        </div>

        <!-- Informasi Pelapor -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pelapor</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama:</label>
                    <p class="text-gray-800">{{ $laporan->nama }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP:</label>
                    <p class="text-gray-800">{{ $laporan->nip }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                    <p class="text-gray-800">{{ $laporan->unitKerja->nama ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">No. HP/WhatsApp:</label>
                    <p class="text-gray-800">{{ $laporan->no_hp }}</p>
                </div>
            </div>
        </div>

        <!-- Detail Permasalahan -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Permasalahan</h2>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Uraian Permasalahan:</label>
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <p class="text-gray-800 whitespace-pre-wrap">{{ $laporan->uraian_permasalahan }}</p>
                </div>
            </div>

            @if($laporan->lokasi_koordinat)
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi/Koordinat:</label>
                <p class="text-gray-800">{{ $laporan->lokasi_koordinat }}</p>
            </div>
            @endif

            @if($laporan->lampiran_foto && count($laporan->lampiran_foto) > 0)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Lampiran Foto:</label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($laporan->lampiran_foto as $foto)
                        <a href="{{ asset('storage/' . $foto) }}" target="_blank" class="block">
                            <img src="{{ asset('storage/' . $foto) }}"
                                 alt="Lampiran"
                                 class="w-full h-32 object-cover rounded-lg border hover:shadow-lg transition-shadow">
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    @if($laporan->status === 'menunggu')
    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-3">Tindakan untuk Laporan Ini</h3>
        <div class="flex gap-3">
            <button onclick="document.getElementById('formProses').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                Proses Laporan
            </button>
            <button onclick="document.getElementById('formTolak').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">
                Tolak Laporan
            </button>
        </div>

        <!-- Form Proses -->
        <form id="formProses" action="{{ route('admin.internet.laporan-gangguan.process', $laporan->id) }}" method="POST" style="display:none;" class="mt-4">
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
        <form id="formTolak" action="{{ route('admin.internet.laporan-gangguan.reject', $laporan->id) }}" method="POST" style="display:none;" class="mt-4">
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

    @if($laporan->status === 'proses')
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-blue-800 mb-3">Selesaikan Laporan</h3>
        <form action="{{ route('admin.internet.laporan-gangguan.complete', $laporan->id) }}" method="POST">
            @csrf
            <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan Penyelesaian (opsional):</label>
            <textarea name="admin_notes" rows="3" class="w-full px-3 py-2 border border-blue-300 rounded-lg" placeholder="Jelaskan tindakan yang telah dilakukan...">{{ $laporan->admin_notes }}</textarea>
            <button type="submit" class="mt-3 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                Tandai Selesai
            </button>
        </form>
    </div>
    @endif

    @if(in_array($laporan->status, ['proses', 'selesai']))
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Catatan Admin</h3>
        <form action="{{ route('admin.internet.laporan-gangguan.update-notes', $laporan->id) }}" method="POST">
            @csrf
            <textarea name="admin_notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Tambahkan catatan...">{{ $laporan->admin_notes }}</textarea>
            <div class="mt-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">
                    Update Catatan
                </button>
            </div>
        </form>

        @if($laporan->processedBy)
        <div class="mt-4 pt-4 border-t">
            <p class="text-sm text-gray-600">Diproses oleh: <span class="font-semibold">{{ $laporan->processedBy->name }}</span></p>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
