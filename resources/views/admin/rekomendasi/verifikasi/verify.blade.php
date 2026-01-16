@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Verifikasi Usulan</h1>
                <p class="text-gray-600 mt-1">{{ $proposal->nama_aplikasi }}</p>
            </div>
            <a href="{{ route('admin.rekomendasi.verifikasi.index') }}"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                Kembali
            </a>
        </div>

        <!-- Status Bar -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div>
                        <span class="text-sm text-blue-600 font-medium">Nomor Tiket:</span>
                        <span class="text-blue-900 font-semibold ml-2">{{ $proposal->ticket_number }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-blue-600 font-medium">Pemohon:</span>
                        <span class="text-blue-900 font-semibold ml-2">{{ $proposal->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-blue-600 font-medium">Unit Kerja:</span>
                        <span class="text-blue-900 font-semibold ml-2">{{ $proposal->pemilikProsesBisnis?->nama ?? '-' }}</span>
                    </div>
                </div>
                <div>
                    @php
                        $statusColors = [
                            'menunggu' => 'bg-gray-100 text-gray-800',
                            'sedang_diverifikasi' => 'bg-yellow-100 text-yellow-800',
                            'disetujui' => 'bg-green-100 text-green-800',
                            'ditolak' => 'bg-red-100 text-red-800',
                            'perlu_revisi' => 'bg-orange-100 text-orange-800',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$verifikasi->status] }}">
                        {{ $verifikasi->status_display }}
                    </span>
                </div>
            </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Checklist Verifikasi -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Checklist Verifikasi</h2>

                    <form method="POST" action="{{ route('admin.rekomendasi.verifikasi.checklist.update', $proposal->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <!-- Checklist Items -->
                            <div class="border-b border-gray-200 pb-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" name="checklist_analisis_kebutuhan"
                                        {{ $verifikasi->checklist_analisis_kebutuhan ? 'checked' : '' }}
                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Dokumen Analisis Kebutuhan</span>
                                        <p class="text-xs text-gray-500">Verifikasi kelengkapan dan kesesuaian dokumen analisis kebutuhan</p>
                                    </div>
                                </label>
                            </div>

                            <div class="border-b border-gray-200 pb-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" name="checklist_perencanaan"
                                        {{ $verifikasi->checklist_perencanaan ? 'checked' : '' }}
                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Dokumen Perencanaan</span>
                                        <p class="text-xs text-gray-500">Verifikasi kelengkapan dan kesesuaian dokumen perencanaan</p>
                                    </div>
                                </label>
                            </div>

                            <div class="border-b border-gray-200 pb-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" name="checklist_manajemen_risiko"
                                        {{ $verifikasi->checklist_manajemen_risiko ? 'checked' : '' }}
                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Dokumen Manajemen Risiko</span>
                                        <p class="text-xs text-gray-500">Verifikasi kelengkapan dan kesesuaian dokumen manajemen risiko</p>
                                    </div>
                                </label>
                            </div>

                            <div class="border-b border-gray-200 pb-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" name="checklist_kelengkapan_data"
                                        {{ $verifikasi->checklist_kelengkapan_data ? 'checked' : '' }}
                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Kelengkapan Data Formulir</span>
                                        <p class="text-xs text-gray-500">Semua field wajib terisi dengan lengkap dan benar</p>
                                    </div>
                                </label>
                            </div>

                            <div class="border-b border-gray-200 pb-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" name="checklist_kesesuaian_peraturan"
                                        {{ $verifikasi->checklist_kesesuaian_peraturan ? 'checked' : '' }}
                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Kesesuaian dengan Peraturan</span>
                                        <p class="text-xs text-gray-500">Usulan sesuai dengan peraturan dan kebijakan yang berlaku</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Catatan Internal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Internal (Opsional)</label>
                                <textarea name="catatan_internal" rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Catatan internal untuk dokumentasi verifikasi (tidak terlihat oleh pemohon)">{{ $verifikasi->catatan_internal }}</textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                    Simpan Checklist
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Dokumen Usulan -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Dokumen Usulan</h2>

                    <div class="space-y-3">
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
                                                Diupload: {{ $dokumen->created_at->format('d M Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.rekomendasi.verifikasi.dokumen.download', [$proposal->id, $dokumen->id]) }}"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2">Belum ada dokumen</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Informasi Usulan -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Informasi Usulan</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Prioritas</p>
                            <p class="text-gray-900 font-medium">{{ ucfirst(str_replace('_', ' ', $proposal->prioritas)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Jenis Layanan</p>
                            <p class="text-gray-900 font-medium">{{ ucfirst($proposal->jenis_layanan) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Estimasi Waktu</p>
                            <p class="text-gray-900 font-medium">{{ $proposal->estimasi_waktu_pengembangan }} bulan</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Estimasi Biaya</p>
                            <p class="text-gray-900 font-medium">Rp {{ number_format($proposal->estimasi_biaya, 0, ',', '.') }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500 mb-1">Deskripsi</p>
                            <p class="text-gray-900">{{ $proposal->deskripsi }}</p>
                        </div>
                        <div class="col-span-2">
                            <a href="{{ route('admin.rekomendasi.verifikasi.show', $proposal->id) }}"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Lihat Detail Lengkap →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Keputusan Verifikasi -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Keputusan Verifikasi</h2>

                    @if($verifikasi->isAllChecklistCompleted())
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                            <div class="flex">
                                <svg class="h-5 w-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <p class="ml-2 text-sm text-green-800">Semua checklist telah dilengkapi</p>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                            <div class="flex">
                                <svg class="h-5 w-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <p class="ml-2 text-sm text-yellow-800">Lengkapi checklist sebelum mengambil keputusan</p>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <!-- Approve -->
                        <button onclick="showApproveModal()"
                            class="w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition font-medium flex items-center justify-center"
                            {{ !$verifikasi->isAllChecklistCompleted() ? 'disabled' : '' }}>
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Setujui Usulan
                        </button>

                        <!-- Request Revision -->
                        <button onclick="showRevisionModal()"
                            class="w-full bg-orange-600 text-white px-4 py-3 rounded-lg hover:bg-orange-700 transition font-medium flex items-center justify-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Minta Revisi
                        </button>

                        <!-- Reject -->
                        <button onclick="showRejectModal()"
                            class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 transition font-medium flex items-center justify-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Tolak Usulan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mx-auto">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4 text-center">Setujui Usulan</h3>
            <form method="POST" action="{{ route('admin.rekomendasi.verifikasi.approve', $proposal->id) }}" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea name="catatan_verifikasi" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                        placeholder="Catatan persetujuan untuk pemohon"></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeApproveModal()"
                        class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Revision Modal -->
<div id="revisionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 mx-auto">
                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4 text-center">Minta Revisi</h3>
            <form method="POST" action="{{ route('admin.rekomendasi.verifikasi.revision', $proposal->id) }}" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Revisi <span class="text-red-500">*</span></label>
                    <textarea name="catatan_verifikasi" rows="4" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                        placeholder="Jelaskan apa yang perlu direvisi oleh pemohon"></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeRevisionModal()"
                        class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
                        Kirim Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mx-auto">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4 text-center">Tolak Usulan</h3>
            <form method="POST" action="{{ route('admin.rekomendasi.verifikasi.reject', $proposal->id) }}" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="catatan_verifikasi" rows="4" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                        placeholder="Jelaskan alasan penolakan usulan ini"></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeRejectModal()"
                        class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                        Tolak Usulan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showApproveModal() {
    document.getElementById('approveModal').classList.remove('hidden');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
}

function showRevisionModal() {
    document.getElementById('revisionModal').classList.remove('hidden');
}

function closeRevisionModal() {
    document.getElementById('revisionModal').classList.add('hidden');
}

function showRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('bg-opacity-50')) {
        closeApproveModal();
        closeRevisionModal();
        closeRejectModal();
    }
}
</script>
@endpush
@endsection
