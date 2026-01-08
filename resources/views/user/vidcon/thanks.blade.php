@extends('layouts.authenticated')
@section('title', '- Permohonan Berhasil')
@section('header-title', 'Permohonan Video Conference')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <!-- Success Icon -->
        <div class="mb-6">
            <svg class="w-20 h-20 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-green-700 mb-4">Permohonan Berhasil Dikirim!</h1>

        <p class="text-gray-700 mb-6">
            Terima kasih telah mengajukan permohonan video conference.<br>
            Permohonan Anda akan segera diproses oleh tim kami.
        </p>

        <!-- Ticket Information -->
        <div class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-6 mb-6 text-left">
            <h2 class="text-lg font-semibold text-purple-800 mb-4">Detail Permohonan</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-purple-700 mb-1">No. Tiket:</label>
                    <p class="text-gray-800 font-mono">{{ $vidconRequest->ticket_no }}</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-purple-700 mb-1">Status:</label>
                    <p>{!! $vidconRequest->status_badge !!}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-purple-700 mb-1">Judul Kegiatan:</label>
                    <p class="text-gray-800">{{ $vidconRequest->judul_kegiatan }}</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-purple-700 mb-1">Tanggal:</label>
                    <p class="text-gray-800">
                        {{ $vidconRequest->tanggal_mulai->format('d/m/Y') }}
                        @if($vidconRequest->tanggal_mulai->format('Y-m-d') !== $vidconRequest->tanggal_selesai->format('Y-m-d'))
                            - {{ $vidconRequest->tanggal_selesai->format('d/m/Y') }}
                        @endif
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-purple-700 mb-1">Waktu:</label>
                    <p class="text-gray-800">{{ $vidconRequest->jam_mulai }} - {{ $vidconRequest->jam_selesai }} WITA</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-purple-700 mb-1">Platform:</label>
                    <p class="text-gray-800">{{ $vidconRequest->platform_display }}</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-purple-700 mb-1">Diajukan:</label>
                    <p class="text-gray-800">{{ $vidconRequest->submitted_at->format('d/m/Y H:i') }} WITA</p>
                </div>
            </div>
        </div>

        <!-- Information Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-blue-700">
                    <p class="font-semibold mb-1">Informasi Penting:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Permohonan Anda akan ditinjau oleh admin</li>
                        <li>Setelah disetujui, Anda akan menerima link meeting dan informasi akses</li>
                        <li>Anda dapat melihat status permohonan di halaman daftar permohonan</li>
                        <li>Jika ada pertanyaan, hubungi admin melalui kontak yang tersedia</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Operating Hours -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6 text-left">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-gray-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-gray-700">
                    <p class="font-semibold mb-1">Jam Layanan:</p>
                    <p>Senin s.d. Kamis: 07.30 - 16.00 WITA<br>
                       Jumat: 07.30 - 16.30 WITA</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('user.vidcon.index') }}"
               class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold">
                Lihat Daftar Permohonan
            </a>
            <a href="{{ route('user.vidcon.create') }}"
               class="bg-white hover:bg-gray-50 text-purple-700 border border-purple-300 px-6 py-2 rounded-lg font-semibold">
                Buat Permohonan Baru
            </a>
            <a href="{{ route('user.dashboard') }}"
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
