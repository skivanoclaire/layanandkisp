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

@endsection
