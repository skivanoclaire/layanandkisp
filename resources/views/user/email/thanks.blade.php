@extends('layouts.authenticated')
@section('title', '- Permohonan Email Terkirim')
@section('header-title', 'Permohonan Email Terkirim')

@section('content')
    <div class="max-w-2xl mx-auto text-center">
        <h1 class="text-2xl font-bold mb-4">Permohonan terkirim</h1>
        <p class="mb-2">Nomor tiket Anda:</p>
        <p class="text-2xl font-mono font-semibold mb-6">{{ $ticket }}</p>
        <p class="mb-6">Silakan pantau status pada menu <strong>Formulir Digital → Email</strong>.</p>

        <a href="{{ route('user.email.index') }}"
            class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            ⟵ Kembali ke Daftar Permohonan
        </a>
    </div>
@endsection
