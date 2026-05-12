@extends('layouts.authenticated')
@section('title', '- Ubah Permohonan Pemendek Tautan')
@section('header-title', 'Pemendek Tautan')

@section('content')
<div class="container mx-auto px-4 max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Ubah Permohonan {{ $item->ticket_no }}</h1>
        <a href="{{ route('user.shortlink.show', $item->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">← Kembali</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3">
            <ul class="list-disc list-inside text-sm text-red-800">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('user.shortlink.update', $item->id) }}" method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">URL Tujuan <span class="text-red-500">*</span></label>
            <input type="url" name="long_url" required value="{{ old('long_url', $item->long_url) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Pendek yang Diinginkan <span class="text-gray-400">(opsional)</span></label>
            <div class="flex items-center">
                <span class="px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-gray-500 text-sm font-mono">link.kaltaraprov.go.id/</span>
                <input type="text" name="requested_keyword" value="{{ old('requested_keyword', $item->requested_keyword) }}"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg font-mono focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <p class="mt-1 text-xs text-gray-500">Huruf, angka, dan tanda hubung (-).</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Judul / Keterangan Link <span class="text-gray-400">(opsional)</span></label>
            <input type="text" name="title" value="{{ old('title', $item->title) }}" maxlength="191"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Keperluan <span class="text-red-500">*</span></label>
            <textarea name="keperluan" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('keperluan', $item->keperluan) }}</textarea>
        </div>

        <div class="flex gap-2 pt-2">
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-semibold">Simpan Perubahan</button>
            <a href="{{ route('user.shortlink.show', $item->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded font-semibold">Batal</a>
        </div>
    </form>
</div>
@endsection
