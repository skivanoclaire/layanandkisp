@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Riwayat Lengkap Usulan</h1>
                <p class="text-gray-600 mt-1">{{ $proposal->nama_aplikasi ?? $proposal->judul_aplikasi }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.rekomendasi.monitoring.dashboard') }}"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    Kembali
                </a>
            </div>
        </div>

        <!-- Application Overview -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Informasi Umum</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500">Nomor Tiket</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $proposal->ticket_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Pemohon</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $proposal->user?->name ?? 'N/A' }}
                        @if($proposal->user?->unitKerja)
                            <span class="text-sm font-normal text-gray-600">({{ $proposal->user->unitKerja->nama }})</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="text-lg">
                        @php
                            $statusColors = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'diajukan' => 'bg-blue-100 text-blue-800',
                                'perlu_revisi' => 'bg-orange-100 text-orange-800',
                                'diproses' => 'bg-yellow-100 text-yellow-800',
                                'disetujui' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $statusColors[$proposal->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst(str_replace('_', ' ', $proposal->status)) }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Fase Saat Ini</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $proposal->fase_saat_ini_display }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tanggal Dibuat</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $proposal->created_at->format('d M Y H:i') }}</p>
                </div>
                @if($proposal->pemilikProsesBisnis)
                <div>
                    <p class="text-sm text-gray-500">Pemilik Proses Bisnis</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $proposal->pemilikProsesBisnis->nama }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Verification Status -->
        @if($proposal->verifikasi)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Status Verifikasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-sm text-gray-500">Status Verifikasi</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $proposal->verifikasi->status_display }}</p>
                </div>
                @if($proposal->verifikasi->verifikator)
                <div>
                    <p class="text-sm text-gray-500">Verifikator</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $proposal->verifikasi->verifikator->name }}</p>
                </div>
                @endif
                @if($proposal->verifikasi->tanggal_verifikasi)
                <div>
                    <p class="text-sm text-gray-500">Tanggal Verifikasi</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $proposal->verifikasi->tanggal_verifikasi->format('d M Y H:i') }}</p>
                </div>
                @endif
            </div>
            @if($proposal->verifikasi->catatan_verifikasi)
            <div class="mt-4">
                <p class="text-sm text-gray-500 mb-1">Catatan Verifikasi</p>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-900">{{ $proposal->verifikasi->catatan_verifikasi }}</p>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Letter Status -->
        @if($proposal->surat)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Status Surat Rekomendasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-sm text-gray-500">Nomor Surat</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $proposal->surat->nomor_surat ?? 'Belum ada' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tanggal Surat</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $proposal->surat->tanggal_surat ? $proposal->surat->tanggal_surat->format('d M Y') : 'Belum ada' }}
                    </p>
                </div>
                @if($proposal->surat->statusKementerian)
                <div>
                    <p class="text-sm text-gray-500">Status Kementerian</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ ucfirst(str_replace('_', ' ', $proposal->surat->statusKementerian->status)) }}
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Development Phase -->
        @if($proposal->fasePengembangan->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Fase Pengembangan</h2>
            <div class="space-y-4">
                @foreach($proposal->fasePengembangan as $fase)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $fase->nama_fase }}</h3>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            @if($fase->status === 'selesai') bg-green-100 text-green-800
                            @elseif($fase->status === 'sedang_berjalan') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $fase->status)) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Mulai</p>
                            <p class="text-gray-900">{{ $fase->tanggal_mulai->format('d M Y') }}</p>
                        </div>
                        @if($fase->tanggal_selesai)
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Selesai</p>
                            <p class="text-gray-900">{{ $fase->tanggal_selesai->format('d M Y') }}</p>
                        </div>
                        @endif
                    </div>
                    @if($fase->milestones->count() > 0)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Milestone:</p>
                        <div class="space-y-2">
                            @foreach($fase->milestones as $milestone)
                            <div class="flex items-center text-sm">
                                <input type="checkbox" {{ $milestone->status === 'selesai' ? 'checked' : '' }} disabled class="mr-2">
                                <span class="{{ $milestone->status === 'selesai' ? 'line-through text-gray-500' : 'text-gray-900' }}">
                                    {{ $milestone->nama_milestone }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Team Members -->
        @if($proposal->timPengembangan->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Tim Pengembangan</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($proposal->timPengembangan as $anggota)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $anggota->nama }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $anggota->peran }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $anggota->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    @if($anggota->status === 'aktif') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($anggota->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Evaluations -->
        @if($proposal->evaluasi->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Evaluasi</h2>
            <div class="space-y-4">
                @foreach($proposal->evaluasi as $eval)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $eval->jenis_evaluasi }}</h3>
                        <span class="text-sm text-gray-500">{{ $eval->tanggal_evaluasi->format('d M Y') }}</span>
                    </div>
                    @if($eval->hasil_evaluasi)
                    <div class="bg-gray-50 rounded-lg p-4 mt-3">
                        <p class="text-sm text-gray-900">{{ $eval->hasil_evaluasi }}</p>
                    </div>
                    @endif
                    @if($eval->rekomendasi)
                    <div class="mt-3">
                        <p class="text-sm font-medium text-gray-700">Rekomendasi:</p>
                        <p class="text-sm text-gray-900 mt-1">{{ $eval->rekomendasi }}</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Activity History Timeline -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Riwayat Aktivitas</h2>
            <div class="space-y-4">
                @forelse($proposal->historiAktivitas->sortByDesc('created_at') as $histori)
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center">
                            <i class="fas fa-history"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
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
                    <i class="fas fa-inbox text-gray-300 text-4xl mb-2"></i>
                    <p class="text-gray-500">Belum ada aktivitas</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
