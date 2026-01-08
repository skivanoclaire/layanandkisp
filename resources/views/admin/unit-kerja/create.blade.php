@extends('layouts.authenticated')

@section('title', '- Tambah Instansi')
@section('header-title', 'Tambah Instansi')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tambah Instansi</h1>
            <p class="text-gray-600 mt-2">Tambahkan instansi baru ke dalam sistem</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('admin.unit-kerja.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Instansi <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="{{ old('nama') }}"
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
                            <option value="{{ $tipe }}" {{ old('tipe') === $tipe ? 'selected' : '' }}>
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
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
