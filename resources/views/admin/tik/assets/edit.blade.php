@extends('layouts.authenticated')

@section('title', '- Edit Barang - Inventaris Digital')
@section('header-title', 'Edit Barang')

@section('content')
    <h1 class="text-2xl font-bold text-green-700 mb-4">Edit Barang - Inventaris Digital</h1>
    <form method="POST" action="{{ route('admin.tik.assets.update', $asset->id) }}" enctype="multipart/form-data"
        class="bg-white p-4 rounded shadow space-y-4">
        @csrf @method('PUT')
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1">Kategori</label>
                <select name="tik_category_id" class="border rounded p-2 w-full" required>
                    @foreach ($cats as $c)
                        <option value="{{ $c->id }}" @selected($asset->tik_category_id == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Nama Aset</label>
                <input type="text" name="name" class="border rounded p-2 w-full" value="{{ $asset->name }}" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Kode</label>
                <input type="text" name="code" class="border rounded p-2 w-full" value="{{ $asset->code }}">
            </div>
            <div>
                <label class="block text-sm mb-1">Serial Number</label>
                <input type="text" name="serial_number" class="border rounded p-2 w-full"
                    value="{{ $asset->serial_number }}">
            </div>
            <div>
                <label class="block text-sm mb-1">Kuantitas</label>
                <input type="number" name="quantity" min="1" class="border rounded p-2 w-full"
                    value="{{ $asset->quantity }}" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Kondisi</label>
                <input type="text" name="condition" class="border rounded p-2 w-full" value="{{ $asset->condition }}">
            </div>
            <div>
                <label class="block text-sm mb-1">Lokasi</label>
                <input type="text" name="location" class="border rounded p-2 w-full" value="{{ $asset->location }}">
            </div>
            <div>
                <label class="block text-sm mb-1">Foto (opsional)</label>
                <input type="file" name="photo" accept="image/*" class="border rounded p-2 w-full">
                @if ($asset->photo_url)
                    <img src="{{ $asset->photo_url }}" class="h-16 w-16 object-cover rounded mt-2">
                @endif
            </div>
        </div>
        <div>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ $asset->is_active ? 'checked' : '' }}>
                <span>Aktif</span>
            </label>
        </div>
        <div>
            <label class="block text-sm mb-1">Catatan</label>
            <textarea name="notes" rows="3" class="border rounded p-2 w-full">{{ $asset->notes }}</textarea>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.tik.assets.index') }}" class="px-4 py-2 border rounded">Batal</a>
            <button class="px-4 py-2 bg-green-600 text-white rounded">Simpan</button>
        </div>
    </form>
@endsection
