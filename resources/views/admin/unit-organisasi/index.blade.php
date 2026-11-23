@extends('layouts.authenticated')

@section('title', '- Master Unit Organisasi')
@section('header-title', 'Master Unit Organisasi')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Master Unit Organisasi</h1>
        <p class="text-sm text-gray-600 mt-1">
            Bidang dan Sekretariat di bawah Perangkat Daerah. Unit ini menjadi tujuan permohonan email unit organisasi.
        </p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Tambah --}}
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <h2 class="font-semibold text-gray-800 mb-3">Tambah Unit Organisasi</h2>
        <form method="POST" action="{{ route('admin.unit-organisasi.store') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            @csrf
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="mis. Bidang Statistik"
                    class="w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                <select name="tipe" required class="w-full border-gray-300 rounded-md shadow-sm">
                    @foreach ($tipeOptions as $tipe)
                        <option value="{{ $tipe }}" {{ old('tipe') === $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Perangkat Daerah Induk</label>
                <select name="unit_kerja_id" required class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">-- Pilih --</option>
                    @foreach ($unitKerjaList as $uk)
                        <option value="{{ $uk->id }}" {{ (string) old('unit_kerja_id') === (string) $uk->id ? 'selected' : '' }}>{{ $uk->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold w-full">Tambah</button>
            </div>
        </form>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.unit-organisasi.index') }}" class="bg-white rounded-lg shadow-md p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="min-w-[260px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter Perangkat Daerah</label>
            <select name="unit_kerja_id" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">-- Semua --</option>
                @foreach ($unitKerjaList as $uk)
                    <option value="{{ $uk->id }}" {{ (string) request('unit_kerja_id') === (string) $uk->id ? 'selected' : '' }}>{{ $uk->nama }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-semibold">Filter</button>
    </form>

    {{-- Daftar --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Perangkat Daerah Induk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($items as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->nama }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item->tipe }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item->unitKerja?->nama ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if ($item->is_active)
                                    <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">Aktif</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-gray-200 text-gray-700">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <details>
                                    <summary class="cursor-pointer text-blue-600 hover:underline">Ubah</summary>
                                    <form method="POST" action="{{ route('admin.unit-organisasi.update', $item) }}" class="mt-3 space-y-2 min-w-[280px]">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="nama" value="{{ $item->nama }}" required class="w-full border-gray-300 rounded-md shadow-sm">
                                        <select name="tipe" required class="w-full border-gray-300 rounded-md shadow-sm">
                                            @foreach ($tipeOptions as $tipe)
                                                <option value="{{ $tipe }}" {{ $item->tipe === $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                                            @endforeach
                                        </select>
                                        <select name="unit_kerja_id" required class="w-full border-gray-300 rounded-md shadow-sm">
                                            @foreach ($unitKerjaList as $uk)
                                                <option value="{{ $uk->id }}" {{ (string) $item->unit_kerja_id === (string) $uk->id ? 'selected' : '' }}>{{ $uk->nama }}</option>
                                            @endforeach
                                        </select>
                                        <label class="inline-flex items-center gap-2 text-sm">
                                            <input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                                            Aktif
                                        </label>
                                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">Simpan</button>
                                    </form>
                                </details>
                                <form method="POST" action="{{ route('admin.unit-organisasi.toggle', $item) }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="text-sm text-gray-600 hover:underline">
                                        {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada unit organisasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>
@endsection
