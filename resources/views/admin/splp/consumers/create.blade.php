@extends('layouts.authenticated')

@section('title', '- Tambah Konsumen SPLP')
@section('header-title', 'Tambah Konsumen SPLP')

@section('content')
<div class="container mx-auto p-6 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Tambah Konsumen SPLP</h1>
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form action="{{ route('admin.splp.consumers.store') }}" method="POST">
            @csrf
            @include('admin.splp.consumers._form')
        </form>
    </div>
</div>
@endsection
