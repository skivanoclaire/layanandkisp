@extends('layouts.authenticated')
@section('title', '- Detail Software')
@section('header-title', 'Detail Software Aset TIK')
@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detail Software Aset TIK</h1>
        <a href="{{ route('admin.google-aset-tik.software.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">Kembali</a>
    </div>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Dasar</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Nama OPD</label><p class="text-gray-900 font-medium">{{ $software->nama_opd }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Nama Aset</label><p class="text-gray-900 font-medium">{{ $software->nama_aset }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Kode Barang</label><p class="text-gray-900 font-medium">{{ $software->kode_barang ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Tahun</label><p class="text-gray-900 font-medium">{{ $software->tahun ?? '-' }}</p></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-500 mb-1">Judul</label><p class="text-gray-900 font-medium">{{ $software->judul ?? '-' }}</p></div>
            </div>
        </div>
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Teknologi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Platform</label><p class="text-gray-900 font-medium">{{ $software->platform ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Database</label><p class="text-gray-900 font-medium">{{ $software->database ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Script</label><p class="text-gray-900 font-medium">{{ $software->script ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Framework</label><p class="text-gray-900 font-medium">{{ $software->framework ?? '-' }}</p></div>
            </div>
        </div>
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">URL & Data</h2>
            <div class="grid grid-cols-1 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">URL</label><p class="text-gray-900"><a href="{{ $software->url }}" target="_blank" class="text-blue-600 hover:underline">{{ $software->url ?? '-' }}</a></p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Data Output</label><p class="text-gray-900">{{ $software->data_output ?? '-' }}</p></div>
            </div>
        </div>
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Status & Utilisasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Status Aktif</label><p class="text-gray-900 font-medium">{{ $software->is_aktif ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Software Berjalan</label><p class="text-gray-900 font-medium">{{ $software->software_berjalan ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Fitur Sesuai</label><p class="text-gray-900 font-medium">{{ $software->fitur_sesuai ?? '-' }}</p></div>
            </div>
        </div>
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Pengembangan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Pengembangan</label><p class="text-gray-900 font-medium">{{ $software->pengembangan ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Sewa</label><p class="text-gray-900 font-medium">{{ $software->sewa ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Integrasi</label><p class="text-gray-900 font-medium">{{ $software->integrasi ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Jenis Perangkat Lunak</label><p class="text-gray-900 font-medium">{{ $software->jenis_perangkat_lunak ?? '-' }}</p></div>
            </div>
        </div>
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Nilai & Keterangan</h2>
            <div class="grid grid-cols-1 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Harga</label><p class="text-gray-900 font-bold text-green-600">Rp {{ number_format($software->harga, 0, ',', '.') }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Keterangan Software</label><p class="text-gray-900">{{ $software->keterangan_software ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Keterangan Utilisasi</label><p class="text-gray-900">{{ $software->keterangan_utilisasi ?? '-' }}</p></div>
            </div>
        </div>
        <div class="mb-8 pb-6 border-b">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Asal & Status</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Asal Usul</label><p class="text-gray-900 font-medium">{{ $software->asal_usul ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Status</label><p class="text-gray-900 font-medium">{{ $software->status ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Terotorisasi</label><p class="text-gray-900 font-medium">{{ $software->terotorisasi ?? '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Aset Vital</label><p class="text-gray-900 font-medium">{{ $software->aset_vital ?? '-' }}</p></div>
            </div>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Sinkronisasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Status Sync</label><p><span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">{{ $software->sync_status }}</span></p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Terakhir Sync</label><p class="text-gray-900">{{ $software->synced_at ? $software->synced_at->format('d/m/Y H:i') : '-' }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500 mb-1">Baris Spreadsheet</label><p class="text-gray-900 font-mono">{{ $software->spreadsheet_row ?? '-' }}</p></div>
            </div>
        </div>
    </div>
</div>
@endsection
