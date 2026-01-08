@extends('layouts.authenticated')
@section('title', '- Inventaris Digital')
@section('header-title', 'Inventaris Digital')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-green-700">Inventaris Digital</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.tik.categories.index') }}" class="px-3 py-2 border rounded">Kelola Kategori</a>
            <a href="{{ route('admin.tik.assets.create') }}" class="px-3 py-2 rounded bg-green-600 text-white">+ Tambah
                Barang</a>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 text-sm bg-green-50 border border-green-300 rounded">{{ session('status') }}</div>
    @endif

    <form method="GET" class="mb-4 flex gap-2">
        <select name="category" class="border rounded p-2">
            <option value="">Semua Kategori</option>
            @foreach ($cats as $c)
                <option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="active" class="border rounded p-2">
            <option value="">Semua Status</option>
            <option value="1" @selected(request('active') === '1')>Aktif</option>
            <option value="0" @selected(request('active') === '0')>Nonaktif</option>
        </select>
        <button class="px-3 py-2 border rounded">Filter</button>
    </form>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">Nama</th>
                    <th class="px-3 py-2 text-left">Kategori</th>
                    <th class="px-3 py-2 text-left">Kode</th>
                    <th class="px-3 py-2 text-left">Serial Number</th>
                    <th class="px-3 py-2 text-left">Qty</th>
                    <th class="px-3 py-2 text-left">Kondisi</th>
                    <th class="px-3 py-2 text-left">Lokasi</th>
                    <th class="px-3 py-2">Foto</th>
                    <th class="px-3 py-2">QR</th>
                    <th class="px-3 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $it)
                    <tr class="border-b">
                        <td class="px-3 py-2">{{ $it->name }}</td>
                        <td class="px-3 py-2">{{ $it->category?->name }}</td>
                        <td class="px-3 py-2 font-mono">{{ $it->code ?: '—' }}</td>
                        <td class="px-3 py-2 font-mono">{{ $it->serial_number ?: '—' }}</td>
                        <td class="px-3 py-2">{{ $it->quantity }}</td>
                        <td class="px-3 py-2">{{ $it->condition }}</td>
                        <td class="px-3 py-2">{{ $it->location ?: '—' }}</td>
                        <td class="px-3 py-2">
                            @if ($it->photo_url)
                                <a href="{{ $it->photo_url }}" target="_blank" title="Lihat Foto">
                                    <img src="{{ $it->photo_url }}" alt="Foto {{ $it->name }}"
                                        class="h-12 w-12 object-cover rounded hover:opacity-80 transition">
                                </a>
                            @else
                                —
                            @endif
                        </td>


                        <td class="px-3 py-2">
                            @if ($it->qr_path)
                                <a href="{{ asset('storage/' . $it->qr_path) }}" target="_blank" title="Lihat QR">
                                    <img src="{{ asset('storage/' . $it->qr_path) }}" alt="QR {{ $it->code }}"
                                        class="h-12 w-12 object-contain bg-white border rounded p-1">
                                </a>
                            @else
                                —
                            @endif
                        </td>

                        <td class="px-3 py-2">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.tik.assets.edit', $it->id) }}"
                                    class="px-3 py-1 border rounded">Edit</a>
                                <form method="POST" action="{{ route('admin.tik.assets.destroy', $it->id) }}"
                                    onsubmit="return confirm('Hapus aset ini?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1 border rounded text-red-600">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-3 py-4 text-center" colspan="8">Belum ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endsection
