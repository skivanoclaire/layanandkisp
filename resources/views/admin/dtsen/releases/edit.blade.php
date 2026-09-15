@extends('layouts.authenticated')

@section('title', '- Ubah Rilis DTSEN')
@section('header-title', 'Ubah Rilis DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('admin.dtsen.releases.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar rilis</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $item->nomor_rilis }}</h1>
    </div>

    <form action="{{ route('admin.dtsen.releases.update', $item->id) }}" method="POST">
        @csrf @method('PUT')
        @include('admin.dtsen.releases._form')
    </form>
</div>
@endsection
