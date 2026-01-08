@extends('layouts.authenticated')
@section('title', '- Layanan Starlink Jelajah')
@section('header-title', 'Layanan Starlink Jelajah')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-purple-700 mb-2">Layanan Starlink Jelajah</h1>
        <p class="text-gray-600">Layanan internet starlink jelajah untuk kegiatan di luar ruangan. Jumlah unit tersedia saat ini hanya 1.</p>
    </div>

    <!-- Service Status Banner -->
    @if($serviceSetting && !$serviceSetting->is_active)
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-red-700 font-semibold">Layanan Tidak Tersedia Sementara</p>
                <p class="text-red-700 text-sm mt-1">{{ $serviceSetting->inactive_reason ?? 'Layanan sedang dalam perbaikan atau digunakan untuk keperluan internal.' }}</p>
            </div>
        </div>
    </div>
    @else
    <div class="mb-6 flex items-center justify-end">
        <a href="{{ route('user.internet.starlink.create') }}"
           class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Permohonan Baru
        </a>
    </div>
    @endif

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-300 bg-green-50 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($requests->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
            </svg>
            <p class="text-gray-600 mb-4">Belum ada permohonan Starlink yang dibuat.</p>
            @if(!$serviceSetting || $serviceSetting->is_active)
            <a href="{{ route('user.internet.starlink.create') }}"
               class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold">
                Ajukan Permohonan Pertama
            </a>
            @endif
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-700 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">No. Tiket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Tanggal Pengajuan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Periode Kegiatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Instansi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($requests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-purple-600">{{ $request->ticket_no }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $request->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $request->tanggal_mulai->format('d/m/Y') }} - {{ $request->tanggal_selesai->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $request->unitKerja->nama ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($request->status === 'menunggu')
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Menunggu
                                </span>
                            @elseif($request->status === 'proses')
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Sedang Diproses
                                </span>
                            @elseif($request->status === 'selesai')
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Disetujui
                                </span>
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('user.internet.starlink.show', $request->id) }}"
                               class="text-purple-600 hover:text-purple-900 font-medium">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
