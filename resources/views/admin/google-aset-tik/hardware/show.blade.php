@extends('layouts.authenticated')
@section('title', '- Detail Hardware')
@section('header-title', 'Detail Hardware Aset TIK')
@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detail Hardware Aset TIK</h1>
        <a href="{{ route('admin.google-aset-tik.hardware.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">Kembali</a>
    </div>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Dasar</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Nama OPD</label><p class="text-gray-900 font-medium">{{ $hardware->nama_opd }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Nama Aset</label><p class="text-gray-900 font-medium">{{ $hardware->nama_aset }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Kode Barang</label><p class="text-gray-900 font-medium">{{ $hardware->kode_gab_barang ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">No Register</label><p class="text-gray-900 font-medium">{{ $hardware->no_register ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Total Unit</label><p class="text-gray-900 font-medium">{{ $hardware->total }}</p></div>
            </div>
        </div>
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Spesifikasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Merk/Type</label><p class="text-gray-900 font-medium">{{ $hardware->merk_type ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Tahun</label><p class="text-gray-900 font-medium">{{ $hardware->tahun ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Jenis Aset TIK</label><p class="text-gray-900 font-medium">{{ $hardware->jenis_aset_tik ?? '-' }}</p></div>
            </div>
        </div>
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Nilai & Sumber</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Nilai Perolehan</label><p class="text-gray-900 font-bold text-green-600">Rp {{ number_format($hardware->nilai_perolehan, 0, ',', '.') }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Sumber Pendanaan</label><p class="text-gray-900 font-medium">{{ $hardware->sumber_pendanaan ?? '-' }}</p></div>
            </div>
        </div>
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Kondisi & Status</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Keadaan Barang</label><p class="text-gray-900 font-medium">{{ $hardware->keadaan_barang ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Status</label><p class="text-gray-900 font-medium">{{ $hardware->status ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Terotorisasi</label><p class="text-gray-900 font-medium">{{ $hardware->terotorisasi ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Aset Vital</label><p class="text-gray-900 font-medium">{{ $hardware->aset_vital ?? '-' }}</p></div>
            </div>
        </div>
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Tanggal</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Perolehan</label><p class="text-gray-900 font-medium">{{ $hardware->tanggal_perolehan ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Penyerahan</label><p class="text-gray-900 font-medium">{{ $hardware->tanggal_penyerahan ? $hardware->tanggal_penyerahan : '-' }}</p></div>
            </div>
        </div>
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Lainnya</h2>
            <div class="grid grid-cols-1 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Asal Usul</label><p class="text-gray-900 font-medium">{{ $hardware->asal_usul ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Keterangan</label><p class="text-gray-900">{{ $hardware->keterangan ?? '-' }}</p></div>
            </div>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Sinkronisasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Status Sync</label><p><span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">{{ $hardware->sync_status }}</span></p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Terakhir Sync</label><p class="text-gray-900">{{ $hardware->synced_at ? $hardware->synced_at->format('d/m/Y H:i') : '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Baris Spreadsheet</label><p class="text-gray-900 font-mono">{{ $hardware->spreadsheet_row ?? '-' }}</p></div>
            </div>
        </div>
    </div>
</div>
@endsection
