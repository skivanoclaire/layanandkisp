@extends('layouts.authenticated')

@section('title', '- Tambah Barang - Inventaris Digital')
@section('header-title', 'Tambah Barang')

@section('content')
    <h1 class="text-2xl font-bold text-green-700 mb-4">Tambah Barang - Inventaris Digital</h1>
    <form method="POST" action="{{ route('admin.tik.assets.store') }}" enctype="multipart/form-data"
        class="bg-white p-4 rounded shadow space-y-4">
        @csrf
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1">Kategori</label>
                <select name="tik_category_id" class="border rounded p-2 w-full" required>
                    <option value="">-- pilih --</option>
                    @foreach ($cats as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Nama Aset</label>
                <input type="text" name="name" class="border rounded p-2 w-full" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Serial Number</label>
                <input type="text" name="serial_number" class="border rounded p-2 w-full">
            </div>
            <div>
                <label class="block text-sm mb-1">Kuantitas</label>
                <input type="number" name="quantity" min="1" value="1" class="border rounded p-2 w-full"
                    required>
            </div>
            <div>
                <label class="block text-sm mb-1">Kondisi</label>
                <input type="text" name="condition" value="baik" class="border rounded p-2 w-full">
            </div>
            <div>
                <label class="block text-sm mb-1">Lokasi</label>
                <input type="text" name="location" class="border rounded p-2 w-full">
            </div>
            <div>
                <label class="block text-sm mb-1">Foto (opsional)</label>
                <input type="file" name="photo" accept="image/*" class="border rounded p-2 w-full">
            </div>
        </div>
        <div>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" checked>
                <span>Aktif</span>
            </label>
        </div>
        <div>
            <label class="block text-sm mb-1">Catatan</label>
            <textarea name="notes" rows="3" class="border rounded p-2 w-full"></textarea>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.tik.assets.index') }}" class="px-4 py-2 border rounded">Batal</a>
            <button class="px-4 py-2 bg-green-600 text-white rounded">Simpan</button>
        </div>
    </form>
@endsection
