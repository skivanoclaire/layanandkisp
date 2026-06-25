@extends('layouts.authenticated')

@section('title', '- Tambah Layanan SPLP')
@section('header-title', 'Tambah Layanan SPLP')

@section('content')
<div class="container mx-auto p-6 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Tambah Layanan SPLP</h1>

    {{-- Import dari SPLP (WSO2) --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 mb-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-bold text-blue-900">Import dari SPLP</h2>
                <p class="text-sm text-blue-800 mt-1">Unggah file ekspor API dari SPLP (WSO2) — <span class="font-mono">.zip</span> bundle atau <span class="font-mono">api.yaml</span>. Field di bawah akan terisi otomatis untuk Anda tinjau. Secret backend tidak ikut disimpan.</p>
            </div>
        </div>
        <form action="{{ route('admin.splp.services.import') }}" method="POST" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
            @csrf
            <input type="file" name="splp_export" accept=".zip,.yaml,.yml,.txt" required
                   class="text-sm border border-blue-300 rounded-lg p-2 bg-white">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Import &amp; Isi Form</button>
        </form>
        @error('splp_export')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
    </div>

    @if (session('import_error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('import_error') }}</div>
    @endif
    @if (session('import_notice'))
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-2">{{ session('import_notice') }}</div>
    @endif
    @if (session('import_warnings'))
        @foreach (session('import_warnings') as $w)
            <div class="bg-amber-50 border border-amber-300 text-amber-800 px-4 py-2 rounded mb-2 text-sm">⚠ {{ $w }}</div>
        @endforeach
    @endif

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form action="{{ route('admin.splp.services.store') }}" method="POST">
            @csrf
            @include('admin.splp.services._form')
        </form>
    </div>
</div>
@endsection
