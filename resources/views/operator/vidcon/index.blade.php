@extends('layouts.authenticated')

@section('title', '- Tugas Fasilitasi Vidcon')
@section('header-title', 'Tugas Fasilitasi Vidcon')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tugas Fasilitasi Saya</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('operator.vidcon.index') }}" class="flex gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="border-gray-300 rounded-md shadow-sm">
                    <option value="">Semua</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Akan Datang</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filter</button>
                <a href="{{ route('operator.vidcon.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Reset</a>
            </div>
        </form>
    </div>

    {{-- Task List --}}
    <div class="grid grid-cols-1 gap-6">
        @forelse($vidconTasks as $task)
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $task->judul_kegiatan }}</h3>
                    <div class="flex items-center text-sm text-gray-600 mb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        {{ $task->unitKerja->nama ?? '-' }}
                    </div>
                </div>
                @php
                    $isUpcoming = $task->tanggal_mulai >= now()->format('Y-m-d');
                    $isToday = $task->tanggal_mulai == now()->format('Y-m-d');
                @endphp
                @if($isToday)
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                        Hari Ini
                    </span>
                @elseif($isUpcoming)
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        Akan Datang
                    </span>
                @else
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                        Selesai
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">
                        <strong>Tanggal:</strong>
                        {{ $task->tanggal_mulai ? $task->tanggal_mulai->format('d/m/Y') : '-' }}
                        @if($task->tanggal_selesai && $task->tanggal_mulai != $task->tanggal_selesai)
                            s/d {{ $task->tanggal_selesai->format('d/m/Y') }}
                        @endif
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong>Waktu:</strong>
                        {{ $task->jam_mulai ? $task->jam_mulai->format('H:i') : '-' }} -
                        {{ $task->jam_selesai ? $task->jam_selesai->format('H:i') : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">
                        <strong>Platform:</strong> {{ $task->platform }}
                    </p>
                    @if($task->platform === 'Zoom' && $task->akun_zoom)
                        <p class="text-sm text-gray-600">
                            <strong>Akun Zoom:</strong>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-orange-100 text-orange-800">
                                Akun {{ $task->akun_zoom }}
                            </span>
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="flex items-center text-sm text-gray-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $task->documentations->count() }} Foto Dokumentasi
                </div>
                <a href="{{ route('operator.vidcon.show', $task->id) }}"
                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Lihat Detail & Upload Dokumentasi
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-gray-500 text-lg">Belum ada tugas yang ditugaskan kepada Anda</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $vidconTasks->links() }}
    </div>
</div>
@endsection
