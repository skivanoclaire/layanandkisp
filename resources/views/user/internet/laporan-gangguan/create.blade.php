@extends('layouts.authenticated')
@section('title', '- Buat Laporan Gangguan Internet')
@section('header-title', 'Laporan Gangguan Internet')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 text-purple-700">Formulir Laporan Gangguan Internet</h1>

    <!-- Operating Hours -->
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm">
                <p class="text-blue-700 font-semibold mb-1">Jam Layanan:</p>
                <p class="text-blue-700">
                    Senin s.d. Kamis: 07.30 - 16.00 WITA<br>
                    Jumat: 07.30 - 16.30 WITA
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('user.internet.laporan-gangguan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Informasi Pemohon -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Pelapor</h2>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text"
                           value="{{ auth()->user()->name }}"
                           disabled
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">NIP</label>
                    <input type="text"
                           value="{{ auth()->user()->nip }}"
                           disabled
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>

                <div class="mb-4">
                    <label for="unit_kerja_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Instansi <span class="text-red-500">*</span>
                    </label>
                    <select id="unit_kerja_id"
                            name="unit_kerja_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('unit_kerja_id') border-red-500 @enderror">
                        <option value="">-- Pilih Instansi --</option>
                        @foreach($unitKerjaList as $uk)
                            <option value="{{ $uk->id }}" {{ old('unit_kerja_id') == $uk->id ? 'selected' : '' }}>
                                {{ $uk->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_kerja_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="no_hp" class="block text-sm font-semibold text-gray-700 mb-2">
                        No. HP/WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="no_hp"
                           name="no_hp"
                           value="{{ old('no_hp', auth()->user()->phone) }}"
                           required
                           placeholder="Contoh: 081234567890"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('no_hp') border-red-500 @enderror">
                    @error('no_hp')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Detail Permasalahan -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Detail Permasalahan</h2>

                <div class="mb-4">
                    <label for="uraian_permasalahan" class="block text-sm font-semibold text-gray-700 mb-2">
                        Uraian Permasalahan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="uraian_permasalahan"
                              name="uraian_permasalahan"
                              rows="5"
                              required
                              placeholder="Jelaskan secara detail permasalahan jaringan/internet yang Anda alami"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('uraian_permasalahan') border-red-500 @enderror">{{ old('uraian_permasalahan') }}</textarea>
                    @error('uraian_permasalahan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="lokasi_koordinat" class="block text-sm font-semibold text-gray-700 mb-2">
                        Lokasi / Koordinat
                    </label>
                    <input type="text"
                           id="lokasi_koordinat"
                           name="lokasi_koordinat"
                           value="{{ old('lokasi_koordinat') }}"
                           placeholder="Contoh: Lantai 3 Ruang TIK / -8.123, 115.456"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('lokasi_koordinat') border-red-500 @enderror">
                    @error('lokasi_koordinat')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-600 mt-1">Opsional - Sebutkan lokasi fisik atau koordinat GPS jika memungkinkan</p>
                </div>

                <div class="mb-4">
                    <label for="lampiran_foto" class="block text-sm font-semibold text-gray-700 mb-2">
                        Lampiran Foto (Maks. 5 foto)
                    </label>
                    <input type="file"
                           id="lampiran_foto"
                           name="lampiran_foto[]"
                           multiple
                           accept="image/jpeg,image/png,image/jpg"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('lampiran_foto') border-red-500 @enderror @error('lampiran_foto.*') border-red-500 @enderror">
                    @error('lampiran_foto')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    @error('lampiran_foto.*')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-600 mt-1">Format: JPG, JPEG, PNG. Maksimal 2MB per foto. Maksimal 5 foto.</p>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold">
                    Kirim Laporan
                </button>
                <a href="{{ route('user.internet.laporan-gangguan.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validate file count
    const fileInput = document.getElementById('lampiran_foto');
    fileInput.addEventListener('change', function() {
        if (this.files.length > 5) {
            alert('Maksimal 5 foto yang dapat diunggah');
            this.value = '';
        }
    });
});
</script>
@endsection
