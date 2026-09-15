@extends('layouts.authenticated')

@section('title', '- Ubah Permintaan Data DTSEN')
@section('header-title', 'Ubah Permintaan Data DTSEN')

@section('content')
<div class="container mx-auto p-6 max-w-5xl">
    <div class="mb-6">
        <a href="{{ route('user.dtsen.permohonan.show', $item->id) }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke detail</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $item->ticket_no }}</h1>
        <p class="text-gray-600 mt-1">{{ $item->nama_program }}</p>
    </div>

    @if ($item->adm_catatan)
        <div class="mb-6 rounded-lg border border-orange-300 bg-orange-50 p-4 text-sm text-orange-900">
            <p class="font-semibold">Catatan perbaikan dari verifikasi administrasi</p>
            <p class="mt-1 whitespace-pre-line">{{ $item->adm_catatan }}</p>
        </div>
    @endif

    <form action="{{ route('user.dtsen.permohonan.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('user.dtsen.permohonan._form')
    </form>

    {{-- Form penghapusan dokumen diletakkan di luar form utama agar tidak bersarang. --}}
    @foreach ($item->documents->where('jenis', 'pendukung') as $doc)
        <form id="hapus-dok-{{ $doc->id }}" action="{{ route('user.dtsen.permohonan.dokumen.destroy', [$item->id, $doc->id]) }}" method="POST" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endforeach
</div>
@endsection
