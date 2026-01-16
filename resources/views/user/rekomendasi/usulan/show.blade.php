@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Detail Usulan Rekomendasi</h1>
                <p class="text-gray-600 mt-1">{{ $proposal->nama_aplikasi }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('user.rekomendasi.usulan.index') }}"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    Kembali
                </a>
                @if(in_array($proposal->status, ['draft', 'perlu_revisi']))
                    <a href="{{ route('user.rekomendasi.usulan.edit', $proposal->id) }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Edit
                    </a>
                @endif
            </div>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="text-lg font-semibold mt-1">
                            @php
                                $statusLabels = [
                                    'draft' => 'Draft',
                                    'diajukan' => 'Diajukan',
                                    'perlu_revisi' => 'Perlu Revisi',
                                    'diproses' => 'Diproses',
                                    'disetujui' => 'Disetujui',
                                    'ditolak' => 'Ditolak',
                                ];
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'diajukan' => 'bg-blue-100 text-blue-800',
                                    'perlu_revisi' => 'bg-orange-100 text-orange-800',
                                    'diproses' => 'bg-yellow-100 text-yellow-800',
                                    'disetujui' => 'bg-green-100 text-green-800',
                                    'ditolak' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-sm {{ $statusColors[$proposal->status] }}">
                                {{ $statusLabels[$proposal->status] }}
                            </span>
                        </p>
                    </div>
                    <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Fase Saat Ini</p>
                        <p class="text-lg font-semibold mt-1">
                            <span class="px-3 py-1 rounded-full text-sm {{ $proposal->fase_badge_color }}">
                                {{ $proposal->fase_saat_ini_display }}
                            </span>
                        </p>
                    </div>
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Nomor Tiket</p>
                        <p class="text-lg font-semibold mt-1">{{ $proposal->ticket_number }}</p>
                    </div>
                    <svg class="h-10 w-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Revision Notice -->
        @if($proposal->status === 'perlu_revisi' && $proposal->verifikasi)
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <svg class="h-5 w-5 text-orange-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-orange-800">Usulan Perlu Revisi</h3>
                        <p class="text-sm text-orange-700 mt-1">{{ $proposal->verifikasi->catatan_verifikasi }}</p>
                        <p class="text-xs text-orange-600 mt-2">
                            Diverifikasi oleh {{ $proposal->verifikasi->verifikator->name }} pada {{ $proposal->verifikasi->tanggal_verifikasi->format('d M Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content Tabs -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button onclick="showTab('informasi')" id="tab-informasi"
                        class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                        Informasi Dasar
                    </button>
                    <button onclick="showTab('dokumen')" id="tab-dokumen"
                        class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Dokumen
                    </button>
                    <button onclick="showTab('timeline')" id="tab-timeline"
                        class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Timeline
                    </button>
                    <button onclick="showTab('analisis')" id="tab-analisis"
                        class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Analisis Kebutuhan
                    </button>
                    <button onclick="showTab('perencanaan')" id="tab-perencanaan"
                        class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Perencanaan
                    </button>
                    <button onclick="showTab('manajemen-risiko')" id="tab-manajemen-risiko"
                        class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Manajemen Risiko
                    </button>
                </nav>
            </div>

            <!-- Tab: Informasi Dasar -->
            <div id="content-informasi" class="tab-content p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Nama Aplikasi</h3>
                        <p class="text-gray-900">{{ $proposal->nama_aplikasi }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Prioritas</h3>
                        <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $proposal->prioritas)) }}</p>
                    </div>

                    <div class="col-span-2">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Deskripsi</h3>
                        <div class="text-gray-900 prose max-w-none">{!! $proposal->deskripsi !!}</div>
                    </div>

                    <div class="col-span-2">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Tujuan</h3>
                        <div class="text-gray-900 prose max-w-none">{!! $proposal->tujuan !!}</div>
                    </div>

                    <div class="col-span-2">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Manfaat</h3>
                        <div class="text-gray-900 prose max-w-none">{!! $proposal->manfaat !!}</div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Pemilik Proses Bisnis</h3>
                        <p class="text-gray-900">{{ $proposal->pemilikProsesBisnis?->nama ?? '-' }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Jenis Layanan</h3>
                        <p class="text-gray-900">{{ ucfirst($proposal->jenis_layanan) }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Target Pengguna</h3>
                        <p class="text-gray-900">{{ $proposal->target_pengguna }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Estimasi Pengguna</h3>
                        <p class="text-gray-900">{{ number_format($proposal->estimasi_pengguna, 0, ',', '.') }} pengguna</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Lingkup Aplikasi</h3>
                        <p class="text-gray-900">{{ ucfirst($proposal->lingkup_aplikasi) }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Platform</h3>
                        <p class="text-gray-900">
                            @if($proposal->platform)
                                @foreach($proposal->platform as $platform)
                                    <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm mr-1">
                                        {{ ucfirst($platform) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </p>
                    </div>

                    @if($proposal->teknologi_diusulkan)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Teknologi yang Diusulkan</h3>
                            <p class="text-gray-900">{{ $proposal->teknologi_diusulkan }}</p>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Estimasi Waktu Pengembangan</h3>
                        <p class="text-gray-900">{{ $proposal->estimasi_waktu_pengembangan }} bulan</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Estimasi Biaya</h3>
                        <p class="text-gray-900">Rp {{ number_format($proposal->estimasi_biaya, 0, ',', '.') }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Sumber Pendanaan</h3>
                        <p class="text-gray-900">{{ strtoupper($proposal->sumber_pendanaan) }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Integrasi dengan Sistem Lain</h3>
                        <p class="text-gray-900">{{ $proposal->integrasi_sistem_lain === 'ya' ? 'Ya' : 'Tidak' }}</p>
                    </div>

                    @if($proposal->integrasi_sistem_lain === 'ya' && $proposal->detail_integrasi)
                        <div class="col-span-2">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Detail Integrasi</h3>
                            <p class="text-gray-900">{{ $proposal->detail_integrasi }}</p>
                        </div>
                    @endif

                    @if($proposal->kebutuhan_khusus)
                        <div class="col-span-2">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Kebutuhan Khusus</h3>
                            <p class="text-gray-900">{{ $proposal->kebutuhan_khusus }}</p>
                        </div>
                    @endif

                    @if($proposal->dampak_tidak_dibangun)
                        <div class="col-span-2">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Dampak Jika Tidak Dibangun</h3>
                            <p class="text-gray-900">{{ $proposal->dampak_tidak_dibangun }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tab: Dokumen -->
            <div id="content-dokumen" class="tab-content p-6 hidden">
                <div class="space-y-4">
                    @forelse($proposal->dokumenUsulan as $dokumen)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <svg class="h-10 w-10 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ $dokumen->jenis_dokumen_display }}</h4>
                                        <p class="text-sm text-gray-500">Versi {{ $dokumen->versi }} - {{ $dokumen->human_file_size }}</p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            Diupload oleh {{ $dokumen->uploader->name }} pada {{ $dokumen->created_at->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <a href="{{ route('user.rekomendasi.usulan.dokumen.download', [$proposal->id, $dokumen->id]) }}"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Belum ada dokumen yang diupload</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Tab: Timeline -->
            <div id="content-timeline" class="tab-content p-6 hidden">
                <div class="space-y-4">
                    @forelse($proposal->historiAktivitas->sortByDesc('created_at') as $histori)
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-gray-900">{{ $histori->aktivitas }}</h4>
                                            @if($histori->deskripsi)
                                                <p class="text-sm text-gray-600 mt-1">{{ $histori->deskripsi }}</p>
                                            @endif
                                            <div class="flex items-center mt-2 text-xs text-gray-500 space-x-4">
                                                <span>{{ $histori->user->name }}</span>
                                                <span>•</span>
                                                <span>{{ $histori->created_at->format('d M Y H:i') }}</span>
                                                <span>•</span>
                                                <span>{{ $histori->ip_address }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Belum ada aktivitas</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Tab: Analisis Kebutuhan (Permenkomdigi No. 6 Tahun 2025) -->
            <div id="content-analisis" class="tab-content p-6 hidden">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Analisis Kebutuhan (Permenkomdigi No. 6 Tahun 2025)</h2>
                    <p class="text-sm text-gray-600 mt-1">Informasi analisis kebutuhan sesuai regulasi Permenkomdigi No. 6 Tahun 2025</p>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Dasar Hukum</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->dasar_hukum)
                                {!! $proposal->dasar_hukum !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Uraian Permasalahan</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->uraian_permasalahan)
                                {!! $proposal->uraian_permasalahan !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Pihak Terkait</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->pihak_terkait)
                                {!! $proposal->pihak_terkait !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Ruang Lingkup</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->ruang_lingkup)
                                {!! $proposal->ruang_lingkup !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Analisis Biaya Manfaat</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->analisis_biaya_manfaat)
                                {!! $proposal->analisis_biaya_manfaat !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Lokasi Implementasi</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->lokasi_implementasi)
                                {!! $proposal->lokasi_implementasi !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Perencanaan (Permenkomdigi No. 6 Tahun 2025) -->
            <div id="content-perencanaan" class="tab-content p-6 hidden">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Perencanaan (Permenkomdigi No. 6 Tahun 2025)</h2>
                    <p class="text-sm text-gray-600 mt-1">Informasi perencanaan sesuai regulasi Permenkomdigi No. 6 Tahun 2025</p>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Uraian Ruang Lingkup</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->uraian_ruang_lingkup)
                                {!! $proposal->uraian_ruang_lingkup !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Proses Bisnis</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->proses_bisnis)
                                {!! $proposal->proses_bisnis !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>

                        @if($proposal->proses_bisnis_file)
                            <div class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-blue-900">Diagram Proses Bisnis</p>
                                            <p class="text-xs text-blue-700">{{ basename($proposal->proses_bisnis_file) }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ Storage::url($proposal->proses_bisnis_file) }}" target="_blank"
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Kerangka Kerja</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->kerangka_kerja)
                                {!! $proposal->kerangka_kerja !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Pelaksana Pembangunan</h3>
                        <p class="text-gray-900">
                            @if($proposal->pelaksana_pembangunan)
                                @php
                                    $pelaksanaLabels = [
                                        'menteri' => 'Menteri',
                                        'swakelola' => 'Swakelola',
                                        'pihak_ketiga' => 'Pihak Ketiga',
                                    ];
                                @endphp
                                {{ $pelaksanaLabels[$proposal->pelaksana_pembangunan] ?? ucfirst(str_replace('_', ' ', $proposal->pelaksana_pembangunan)) }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Peran Tanggung Jawab</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->peran_tanggung_jawab)
                                {!! $proposal->peran_tanggung_jawab !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Jadwal Pelaksanaan</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->jadwal_pelaksanaan)
                                {!! $proposal->jadwal_pelaksanaan !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Rencana Aksi</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->rencana_aksi)
                                {!! $proposal->rencana_aksi !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Keamanan Informasi</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->keamanan_informasi)
                                {!! $proposal->keamanan_informasi !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Sumber Daya Manusia</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->sumber_daya_manusia)
                                {!! $proposal->sumber_daya_manusia !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Sumber Daya Anggaran</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->sumber_daya_anggaran)
                                {!! $proposal->sumber_daya_anggaran !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Sumber Daya Sarana</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->sumber_daya_sarana)
                                {!! $proposal->sumber_daya_sarana !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Indikator Keberhasilan</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->indikator_keberhasilan)
                                {!! $proposal->indikator_keberhasilan !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Alih Pengetahuan</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->alih_pengetahuan)
                                {!! $proposal->alih_pengetahuan !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Pemantauan Pelaporan</h3>
                        <div class="text-gray-900 prose max-w-none">
                            @if($proposal->pemantauan_pelaporan)
                                {!! $proposal->pemantauan_pelaporan !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Manajemen Risiko (Permenkomdigi No. 6 Tahun 2025) -->
            <div id="content-manajemen-risiko" class="tab-content p-6 hidden">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800">Manajemen Risiko (Permenkomdigi No. 6 Tahun 2025)</h2>
                    <p class="text-sm text-gray-600 mt-1">Identifikasi dan mitigasi risiko sesuai regulasi Permenkomdigi No. 6 Tahun 2025</p>
                </div>

                <div class="space-y-4">
                    @forelse($proposal->risiko_items ?? [] as $index => $risiko)
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <div class="flex items-start mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg flex items-center justify-center font-bold text-lg shadow-md">
                                            {{ $index + 1 }}
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $risiko['jenis'] ?? $risiko['nama'] ?? 'Risiko ' . ($index + 1) }}</h3>
                                        @if(isset($risiko['kategori']) && $risiko['kategori'])
                                            <p class="text-sm text-gray-500 mt-1">Kategori: {{ $risiko['kategori'] }}</p>
                                        @endif
                                    </div>
                                    <div class="flex-shrink-0 ml-4">
                                        @php
                                            $tingkat = $risiko['tingkat'] ?? '';
                                            $tingkatColors = [
                                                'rendah' => 'bg-green-100 text-green-800 border-green-200',
                                                'sedang' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'tinggi' => 'bg-orange-100 text-orange-800 border-orange-200',
                                                'sangat_tinggi' => 'bg-red-100 text-red-800 border-red-200',
                                            ];
                                            $tingkatLabels = [
                                                'rendah' => 'Rendah',
                                                'sedang' => 'Sedang',
                                                'tinggi' => 'Tinggi',
                                                'sangat_tinggi' => 'Sangat Tinggi',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold border {{ $tingkatColors[$tingkat] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $tingkatLabels[$tingkat] ?? ucfirst($tingkat) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    @if(isset($risiko['deskripsi']) && $risiko['deskripsi'])
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Deskripsi Risiko
                                            </h4>
                                            <p class="text-sm text-gray-700 leading-relaxed">{{ $risiko['deskripsi'] }}</p>
                                        </div>
                                    @endif

                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <h4 class="text-sm font-semibold text-blue-900 mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            Strategi Mitigasi
                                        </h4>
                                        <p class="text-sm text-blue-900 leading-relaxed">{{ $risiko['mitigasi'] ?? '-' }}</p>
                                    </div>

                                    @if(isset($risiko['penanggung_jawab']) && $risiko['penanggung_jawab'])
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span class="font-medium text-gray-700">Penanggung Jawab:</span>
                                            <span class="ml-2">{{ $risiko['penanggung_jawab'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">Tidak ada risiko teridentifikasi</h3>
                            <p class="text-sm text-gray-500">Belum ada data manajemen risiko untuk usulan ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });

    // Show selected tab content
    document.getElementById(`content-${tabName}`).classList.remove('hidden');

    // Add active state to selected tab
    const activeTab = document.getElementById(`tab-${tabName}`);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-500', 'text-blue-600');
}
</script>
@endpush
@endsection
