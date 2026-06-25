@extends('layouts.authenticated')

@section('title', '- Pendaftaran Akses Konsumen SPLP')
@section('header-title', 'Pendaftaran Akses Konsumen (SPLP)')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pendaftaran Akses Konsumen Layanan</h1>
        <p class="text-gray-600 mt-1">Ajukan akses ke layanan yang sudah terdaftar & aktif di SPLP.</p>
    </div>

    <form action="{{ route('user.splp.consumer.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('user.splp.consumer._form')
    </form>
</div>
@endsection
