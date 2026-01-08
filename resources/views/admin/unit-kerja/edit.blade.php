@extends('layouts.authenticated')

@section('title', '- Edit Instansi')
@section('header-title', 'Edit Instansi')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Instansi</h1>
            <p class="text-gray-600 mt-2">Perbarui informasi instansi</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('admin.unit-kerja.update', $unitKerja) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Instansi <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="{{ old('nama', $unitKerja->nama) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror"
                        required
                        placeholder="Contoh: DINAS PENDIDIKAN DAN KEBUDAYAAN"
                    >
                    @error('nama')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="tipe" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipe <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="tipe"
                        name="tipe"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tipe') border-red-500 @enderror"
                        required
                    >
                        <option value="">-- Pilih Tipe --</option>
                        @foreach($tipeOptions as $tipe)
                            <option value="{{ $tipe }}" {{ old('tipe', $unitKerja->tipe) === $tipe ? 'selected' : '' }}>
                                {{ $tipe }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipe')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Tipe akan mengelompokkan instansi berdasarkan kategorinya
                    </p>
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input
                            type="hidden"
                            name="is_active"
                            value="0"
                        >
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $unitKerja->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">Instansi Aktif</span>
                    </label>
                    <p class="mt-1 text-sm text-gray-500 ml-6">
                        Instansi tidak aktif tidak akan muncul di pilihan form
                    </p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a
                        href="{{ route('admin.unit-kerja.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Perbarui
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-red-800 mb-2">Hapus Instansi</h3>
            <p class="text-sm text-red-600 mb-4">
                Tindakan ini tidak dapat dibatalkan. Pastikan tidak ada data terkait sebelum menghapus.
            </p>
            <form action="{{ route('admin.unit-kerja.destroy', $unitKerja) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus instansi ini?');">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors"
                >
                    Hapus Instansi
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
