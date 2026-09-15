@extends('layouts.authenticated')

@section('title', '- Daftarkan Akun Layanan DTSEN')
@section('header-title', 'Pendaftaran Akun Layanan DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-5xl">
    <div class="mb-6">
        <a href="{{ route('user.dtsen.akun.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Formulir Registrasi Akun Layanan DTSEN</h1>
        <p class="text-gray-600 mt-1">Tahap 1 dari alur berbagi pakai data DTSEN (Lampiran I Juknis).</p>
    </div>

    <form action="{{ route('user.dtsen.akun.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('user.dtsen.akun._form')
    </form>
</div>
@endsection
