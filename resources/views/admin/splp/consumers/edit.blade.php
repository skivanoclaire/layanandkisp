@extends('layouts.authenticated')

@section('title', '- Edit Konsumen SPLP')
@section('header-title', 'Edit Konsumen SPLP')

@section('content')
<div class="container mx-auto p-6 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Konsumen — {{ $consumer->nama_konsumen }}</h1>
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form action="{{ route('admin.splp.consumers.update', $consumer) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.splp.consumers._form')
        </form>
    </div>
</div>
@endsection
