@extends('layouts.authenticated')

@section('title', '- Tambah Data Web Monitor')
@section('header-title', 'Tambah Data Web Monitor')

@section('content')
<div class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Tambah Data Web Monitor</h1>

    <form action="{{ route('admin.web-monitor.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Nama Instansi</label>
            <input type="text" name="nama_instansi" class="w-full border-gray-300 rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Subdomain</label>
            <input type="text" name="subdomain" class="w-full border-gray-300 rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Domain</label>
            <input type="text" name="domain" class="w-full border-gray-300 rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Status</label>
            <select name="status" class="w-full border-gray-300 rounded px-3 py-2">
                <option value="Aktif">Aktif</option>
                <option value="Tidak Aktif">Tidak Aktif</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Keterangan</label>
            <textarea name="keterangan" class="w-full border-gray-300 rounded px-3 py-2" rows="3"></textarea>
        </div>
        <div class="flex justify-end">
            <a href="{{ route('admin.web-monitor.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded mr-2">Batal</a>
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Simpan</button>
        </div>
    </form>
</div>
@endsection
