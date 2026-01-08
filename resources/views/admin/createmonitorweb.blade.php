@extends('layouts.authenticated')

@section('title', '- Tambah Monitor Web')
@section('header-title', 'Tambah Monitor Web')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Tambah Data Monitor Website</h1>

    <form action="{{ route('admin.monitorweb.store') }}" method="POST" class="mb-4">
        @csrf
        <input type="text" name="nama_instansi" placeholder="Nama Instansi" class="border p-2 w-full mb-2" required>
        <input type="text" name="subdomain" placeholder="Subdomain" class="border p-2 w-full mb-2" required>
        <select name="status" class="border p-2 w-full mb-2" required>
            <option value="Aktif">Aktif</option>
            <option value="Tidak Aktif">Tidak Aktif</option>
        </select>
        <input type="text" name="keterangan" placeholder="Keterangan" class="border p-2 w-full mb-2">
        <select name="jenis" class="border p-2 w-full mb-2" required>
            <option value="Induk">Induk</option>
            <option value="Cabang">Cabang</option>
        </select>
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Simpan</button>
        <a href="{{ route('admin.monitorweb') }}" class="ml-2 text-blue-600">Kembali</a>
    </form>
</div>
@endsection
