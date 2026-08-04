@extends('layouts.authenticated')

@section('title', '- Edit Dokumen Knowledge Base')
@section('header-title', 'Edit Dokumen Knowledge Base')

@section('content')
<div class="container mx-auto px-4 max-w-4xl">

    <div class="mb-5 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-green-700">Edit Dokumen</h1>
        <a href="{{ route('admin.konsultasi-ai.index') }}"
           class="inline-flex items-center px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 text-sm bg-red-50 border border-red-300 text-red-700 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($dokumen->file_path && ! $dokumen->hasKonten())
        <div class="mb-4 p-3 text-sm bg-amber-50 border border-amber-300 text-amber-800 rounded-lg">
            Isi dokumen ini belum bisa diekstrak otomatis. Buka berkasnya, salin isi yang relevan,
            lalu tempelkan pada kolom <strong>Isi Teks</strong> agar dapat dipakai sebagai dasar jawaban.
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.konsultasi-ai.dokumen.update', $dokumen) }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                <input type="text" name="judul" value="{{ old('judul', $dokumen->judul) }}" required maxlength="200"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Kategori</label>
                    <input type="text" name="kategori" value="{{ old('kategori', $dokumen->kategori) }}" maxlength="100"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Berkas Terlampir</label>
                    @if ($dokumen->file_path)
                        <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank"
                           class="inline-flex items-center text-sm text-blue-600 hover:underline py-2">
                            {{ $dokumen->file_name }} ({{ $dokumen->ukuranTerbaca() }})
                        </a>
                    @else
                        <p class="text-sm text-gray-500 py-2">Tidak ada berkas.</p>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-700 mb-1">Deskripsi Singkat</label>
                <textarea name="deskripsi" rows="2" maxlength="1000"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('deskripsi', $dokumen->deskripsi) }}</textarea>
            </div>

            <div>
                <label class="block text-sm text-gray-700 mb-1">Isi Teks</label>
                <textarea name="konten" rows="20"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono">{{ old('konten', $dokumen->konten) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">
                    Teks inilah yang dibaca asisten. Pisahkan antar bagian dengan baris kosong agar pencarian kutipan lebih akurat.
                </p>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                       @checked(old('is_active', $dokumen->is_active))>
                Aktif — dipakai sebagai dasar jawaban
            </label>

            <div class="flex gap-2 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.konsultasi-ai.index') }}"
                   class="px-5 py-2.5 border border-gray-300 text-sm rounded-lg hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
