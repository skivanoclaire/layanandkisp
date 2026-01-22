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

                    @if($proposal->status === 'draft')
                        <form action="{{ route('user.rekomendasi.usulan.submit', $proposal->id) }}"
                              method="POST"
                              class="inline"
                              onsubmit="return confirm('Yakin ingin mengajukan usulan ini? Usulan yang sudah diajukan tidak dapat diubah kembali.')">
                            @csrf
                            <button type="submit"
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Ajukan Usulan
                            </button>
                        </form>
                    @elseif($proposal->status === 'perlu_revisi')
                        <form action="{{ route('user.rekomendasi.usulan.submit', $proposal->id) }}"
                              method="POST"
                              class="inline"
                              onsubmit="return confirm('Yakin ingin mengajukan ulang usulan ini setelah melakukan revisi?')">
                            @csrf
                            <button type="submit"
                                    class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Ajukan Ulang
                            </button>
                        </form>
                    @endif
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

        <!-- Status Persetujuan Kementerian -->
        @php
            // Support both direct relation and legacy relation via surat
            $statusKementerian = $proposal->statusKementerian ?? ($proposal->surat?->statusKementerian);
        @endphp
        @if(in_array($proposal->fase_saat_ini, ['menunggu_kementerian', 'pengembangan', 'selesai']) && $statusKementerian)
            @php
                $statusKementerianColors = [
                    'terkirim' => 'bg-blue-50 border-blue-200',
                    'menunggu' => 'bg-yellow-50 border-yellow-200',
                    'diproses' => 'bg-yellow-50 border-yellow-200',
                    'disetujui' => 'bg-green-50 border-green-200',
                    'ditolak' => 'bg-red-50 border-red-200',
                    'revisi_diminta' => 'bg-orange-50 border-orange-200',
                ];
                $statusKementerianBadgeColors = [
                    'terkirim' => 'bg-blue-100 text-blue-800',
                    'menunggu' => 'bg-yellow-100 text-yellow-800',
                    'diproses' => 'bg-yellow-100 text-yellow-800',
                    'disetujui' => 'bg-green-100 text-green-800',
                    'ditolak' => 'bg-red-100 text-red-800',
                    'revisi_diminta' => 'bg-orange-100 text-orange-800',
                ];
                $statusKementerianLabels = [
                    'terkirim' => 'Surat Terkirim ke Kementerian',
                    'menunggu' => 'Menunggu Respons Kementerian',
                    'diproses' => 'Sedang Diproses Kementerian',
                    'disetujui' => 'Disetujui Kementerian Komunikasi dan Digital RI',
                    'ditolak' => 'Ditolak oleh Kementerian',
                    'revisi_diminta' => 'Kementerian Meminta Revisi',
                ];
                $statusKementerianIcons = [
                    'terkirim' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                    'menunggu' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'diproses' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                    'disetujui' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'ditolak' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'revisi_diminta' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                ];
                $currentStatus = $statusKementerian->status;
            @endphp
            <div class="rounded-lg border p-6 mb-6 {{ $statusKementerianColors[$currentStatus] ?? 'bg-gray-50 border-gray-200' }}">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $currentStatus === 'disetujui' ? 'bg-green-100' : ($currentStatus === 'ditolak' ? 'bg-red-100' : 'bg-blue-100') }}">
                            <svg class="h-6 w-6 {{ $currentStatus === 'disetujui' ? 'text-green-600' : ($currentStatus === 'ditolak' ? 'text-red-600' : 'text-blue-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusKementerianIcons[$currentStatus] ?? $statusKementerianIcons['menunggu'] }}" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Status Persetujuan Kementerian</h3>
                                <span class="inline-flex items-center mt-2 px-3 py-1 rounded-full text-sm font-medium {{ $statusKementerianBadgeColors[$currentStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusKementerianLabels[$currentStatus] ?? ucfirst(str_replace('_', ' ', $currentStatus)) }}
                                </span>
                            </div>
                        </div>

                        @if($statusKementerian->tanggal_surat_respons || $statusKementerian->tanggal_diterima)
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                @if($statusKementerian->nomor_surat_respons)
                                    <div>
                                        <span class="text-gray-500">Nomor Surat:</span>
                                        <span class="ml-1 text-gray-900 font-medium">{{ $statusKementerian->nomor_surat_respons }}</span>
                                    </div>
                                @endif
                                @if($statusKementerian->tanggal_surat_respons)
                                    <div>
                                        <span class="text-gray-500">Tanggal Surat:</span>
                                        <span class="ml-1 text-gray-900 font-medium">{{ $statusKementerian->tanggal_surat_respons->format('d F Y') }}</span>
                                    </div>
                                @endif
                                @if($statusKementerian->tanggal_diterima)
                                    <div>
                                        <span class="text-gray-500">Tanggal Diterima:</span>
                                        <span class="ml-1 text-gray-900 font-medium">{{ $statusKementerian->tanggal_diterima->format('d F Y') }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($currentStatus === 'ditolak' && $statusKementerian->alasan_ditolak)
                            <div class="mt-3 p-3 bg-red-100 rounded-lg">
                                <p class="text-sm font-medium text-red-800">Alasan Penolakan:</p>
                                <p class="text-sm text-red-700 mt-1">{{ $statusKementerian->alasan_ditolak }}</p>
                            </div>
                        @endif

                        @if($currentStatus === 'revisi_diminta' && !empty($statusKementerian->catatan_revisi))
                            <div class="mt-3 p-3 bg-orange-100 rounded-lg">
                                <p class="text-sm font-medium text-orange-800">Catatan Revisi dari Kementerian:</p>
                                <ul class="mt-1 list-disc list-inside text-sm text-orange-700">
                                    @foreach($statusKementerian->catatan_revisi as $catatan)
                                        <li>{{ $catatan }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Download Surat Persetujuan Kementerian --}}
                        @if($statusKementerian->file_respons_path)
                            <div class="mt-4 p-4 bg-white border border-gray-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">
                                                @if($currentStatus === 'disetujui')
                                                    Surat Persetujuan Kementerian Komdigi
                                                @else
                                                    Surat Respons Kementerian Komdigi
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">Dokumen PDF</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('user.rekomendasi.usulan.download-surat-kementerian', $proposal->id) }}"
                                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition shadow-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Download Surat
                                    </a>
                                </div>
                            </div>
                        @endif
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
                        <div class="text-gray-900 prose prose-sm max-w-none">{!! $proposal->deskripsi !!}</div>
                    </div>

                    <div class="col-span-2">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Tujuan</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">{!! $proposal->tujuan !!}</div>
                    </div>

                    <div class="col-span-2">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Manfaat</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">{!! $proposal->manfaat !!}</div>
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
                            <div class="text-gray-900 prose prose-sm max-w-none">{!! $proposal->detail_integrasi !!}</div>
                        </div>
                    @endif

                    @if($proposal->kebutuhan_khusus)
                        <div class="col-span-2">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Kebutuhan Khusus</h3>
                            <div class="text-gray-900 prose prose-sm max-w-none">{!! $proposal->kebutuhan_khusus !!}</div>
                        </div>
                    @endif

                    @if($proposal->dampak_tidak_dibangun)
                        <div class="col-span-2">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Dampak Jika Tidak Dibangun</h3>
                            <div class="text-gray-900 prose prose-sm max-w-none">{!! $proposal->dampak_tidak_dibangun !!}</div>
                        </div>
                    @endif
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
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->dasar_hukum)
                                {!! $proposal->dasar_hukum !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Uraian Permasalahan</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->uraian_permasalahan)
                                {!! $proposal->uraian_permasalahan !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Pihak Terkait</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->pihak_terkait)
                                {!! $proposal->pihak_terkait !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Ruang Lingkup</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->ruang_lingkup)
                                {!! $proposal->ruang_lingkup !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Analisis Biaya Manfaat</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->analisis_biaya_manfaat)
                                {!! $proposal->analisis_biaya_manfaat !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Lokasi Implementasi</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
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
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->uraian_ruang_lingkup)
                                {!! $proposal->uraian_ruang_lingkup !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Proses Bisnis</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
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
                                    <a href="{{ route('file.download', $proposal->proses_bisnis_file) }}" target="_blank"
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
                        <div class="text-gray-900 prose prose-sm max-w-none">
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
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->peran_tanggung_jawab)
                                {!! $proposal->peran_tanggung_jawab !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Jadwal Pelaksanaan</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->jadwal_pelaksanaan)
                                {!! $proposal->jadwal_pelaksanaan !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Rencana Aksi</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->rencana_aksi)
                                {!! $proposal->rencana_aksi !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Keamanan Informasi</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->keamanan_informasi)
                                {!! $proposal->keamanan_informasi !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Sumber Daya Manusia</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->sumber_daya_manusia)
                                {!! $proposal->sumber_daya_manusia !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Sumber Daya Anggaran</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->sumber_daya_anggaran)
                                {!! $proposal->sumber_daya_anggaran !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Sumber Daya Sarana</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->sumber_daya_sarana)
                                {!! $proposal->sumber_daya_sarana !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Indikator Keberhasilan</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->indikator_keberhasilan)
                                {!! $proposal->indikator_keberhasilan !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Alih Pengetahuan</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
                            @if($proposal->alih_pengetahuan)
                                {!! $proposal->alih_pengetahuan !!}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Pemantauan Pelaporan</h3>
                        <div class="text-gray-900 prose prose-sm max-w-none">
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
                        @php
                            // Get risk level based on besaran_risiko_nilai
                            $besaranNilai = $risiko['besaran_risiko_nilai'] ?? 0;
                            $riskLevelColor = 'bg-gray-100 text-gray-800 border-gray-200';
                            $riskLevelText = 'Tidak Diketahui';

                            if ($besaranNilai >= 15) {
                                $riskLevelColor = 'bg-red-100 text-red-800 border-red-200';
                                $riskLevelText = 'Sangat Tinggi';
                            } elseif ($besaranNilai >= 10) {
                                $riskLevelColor = 'bg-orange-100 text-orange-800 border-orange-200';
                                $riskLevelText = 'Tinggi';
                            } elseif ($besaranNilai >= 5) {
                                $riskLevelColor = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                $riskLevelText = 'Sedang';
                            } elseif ($besaranNilai >= 1) {
                                $riskLevelColor = 'bg-green-100 text-green-800 border-green-200';
                                $riskLevelText = 'Rendah';
                            }

                            // Get jenis risiko text
                            $jenisText = 'Risiko ' . ($index + 1);
                            if (isset($risiko['jenis_risiko'])) {
                                $jenisText = $risiko['jenis_risiko'] === 'positif' ? 'Positif (Peluang)' : 'Negatif (Ancaman)';
                            }
                        @endphp

                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <div class="flex items-start mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg flex items-center justify-center font-bold text-lg shadow-md">
                                            {{ $index + 1 }}
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">Risiko SPBE #{{ $index + 1 }}</h3>
                                        <div class="flex items-center gap-3 mt-2 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-700 border border-blue-200">
                                                <strong class="mr-1">Jenis:</strong> {{ $jenisText }}
                                            </span>
                                            @if(isset($risiko['kategori_risiko']) && $risiko['kategori_risiko'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-purple-50 text-purple-700 border border-purple-200">
                                                    <strong class="mr-1">Kategori:</strong> {{ ucwords(str_replace('_', ' ', $risiko['kategori_risiko'])) }}
                                                </span>
                                            @endif
                                            @if(isset($risiko['area_dampak']) && $risiko['area_dampak'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    <strong class="mr-1">Area Dampak:</strong> {{ ucwords(str_replace('_', ' ', $risiko['area_dampak'])) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 ml-4">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold border {{ $riskLevelColor }}">
                                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $riskLevelText }}
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    @if(isset($risiko['uraian_kejadian']) && $risiko['uraian_kejadian'])
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Uraian Kejadian Risiko
                                            </h4>
                                            <p class="text-sm text-gray-700 leading-relaxed">{!! nl2br(e($risiko['uraian_kejadian'])) !!}</p>
                                        </div>
                                    @endif

                                    @if((isset($risiko['penyebab']) && $risiko['penyebab']) || (isset($risiko['dampak']) && $risiko['dampak']))
                                        <div class="grid grid-cols-2 gap-4">
                                            @if(isset($risiko['penyebab']) && $risiko['penyebab'])
                                                <div class="bg-red-50 rounded-lg p-4">
                                                    <h4 class="text-sm font-semibold text-red-900 mb-2">Penyebab</h4>
                                                    <p class="text-sm text-red-800 leading-relaxed">{{ $risiko['penyebab'] }}</p>
                                                </div>
                                            @endif
                                            @if(isset($risiko['dampak']) && $risiko['dampak'])
                                                <div class="bg-orange-50 rounded-lg p-4">
                                                    <h4 class="text-sm font-semibold text-orange-900 mb-2">Dampak</h4>
                                                    <p class="text-sm text-orange-800 leading-relaxed">{{ $risiko['dampak'] }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if(isset($risiko['level_kemungkinan']) && isset($risiko['level_dampak']))
                                        <div class="bg-purple-50 rounded-lg p-4">
                                            <h4 class="text-sm font-semibold text-purple-900 mb-2">Penilaian Risiko</h4>
                                            <p class="text-sm text-purple-800">
                                                Level Kemungkinan: <strong>{{ $risiko['level_kemungkinan'] }}</strong> ×
                                                Level Dampak: <strong>{{ $risiko['level_dampak'] }}</strong> =
                                                Besaran Risiko: <strong class="{{ str_replace(['bg-', 'border-'], ['text-', ''], $riskLevelColor) }}">{{ $besaranNilai }} ({{ $riskLevelText }})</strong>
                                            </p>
                                        </div>
                                    @endif

                                    @if(isset($risiko['perlu_penanganan']) && $risiko['perlu_penanganan'] === 'ya')
                                        <div class="bg-blue-50 rounded-lg p-4 space-y-3">
                                            <h4 class="text-sm font-semibold text-blue-900 mb-2 flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                Penanganan Risiko
                                            </h4>

                                            @if(isset($risiko['opsi_penanganan']) && $risiko['opsi_penanganan'])
                                                <div>
                                                    <p class="text-xs font-semibold text-blue-700 mb-1">Opsi Penanganan:</p>
                                                    <p class="text-sm text-blue-900">{{ ucwords(str_replace('_', ' ', $risiko['opsi_penanganan'])) }}</p>
                                                </div>
                                            @endif

                                            @if(isset($risiko['rencana_aksi']) && $risiko['rencana_aksi'])
                                                <div>
                                                    <p class="text-xs font-semibold text-blue-700 mb-1">Rencana Aksi:</p>
                                                    <p class="text-sm text-blue-900 leading-relaxed">{!! nl2br(e($risiko['rencana_aksi'])) !!}</p>
                                                </div>
                                            @endif

                                            @if(isset($risiko['jadwal_implementasi']) && $risiko['jadwal_implementasi'])
                                                <div>
                                                    <p class="text-xs font-semibold text-blue-700 mb-1">Jadwal Implementasi:</p>
                                                    <p class="text-sm text-blue-900">{{ $risiko['jadwal_implementasi'] }}</p>
                                                </div>
                                            @endif

                                            @if(isset($risiko['penanggung_jawab']) && $risiko['penanggung_jawab'])
                                                <div class="flex items-center text-sm text-blue-900">
                                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    <span class="font-semibold">Penanggung Jawab:</span>
                                                    <span class="ml-2">{{ $risiko['penanggung_jawab'] }}</span>
                                                </div>
                                            @endif

                                            @if(isset($risiko['risiko_residual']))
                                                <div>
                                                    <p class="text-xs font-semibold text-blue-700 mb-1">Risiko Residual:</p>
                                                    <p class="text-sm text-blue-900">{{ $risiko['risiko_residual'] === 'ya' ? 'Ada' : 'Tidak Ada' }}</p>
                                                </div>
                                            @endif
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
