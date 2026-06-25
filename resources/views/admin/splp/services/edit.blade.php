@extends('layouts.authenticated')

@section('title', '- Edit Layanan SPLP')
@section('header-title', 'Edit Layanan SPLP')

@section('content')
<div class="container mx-auto p-6 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Layanan — {{ $service->kode_layanan }}</h1>
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form action="{{ route('admin.splp.services.update', $service) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.splp.services._form')
        </form>
    </div>
</div>
@endsection
