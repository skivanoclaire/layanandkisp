@extends('layouts.authenticated')

@section('title', '- Kategori Barang - Inventaris Digital')
@section('header-title', 'Kategori Barang')

@section('content')
    <h1 class="text-2xl font-bold text-green-700 mb-4">Kategori Barang - Inventaris Digital</h1>

    @if (session('status'))
        <div class="mb-4 p-3 text-sm bg-green-50 border border-green-300 rounded">{{ session('status') }}</div>
    @endif

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white p-4 rounded shadow">
            <h2 class="font-semibold mb-3">Tambah Kategori</h2>
            <form method="POST" action="{{ route('admin.tik.categories.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm mb-1">Nama</label>
                    <input type="text" name="name" class="border rounded p-2 w-full" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">Kode Kategori (mis. TRM, CMR, ACR)</label>
                    <input type="text" name="code" class="border rounded p-2 w-full" required>
                </div>

                <div>
                    <label class="block text-sm mb-1">Deskripsi (opsional)</label>
                    <input type="text" name="description" class="border rounded p-2 w-full">
                </div>
                <button class="px-4 py-2 bg-green-600 text-white rounded">Simpan</button>
            </form>
        </div>

        <div class="bg-white p-4 rounded shadow overflow-x-auto">
            <h2 class="font-semibold mb-3">Daftar Kategori</h2>
            <table class="min-w-full table-auto">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left">Nama</th>
                        <th class="px-3 py-2 text-left">Kode Kategori</th>
                        <th class="px-3 py-2 text-left">Deskripsi</th>
                        <th class="px-3 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $it)
                        <tr class="border-b">
                            <td class="px-3 py-2">{{ $it->name }}</td>
                            <td class="px-3 py-2">{{ $it->code }}</td>
                            <td class="px-3 py-2">{{ $it->description ?: '—' }}</td>
                            <td class="px-3 py-2">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.tik.categories.edit', $it->id) }}"
                                        class="px-3 py-1 border rounded">Edit</a>
                                    <form method="POST" action="{{ route('admin.tik.categories.destroy', $it->id) }}"
                                        onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-1 border rounded text-red-600">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-3 py-4 text-center" colspan="3">Belum ada kategori</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
