@extends('layouts.authenticated')

@section('title', '- Ajukan Permintaan Data DTSEN')
@section('header-title', 'Permintaan Data DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-5xl">
    <div class="mb-6">
        <a href="{{ route('user.dtsen.permohonan.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Formulir Permintaan Data DTSEN</h1>
        <p class="text-gray-600 mt-1">Tahap 2 dari alur berbagi pakai data DTSEN (Lampiran II &amp; IV Juknis).</p>
    </div>

    <form action="{{ route('user.dtsen.permohonan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('user.dtsen.permohonan._form')
    </form>
</div>
@endsection
