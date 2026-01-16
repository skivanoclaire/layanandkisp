@extends('layouts.authenticated')

@section('title', '- Daftar Usulan Rekomendasi Aplikasi')
@section('header-title', 'Usulan Rekomendasi Aplikasi')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Daftar Usulan Rekomendasi Aplikasi</h1>
            <a href="{{ route('user.rekomendasi.usulan.create') }}"
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Usulan Baru
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
            <form method="GET" action="{{ route('user.rekomendasi.usulan.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nama aplikasi..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="perlu_revisi" {{ request('status') == 'perlu_revisi' ? 'selected' : '' }}>Perlu Revisi</option>
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <!-- Fase Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fase</label>
                    <select name="fase" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Fase</option>
                        <option value="usulan" {{ request('fase') == 'usulan' ? 'selected' : '' }}>Pengajuan Usulan</option>
                        <option value="verifikasi" {{ request('fase') == 'verifikasi' ? 'selected' : '' }}>Verifikasi</option>
                        <option value="penandatanganan" {{ request('fase') == 'penandatanganan' ? 'selected' : '' }}>Penandatanganan</option>
                        <option value="menunggu_kementerian" {{ request('fase') == 'menunggu_kementerian' ? 'selected' : '' }}>Menunggu Kementerian</option>
                        <option value="pengembangan" {{ request('fase') == 'pengembangan' ? 'selected' : '' }}>Pengembangan</option>
                        <option value="selesai" {{ request('fase') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Proposals List -->
    @if ($proposals->isEmpty())
        <div class="bg-white p-8 rounded-lg shadow-sm text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada usulan</h3>
            <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat usulan rekomendasi aplikasi baru.</p>
            <div class="mt-6">
                <a href="{{ route('user.rekomendasi.usulan.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                    Buat Usulan Pertama
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($proposals as $proposal)
                <div class="border border-gray-200 p-5 rounded-lg bg-white shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <!-- Title and Ticket -->
                            <div class="flex items-start gap-3">
                                <h2 class="font-semibold text-lg text-gray-900">{{ $proposal->judul_aplikasi }}</h2>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $proposal->ticket_number }}</p>

                            <!-- Status and Fase Badges -->
                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                <!-- Status Badge -->
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
                                <span class="px-3 py-1 {{ $statusColors[$proposal->status] ?? 'bg-gray-100 text-gray-800' }} text-xs font-medium rounded-full">
                                    {{ ucfirst(str_replace('_', ' ', $proposal->status)) }}
                                </span>

                                <!-- Fase Badge -->
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">
                                    {{ $proposal->fase_saat_ini_display }}
                                </span>
                            </div>

                            <!-- Unit Kerja -->
                            @if ($proposal->pemilikProsesBisnis)
                                <p class="text-sm text-gray-600 mt-2">
                                    <span class="font-medium">Unit Kerja:</span> {{ $proposal->pemilikProsesBisnis->nama }}
                                </p>
                            @endif

                            <!-- Revision Notes -->
                            @if ($proposal->status == 'perlu_revisi' && $proposal->revision_notes)
                                <div class="mt-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                    <p class="text-xs font-semibold text-orange-900 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Catatan Revisi dari Admin:
                                    </p>
                                    <p class="text-sm text-orange-800 mt-1">{{ $proposal->revision_notes }}</p>
                                </div>
                            @endif

                            <!-- Created Date -->
                            <p class="text-xs text-gray-400 mt-2">
                                Dibuat: {{ $proposal->created_at->format('d M Y, H:i') }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col space-y-2 ml-4">
                            <a href="{{ route('user.rekomendasi.usulan.show', $proposal->id) }}"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm text-center">
                                Lihat Detail
                            </a>

                            @if (in_array($proposal->status, ['draft', 'perlu_revisi']))
                                <a href="{{ route('user.rekomendasi.usulan.edit', $proposal->id) }}"
                                    class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition text-sm text-center">
                                    Edit
                                </a>
                            @endif

                            @if ($proposal->status == 'draft')
                                <form action="{{ route('user.rekomendasi.usulan.destroy', $proposal->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus usulan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $proposals->links() }}
        </div>
    @endif
@endsection
