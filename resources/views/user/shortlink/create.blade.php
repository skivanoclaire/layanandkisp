@extends('layouts.authenticated')
@section('title', '- Ajukan Pemendek Tautan')
@section('header-title', 'Pemendek Tautan')

@section('content')
<div class="container mx-auto px-4 max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Ajukan Permohonan Pemendek Tautan</h1>
        <a href="{{ route('user.shortlink.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">← Kembali</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3">
            <ul class="list-disc list-inside text-sm text-red-800">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('user.shortlink.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
            <input type="text" value="{{ Auth::user()->name }}" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">NIP</label>
                <input type="text" value="{{ Auth::user()->nik }}" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi</label>
                <input type="text" value="{{ Auth::user()->unitKerja->nama ?? '-' }}" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">URL Tujuan <span class="text-red-500">*</span></label>
            <input type="url" name="long_url" required value="{{ old('long_url') }}" placeholder="https://contoh.kaltaraprov.go.id/dokumen/123"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            <p class="mt-1 text-xs text-gray-500">Alamat lengkap halaman/dokumen yang ingin dipendekkan (diawali http:// atau https://).</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Pendek yang Diinginkan <span class="text-gray-400">(opsional)</span></label>
            <div class="flex items-center">
                <span class="px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-gray-500 text-sm font-mono">link.kaltaraprov.go.id/</span>
                <input type="text" name="requested_keyword" value="{{ old('requested_keyword') }}" placeholder="mis. lpse-2026"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg font-mono focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <p class="mt-1 text-xs text-gray-500">Huruf, angka, dan tanda hubung (-). Jika kosong atau sudah dipakai, sistem akan membuatkan kode otomatis. Keputusan akhir pada admin.</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Judul / Keterangan Link <span class="text-gray-400">(opsional)</span></label>
            <input type="text" name="title" value="{{ old('title') }}" maxlength="191" placeholder="mis. Pengumuman Seleksi PPPK 2026"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Keperluan <span class="text-red-500">*</span></label>
            <textarea name="keperluan" rows="4" required placeholder="Jelaskan untuk apa tautan ini digunakan dan di mana akan disebarkan..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('keperluan') }}</textarea>
        </div>

        <div class="flex gap-2 pt-2">
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-semibold">Ajukan Permohonan</button>
            <a href="{{ route('user.shortlink.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded font-semibold">Batal</a>
        </div>
    </form>
</div>
@endsection
