@extends('layouts.authenticated')
@section('title', '- Ajukan Reset Akun VPN')
@section('header-title', 'Reset Akun VPN')

@section('content')
<div class="container mx-auto px-4 max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Ajukan Reset Akun VPN</h1>
        <a href="{{ route('user.vpn.reset.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    <!-- Info -->
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <p class="text-sm text-blue-800">
            Gunakan layanan ini jika Anda lupa username atau password VPN Anda. Admin akan memberikan kredensial baru.
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

    <form action="{{ route('user.vpn.reset.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
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

            <!-- Username VPN Lama -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Username VPN Lama
                </label>
                <input type="text" name="username_vpn_lama" value="{{ old('username_vpn_lama') }}"
                       placeholder="Jika masih ingat, isikan username VPN lama Anda"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                <p class="mt-1 text-xs text-gray-500">Opsional - Kosongkan jika tidak ingat</p>
            </div>

            <!-- Alasan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Alasan Reset <span class="text-red-500">*</span>
                </label>
                <textarea name="alasan" rows="5" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                          placeholder="Jelaskan alasan Anda meminta reset akun VPN (contoh: lupa password, lupa username, dll)">{{ old('alasan') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-2 pt-4">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-semibold">
                    Ajukan Permohonan
                </button>
                <a href="{{ route('user.vpn.reset.index') }}"
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
