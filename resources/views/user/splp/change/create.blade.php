@extends('layouts.authenticated')

@section('title', '- Perubahan/Perpanjangan SPLP')
@section('header-title', 'Perubahan / Perpanjangan Endpoint (SPLP)')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Perubahan / Perpanjangan Konfigurasi Endpoint</h1>
        <p class="text-gray-600 mt-1">Ajukan perubahan atau perpanjangan layanan SPLP yang sudah terdaftar.</p>
    </div>
    <form action="{{ route('user.splp.change.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('user.splp.change._form')
    </form>
</div>
@endsection
