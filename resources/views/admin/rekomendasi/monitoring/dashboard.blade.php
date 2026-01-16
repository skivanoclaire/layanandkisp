@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Monitoring Rekomendasi Aplikasi</h1>
                <p class="text-gray-600 mt-1">Dashboard dan laporan lengkap</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.rekomendasi.monitoring.export.excel') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
                <a href="{{ route('admin.rekomendasi.monitoring.export.pdf') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-list text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Draft</p>
                        <p class="text-2xl font-bold text-gray-600">{{ $stats['draft'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-file text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Verifikasi</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['menunggu_verifikasi'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Disetujui</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['disetujui'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Ditolak</p>
                        <p class="text-2xl font-bold text-red-600">{{ $stats['ditolak'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Revisi</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $stats['perlu_revisi'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-redo text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phase Statistics -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Distribusi per Fase</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @php
                    $phaseLabels = [
                        'usulan' => 'Usulan',
                        'verifikasi' => 'Verifikasi',
                        'penandatanganan' => 'Penandatanganan',
                        'menunggu_kementerian' => 'Menunggu Kementerian',
                        'pengembangan' => 'Pengembangan',
                        'selesai' => 'Selesai',
                    ];
                @endphp
                @foreach($phaseStats as $phase => $count)
                    <a href="{{ route('admin.rekomendasi.monitoring.by-phase', $phase) }}"
                        class="text-center p-4 border border-gray-200 rounded-lg hover:shadow-md hover:border-blue-500 transition">
                        <p class="text-3xl font-bold text-blue-600">{{ $count }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $phaseLabels[$phase] }}</p>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" action="{{ route('admin.rekomendasi.monitoring.dashboard') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nama aplikasi atau tiket..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Semua Status --</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            <option value="perlu_revisi" {{ request('status') == 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fase</label>
                        <select name="fase" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Semua Fase --</option>
                            <option value="usulan" {{ request('fase') == 'usulan' ? 'selected' : '' }}>Usulan</option>
                            <option value="verifikasi" {{ request('fase') == 'verifikasi' ? 'selected' : '' }}>Verifikasi</option>
                            <option value="penandatanganan" {{ request('fase') == 'penandatanganan' ? 'selected' : '' }}>Penandatanganan</option>
                            <option value="menunggu_kementerian" {{ request('fase') == 'menunggu_kementerian' ? 'selected' : '' }}>Menunggu Kementerian</option>
                            <option value="pengembangan" {{ request('fase') == 'pengembangan' ? 'selected' : '' }}>Pengembangan</option>
                            <option value="selesai" {{ request('fase') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Applications Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Daftar Aplikasi</h2>
            </div>

            @if($applications->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiket</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aplikasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemohon</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fase</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($applications as $app)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                        {{ $app->ticket_number }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $app->nama_aplikasi }}</p>
                                        <p class="text-xs text-gray-500">{{ $app->pemilikProsesBisnis?->nama ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $app->user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'draft' => 'bg-gray-100 text-gray-800',
                                                'menunggu_verifikasi' => 'bg-yellow-100 text-yellow-800',
                                                'disetujui' => 'bg-green-100 text-green-800',
                                                'ditolak' => 'bg-red-100 text-red-800',
                                                'perlu_revisi' => 'bg-orange-100 text-orange-800',
                                            ];
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$app->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $app->fase_saat_ini)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $app->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.rekomendasi.monitoring.history', $app->id) }}"
                                            class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $applications->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500">Tidak ada data yang sesuai dengan filter</p>
                </div>
            @endif
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h2>
            <div class="space-y-3">
                @foreach($recentActivities as $activity)
                    <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-history text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $activity->aktivitas }}</p>
                            <p class="text-xs text-gray-600">
                                {{ $activity->proposal?->nama_aplikasi ?? $activity->proposal?->judul_aplikasi ?? 'N/A' }} -
                                {{ $activity->user->name }} -
                                {{ $activity->created_at->diffForHumans() }}
                            </p>
                            @if($activity->deskripsi)
                                <p class="text-xs text-gray-500 mt-1">{{ $activity->deskripsi }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
