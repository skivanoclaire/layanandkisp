@extends('layouts.authenticated')

@section('title', '- Master Variabel DTSEN')
@section('header-title', 'Master Variabel DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Katalog Variabel / Indikator DTSEN</h1>
            <p class="text-gray-600 mt-1">Dikelola Bapperida selaku koordinator Forum Satu Data Daerah, berversi mengikuti rilis DTSEN.</p>
        </div>
        <a href="{{ route('admin.dtsen.variables.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">+ Tambah Variabel</a>
    </div>

    @include('partials.dtsen.errors')

    <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
            <select name="kategori" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach ($kategoriList as $k)
                    <option value="{{ $k }}" @selected(request('kategori') === $k)>{{ $k }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Level</label>
            <select name="level" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach ([1, 2, 3, 4] as $lvl)
                    <option value="{{ $lvl }}" @selected((string) request('level') === (string) $lvl)>Level {{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Rilis</label>
            <select name="release" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                @foreach ($releases as $r)
                    <option value="{{ $r->id }}" @selected((string) request('release') === (string) $r->id)>{{ $r->nomor_rilis }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Kode / nama"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-56">
        </div>
        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Terapkan</button>
        <a href="{{ route('admin.dtsen.variables.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Reset</a>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Variabel</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-center">Level</th>
                    <th class="px-4 py-3 text-left">Rilis</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $item->kode }}</td>
                        <td class="px-4 py-3">
                            {{ $item->nama }}
                            @if ($item->deskripsi)
                                <p class="text-xs text-gray-500">{{ Str::limit($item->deskripsi, 90) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $item->kategori ?: '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold
                                {{ $item->level_minimal >= 4 ? 'bg-red-100 text-red-700' : ($item->level_minimal === 3 ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                L{{ $item->level_minimal }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $item->release->nomor_rilis ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('admin.dtsen.variables.toggle', $item->id) }}" method="POST" class="inline">
                                @csrf
                                <button class="px-2 py-0.5 rounded text-xs font-semibold {{ $item->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <a href="{{ route('admin.dtsen.variables.edit', $item->id) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.dtsen.variables.destroy', $item->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Hapus variabel ini? Variabel yang pernah dimohonkan akan dinonaktifkan, bukan dihapus.')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada variabel.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
