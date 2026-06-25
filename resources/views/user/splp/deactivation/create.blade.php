@extends('layouts.authenticated')

@section('title', '- Penonaktifan/Pencabutan SPLP')
@section('header-title', 'Penonaktifan / Pencabutan Endpoint (SPLP)')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Penonaktifan / Pencabutan Endpoint</h1>
        <p class="text-gray-600 mt-1">Ajukan penonaktifan atau pencabutan layanan/konsumen SPLP.</p>
    </div>
    <form action="{{ route('user.splp.deactivation.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('user.splp.deactivation._form')
    </form>
</div>
@endsection
