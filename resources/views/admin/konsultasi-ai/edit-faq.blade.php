@extends('layouts.authenticated')

@section('title', '- Edit Pertanyaan Contoh')
@section('header-title', 'Edit Pertanyaan Contoh')

@section('content')
<div class="container mx-auto px-4 max-w-4xl">

    <div class="mb-5 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-green-700">Edit Pertanyaan Contoh</h1>
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

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.konsultasi-ai.faq.update', $faq) }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm text-gray-700 mb-1">Pertanyaan <span class="text-red-500">*</span></label>
                <input type="text" name="pertanyaan" value="{{ old('pertanyaan', $faq->pertanyaan) }}" required maxlength="255"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm text-gray-700 mb-1">Jawaban <span class="text-red-500">*</span></label>
                <textarea name="jawaban" rows="14" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('jawaban', $faq->jawaban) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">
                    Format ringan: <code>**tebal**</code>, <code>_miring_</code>, baris <code>- </code> untuk poin, <code>1. </code> untuk penomoran.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Kategori</label>
                    <input type="text" name="kategori" value="{{ old('kategori', $faq->kategori) }}" maxlength="100"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="urutan" value="{{ old('urutan', $faq->urutan) }}" min="0" max="9999"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-700 mb-1">Kata Kunci Pemicu</label>
                <input type="text" name="kata_kunci" value="{{ old('kata_kunci', $faq->kata_kunci) }}" maxlength="255"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <p class="text-xs text-gray-500 mt-1">Pisahkan dengan koma. Dipakai mencocokkan pertanyaan pengguna pada mode prototipe.</p>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                       @checked(old('is_active', $faq->is_active))>
                Aktif
            </label>

            <p class="text-xs text-gray-500">Sudah dipakai menjawab sebanyak {{ $faq->hit_count }} kali.</p>

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
