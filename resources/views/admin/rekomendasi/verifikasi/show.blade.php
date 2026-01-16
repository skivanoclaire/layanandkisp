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
                <a href="{{ route('admin.rekomendasi.verifikasi.index') }}"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    Kembali
                </a>
                @if($proposal->verifikasi && in_array($proposal->verifikasi->status, ['menunggu', 'sedang_diverifikasi']))
                    <a href="{{ route('admin.rekomendasi.verifikasi.verify', $proposal->id) }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Proses Verifikasi
                    </a>
                @endif
            </div>
        </div>

        <!-- Status Cards (same as user view but read-only) -->
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
                        <p class="text-xs text-gray-500 mt-1">{{ $proposal->created_at->format('d M Y') }}</p>
                    </div>
                    <svg class="h-10 w-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pemohon Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-blue-600 font-medium">Diajukan oleh:</p>
                    <p class="text-blue-900 font-semibold">
                        {{ $proposal->user?->name ?? 'Tidak Diketahui' }}
                        @if($proposal->user?->unitKerja)
                            - {{ $proposal->user->unitKerja->nama }}
                        @elseif($proposal->pemilikProsesBisnis)
                            - {{ $proposal->pemilikProsesBisnis->nama }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content Tabs (same structure as user view) -->
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
                    <button onclick="showTab('risiko')" id="tab-risiko"
                        class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Risiko
                    </button>
                    <button onclick="showTab('verifikasi')" id="tab-verifikasi"
                        class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Verifikasi
                    </button>
                    <button onclick="showTab('timeline')" id="tab-timeline"
                        class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Timeline
                    </button>
                </nav>
            </div>

            <!-- Tab: Informasi Dasar (same as user show view) -->
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
                        <p class="text-gray-900">{{ $proposal->deskripsi }}</p>
                    </div>

                    <div class="col-span-2">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Tujuan</h3>
                        <p class="text-gray-900">{{ $proposal->tujuan }}</p>
                    </div>

                    <div class="col-span-2">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Manfaat</h3>
                        <p class="text-gray-900">{{ $proposal->manfaat }}</p>
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
                            @forelse(($proposal->platform ?? []) as $platform)
                                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm mr-1">
                                    {{ ucfirst($platform) }}
                                </span>
                            @empty
                                <span class="text-gray-500">-</span>
                            @endforelse
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

            <!-- Tab: Dokumen (same as user show view) -->
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
                                    <a href="{{ route('admin.rekomendasi.verifikasi.dokumen.download', [$proposal->id, $dokumen->id]) }}"
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

            <!-- Tab: Risiko (same as user show view) -->
            <div id="content-risiko" class="tab-content p-6 hidden">
                <div class="space-y-4">
                    @forelse(($proposal->risiko_items ?? []) as $index => $risiko)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold">
                                        {{ $index + 1 }}
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Jenis Risiko</h4>
                                            <p class="text-gray-900 mt-1">{{ $risiko['jenis'] }}</p>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Tingkat</h4>
                                            <p class="mt-1">
                                                @php
                                                    $tingkatColors = [
                                                        'rendah' => 'bg-green-100 text-green-800',
                                                        'sedang' => 'bg-yellow-100 text-yellow-800',
                                                        'tinggi' => 'bg-red-100 text-red-800',
                                                    ];
                                                @endphp
                                                <span class="px-2 py-1 rounded text-sm {{ $tingkatColors[$risiko['tingkat']] }}">
                                                    {{ ucfirst($risiko['tingkat']) }}
                                                </span>
                                            </p>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Mitigasi</h4>
                                            <p class="text-gray-900 mt-1">{{ $risiko['mitigasi'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Belum ada risiko yang diidentifikasi</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Tab: Verifikasi -->
            <div id="content-verifikasi" class="tab-content p-6 hidden">
                @if($proposal->verifikasi)
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Status Verifikasi</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Status</p>
                                        <p class="text-gray-900 font-medium">{{ $proposal->verifikasi->status_display }}</p>
                                    </div>
                                    @if($proposal->verifikasi->verifikator)
                                        <div>
                                            <p class="text-sm text-gray-500">Verifikator</p>
                                            <p class="text-gray-900 font-medium">{{ $proposal->verifikasi->verifikator->name }}</p>
                                        </div>
                                    @endif
                                    @if($proposal->verifikasi->tanggal_verifikasi)
                                        <div>
                                            <p class="text-sm text-gray-500">Tanggal Verifikasi</p>
                                            <p class="text-gray-900 font-medium">{{ $proposal->verifikasi->tanggal_verifikasi->format('d M Y H:i') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($proposal->verifikasi->catatan_verifikasi)
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Catatan Verifikasi</h3>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-gray-900">{{ $proposal->verifikasi->catatan_verifikasi }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Belum ada data verifikasi</p>
                    </div>
                @endif
            </div>

            <!-- Tab: Timeline (same as user show view) -->
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
