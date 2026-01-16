@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Verifikasi Usulan Rekomendasi Aplikasi</h1>
            <p class="text-gray-600 mt-1">Kelola dan verifikasi usulan rekomendasi aplikasi dari instansi</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Menunggu Verifikasi</p>
                        <p class="text-2xl font-bold text-blue-800 mt-1">
                            {{ $proposals->where('verifikasi.status', 'menunggu')->count() }}
                        </p>
                    </div>
                    <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-yellow-600 font-medium">Sedang Diverifikasi</p>
                        <p class="text-2xl font-bold text-yellow-800 mt-1">
                            {{ $proposals->where('verifikasi.status', 'sedang_diverifikasi')->count() }}
                        </p>
                    </div>
                    <svg class="h-10 w-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium">Disetujui</p>
                        <p class="text-2xl font-bold text-green-800 mt-1">
                            {{ $proposals->where('verifikasi.status', 'disetujui')->count() }}
                        </p>
                    </div>
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-orange-600 font-medium">Perlu Revisi</p>
                        <p class="text-2xl font-bold text-orange-800 mt-1">
                            {{ $proposals->where('status', 'perlu_revisi')->count() }}
                        </p>
                    </div>
                    <svg class="h-10 w-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <form method="GET" action="{{ route('admin.rekomendasi.verifikasi.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nama aplikasi atau nomor tiket"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Usulan</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="diajukan" {{ request('status') === 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="diproses" {{ request('status') === 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="perlu_revisi" {{ request('status') === 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                        <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Verifikasi</label>
                    <select name="verification_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="menunggu" {{ request('verification_status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="sedang_diverifikasi" {{ request('verification_status') === 'sedang_diverifikasi' ? 'selected' : '' }}>Sedang Diverifikasi</option>
                        <option value="disetujui" {{ request('verification_status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('verification_status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="perlu_revisi" {{ request('verification_status') === 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Proposals List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            @if($proposals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Usulan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pemohon
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Verifikasi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dokumen
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($proposals as $proposal)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $proposal->nama_aplikasi }}</p>
                                            <p class="text-xs text-gray-500">{{ $proposal->ticket_number }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ $proposal->created_at->format('d M Y H:i') }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm text-gray-900">{{ $proposal->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $proposal->pemilikProsesBisnis?->nama ?? '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'diajukan' => 'bg-blue-100 text-blue-800',
                                                'diproses' => 'bg-yellow-100 text-yellow-800',
                                                'perlu_revisi' => 'bg-orange-100 text-orange-800',
                                                'disetujui' => 'bg-green-100 text-green-800',
                                                'ditolak' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$proposal->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $proposal->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($proposal->verifikasi)
                                            @php
                                                $verifikasiColors = [
                                                    'menunggu' => 'bg-gray-100 text-gray-800',
                                                    'sedang_diverifikasi' => 'bg-yellow-100 text-yellow-800',
                                                    'disetujui' => 'bg-green-100 text-green-800',
                                                    'ditolak' => 'bg-red-100 text-red-800',
                                                    'perlu_revisi' => 'bg-orange-100 text-orange-800',
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $verifikasiColors[$proposal->verifikasi->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $proposal->verifikasi->status_display }}
                                            </span>
                                            @if($proposal->verifikasi->verifikator)
                                                <p class="text-xs text-gray-500 mt-1">{{ $proposal->verifikasi->verifikator->name }}</p>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-gray-600">
                                            {{ $proposal->dokumenUsulan->count() }} / 3 dokumen
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.rekomendasi.verifikasi.show', $proposal->id) }}"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                Detail
                                            </a>
                                            @if($proposal->verifikasi && $proposal->verifikasi->status === 'menunggu')
                                                <form method="POST" action="{{ route('admin.rekomendasi.verifikasi.start', $proposal->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                        Mulai Verifikasi
                                                    </button>
                                                </form>
                                            @elseif($proposal->verifikasi && $proposal->verifikasi->status === 'sedang_diverifikasi')
                                                <a href="{{ route('admin.rekomendasi.verifikasi.verify', $proposal->id) }}"
                                                    class="text-yellow-600 hover:text-yellow-800 text-sm font-medium">
                                                    Lanjutkan
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $proposals->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada usulan</h3>
                    <p class="mt-1 text-sm text-gray-500">Belum ada usulan yang perlu diverifikasi.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
