@extends('layouts.authenticated')
@section('title', '- Ajukan Cloud Storage')
@section('header-title', 'Cloud Storage')

@section('content')
<div class="container mx-auto px-4 max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Ajukan Permohonan Cloud Storage</h1>
        <a href="{{ route('user.datacenter.cloud-storage.index') }}"
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

    <form action="{{ route('user.datacenter.cloud-storage.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
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

            <!-- Spesifikasi Cloud Storage -->
            <div class="pt-4 pb-3 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">Spesifikasi Cloud Storage</h2>
            </div>

            <!-- Kapasitas -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Kapasitas Maksimal (GB) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="kapasitas_gb" value="{{ old('kapasitas_gb') }}" required min="1"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                <p class="mt-1 text-xs text-gray-500">Contoh: 100, 500, 1000 GB</p>
            </div>

            <!-- Tipe Cloud Storage -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Tipe Cloud Storage <span class="text-red-500">*</span>
                </label>
                <select name="tipe" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">-- Pilih Tipe Cloud Storage --</option>
                    <option value="Internal Cloud (Synology)" {{ old('tipe') == 'Internal Cloud (Synology)' ? 'selected' : '' }}>
                        Internal Cloud (Synology)
                    </option>
                    <option value="GoogleDrive" {{ old('tipe') == 'GoogleDrive' ? 'selected' : '' }}>
                        GoogleDrive
                    </option>
                </select>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Keterangan <span class="text-red-500">*</span>
                </label>
                <textarea name="keterangan" rows="5" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                          placeholder="Jelaskan tujuan penggunaan cloud storage, jenis data yang akan disimpan, jumlah pengguna, dll...">{{ old('keterangan') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-2 pt-4">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-semibold">
                    Ajukan Permohonan
                </button>
                <a href="{{ route('user.datacenter.cloud-storage.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded font-semibold">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
