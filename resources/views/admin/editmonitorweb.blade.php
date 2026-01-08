@extends('layouts.authenticated')

@section('title', '- Edit Monitor Web')
@section('header-title', 'Edit Monitor Web')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Data Monitor Website</h1>

    <form action="{{ route('admin.monitorweb.update', $formData->id) }}" method="POST" class="mb-4">
        @csrf
        <input type="text" name="nama_instansi" placeholder="Nama Instansi" class="border p-2" value="{{ $formData->nama_instansi }}" required>
        <input type="text" name="subdomain" placeholder="Subdomain" class="border p-2" value="{{ $formData->subdomain }}" required>
        <select name="status" class="border p-2" required>
            <option value="Aktif" {{ $formData->status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="Tidak Aktif" {{ $formData->status == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
        <input type="text" name="keterangan" placeholder="Keterangan" class="border p-2" value="{{ $formData->keterangan }}">
        <select name="jenis" class="border p-2" required>
            <option value="Induk" {{ $formData->jenis == 'Induk' ? 'selected' : '' }}>Induk</option>
            <option value="Cabang" {{ $formData->jenis == 'Cabang' ? 'selected' : '' }}>Cabang</option>
        </select>
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update</button>
        <a href="{{ route('admin.monitorweb') }}" class="ml-2 text-blue-600">Kembali</a>
    </form>
</div>
@endsection
