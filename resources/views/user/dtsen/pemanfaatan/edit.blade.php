@extends('layouts.authenticated')

@section('title', '- Perbaiki Laporan Pemanfaatan')
@section('header-title', 'Perbaikan Laporan Pemanfaatan DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('user.dtsen.pemanfaatan.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Perbaikan Laporan Pemanfaatan</h1>
    </div>

    @if ($report->catatan_review)
        <div class="mb-6 rounded-lg border border-orange-300 bg-orange-50 p-4 text-sm text-orange-900">
            <p class="font-semibold">Catatan dari verifikator</p>
            <p class="mt-1 whitespace-pre-line">{{ $report->catatan_review }}</p>
        </div>
    @endif

    <form action="{{ route('user.dtsen.pemanfaatan.update', $report->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('user.dtsen.pemanfaatan._form')
    </form>
</div>
@endsection
