@extends('layouts.authenticated')

@section('title', '- Master Wilayah DTSEN')
@section('header-title', 'Master Wilayah DTSEN')

@section('content')
@php
    // Tingkat yang wajar ditambahkan di bawah wilayah yang sedang dibuka.
    $tingkatBerikutnya = match ($parent?->tingkat) {
        null => 'provinsi',
        'provinsi' => 'kabupaten_kota',
        'kabupaten_kota' => 'kecamatan',
        'kecamatan' => 'desa_kelurahan',
        default => null,
    };
@endphp

<div class="container mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Wilayah Berjenjang</h1>
        <p class="text-gray-600 mt-1">Dipakai pada isian cakupan wilayah permintaan data DTSEN.</p>
    </div>

    @include('partials.dtsen.errors')

    {{-- Remah roti navigasi berjenjang --}}
    <nav class="mb-4 text-sm text-gray-600">
        <a href="{{ route('admin.dtsen.wilayah.index') }}" class="text-blue-600 hover:underline">Semua Provinsi</a>
        @if ($parent)
            @foreach (array_reverse(array_filter([$parent->parent?->parent, $parent->parent])) as $ancestor)
                <span class="mx-1">/</span>
                <a href="{{ route('admin.dtsen.wilayah.index', ['parent' => $ancestor->id]) }}" class="text-blue-600 hover:underline">{{ $ancestor->nama }}</a>
            @endforeach
            <span class="mx-1">/</span>
            <span class="font-semibold text-gray-800">{{ $parent->nama }}</span>
        @endif
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">Tingkat</th>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-center">Sub-wilayah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $item->nama }}</td>
                            <td class="px-4 py-3">{{ $item->tingkat_label }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $item->kode ?: '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($item->children_count > 0)
                                    <a href="{{ route('admin.dtsen.wilayah.index', ['parent' => $item->id]) }}"
                                       class="text-blue-600 hover:underline">{{ $item->children_count }}</a>
                                @else
                                    <a href="{{ route('admin.dtsen.wilayah.index', ['parent' => $item->id]) }}"
                                       class="text-gray-400 hover:underline">0</a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $item->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <details class="inline-block text-left">
                                    <summary class="text-blue-600 hover:underline cursor-pointer">Ubah</summary>
                                    <form action="{{ route('admin.dtsen.wilayah.update', $item->id) }}" method="POST"
                                          class="mt-2 p-3 border rounded-lg bg-gray-50 space-y-2 w-64">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="parent_id" value="{{ $item->parent_id }}">
                                        <input type="hidden" name="tingkat" value="{{ $item->tingkat }}">
                                        <input type="text" name="nama" value="{{ $item->nama }}" required
                                               class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                        <input type="text" name="kode" value="{{ $item->kode }}" placeholder="Kode"
                                               class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                        <label class="flex items-center gap-2 text-xs">
                                            <input type="checkbox" name="is_active" value="1" @checked($item->is_active)>
                                            <span>Aktif</span>
                                        </label>
                                        <button class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded text-sm font-semibold">Simpan</button>
                                    </form>
                                </details>
                                <form action="{{ route('admin.dtsen.wilayah.destroy', $item->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Hapus wilayah ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline ml-2">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada wilayah pada tingkat ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tingkatBerikutnya)
            <div class="bg-white rounded-lg shadow-sm border p-6 h-fit">
                <h3 class="font-bold text-gray-800 mb-1">Tambah Wilayah</h3>
                <p class="text-xs text-gray-500 mb-4">
                    {{ $parent ? 'Di bawah ' . $parent->nama : 'Tingkat provinsi' }}
                </p>
                <form action="{{ route('admin.dtsen.wilayah.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $parent?->id }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat</label>
                        <select name="tingkat" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            @foreach (\App\Models\DtsenWilayah::tingkatLabels() as $val => $label)
                                <option value="{{ $val }}" @selected($val === $tingkatBerikutnya)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                        <input type="text" name="kode" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Aktif</span>
                    </label>
                    <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Tambah</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
