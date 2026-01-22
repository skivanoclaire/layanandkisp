@extends('layouts.authenticated')

@section('title', '- Akses Ditolak')
@section('header-title', 'Akses Ditolak')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8 text-center">
        <svg class="w-24 h-24 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>

        <h1 class="text-3xl font-bold text-gray-800 mb-4">403 - Akses Ditolak</h1>
        <p class="text-gray-600 mb-6">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}
        </p>

        <div class="flex justify-center gap-4">
            @php
                $dashboardRoute = auth()->check() && auth()->user()->hasRole('Admin') ? 'admin.dashboard' : 'user.dashboard';
            @endphp
            <a href="{{ route($dashboardRoute) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold">
                Kembali ke Dashboard
            </a>
            <a href="javascript:history.back()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded font-semibold">
                Kembali
            </a>
        </div>
    </div>
</div>
@endsection
