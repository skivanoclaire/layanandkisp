@extends('layouts.authenticated')
@section('title', '- Ajukan Akses JIP PDNS')
@section('header-title', 'Akses JIP PDNS')

@section('content')
<div class="container mx-auto px-4 max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Ajukan Akses JIP PDNS</h1>
        <a href="{{ route('user.vpn.jip-pdns.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    <!-- Info -->
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <p class="text-sm text-blue-800">
            <strong>Catatan:</strong> Untuk pengguna Kabupaten/Kota, silakan centang opsi di bawah ini.
            Provinsi membutuhkan informasi Segment IPSec dari Kab/Kota karena CPE Provinsi di-NAT di belakang Edge Provinsi Kaltara.
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
    @endif>

    <form action="{{ route('user.vpn.jip-pdns.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
        @csrf

        <div class="space-y-4">
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
                <input type="text" name="nip" value="{{ old('nip', Auth::user()->nik) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- Checkbox Kab/Kota -->
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="is_kabupaten_kota" id="isKabKota" value="1"
                           {{ old('is_kabupaten_kota') ? 'checked' : '' }}
                           onchange="toggleKabKotaFields()"
                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    <span class="ml-2 text-sm font-semibold text-purple-800">
                        Saya dari Kabupaten/Kota
                    </span>
                </label>
            </div>

            <!-- Kabupaten/Kota Fields (shown when checked) -->
            <div id="kabKotaFields" style="display: {{ old('is_kabupaten_kota') ? 'block' : 'none' }};" class="space-y-4 pl-4 border-l-4 border-purple-300">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Pilih Kabupaten/Kota <span class="text-red-500">*</span>
                    </label>
                    <select name="kabupaten_kota" id="kabupatenKota"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">-- Pilih Kabupaten/Kota --</option>
                        <option value="Bulungan" {{ old('kabupaten_kota') == 'Bulungan' ? 'selected' : '' }}>Bulungan</option>
                        <option value="Malinau" {{ old('kabupaten_kota') == 'Malinau' ? 'selected' : '' }}>Malinau</option>
                        <option value="Tana Tidung" {{ old('kabupaten_kota') == 'Tana Tidung' ? 'selected' : '' }}>Tana Tidung</option>
                        <option value="Tarakan" {{ old('kabupaten_kota') == 'Tarakan' ? 'selected' : '' }}>Tarakan</option>
                        <option value="Nunukan" {{ old('kabupaten_kota') == 'Nunukan' ? 'selected' : '' }}>Nunukan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Nama Instansi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="unit_kerja_manual" id="unitKerjaManual" value="{{ old('unit_kerja_manual') }}"
                           placeholder="Contoh: Dinas Komunikasi dan Informatika"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <p class="mt-1 text-xs text-gray-500">Isikan nama unit kerja Anda secara manual</p>
                </div>
            </div>

            <!-- Provinsi Field (shown when unchecked) -->
            <div id="provinsiField" style="display: {{ old('is_kabupaten_kota') ? 'none' : 'block' }};">
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Instansi <span class="text-red-500">*</span>
                </label>
                <select name="unit_kerja_id" id="unitKerjaId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">-- Pilih Instansi --</option>
                    @foreach($unitKerjas as $uk)
                        <option value="{{ $uk->id }}" {{ old('unit_kerja_id') == $uk->id ? 'selected' : '' }}>
                            {{ $uk->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Uraian Permohonan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Uraian Permohonan <span class="text-red-500">*</span>
                </label>
                <textarea name="uraian_permohonan" rows="5" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                          placeholder="Jelaskan permohonan akses JIP PDNS Anda secara detail...">{{ old('uraian_permohonan') }}</textarea>
            </div>

            <!-- Keterangan (for Segment IPSec info) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Keterangan / Informasi Tambahan
                </label>
                <textarea name="keterangan" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                          placeholder="Informasi tambahan seperti Segment IPSec atau detail teknis lainnya...">{{ old('keterangan') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Opsional - Gunakan untuk menyampaikan informasi Segment IPSec atau detail lainnya</p>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-2 pt-4">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-semibold">
                    Ajukan Permohonan
                </button>
                <a href="{{ route('user.vpn.jip-pdns.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded font-semibold">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>

<script>
function toggleKabKotaFields() {
    const checkbox = document.getElementById('isKabKota');
    const kabKotaFields = document.getElementById('kabKotaFields');
    const provinsiField = document.getElementById('provinsiField');
    const kabupatenKota = document.getElementById('kabupatenKota');
    const unitKerjaManual = document.getElementById('unitKerjaManual');
    const unitKerjaId = document.getElementById('unitKerjaId');

    if (checkbox.checked) {
        kabKotaFields.style.display = 'block';
        provinsiField.style.display = 'none';
        kabupatenKota.required = true;
        unitKerjaManual.required = true;
        unitKerjaId.required = false;
        unitKerjaId.value = '';
    } else {
        kabKotaFields.style.display = 'none';
        provinsiField.style.display = 'block';
        kabupatenKota.required = false;
        unitKerjaManual.required = false;
        unitKerjaId.required = true;
        kabupatenKota.value = '';
        unitKerjaManual.value = '';
    }
}
</script>
@endsection
