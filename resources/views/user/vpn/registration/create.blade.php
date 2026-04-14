@extends('layouts.authenticated')
@section('title', '- Ajukan Pendaftaran VPN')
@section('header-title', 'Pendaftaran VPN')

@section('content')
<div class="container mx-auto px-4 max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Ajukan Pendaftaran VPN</h1>
        <a href="{{ route('user.vpn.registration.index') }}"
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

    <form action="{{ route('user.vpn.registration.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
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
            <input type="hidden" name="is_kabupaten_kota" value="0">
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
                        @foreach(['Bulungan','Malinau','Tana Tidung','Tarakan','Nunukan'] as $kab)
                            <option value="{{ $kab }}" {{ old('kabupaten_kota') == $kab ? 'selected' : '' }}>{{ $kab }}</option>
                        @endforeach
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

            <!-- Tipe VPN -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Tipe VPN <span class="text-red-500">*</span>
                </label>
                <select name="tipe" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">-- Pilih Tipe VPN --</option>
                    <option value="VPN PPTP" {{ old('tipe') == 'VPN PPTP' ? 'selected' : '' }}>VPN PPTP</option>
                    <option value="VPN IPSec/L2TP" {{ old('tipe') == 'VPN IPSec/L2TP' ? 'selected' : '' }}>VPN IPSec/L2TP</option>
                    <option value="SDWAN" {{ old('tipe') == 'SDWAN' ? 'selected' : '' }}>SDWAN</option>
                    <option value="Metro-E" {{ old('tipe') == 'Metro-E' ? 'selected' : '' }}>Metro-E</option>
                </select>
            </div>

            <!-- Bandwidth -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Bandwidth
                </label>
                <input type="text" name="bandwidth" value="{{ old('bandwidth') }}"
                       placeholder="Contoh: 10 Mbps, 100 Mbps, dll"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                <p class="mt-1 text-xs text-gray-500">Opsional - Isikan bandwidth yang dibutuhkan</p>
            </div>

            <!-- Uraian Kebutuhan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Uraian Kebutuhan <span class="text-red-500">*</span>
                </label>
                <textarea name="uraian_kebutuhan" rows="5" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                          placeholder="Jelaskan kebutuhan VPN Anda secara detail...">{{ old('uraian_kebutuhan') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-2 pt-4">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-semibold">
                    Ajukan Permohonan
                </button>
                <a href="{{ route('user.vpn.registration.index') }}"
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
