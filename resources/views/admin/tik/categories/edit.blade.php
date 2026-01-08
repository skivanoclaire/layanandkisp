@extends('layouts.authenticated')

@section('title', '- Edit Kategori')
@section('header-title', 'Edit Kategori')

@section('content')
    <h1 class="text-2xl font-bold text-green-700 mb-4">Edit Kategori</h1>
    <form method="POST" action="{{ route('admin.tik.categories.update', $category->id) }}"
        class="bg-white p-4 rounded shadow space-y-3">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm mb-1">Nama</label>
            <input type="text" name="name" class="border rounded p-2 w-full" value="{{ $category->name }}" required>
        </div>
        <div>
            <label class="block text-sm mb-1">Kode Kategori</label>
            <input type="text" name="code" class="border rounded p-2 w-full" value="{{ $category->code }}" required>
        </div>

        <div>
            <label class="block text-sm mb-1">Deskripsi</label>
            <input type="text" name="description" class="border rounded p-2 w-full" value="{{ $category->description }}">
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.tik.categories.index') }}" class="px-4 py-2 border rounded">Batal</a>
            <button class="px-4 py-2 bg-green-600 text-white rounded">Simpan</button>
        </div>
    </form>
@endsection
