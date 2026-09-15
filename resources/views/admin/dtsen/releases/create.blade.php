@extends('layouts.authenticated')

@section('title', '- Tambah Rilis DTSEN')
@section('header-title', 'Tambah Rilis DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('admin.dtsen.releases.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar rilis</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Tambah Rilis</h1>
    </div>

    <form action="{{ route('admin.dtsen.releases.store') }}" method="POST">
        @csrf
        @include('admin.dtsen.releases._form')
    </form>
</div>
@endsection
