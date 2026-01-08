@extends('layouts.authenticated')

@section('title', '- Import NIP Email')
@section('header-title', 'Import NIP Email')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Import NIP ke Master Data Email</h1>
        <a href="{{ route('admin.email-accounts.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">
            Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3">Petunjuk Upload CSV</h2>
            <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-4">
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>File harus berformat CSV (.csv atau .txt)</li>
                    <li>Format file: <strong>NIP,Email</strong> (2 kolom)</li>
                    <li>Contoh isi file:
                        <pre class="bg-gray-100 p-2 rounded mt-2 text-sm">196303291992031006,udaurobinson29@kaltaraprov.go.id
196712151997031007,jalilabdul@kaltaraprov.go.id
196505061994031012,budyo6565@kaltaraprov.go.id</pre>
                    </li>
                    <li>Sistem akan mencocokkan alamat email lengkap dengan kolom <strong>email</strong> di master data</li>
                    <li>Jika email ditemukan, NIP akan disimpan/diupdate ke database</li>
                    <li>Ukuran file maksimal: 2MB</li>
                </ol>
            </div>
        </div>

        <form action="{{ route('admin.email-accounts.import-nip') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">
                    Upload File CSV <span class="text-red-500">*</span>
                </label>
                <input
                    type="file"
                    name="csv_file"
                    id="csv_file"
                    accept=".csv,.txt"
                    required
                    class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100
                        border border-gray-300 rounded-md cursor-pointer"
                >
                <p class="mt-1 text-sm text-gray-500">Format: CSV atau TXT, maksimal 2MB</p>
            </div>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded font-semibold"
                >
                    Upload dan Import NIP
                </button>
                <a
                    href="{{ route('admin.email-accounts.index') }}"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded font-semibold"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>

    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded p-4">
        <h3 class="font-semibold text-yellow-800 mb-2">Catatan Penting:</h3>
        <ul class="list-disc list-inside space-y-1 text-yellow-700 text-sm">
            <li>Import akan memperbarui NIP yang sudah ada jika berbeda</li>
            <li>Email yang tidak ditemukan di master data akan dilewati dan dicatat di log</li>
            <li>Proses import akan memberikan laporan jumlah data yang berhasil diupdate</li>
        </ul>
    </div>
</div>
@endsection
