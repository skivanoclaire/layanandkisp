@extends('layouts.authenticated')

@section('title', '- Detail Subdomain')
@section('header-title', 'Detail Subdomain')

@section('content')
<div class="max-w-6xl mx-auto py-6 px-4">
    <div class="mb-4">
        <a href="{{ route('admin.web-monitor.index') }}" class="text-blue-600 hover:underline">
            &larr; Kembali ke Master Data Subdomain
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">{{ $webMonitor->subdomain ?: 'IP: ' . $webMonitor->ip_address }}</h1>
                    <p class="text-green-100">{{ $webMonitor->nama_sistem ?: '-' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.web-monitor.edit', $webMonitor) }}"
                       class="bg-white text-green-700 px-4 py-2 rounded hover:bg-green-50 font-semibold">
                        Edit
                    </a>
                    <form action="{{ route('admin.web-monitor.check-status', $webMonitor) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 font-semibold">
                            Cek Status
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="p-6">
            {{-- Session Messages --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Left Column --}}
                <div class="space-y-6">
                    {{-- Informasi Dasar --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h2 class="text-lg font-bold mb-3 text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Informasi Dasar
                        </h2>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Instansi:</span>
                                <span class="font-semibold">{{ $webMonitor->instansi->nama ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nama Sistem:</span>
                                <span class="font-semibold">{{ $webMonitor->nama_sistem ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subdomain:</span>
                                @if($webMonitor->subdomain)
                                    <a href="https://{{ $webMonitor->subdomain }}" target="_blank" class="text-blue-600 hover:underline font-semibold">
                                        {{ $webMonitor->subdomain }}
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">IP Address:</span>
                                <span class="font-mono font-semibold">{{ $webMonitor->ip_address ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Jenis:</span>
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                    @if($webMonitor->jenis === 'Website Resmi') bg-blue-100 text-blue-800
                                    @elseif($webMonitor->jenis === 'Aplikasi Layanan Publik') bg-green-100 text-green-800
                                    @elseif($webMonitor->jenis === 'Aplikasi Administrasi Pemerintah') bg-purple-100 text-purple-800
                                    @else bg-orange-100 text-orange-800
                                    @endif">
                                    {{ $webMonitor->jenis ?: '-' }}
                                </span>
                            </div>
                            @if($webMonitor->keterangan)
                            <div class="pt-2 border-t">
                                <span class="text-gray-600">Keterangan:</span>
                                <p class="mt-1 text-gray-800">{{ $webMonitor->keterangan }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Status Monitoring --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h2 class="text-lg font-bold mb-3 text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Status Monitoring
                        </h2>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Status:</span>
                                <div class="flex items-center gap-2">
                                    @if($webMonitor->status === 'active')
                                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                        <span class="text-green-700 font-semibold">Aktif</span>
                                    @else
                                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                        <span class="text-red-700 font-semibold">Tidak Aktif</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Terakhir Dicek:</span>
                                <span class="font-semibold">
                                    @if($webMonitor->last_checked_at)
                                        {{ $webMonitor->last_checked_at->format('d/m/Y H:i') }}
                                        <span class="text-xs text-gray-500">({{ $webMonitor->last_checked_at->diffForHumans() }})</span>
                                    @else
                                        <span class="text-gray-400">Belum pernah dicek</span>
                                    @endif
                                </span>
                            </div>
                            @if($webMonitor->check_error)
                            <div class="pt-2 border-t">
                                <span class="text-gray-600">Error:</span>
                                <p class="mt-1 text-red-600 text-sm">{{ $webMonitor->check_error }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Konfigurasi Cloudflare --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h2 class="text-lg font-bold mb-3 text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13.5 8.5l-4 4m0 0l-4-4m4 4V3"/>
                            </svg>
                            Konfigurasi Cloudflare
                        </h2>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Record ID:</span>
                                <span class="font-mono text-sm">{{ $webMonitor->cloudflare_record_id ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status Proxy:</span>
                                @if($webMonitor->cloudflare_record_id)
                                    @if($webMonitor->is_proxied)
                                        <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-semibold">Proxied</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-semibold">DNS Only</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Kategori Sistem Elektronik (ESC) --}}
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 p-4 rounded-lg border-2 border-indigo-200">
                        <h2 class="text-lg font-bold mb-3 text-indigo-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Kategori Sistem Elektronik (ESC)
                        </h2>
                        @if($webMonitor->esc_category)
                            <div class="space-y-3">
                                {{-- Category Badge --}}
                                <div class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm">
                                    <div>
                                        <p class="text-xs text-gray-600 mb-1">Kategori:</p>
                                        <span class="inline-block px-4 py-2 rounded-lg font-bold text-lg
                                            @if($webMonitor->esc_category === 'Strategis') bg-red-100 text-red-700
                                            @elseif($webMonitor->esc_category === 'Tinggi') bg-orange-100 text-orange-700
                                            @else bg-green-100 text-green-700
                                            @endif">
                                            {{ $webMonitor->esc_category }}
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-600 mb-1">Total Skor:</p>
                                        <p class="text-2xl font-bold text-indigo-700">{{ $webMonitor->esc_total_score }}<span class="text-sm text-gray-500">/50</span></p>
                                    </div>
                                </div>

                                {{-- ESC Details --}}
                                <div class="bg-white p-3 rounded-lg">
                                    <p class="text-xs font-semibold text-gray-700 mb-2">Detail Jawaban Kuesioner:</p>
                                    <div class="space-y-1 text-xs">
                                        @php
                                            $questions = [
                                                '1_1' => 'Nilai investasi sistem elektronik',
                                                '1_2' => 'Total anggaran operasional tahunan',
                                                '1_3' => 'Kewajiban kepatuhan peraturan perundang-undangan',
                                                '1_4' => 'Teknik kriptografi khusus yang digunakan',
                                                '1_5' => 'Jumlah pengguna sistem elektronik',
                                                '1_6' => 'Data pribadi yang dikelola',
                                                '1_7' => 'Tingkat klasifikasi data',
                                                '1_8' => 'Tingkat kekritisan proses bisnis',
                                                '1_9' => 'Dampak kegagalan sistem',
                                                '1_10' => 'Potensi kerugian akibat insiden siber',
                                            ];
                                            $answerScores = ['A' => 5, 'B' => 2, 'C' => 1];
                                        @endphp
                                        @foreach($questions as $qId => $qText)
                                            @if(isset($webMonitor->esc_answers[$qId]))
                                                <div class="flex justify-between py-1 border-b border-gray-100">
                                                    <span class="text-gray-600">{{ $qId }}. {{ $qText }}:</span>
                                                    <span class="font-semibold
                                                        @if($webMonitor->esc_answers[$qId] === 'A') text-red-600
                                                        @elseif($webMonitor->esc_answers[$qId] === 'B') text-orange-600
                                                        @else text-green-600
                                                        @endif">
                                                        {{ $webMonitor->esc_answers[$qId] }}
                                                        <span class="text-gray-500">({{ $answerScores[$webMonitor->esc_answers[$qId]] }} poin)</span>
                                                    </span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Document --}}
                                @if($webMonitor->esc_document_path)
                                    <div class="bg-white p-3 rounded-lg">
                                        <p class="text-xs font-semibold text-gray-700 mb-2">Dokumen Pendukung:</p>
                                        <a href="{{ asset('storage/' . $webMonitor->esc_document_path) }}"
                                           target="_blank"
                                           class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            {{ basename($webMonitor->esc_document_path) }}
                                        </a>
                                    </div>
                                @endif

                                {{-- Timestamp --}}
                                <div class="text-xs text-gray-600 bg-white p-2 rounded">
                                    <span class="font-semibold">Terakhir diisi:</span> {{ $webMonitor->esc_filled_at->format('d/m/Y H:i') }}
                                    @if($webMonitor->esc_updated_by)
                                        oleh {{ $webMonitor->escUpdatedBy->name ?? 'Admin' }}
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <p class="text-sm text-yellow-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Kuesioner ESC belum diisi
                                </p>
                                <p class="text-xs text-yellow-700 mt-1 ml-7">
                                    Silakan <a href="{{ route('admin.web-monitor.edit', $webMonitor) }}" class="underline font-semibold">edit data</a> untuk mengisi kuesioner Kategori Sistem Elektronik.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Klasifikasi Data --}}
                    <div class="bg-gradient-to-br from-cyan-50 to-blue-50 p-4 rounded-lg border-2 border-cyan-200">
                        <h2 class="text-lg font-bold mb-3 text-cyan-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            Klasifikasi Data
                        </h2>
                        @if($webMonitor->dc_data_name)
                            <div class="space-y-3">
                                {{-- Data Info --}}
                                <div class="bg-white p-3 rounded-lg shadow-sm">
                                    <div class="grid grid-cols-1 gap-2 text-sm">
                                        <div>
                                            <p class="text-xs text-gray-600">Nama Data:</p>
                                            <p class="font-semibold text-gray-800">{{ $webMonitor->dc_data_name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-600">Atribut Data:</p>
                                            <p class="text-gray-700">{{ $webMonitor->dc_data_attributes }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Scores --}}
                                <div class="bg-white p-3 rounded-lg shadow-sm">
                                    <p class="text-xs font-semibold text-gray-700 mb-2">Area Dampak:</p>
                                    <div class="grid grid-cols-4 gap-2">
                                        <div class="text-center border-r">
                                            <p class="text-xs text-gray-600 mb-1">Kerahasiaan</p>
                                            <p class="text-lg font-bold
                                                @if($webMonitor->dc_confidentiality === 'Tinggi') text-red-600
                                                @elseif($webMonitor->dc_confidentiality === 'Sedang') text-orange-600
                                                @else text-green-600
                                                @endif">
                                                {{ $webMonitor->dc_confidentiality }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                @if($webMonitor->dc_confidentiality === 'Tinggi') (5 poin)
                                                @elseif($webMonitor->dc_confidentiality === 'Sedang') (3 poin)
                                                @else (1 poin)
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-center border-r">
                                            <p class="text-xs text-gray-600 mb-1">Integritas</p>
                                            <p class="text-lg font-bold
                                                @if($webMonitor->dc_integrity === 'Tinggi') text-red-600
                                                @elseif($webMonitor->dc_integrity === 'Sedang') text-orange-600
                                                @else text-green-600
                                                @endif">
                                                {{ $webMonitor->dc_integrity }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                @if($webMonitor->dc_integrity === 'Tinggi') (5 poin)
                                                @elseif($webMonitor->dc_integrity === 'Sedang') (3 poin)
                                                @else (1 poin)
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-center border-r">
                                            <p class="text-xs text-gray-600 mb-1">Ketersediaan</p>
                                            <p class="text-lg font-bold
                                                @if($webMonitor->dc_availability === 'Tinggi') text-red-600
                                                @elseif($webMonitor->dc_availability === 'Sedang') text-orange-600
                                                @else text-green-600
                                                @endif">
                                                {{ $webMonitor->dc_availability }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                @if($webMonitor->dc_availability === 'Tinggi') (5 poin)
                                                @elseif($webMonitor->dc_availability === 'Sedang') (3 poin)
                                                @else (1 poin)
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs text-gray-600 mb-1">Total Skor</p>
                                            <p class="text-2xl font-bold text-cyan-700">{{ $webMonitor->dc_total_score }}</p>
                                            <p class="text-xs text-gray-500">(dari 15)</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Category --}}
                                <div class="bg-white p-3 rounded-lg shadow-sm">
                                    <p class="text-xs font-semibold text-gray-700 mb-2 text-center">Kategori Klasifikasi Data:</p>
                                    <div class="text-center">
                                        @php
                                            $dcCategory = 'Rendah';
                                            $dcCategoryColor = 'bg-green-100 text-green-700';
                                            if ($webMonitor->dc_total_score >= 13) {
                                                $dcCategory = 'Tinggi';
                                                $dcCategoryColor = 'bg-red-100 text-red-700';
                                            } elseif ($webMonitor->dc_total_score >= 9) {
                                                $dcCategory = 'Sedang';
                                                $dcCategoryColor = 'bg-orange-100 text-orange-700';
                                            }
                                        @endphp
                                        <span class="inline-block px-4 py-2 rounded-lg font-bold text-lg {{ $dcCategoryColor }}">
                                            {{ $dcCategory }}
                                        </span>
                                        <p class="text-xs text-gray-500 mt-2">
                                            ≥13: Tinggi | 9-12: Sedang | ≤8: Rendah
                                        </p>
                                    </div>
                                </div>

                                {{-- Timestamp --}}
                                <div class="text-xs text-gray-600 bg-white p-2 rounded">
                                    <span class="font-semibold">Terakhir diisi:</span> {{ $webMonitor->dc_filled_at->format('d/m/Y H:i') }}
                                    @if($webMonitor->dc_updated_by)
                                        oleh {{ $webMonitor->dcUpdatedBy->name ?? 'Admin' }}
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <p class="text-sm text-yellow-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Klasifikasi Data belum diisi
                                </p>
                                <p class="text-xs text-yellow-700 mt-1 ml-7">
                                    Silakan <a href="{{ route('admin.web-monitor.edit', $webMonitor) }}" class="underline font-semibold">edit data</a> untuk mengisi Klasifikasi Data.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="space-y-6">
                    {{-- Informasi Aplikasi --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h2 class="text-lg font-bold mb-3 text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Informasi Aplikasi
                        </h2>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nama Aplikasi:</span>
                                <span class="font-semibold">{{ $webMonitor->nama_aplikasi ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tahun Pembuatan:</span>
                                <span class="font-semibold">{{ $webMonitor->tahun_pembuatan ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Developer:</span>
                                <span class="font-semibold">{{ $webMonitor->developer ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Contact Person:</span>
                                <span class="font-semibold">{{ $webMonitor->contact_person ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">No. HP:</span>
                                <span class="font-semibold">{{ $webMonitor->contact_phone ?: '-' }}</span>
                            </div>
                        </div>

                        @if($webMonitor->description)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <span class="text-gray-600 block mb-2 font-semibold">Deskripsi Website/Aplikasi:</span>
                            <div class="text-gray-800 prose prose-sm max-w-none ck-content">{!! $webMonitor->description !!}</div>
                        </div>
                        @endif

                        @if($webMonitor->latar_belakang)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <span class="text-gray-600 block mb-2 font-semibold">Latar Belakang Pembuatan:</span>
                            <div class="text-gray-800 prose prose-sm max-w-none ck-content">{!! $webMonitor->latar_belakang !!}</div>
                        </div>
                        @endif

                        @if($webMonitor->manfaat_aplikasi)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <span class="text-gray-600 block mb-2 font-semibold">Manfaat Aplikasi:</span>
                            <div class="text-gray-800 prose prose-sm max-w-none ck-content">{!! $webMonitor->manfaat_aplikasi !!}</div>
                        </div>
                        @endif
                    </div>

                    {{-- Teknologi yang Digunakan --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h2 class="text-lg font-bold mb-3 text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                            Teknologi yang Digunakan
                        </h2>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Bahasa Pemrograman:</span>
                                <span class="font-semibold">
                                    {{ $webMonitor->programmingLanguage->name ?? '-' }}
                                    @if($webMonitor->programming_language_version)
                                        <span class="text-gray-500">({{ $webMonitor->programming_language_version }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Framework:</span>
                                <span class="font-semibold">
                                    {{ $webMonitor->framework->name ?? '-' }}
                                    @if($webMonitor->framework_version)
                                        <span class="text-gray-500">({{ $webMonitor->framework_version }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database:</span>
                                <span class="font-semibold">
                                    {{ $webMonitor->database->name ?? '-' }}
                                    @if($webMonitor->database_version)
                                        <span class="text-gray-500">({{ $webMonitor->database_version }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Frontend:</span>
                                <span class="font-semibold">{{ $webMonitor->frontend_tech ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Server --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h2 class="text-lg font-bold mb-3 text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                            </svg>
                            Server
                        </h2>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Kepemilikan:</span>
                                <span class="font-semibold">{{ $webMonitor->server_ownership ?: '-' }}</span>
                            </div>
                            @if($webMonitor->server_ownership === 'Pihak Ketiga' && $webMonitor->server_owner_name)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nama Pemilik:</span>
                                <span class="font-semibold">{{ $webMonitor->server_owner_name }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Lokasi Server:</span>
                                <span class="font-semibold">{{ $webMonitor->serverLocation->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Link ke Permohonan --}}
                    @if($webMonitor->subdomain_request_id)
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h2 class="text-lg font-bold mb-3 text-blue-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            Permohonan Terkait
                        </h2>
                        <p class="text-sm text-gray-600 mb-2">Subdomain ini dibuat dari permohonan:</p>
                        <a href="{{ route('admin.subdomain.show', $webMonitor->subdomain_request_id) }}"
                           class="inline-flex items-center text-blue-600 hover:underline font-semibold">
                            Lihat Detail Permohonan
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="mt-6 pt-4 border-t flex justify-between items-center">
                <span class="text-sm text-gray-500">
                    Dibuat: {{ $webMonitor->created_at->format('d/m/Y H:i') }}
                    @if($webMonitor->updated_at != $webMonitor->created_at)
                        | Diperbarui: {{ $webMonitor->updated_at->format('d/m/Y H:i') }}
                    @endif
                </span>
                <div class="flex gap-2">
                    <a href="{{ route('admin.web-monitor.edit', $webMonitor) }}"
                       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-semibold">
                        Edit Data
                    </a>
                    <form action="{{ route('admin.web-monitor.destroy', $webMonitor) }}"
                          method="POST"
                          class="inline"
                          onsubmit="return confirm('Yakin ingin menghapus {{ $webMonitor->subdomain ?: $webMonitor->nama_sistem }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 font-semibold">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    /* CKEditor Content Styling */
    .ck-content h1 { font-size: 1.5em; font-weight: bold; margin: 0.5em 0; }
    .ck-content h2 { font-size: 1.3em; font-weight: bold; margin: 0.5em 0; }
    .ck-content h3 { font-size: 1.1em; font-weight: bold; margin: 0.5em 0; }
    .ck-content p { margin: 0.5em 0; line-height: 1.6; }
    .ck-content ul, .ck-content ol { margin: 0.5em 0; padding-left: 2em; }
    .ck-content li { margin: 0.25em 0; }
    .ck-content strong { font-weight: 600; }
    .ck-content em { font-style: italic; }
    .ck-content u { text-decoration: underline; }
    .ck-content a { color: #2563eb; text-decoration: underline; }
</style>
@endpush

@endsection
