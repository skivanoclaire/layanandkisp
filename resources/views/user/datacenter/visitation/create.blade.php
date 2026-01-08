@extends('layouts.authenticated')
@section('title', '- Ajukan Kunjungan/Colocation')
@section('header-title', 'Kunjungan/Colocation')

@section('content')
<div class="container mx-auto px-4 max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Ajukan Permohonan Kunjungan/Colocation</h1>
        <a href="{{ route('user.datacenter.visitation.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    <!-- Operating Hours Info -->
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <p class="text-sm text-blue-800">
            <strong>Jam Layanan:</strong><br>
            Senin - Kamis: 07.30 - 16.00 WITA<br>
            Jumat: 07.30 - 16.30 WITA
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3">
            <ul class="list-disc list-inside text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('user.datacenter.visitation.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
        @csrf

        <div class="space-y-4">
            <!-- Data Pemohon -->
            <div class="pb-3 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">Data Pemohon</h2>
            </div>

            <!-- Nama -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Nama <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" value="{{ old('nama', Auth::user()->name) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- NIP -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    NIP <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nip" value="{{ Auth::user()->nip ?? '' }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 cursor-not-allowed">
            </div>

            <!-- Instansi -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Instansi <span class="text-red-500">*</span>
                </label>
                <select name="unit_kerja_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">-- Pilih Instansi --</option>
                    @foreach($unitKerjas as $uk)
                        <option value="{{ $uk->id }}" {{ old('unit_kerja_id') == $uk->id ? 'selected' : '' }}>
                            {{ $uk->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Informasi Kunjungan -->
            <div class="pt-4 pb-3 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">Informasi Kunjungan</h2>
            </div>

            <!-- Tujuan Kunjungan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Tujuan Kunjungan <span class="text-red-500">*</span>
                </label>
                <select name="tujuan_kunjungan" id="tujuan_kunjungan" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">-- Pilih Tujuan Kunjungan --</option>
                    <option value="Kunjungan & Inspeksi Formal" {{ old('tujuan_kunjungan') == 'Kunjungan & Inspeksi Formal' ? 'selected' : '' }}>Kunjungan & Inspeksi Formal</option>
                    <option value="Penempatan Aset" {{ old('tujuan_kunjungan') == 'Penempatan Aset' ? 'selected' : '' }}>Penempatan Aset</option>
                    <option value="Pengambilan Aset" {{ old('tujuan_kunjungan') == 'Pengambilan Aset' ? 'selected' : '' }}>Pengambilan Aset</option>
                </select>
            </div>

            <!-- Tanggal Kunjungan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Tanggal Kunjungan <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal_kunjungan" value="{{ old('tanggal_kunjungan') }}" required
                       min="{{ date('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- Jam Mulai & Selesai -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Jam Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Jam Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Keterangan <span class="text-red-500">*</span>
                </label>
                <textarea name="keterangan" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                          placeholder="Jelaskan tujuan kunjungan Anda secara detail...">{{ old('keterangan') }}</textarea>
            </div>

            <!-- Data Aset (Conditional) -->
            <div id="asset-section" class="hidden space-y-4">
                <div class="pt-4 pb-3 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-800">Data Aset</h2>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Nama Aset
                    </label>
                    <input type="text" name="nama_aset" value="{{ old('nama_aset') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Contoh: Server Dell PowerEdge R740">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Nomor Aset
                    </label>
                    <input type="text" name="nomor_aset" value="{{ old('nomor_aset') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Nomor inventaris aset">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Catatan Aset
                    </label>
                    <textarea name="catatan_aset" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                              placeholder="Catatan tambahan terkait aset (spesifikasi, kondisi, dll)">{{ old('catatan_aset') }}</textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-2 pt-4">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-semibold">
                    Ajukan Permohonan
                </button>
                <a href="{{ route('user.datacenter.visitation.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded font-semibold">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tujuanSelect = document.getElementById('tujuan_kunjungan');
    const assetSection = document.getElementById('asset-section');

    function toggleAssetSection() {
        const value = tujuanSelect.value;
        if (value === 'Penempatan Aset' || value === 'Pengambilan Aset') {
            assetSection.classList.remove('hidden');
        } else {
            assetSection.classList.add('hidden');
        }
    }

    tujuanSelect.addEventListener('change', toggleAssetSection);

    // Initial check on page load
    toggleAssetSection();
});
</script>
@endsection
