@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Fase Pengembangan Aplikasi</h1>
                <p class="text-gray-600 mt-1">{{ $proposal->nama_aplikasi }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('user.rekomendasi.fase.team', $proposal->id) }}"
                    class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-users mr-2"></i>Kelola Tim
                </a>
                <a href="{{ route('user.rekomendasi.usulan.show', $proposal->id) }}"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    Kembali
                </a>
            </div>
        </div>

        <!-- Application Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                <div>
                    <span class="text-blue-600">Nomor Tiket:</span>
                    <span class="text-blue-900 font-medium ml-2">{{ $proposal->ticket_number }}</span>
                </div>
                <div>
                    <span class="text-blue-600">Status Kementerian:</span>
                    <span class="ml-2">
                        @if($proposal->surat && $proposal->surat->statusKementerian)
                            @php
                                $statusKementerian = $proposal->surat->statusKementerian->status;
                                $badgeColors = [
                                    'terkirim' => 'bg-blue-100 text-blue-800',
                                    'diproses' => 'bg-yellow-100 text-yellow-800',
                                    'disetujui' => 'bg-green-100 text-green-800',
                                    'ditolak' => 'bg-red-100 text-red-800',
                                    'perlu_revisi' => 'bg-orange-100 text-orange-800',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badgeColors[$statusKementerian] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $statusKementerian)) }}
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                Belum Ada Respons
                            </span>
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-blue-600">Fase Saat Ini:</span>
                    <span class="text-blue-900 font-medium ml-2">{{ ucfirst(str_replace('_', ' ', $proposal->fase_saat_ini)) }}</span>
                </div>
            </div>
        </div>

        <!-- Overall Progress -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Progress Keseluruhan</h2>
            @php
                $totalProgress = $proposal->fasePengembangan->avg('progress_persen') ?? 0;
            @endphp
            <div class="mb-2 flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Total Progress</span>
                <span class="text-sm font-semibold text-blue-600">{{ number_format($totalProgress, 1) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="bg-blue-600 h-4 rounded-full transition-all duration-300" style="width: {{ $totalProgress }}%"></div>
            </div>
        </div>

        <!-- Development Phases -->
        <div class="space-y-6">
            @php
                $phaseNames = [
                    'rancang_bangun' => 'Rancang Bangun',
                    'implementasi' => 'Implementasi',
                    'uji_kelaikan' => 'Uji Kelaikan',
                    'pemeliharaan' => 'Pemeliharaan',
                    'evaluasi' => 'Evaluasi',
                ];
                $phaseIcons = [
                    'rancang_bangun' => 'fa-drafting-compass',
                    'implementasi' => 'fa-code',
                    'uji_kelaikan' => 'fa-vial',
                    'pemeliharaan' => 'fa-tools',
                    'evaluasi' => 'fa-chart-line',
                ];
                $phaseColors = [
                    'rancang_bangun' => 'blue',
                    'implementasi' => 'green',
                    'uji_kelaikan' => 'yellow',
                    'pemeliharaan' => 'purple',
                    'evaluasi' => 'pink',
                ];
            @endphp

            @foreach($proposal->fasePengembangan as $fase)
                @php
                    $color = $phaseColors[$fase->fase] ?? 'gray';
                    $statusBadge = [
                        'belum_mulai' => 'bg-gray-100 text-gray-800',
                        'sedang_berjalan' => 'bg-blue-100 text-blue-800',
                        'selesai' => 'bg-green-100 text-green-800',
                    ];
                @endphp

                <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-{{ $color }}-500">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-{{ $color }}-100 rounded-lg flex items-center justify-center">
                                    <i class="fas {{ $phaseIcons[$fase->fase] }} text-{{ $color }}-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">{{ $phaseNames[$fase->fase] }}</h3>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusBadge[$fase->status] }}">
                                        {{ ucfirst(str_replace('_', ' ', $fase->status)) }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('user.rekomendasi.fase.show', [$proposal->id, $fase->id]) }}"
                                class="bg-{{ $color }}-600 text-white px-4 py-2 rounded-lg hover:bg-{{ $color }}-700 transition">
                                Kelola Fase
                            </a>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="mb-2 flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Progress</span>
                                <span class="text-sm font-semibold text-{{ $color }}-600">{{ $fase->progress_persen }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-{{ $color }}-600 h-3 rounded-full transition-all duration-300" style="width: {{ $fase->progress_persen }}%"></div>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <span class="text-xs text-gray-500">Tanggal Mulai</span>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $fase->tanggal_mulai ? $fase->tanggal_mulai->format('d M Y') : '-' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Tanggal Selesai</span>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $fase->tanggal_selesai ? $fase->tanggal_selesai->format('d M Y') : '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-{{ $color }}-600">{{ $fase->dokumenPengembangan->count() }}</p>
                                <p class="text-xs text-gray-500">Dokumen</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-{{ $color }}-600">{{ $fase->milestones->count() }}</p>
                                <p class="text-xs text-gray-500">Milestone</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-{{ $color }}-600">
                                    {{ $fase->milestones->where('status', 'completed')->count() }}
                                </p>
                                <p class="text-xs text-gray-500">Milestone Selesai</p>
                            </div>
                        </div>

                        @if($fase->keterangan)
                            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-700">{{ $fase->keterangan }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Team Members Summary -->
        @if($proposal->timPengembangan->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Tim Pengembangan</h3>
                    <a href="{{ route('user.rekomendasi.fase.team', $proposal->id) }}"
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Lihat Semua
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($proposal->timPengembangan->take(6) as $member)
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $member->nama }}</p>
                                <p class="text-xs text-gray-500">{{ $member->peran }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    /* Dynamic color classes for Tailwind */
    .border-blue-500 { border-left-color: #3b82f6; }
    .border-green-500 { border-left-color: #10b981; }
    .border-yellow-500 { border-left-color: #f59e0b; }
    .border-purple-500 { border-left-color: #8b5cf6; }
    .border-pink-500 { border-left-color: #ec4899; }

    .bg-blue-100 { background-color: #dbeafe; }
    .bg-green-100 { background-color: #d1fae5; }
    .bg-yellow-100 { background-color: #fef3c7; }
    .bg-purple-100 { background-color: #ede9fe; }
    .bg-pink-100 { background-color: #fce7f3; }

    .text-blue-600 { color: #2563eb; }
    .text-green-600 { color: #059669; }
    .text-yellow-600 { color: #d97706; }
    .text-purple-600 { color: #7c3aed; }
    .text-pink-600 { color: #db2777; }

    .bg-blue-600 { background-color: #2563eb; }
    .bg-green-600 { background-color: #059669; }
    .bg-yellow-600 { background-color: #d97706; }
    .bg-purple-600 { background-color: #7c3aed; }
    .bg-pink-600 { background-color: #db2777; }

    .hover\:bg-blue-700:hover { background-color: #1d4ed8; }
    .hover\:bg-green-700:hover { background-color: #047857; }
    .hover\:bg-yellow-700:hover { background-color: #b45309; }
    .hover\:bg-purple-700:hover { background-color: #6d28d9; }
    .hover\:bg-pink-700:hover { background-color: #be185d; }
</style>
@endpush
@endsection
