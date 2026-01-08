@extends('layouts.authenticated')
@section('title', '- Permohonan Starlink Jelajah')
@section('header-title', 'Permohonan Starlink Jelajah')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 text-purple-700">Formulir Permohonan Starlink Jelajah</h1>

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

    <!-- Service Info -->
    <div class="mb-6 bg-purple-50 border-l-4 border-purple-500 rounded-lg p-4">
        <div class="text-sm">
            <p class="text-purple-700 font-semibold mb-1">Informasi Layanan:</p>
            <p class="text-purple-700">
                Layanan internet starlink jelajah untuk kegiatan di luar ruangan. <strong>Jumlah unit tersedia saat ini hanya 1.</strong><br>
                Mohon ajukan permohonan jauh-jauh hari sebelum kegiatan untuk memastikan ketersediaan.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('user.internet.starlink.store') }}" method="POST">
            @csrf

            <!-- Informasi Pemohon -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Pemohon</h2>

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

            <!-- Detail Kegiatan -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Detail Kegiatan</h2>

                <div class="mb-4">
                    <label for="uraian_kegiatan" class="block text-sm font-semibold text-gray-700 mb-2">
                        Uraian Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="uraian_kegiatan"
                              name="uraian_kegiatan"
                              rows="4"
                              required
                              placeholder="Jelaskan detail kegiatan yang memerlukan Starlink Jelajah"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('uraian_kegiatan') border-red-500 @enderror">{{ old('uraian_kegiatan') }}</textarea>
                    @error('uraian_kegiatan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="tanggal_mulai"
                               name="tanggal_mulai"
                               value="{{ old('tanggal_mulai') }}"
                               min="{{ date('Y-m-d') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('tanggal_mulai') border-red-500 @enderror">
                        @error('tanggal_mulai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="tanggal_selesai"
                               name="tanggal_selesai"
                               value="{{ old('tanggal_selesai') }}"
                               min="{{ date('Y-m-d') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('tanggal_selesai') border-red-500 @enderror">
                        @error('tanggal_selesai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="jam_mulai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time"
                               id="jam_mulai"
                               name="jam_mulai"
                               value="{{ old('jam_mulai') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('jam_mulai') border-red-500 @enderror">
                        @error('jam_mulai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_selesai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time"
                               id="jam_selesai"
                               name="jam_selesai"
                               value="{{ old('jam_selesai') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('jam_selesai') border-red-500 @enderror">
                        @error('jam_selesai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold">
                    Kirim Permohonan
                </button>
                <a href="{{ route('user.internet.starlink.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-set tanggal_selesai to match tanggal_mulai
    const tanggalMulai = document.getElementById('tanggal_mulai');
    const tanggalSelesai = document.getElementById('tanggal_selesai');

    tanggalMulai.addEventListener('change', function() {
        if (!tanggalSelesai.value || tanggalSelesai.value < tanggalMulai.value) {
            tanggalSelesai.value = tanggalMulai.value;
        }
        tanggalSelesai.min = tanggalMulai.value;
    });
});
</script>
@endsection
