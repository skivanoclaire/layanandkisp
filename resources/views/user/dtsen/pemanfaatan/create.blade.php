@extends('layouts.authenticated')

@section('title', '- Lapor Pemanfaatan DTSEN')
@section('header-title', 'Laporan Pemanfaatan DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('user.dtsen.pemanfaatan.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Laporan Pemanfaatan Data DTSEN</h1>
        <p class="text-gray-600 mt-1">Wajib disampaikan minimal satu kali per enam bulan (Bab VIII huruf A Juknis).</p>
    </div>

    <form action="{{ route('user.dtsen.pemanfaatan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('user.dtsen.pemanfaatan._form')
    </form>
</div>
@endsection
