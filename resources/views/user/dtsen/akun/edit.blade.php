@extends('layouts.authenticated')

@section('title', '- Ubah Permohonan Akun DTSEN')
@section('header-title', 'Ubah Permohonan Akun Layanan DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-5xl">
    <div class="mb-6">
        <a href="{{ route('user.dtsen.akun.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $item->ticket_no }}</h1>
        <p class="text-gray-600 mt-1">Lengkapi isian, lalu ajukan kembali untuk diverifikasi Admin DKISP.</p>
    </div>

    @if ($item->catatan_perbaikan)
        <div class="mb-6 rounded-lg border border-orange-300 bg-orange-50 p-4 text-sm text-orange-900">
            <p class="font-semibold">Catatan perbaikan dari verifikator</p>
            <p class="mt-1 whitespace-pre-line">{{ $item->catatan_perbaikan }}</p>
        </div>
    @endif

    <form action="{{ route('user.dtsen.akun.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('user.dtsen.akun._form')
    </form>
</div>
@endsection
