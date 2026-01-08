@extends('layouts.authenticated')
@section('title', '- Permohonan Subdomain Terkirim')
@section('header-title', 'Permohonan Subdomain Terkirim')

@section('content')
    <div class="max-w-2xl mx-auto text-center">
        <div class="bg-green-50 border border-green-200 rounded-lg p-8 mb-6">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h1 class="text-2xl font-bold text-green-800 mb-4">Permohonan Subdomain Terkirim!</h1>
            <p class="text-gray-700 mb-2">Nomor tiket Anda:</p>
            <p class="text-3xl font-mono font-semibold text-green-700 mb-6">{{ $ticket }}</p>
            <p class="text-gray-600">
                Permohonan Anda akan diproses oleh Administrator.<br>
                Silakan pantau status pada menu <strong>Formulir Digital → Subdomain</strong>.
            </p>
        </div>

        <div class="flex gap-4 justify-center">
            <a href="{{ route('user.subdomain.index') }}"
                class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                ⟵ Kembali ke Daftar Permohonan
            </a>
            <a href="{{ route('user.subdomain.create') }}"
                class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                + Ajukan Subdomain Lain
            </a>
        </div>
    </div>
@endsection
