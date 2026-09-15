@extends('layouts.authenticated')

@section('title', '- Berita Acara Pemusnahan DTSEN')
@section('header-title', 'Berita Acara Pemusnahan Data DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('user.dtsen.pemusnahan.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Berita Acara Pemusnahan Data</h1>
        <p class="text-gray-600 mt-1">Bab VII huruf C Juknis.</p>
    </div>

    <form action="{{ route('user.dtsen.pemusnahan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('user.dtsen.pemusnahan._form')
    </form>
</div>
@endsection
