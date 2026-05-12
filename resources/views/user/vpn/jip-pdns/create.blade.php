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
            <strong>Catatan:</strong> Untuk pengguna Kabupaten/Kota, mohon mencantumkan informasi Segment IPSec pada kolom Keterangan
            karena CPE Provinsi di-NAT di belakang Edge Provinsi Kaltara.
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
            <!-- Nama (terkunci, ambil dari profil) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                <input type="text" name="nama" value="{{ Auth::user()->name }}" readonly
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
            </div>

            <!-- NIP (terkunci, ambil dari profil) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">NIP</label>
                <input type="text" name="nip" value="{{ Auth::user()->nik }}" readonly
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
            </div>

            <!-- Instansi (terkunci, ambil dari profil) -->
            <input type="hidden" name="is_kabupaten_kota" value="0">
            <input type="hidden" name="unit_kerja_id" value="{{ Auth::user()->unit_kerja_id }}">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi</label>
                <input type="text" value="{{ Auth::user()->unitKerja->nama ?? '-' }}" readonly
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-500">Diambil dari profil. Hubungi admin jika perlu perubahan.</p>
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

@endsection
