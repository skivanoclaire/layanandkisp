@extends('layouts.authenticated')
@section('title', '- Permohonan Pasang Baru Internet')
@section('header-title', 'Permohonan Pasang Baru Internet')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 text-purple-700">Formulir Permohonan Pasang Baru Internet</h1>

    <div class="mb-6 bg-purple-50 border-l-4 border-purple-500 rounded-lg p-4 text-sm text-purple-700">
        Isi lokasi dan kebutuhan titik jaringan dengan jelas agar tim dapat melakukan survei dan pemasangan. Permohonan akan ditindaklanjuti oleh tim teknis.
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('user.internet.pasang-baru.store') }}" method="POST">
            @csrf

            <!-- Informasi Pemohon -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Pemohon</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" value="{{ auth()->user()->name }}" disabled
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">NIP</label>
                        <input type="text" value="{{ auth()->user()->nip }}" disabled
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Kerja</label>
                    <input type="text" value="{{ auth()->user()->unitKerja?->nama ?? '-' }}" disabled
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    <p class="text-xs text-gray-500 mt-1">Unit Kerja diambil dari data akun Anda</p>
                </div>

                <div class="mb-4">
                    <label for="no_hp" class="block text-sm font-semibold text-gray-700 mb-2">
                        No. HP/WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', auth()->user()->phone) }}" required
                           placeholder="Contoh: 081234567890"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('no_hp') border-red-500 @enderror">
                    @error('no_hp')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Lokasi & Kebutuhan -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Lokasi & Kebutuhan Pemasangan</h2>

                <div class="mb-4">
                    <label for="lokasi_pemasangan" class="block text-sm font-semibold text-gray-700 mb-2">
                        Lokasi Pemasangan (gedung/alamat) <span class="text-red-500">*</span>
                    </label>
                    <textarea id="lokasi_pemasangan" name="lokasi_pemasangan" rows="2" required
                              placeholder="Contoh: Gedung Kantor Dinas, Jl. Contoh No. 1, Tanjung Selor"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('lokasi_pemasangan') border-red-500 @enderror">{{ old('lokasi_pemasangan') }}</textarea>
                    @error('lokasi_pemasangan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="ruangan" class="block text-sm font-semibold text-gray-700 mb-2">Ruangan / Lantai</label>
                        <input type="text" id="ruangan" name="ruangan" value="{{ old('ruangan') }}" placeholder="Contoh: Ruang Bidang TIK, Lantai 2"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('ruangan') border-red-500 @enderror">
                        @error('ruangan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="pic_lokasi" class="block text-sm font-semibold text-gray-700 mb-2">PIC di Lokasi</label>
                        <input type="text" id="pic_lokasi" name="pic_lokasi" value="{{ old('pic_lokasi') }}" placeholder="Nama yang dapat ditemui saat survei"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('pic_lokasi') border-red-500 @enderror">
                        @error('pic_lokasi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="jumlah_titik" class="block text-sm font-semibold text-gray-700 mb-2">
                        Jumlah Titik Jaringan <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="jumlah_titik" name="jumlah_titik" value="{{ old('jumlah_titik', 1) }}" min="1" max="20" required
                           class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('jumlah_titik') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">Perkiraan jumlah perangkat yang perlu terhubung (maksimal 20 titik).</p>
                    @error('jumlah_titik')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="uraian_kebutuhan" class="block text-sm font-semibold text-gray-700 mb-2">
                        Uraian Kebutuhan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="uraian_kebutuhan" name="uraian_kebutuhan" rows="4" required
                              placeholder="Jelaskan kebutuhan, mis. untuk perangkat apa, dan alasan pemasangan baru"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('uraian_kebutuhan') border-red-500 @enderror">{{ old('uraian_kebutuhan') }}</textarea>
                    @error('uraian_kebutuhan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold">
                    Kirim Permohonan
                </button>
                <a href="{{ route('user.internet.pasang-baru.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
