@extends('layouts.authenticated')

@section('title', '- Pendaftaran Endpoint Penyedia SPLP')
@section('header-title', 'Pendaftaran Endpoint Penyedia (SPLP)')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pendaftaran Endpoint Penyedia Layanan</h1>
        <p class="text-gray-600 mt-1">Daftarkan endpoint backend instansi Anda agar dapat dipublikasikan melalui SPLP.</p>
    </div>

    <form action="{{ route('user.splp.provider.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('user.splp.provider._form')
    </form>
</div>
@endsection
