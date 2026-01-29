@extends('layouts.authenticated')
@section('title', '- Permohonan Pendampingan TTE')
@section('header-title', 'Permohonan Pendampingan TTE')

@section('content')
<div class="container mx-auto px-4 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-purple-700">Permohonan Pendampingan Aktivasi dan Penggunaan TTE Provinsi Kalimantan Utara</h1>
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

    <form action="{{ route('user.tte.assistance.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
        @csrf

        <div class="space-y-6">
            <!-- Nama Perangkat Daerah -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Perangkat Daerah <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" value="{{ $user->name }}" readonly
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                <p class="text-xs text-gray-500 mt-1">Nama Instansi/OPD</p>
            </div>

            <!-- NIP -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    NIP <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nip" value="{{ $user->nip }}" readonly
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
            </div>

            <!-- Email Resmi Pemerintahan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Email Resmi Pemerintahan <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email_resmi" value="{{ $emailAccount->email }}" readonly
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                <p class="text-xs text-gray-500 mt-1">Wajib isi dengan email @kaltaraprov.go.id kalau tidak ada silahkan download form nya di link .....</p>
            </div>

            <!-- Instansi -->
            <div>
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
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Jabatan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="jabatan" value="{{ old('jabatan') }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="Masukkan jabatan Anda">
            </div>

            <!-- Contact Person -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Contact Person <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="no_hp" value="{{ $user->phone }}" readonly
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600"
                    placeholder="Nomor HP">
                <p class="text-xs text-gray-500 mt-1">Nomor HP dari profil pengguna</p>
            </div>

            <!-- Waktu Permohonan Pendampingan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Waktu Permohonan Pendampingan <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="waktu_pendampingan" value="{{ old('waktu_pendampingan') }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    min="{{ date('Y-m-d\TH:i') }}">
                <p class="text-xs text-gray-500 mt-1">dd-MMM-yyyy HH:MM</p>
                <p class="text-xs text-purple-600 mt-1">Choose a date</p>
            </div>

            <!-- Upload Surat Permohonan -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Upload Surat Permohonan <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-400 transition">
                    <input type="file" name="surat_permohonan" required accept=".pdf"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <p class="text-sm text-gray-600 mt-2">File PDF, maksimal 2MB</p>
                </div>
                <p class="text-xs text-gray-600 mt-2">
                    Setelah input silahkan lapor ke grup Helpdesk Layanan TTE Prov Kaltara
                    <a href="https://chat.whatsapp.com/DbcCNvweGp0IPTvvYL0hm5" target="_blank" class="text-purple-600 hover:underline">https://chat.whatsapp.com/DbcCNvweGp0IPTvvYL0hm5</a>
                    kemudian pilih helpdesk dan join grup nya
                </p>
            </div>
        </div>

        <!-- Buttons -->
        <div class="mt-8 flex gap-3">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-semibold transition">
                Submit
            </button>
            <a href="{{ route('user.tte.assistance.index') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold transition inline-block">
                Batal
            </a>
        </div>

    </form>
</div>
@endsection
