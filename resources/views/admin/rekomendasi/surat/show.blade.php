@extends('layouts.authenticated')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Detail Surat Rekomendasi</h1>
                <p class="text-gray-600 mt-1">{{ $surat->proposal->nama_aplikasi }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.rekomendasi.surat.index') }}"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    Kembali
                </a>
                @if(!$surat->isSigned())
                    <a href="{{ route('admin.rekomendasi.surat.edit', $surat->id) }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Edit Surat
                    </a>
                @endif
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
                <!-- Letter Info -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Informasi Surat</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Nomor Surat</p>
                            @if($surat->isSigned())
                                <p class="text-gray-900 font-semibold">{{ $surat->nomor_surat_final }}</p>
                                <p class="text-xs text-gray-500">(Draft: {{ $surat->nomor_surat_draft }})</p>
                            @else
                                <p class="text-gray-900 font-semibold">{{ $surat->nomor_surat_draft }}</p>
                                <p class="text-xs text-orange-600">(Masih Draft)</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Tanggal Surat</p>
                            <p class="text-gray-900 font-medium">{{ $surat->tanggal_surat->format('d F Y') }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Perihal</p>
                            <p class="text-gray-900 font-medium">{{ $surat->perihal }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Tujuan</p>
                            <p class="text-gray-900 font-medium">{{ $surat->tujuan_surat }}</p>
                        </div>

                        @if($surat->isSigned())
                            <div>
                                <p class="text-sm text-gray-500">Penandatangan</p>
                                <p class="text-gray-900 font-medium">{{ $surat->nama_penandatangan }}</p>
                                <p class="text-xs text-gray-500">{{ $surat->jabatan_penandatangan }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Tanggal Penandatanganan</p>
                                <p class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($surat->tanggal_ttd)->format('d F Y') }}</p>
                            </div>
                        @endif

                        <div>
                            <p class="text-sm text-gray-500">Dibuat Oleh</p>
                            <p class="text-gray-900 font-medium">{{ $surat->creator->name }}</p>
                            <p class="text-xs text-gray-500">{{ $surat->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    @if(!empty($surat->referensi_hukum))
                        <div class="mt-4 pt-4 border-t">
                            <p class="text-sm text-gray-500 mb-2">Referensi Hukum</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($surat->referensi_hukum as $ref)
                                    <li class="text-gray-900 text-sm">{{ $ref }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Letter Content -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Isi Surat</h2>
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <pre class="whitespace-pre-wrap font-sans text-sm text-gray-900">{{ $surat->isi_surat }}</pre>
                    </div>
                </div>

                <!-- Delivery Info -->
                @if($surat->pengiriman->count() > 0)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Riwayat Pengiriman</h2>

                        <div class="space-y-4">
                            @foreach($surat->pengiriman as $kirim)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-sm text-gray-500">Metode Pengiriman</p>
                                            <p class="text-gray-900 font-medium">{{ strtoupper($kirim->metode_pengiriman) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Tanggal Pengiriman</p>
                                            <p class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($kirim->tanggal_pengiriman)->format('d F Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Penerima</p>
                                            <p class="text-gray-900 font-medium">{{ $kirim->penerima }}</p>
                                        </div>
                                        @if($kirim->nomor_resi)
                                            <div>
                                                <p class="text-sm text-gray-500">Nomor Resi</p>
                                                <p class="text-gray-900 font-medium">{{ $kirim->nomor_resi }}</p>
                                            </div>
                                        @endif
                                        @if($kirim->catatan_pengiriman)
                                            <div class="col-span-2">
                                                <p class="text-sm text-gray-500">Catatan</p>
                                                <p class="text-gray-900">{{ $kirim->catatan_pengiriman }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Ministry Status -->
                @if($surat->statusKementerian)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Status Kementerian</h2>

                        <div class="space-y-4">
                            <div>
                                @php
                                    $statusColors = [
                                        'terkirim' => 'bg-blue-100 text-blue-800',
                                        'diproses' => 'bg-yellow-100 text-yellow-800',
                                        'disetujui' => 'bg-green-100 text-green-800',
                                        'ditolak' => 'bg-red-100 text-red-800',
                                        'perlu_revisi' => 'bg-orange-100 text-orange-800',
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$surat->statusKementerian->status] }}">
                                    {{ ucfirst(str_replace('_', ' ', $surat->statusKementerian->status)) }}
                                </span>
                            </div>

                            @if($surat->statusKementerian->tanggal_respons)
                                <div>
                                    <p class="text-sm text-gray-500">Tanggal Respons</p>
                                    <p class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($surat->statusKementerian->tanggal_respons)->format('d F Y') }}</p>
                                </div>
                            @endif

                            @if($surat->statusKementerian->catatan_kementerian)
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Catatan dari Kementerian</p>
                                    <div class="bg-gray-50 rounded p-3">
                                        <p class="text-gray-900 text-sm">{{ $surat->statusKementerian->catatan_kementerian }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($surat->statusKementerian->file_respons_path)
                                <div>
                                    <a href="{{ asset('storage/' . $surat->statusKementerian->file_respons_path) }}" target="_blank"
                                        class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Download File Respons
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar Actions -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6 space-y-4">
                    <h3 class="font-semibold text-gray-800">Aksi</h3>

                    <!-- Sign Letter -->
                    @if(!$surat->isSigned())
                        <button onclick="showSignModal()" class="w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition font-medium">
                            Tandatangani Surat
                        </button>
                    @else
                        <a href="{{ route('admin.rekomendasi.surat.download', $surat->id) }}"
                            class="block w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition font-medium text-center">
                            Download Surat Resmi
                        </a>

                        @if(!$surat->isSent())
                            <button onclick="showDeliveryModal()" class="w-full bg-purple-600 text-white px-4 py-3 rounded-lg hover:bg-purple-700 transition font-medium">
                                Catat Pengiriman
                            </button>
                        @else
                            <button onclick="showMinistryStatusModal()" class="w-full bg-yellow-600 text-white px-4 py-3 rounded-lg hover:bg-yellow-700 transition font-medium">
                                Update Status Kementerian
                            </button>
                        @endif
                    @endif

                    <!-- Proposal Link -->
                    <a href="{{ route('admin.rekomendasi.verifikasi.show', $surat->proposal->id) }}"
                        class="block w-full bg-gray-600 text-white px-4 py-3 rounded-lg hover:bg-gray-700 transition font-medium text-center">
                        Lihat Detail Usulan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sign Modal -->
<div id="signModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Tandatangani Surat</h3>
            <form method="POST" action="{{ route('admin.rekomendasi.surat.sign', $surat->id) }}" enctype="multipart/form-data">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Surat Final <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nomor_surat_final" required
                            value="{{ $surat->nomor_surat_draft }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <p class="text-xs text-gray-500 mt-1">Nomor surat yang sudah ditandatangani</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Upload Surat Bertandatangan <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="file_signed" accept=".pdf" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <p class="text-xs text-gray-500 mt-1">PDF maksimal 10MB</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Tanda Tangan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_ttd" required value="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Penandatangan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_penandatangan" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jabatan Penandatangan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="jabatan_penandatangan" required
                            placeholder="Contoh: Kepala Dinas Komunikasi dan Informatika"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button type="button" onclick="closeSignModal()"
                        class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Tandatangani
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delivery Modal -->
<div id="deliveryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Catat Pengiriman Surat</h3>
            <form method="POST" action="{{ route('admin.rekomendasi.surat.delivery', $surat->id) }}" enctype="multipart/form-data">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Metode Pengiriman <span class="text-red-500">*</span>
                        </label>
                        <select name="metode_pengiriman" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="">Pilih Metode</option>
                            <option value="pos">Pos</option>
                            <option value="email">Email</option>
                            <option value="online">Sistem Online</option>
                            <option value="kurir">Kurir</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Pengiriman <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_pengiriman" required value="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Penerima <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="penerima" required
                            placeholder="Nama bagian/unit di kementerian"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Resi (Opsional)
                        </label>
                        <input type="text" name="nomor_resi"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan (Opsional)
                        </label>
                        <textarea name="catatan_pengiriman" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Bukti Pengiriman (Opsional)
                        </label>
                        <input type="file" name="file_bukti" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <p class="text-xs text-gray-500 mt-1">PDF/Image maksimal 5MB</p>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button type="button" onclick="closeDeliveryModal()"
                        class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ministry Status Modal -->
<div id="ministryStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Update Status Kementerian</h3>
            <form method="POST" action="{{ route('admin.rekomendasi.surat.ministry-status', $surat->id) }}" enctype="multipart/form-data">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                            <option value="terkirim">Terkirim</option>
                            <option value="diproses">Diproses</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="perlu_revisi">Perlu Revisi</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Respons
                        </label>
                        <input type="date" name="tanggal_respons" value="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            File Respons (PDF)
                        </label>
                        <input type="file" name="file_respons" accept=".pdf"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan dari Kementerian
                        </label>
                        <textarea name="catatan_kementerian" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500"></textarea>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button type="button" onclick="closeMinistryStatusModal()"
                        class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showSignModal() {
    document.getElementById('signModal').classList.remove('hidden');
}

function closeSignModal() {
    document.getElementById('signModal').classList.add('hidden');
}

function showDeliveryModal() {
    document.getElementById('deliveryModal').classList.remove('hidden');
}

function closeDeliveryModal() {
    document.getElementById('deliveryModal').classList.add('hidden');
}

function showMinistryStatusModal() {
    document.getElementById('ministryStatusModal').classList.remove('hidden');
}

function closeMinistryStatusModal() {
    document.getElementById('ministryStatusModal').classList.add('hidden');
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('bg-opacity-50')) {
        closeSignModal();
        closeDeliveryModal();
        closeMinistryStatusModal();
    }
}
</script>
@endpush
@endsection
