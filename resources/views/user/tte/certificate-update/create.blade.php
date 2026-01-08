@extends('layouts.authenticated')
@section('title', '- Pembaruan Sertifikat TTE')
@section('header-title', 'Pembaruan Sertifikat TTE')

@section('content')
<div class="container mx-auto px-4 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-700">Permohonan Pembaruan Sertifikat Elektronik TTE Provinsi Kalimantan Utara</h1>
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

    <form action="{{ route('user.tte.certificate-update.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Nama Lengkap -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama" value="{{ $user->name }}" readonly
                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600"
                placeholder="Nama.....">
            <p class="text-xs text-gray-500 mt-1">Nama Lengkap Tanpa Gelar</p>
        </div>

        <!-- Email Resmi Pemerintahan -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Email Resmi Pemerintahan <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email_resmi" value="{{ $emailAccount->email }}" readonly
                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
            <p class="text-xs text-gray-500 mt-1">Wajib isi dengan email @kaltaraprov.go.id yang telah didaftarkan di BSrE . Link reset passphrase akan dikirimkan ke email tersebut</p>
        </div>

        <!-- Instansi -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Instansi <span class="text-red-500">*</span>
            </label>
            <select name="instansi" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">-- Pilih Instansi --</option>
                @foreach($unitKerjas as $unitKerja)
                    <option value="{{ $unitKerja->nama }}" {{ old('instansi') == $unitKerja->nama ? 'selected' : '' }}>
                        {{ $unitKerja->nama }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Pilih Instansi</p>
        </div>

        <!-- Jabatan -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Jabatan <span class="text-red-500">*</span>
            </label>
            <input type="text" name="jabatan" value="{{ old('jabatan') }}" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                placeholder="Masukkan jabatan Anda">
        </div>

        <!-- Contact Person -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Contact Person <span class="text-red-500">*</span>
            </label>
            <input type="tel" name="no_hp" value="{{ $user->phone }}" readonly
                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600"
                placeholder="Nomor HP">
            <p class="text-xs text-gray-500 mt-1">Nomor HP dari profil pengguna</p>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-semibold transition">
                Submit
            </button>
            <a href="{{ route('user.tte.certificate-update.index') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold transition inline-block">
                Batal
            </a>
        </div>

        <!-- Info Footer -->
        <div class="bg-gray-800 text-white p-4 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm">
                    Do not submit confidential information such as credit card details, mobile and ATM PINs, OTPs, account passwords, etc. through this form.
                </p>
            </div>
        </div>
    </form>
</div>
@endsection
