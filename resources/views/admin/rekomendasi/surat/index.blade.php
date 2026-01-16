@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Surat Rekomendasi</h1>
            <p class="text-gray-600 mt-1">Kelola surat rekomendasi untuk usulan yang telah disetujui</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-yellow-600 font-medium">Belum Dibuat Surat</p>
                        <p class="text-2xl font-bold text-yellow-800 mt-1">
                            {{ $proposals->where('surat', null)->count() }}
                        </p>
                    </div>
                    <svg class="h-10 w-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Draft Surat</p>
                        <p class="text-2xl font-bold text-blue-800 mt-1">
                            {{ $proposals->where('surat')->filter(fn($p) => $p->surat && !$p->surat->isSigned())->count() }}
                        </p>
                    </div>
                    <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium">Sudah Ditandatangani</p>
                        <p class="text-2xl font-bold text-green-800 mt-1">
                            {{ $proposals->where('surat')->filter(fn($p) => $p->surat && $p->surat->isSigned())->count() }}
                        </p>
                    </div>
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium">Sudah Dikirim</p>
                        <p class="text-2xl font-bold text-purple-800 mt-1">
                            {{ $proposals->where('surat')->filter(fn($p) => $p->surat && $p->surat->isSent())->count() }}
                        </p>
                    </div>
                    <svg class="h-10 w-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <form method="GET" action="{{ route('admin.rekomendasi.surat.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nama aplikasi atau nomor tiket"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Surat</label>
                    <select name="surat_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="belum_dibuat" {{ request('surat_status') === 'belum_dibuat' ? 'selected' : '' }}>Belum Dibuat</option>
                        <option value="draft" {{ request('surat_status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="signed" {{ request('surat_status') === 'signed' ? 'selected' : '' }}>Sudah Ditandatangani</option>
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

        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4 mb-6">
                {{ session('info') }}
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
                                    Status Surat
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status Kementerian
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
                                            <p class="text-sm font-medium text-gray-900">{{ $proposal->judul_aplikasi }}</p>
                                            <p class="text-xs text-gray-500">{{ $proposal->ticket_number }}</p>
                                            <p class="text-xs text-gray-400 mt-1">Disetujui: {{ $proposal->verifikasi?->tanggal_verifikasi?->format('d M Y') ?? '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm text-gray-900">{{ $proposal->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $proposal->pemilikProsesBisnis?->nama ?? '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if(!$proposal->surat)
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Belum Dibuat
                                            </span>
                                        @elseif(!$proposal->surat->isSigned())
                                            <div>
                                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Draft
                                                </span>
                                                <p class="text-xs text-gray-500 mt-1">{{ $proposal->surat->nomor_surat_draft }}</p>
                                            </div>
                                        @else
                                            <div>
                                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Ditandatangani
                                                </span>
                                                <p class="text-xs text-gray-500 mt-1">{{ $proposal->surat->nomor_surat_final }}</p>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($proposal->surat && $proposal->surat->statusKementerian)
                                            @php
                                                $statusColors = [
                                                    'terkirim' => 'bg-blue-100 text-blue-800',
                                                    'diproses' => 'bg-yellow-100 text-yellow-800',
                                                    'disetujui' => 'bg-green-100 text-green-800',
                                                    'ditolak' => 'bg-red-100 text-red-800',
                                                    'perlu_revisi' => 'bg-orange-100 text-orange-800',
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$proposal->surat->statusKementerian->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst(str_replace('_', ' ', $proposal->surat->statusKementerian->status)) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            @if(!$proposal->surat)
                                                <a href="{{ route('admin.rekomendasi.surat.create', $proposal->id) }}"
                                                    class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                    Buat Surat
                                                </a>
                                            @else
                                                <a href="{{ route('admin.rekomendasi.surat.show', $proposal->surat->id) }}"
                                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                    Detail
                                                </a>
                                                @if(!$proposal->surat->isSigned())
                                                    <a href="{{ route('admin.rekomendasi.surat.edit', $proposal->surat->id) }}"
                                                        class="text-yellow-600 hover:text-yellow-800 text-sm font-medium">
                                                        Edit
                                                    </a>
                                                @endif
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
                    <p class="mt-1 text-sm text-gray-500">Belum ada usulan yang disetujui dan siap untuk dibuatkan surat.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
