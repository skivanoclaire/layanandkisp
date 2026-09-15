@extends('layouts.authenticated')

@section('title', '- Sampaikan Pengaduan DTSEN')
@section('header-title', 'Pengaduan, Saran & Masukan DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('user.dtsen.pengaduan.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Sampaikan Pengaduan, Saran, atau Masukan</h1>
    </div>

    @include('partials.dtsen.errors')

    <form action="{{ route('user.dtsen.pengaduan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6" x-data="{ anonim: {{ old('is_anonim') ? 'true' : 'false' }} }">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="kategori" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        @foreach (\App\Models\DtsenComplaint::kategoriLabels() as $val => $label)
                            <option value="{{ $val }}" @selected(old('kategori') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <label class="flex items-start gap-2">
                    <input type="checkbox" name="is_anonim" value="1" x-model="anonim" class="mt-1">
                    <span class="text-sm text-gray-700">
                        Sampaikan secara anonim — identitas saya tidak ditampilkan pada rekapitulasi dan hanya diketahui
                        Prosesor DTSEN untuk keperluan tindak lanjut.
                    </span>
                </label>

                <div x-show="!anonim" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelapor</label>
                        <input type="text" name="nama_pelapor" value="{{ old('nama_pelapor', auth()->user()->name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kontak</label>
                        <input type="text" name="kontak_pelapor" value="{{ old('kontak_pelapor', auth()->user()->email) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Uraian <span class="text-red-500">*</span></label>
                    <textarea name="uraian" rows="6" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('uraian') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran (opsional)</label>
                    <input type="file" name="lampiran" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.zip"
                           class="w-full text-sm border border-gray-300 rounded-lg p-2">
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Kirim</button>
            <a href="{{ route('user.dtsen.pengaduan.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
        </div>
    </form>
</div>
@endsection
