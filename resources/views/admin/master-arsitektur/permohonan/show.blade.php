@extends('layouts.authenticated')

@section('title', '- Permohonan Pembaruan Data Arsitektur')
@section('header-title', 'Permohonan Pembaruan Data Arsitektur')

@section('content')
<div class="container mx-auto p-6">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.arsitektur-permohonan.index') }}" class="text-sm text-green-700 hover:underline">← Kembali ke daftar permohonan</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $permohonan->nama_entri }}</h1>
            @if ($permohonan->item)
                <a href="{{ route('admin.master-arsitektur.show', $permohonan->item) }}" class="text-sm text-green-700 hover:underline">Lihat entri pada Master Data Arsitektur</a>
            @endif
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $galat)
                        <li>{{ $galat }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($permohonan->menunggu() && $permohonan->aksi === \App\Models\MasterArsitektur\Permohonan::AKSI_UBAH && ! $permohonan->item)
            <div class="bg-orange-50 border border-orange-300 text-orange-800 px-4 py-3 rounded mb-4 text-sm">
                Entri yang diusulkan berubah sudah tidak ada di Master Data Arsitektur, sehingga permohonan ini hanya dapat ditolak.
            </div>
        @endif

        @include('admin.master-arsitektur.permohonan._rincian')

        @if ($permohonan->menunggu())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <form method="POST" action="{{ route('admin.arsitektur-permohonan.setujui', $permohonan) }}" class="bg-white rounded-lg shadow-md p-4"
                      onsubmit="return confirm('Setujui usulan ini? Data arsitektur akan langsung diperbarui.');">
                    @csrf
                    <h2 class="text-lg font-bold text-gray-800 mb-2">Setujui</h2>
                    <label for="catatan-setuju" class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                    <textarea name="catatan_admin" id="catatan-setuju" rows="3" maxlength="1000"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    <button type="submit" class="mt-3 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">Setujui dan Terapkan</button>
                </form>

                <form method="POST" action="{{ route('admin.arsitektur-permohonan.tolak', $permohonan) }}" class="bg-white rounded-lg shadow-md p-4">
                    @csrf
                    <h2 class="text-lg font-bold text-gray-800 mb-2">Tolak</h2>
                    <label for="catatan-tolak" class="block text-sm font-medium text-gray-700 mb-1">Alasan penolakan <span class="text-red-500">*</span></label>
                    <textarea name="catatan_admin" id="catatan-tolak" rows="3" maxlength="1000" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                    <button type="submit" class="mt-3 bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold">Tolak</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
