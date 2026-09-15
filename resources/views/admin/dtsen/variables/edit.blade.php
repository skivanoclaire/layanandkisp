@extends('layouts.authenticated')

@section('title', '- Ubah Variabel DTSEN')
@section('header-title', 'Ubah Variabel DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('admin.dtsen.variables.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke katalog</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $item->kode }} — {{ $item->nama }}</h1>
    </div>

    <form action="{{ route('admin.dtsen.variables.update', $item->id) }}" method="POST">
        @csrf @method('PUT')
        @include('admin.dtsen.variables._form')
    </form>
</div>
@endsection
