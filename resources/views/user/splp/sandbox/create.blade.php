@extends('layouts.authenticated')

@section('title', '- Uji Coba Sandbox SPLP')
@section('header-title', 'Permohonan Uji Coba (Sandbox) SPLP')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Permohonan Uji Coba (Sandbox)</h1>
        <p class="text-gray-600 mt-1">Ajukan lingkungan sandbox untuk menguji integrasi sebelum produksi.</p>
    </div>
    <form action="{{ route('user.splp.sandbox.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('user.splp.sandbox._form')
    </form>
</div>
@endsection
