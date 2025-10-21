@extends('layouts.user')

@section('title', 'Formulir Digital')

@section('content')
    <h1 class="text-2xl font-bold mb-6 text-green-700">Formulir Digital</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

        {{-- Menu Aktif --}}
        <a href="{{ route('user.digital.rekomendasi') }}">
            <div
                class="bg-white shadow rounded-lg p-6 text-center hover:shadow-lg transition duration-300 cursor-pointer border border-gray-100">
                <div class="text-4xl mb-3">📄</div>
                <div class="text-md font-semibold text-gray-800">Rekomendasi</div>
            </div>
        </a>

        <a href="{{ route('user.email.index') }}">
            <div
                class="bg-white shadow rounded-lg p-6 text-center hover:shadow-lg transition duration-300 cursor-pointer border border-gray-100">
                <div class="text-4xl mb-3">✉️</div>
                <div class="text-md font-semibold text-gray-800">Email</div>
            </div>
        </a>



        {{-- Menu Belum Aktif --}}
        @php
            $menus = [
                ['icon' => '📹', 'label' => 'Peliputan'],
                ['icon' => '📰', 'label' => 'Publikasi'],
                ['icon' => '🎞️', 'label' => 'Konten Multimedia'],
                ['icon' => '🌐', 'label' => 'Subdomain'],
                ['icon' => '🏛️', 'label' => 'Pendaftaran Sistem Elektronik PSE'],
                ['icon' => '💾', 'label' => 'Pusat Data'],
                ['icon' => '🛡️', 'label' => 'TTE'],
                ['icon' => '📈', 'label' => 'Portal Data'],
                ['icon' => '🖧', 'label' => 'SPLP'],
                ['icon' => '📖', 'label' => 'SPBE'],
                ['icon' => '📘', 'label' => 'PPID'],
                ['icon' => '🛡️', 'label' => 'Keamanan Informasi'],
                ['icon' => '📡', 'label' => 'Jaringan Internet'],
                ['icon' => '🔗', 'label' => 'VPN'],
                ['icon' => '📶', 'label' => 'Wifi Publik'],
                ['icon' => '☁️', 'label' => 'Cloud Storage'],
                ['icon' => '❓', 'label' => 'Helpdesk TIK'],
                ['icon' => '🎥', 'label' => 'Zoom/Youtube Live Streaming'],
            ];
        @endphp

        @foreach ($menus as $menu)
            <div class="bg-gray-100 opacity-50 rounded-lg p-6 text-center cursor-not-allowed border border-gray-200">
                <div class="text-4xl mb-3">{{ $menu['icon'] }}</div>
                <div class="text-md font-semibold text-gray-800">{{ $menu['label'] }}</div>
                <div class="text-sm text-gray-500 mt-2 italic">(Belum tersedia)</div>
            </div>
        @endforeach

    </div>
@endsection
