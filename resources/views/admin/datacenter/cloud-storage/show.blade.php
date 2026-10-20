@extends('layouts.authenticated')
@section('title', '- Detail Cloud Storage')
@section('header-title', 'Detail Cloud Storage')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-purple-700">Detail Permohonan Cloud Storage</h1>
        <a href="{{ route('admin.datacenter.cloud-storage.index') }}"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">
            ← Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <!-- Ticket & Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">No. Tiket:</label>
                <p class="text-gray-800 font-mono text-purple-600 font-bold">{{ $cloudStorageRequest->ticket_no }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status:</label>
                @if($cloudStorageRequest->status === 'menunggu')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                @elseif($cloudStorageRequest->status === 'proses')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Sedang Diproses</span>
                @elseif($cloudStorageRequest->status === 'selesai')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Diajukan:</label>
                <p class="text-gray-800">{{ $cloudStorageRequest->created_at->format('d/m/Y H:i') }} WITA</p>
            </div>
        </div>

        <!-- Informasi Pemohon -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemohon</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama:</label>
                    <p class="text-gray-800">{{ $cloudStorageRequest->nama }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">NIP:</label>
                    <p class="text-gray-800">{{ $cloudStorageRequest->nip }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Instansi:</label>
                    <p class="text-gray-800">{{ $cloudStorageRequest->unitKerja->nama ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Spesifikasi Cloud Storage -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Spesifikasi Cloud Storage</h2>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kapasitas Diminta</label>
                        <p class="text-2xl font-bold text-purple-700">{{ $cloudStorageRequest->kapasitas_gb }} GB</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kapasitas Disetujui</label>
                        @if($cloudStorageRequest->kapasitas_disetujui_gb)
                            <p class="text-2xl font-bold text-green-700">{{ $cloudStorageRequest->kapasitas_disetujui_gb }} GB</p>
                            @if($cloudStorageRequest->kapasitas_disetujui_gb != $cloudStorageRequest->kapasitas_gb)
                                <span class="text-xs font-semibold text-orange-700">Direvisi admin</span>
                            @endif
                        @else
                            <p class="text-lg text-gray-500">Belum ditetapkan</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe</label>
                        <p class="text-lg font-bold text-purple-700">{{ $cloudStorageRequest->tipe }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keterangan -->
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Keterangan</h2>
            <div class="bg-gray-50 p-4 rounded-lg border">
                <p class="text-gray-800 whitespace-pre-wrap">{{ $cloudStorageRequest->keterangan }}</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($cloudStorageRequest->status === 'menunggu')
    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-3">Tindakan untuk Permohonan Ini</h3>
        <div class="flex gap-3">
            <button onclick="document.getElementById('formProcess').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                Proses Permohonan
            </button>
            <button onclick="document.getElementById('formReject').style.display='block'; this.parentElement.style.display='none';"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">
                Tolak Permohonan
            </button>
        </div>

        <form id="formProcess" action="{{ route('admin.datacenter.cloud-storage.process', $cloudStorageRequest->id) }}" method="POST" style="display:none;" class="mt-4">
            @csrf
            <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan (opsional):</label>
            <textarea name="admin_notes" rows="3" class="w-full px-3 py-2 border border-blue-300 rounded-lg"></textarea>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Konfirmasi Proses</button>
                <button type="button" onclick="this.closest('form').style.display='none'; this.closest('.bg-yellow-50').querySelector('.flex.gap-3').style.display='flex';"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">Batal</button>
            </div>
        </form>

        <form id="formReject" action="{{ route('admin.datacenter.cloud-storage.reject', $cloudStorageRequest->id) }}" method="POST" style="display:none;" class="mt-4">
            @csrf
            <label class="block text-sm font-semibold text-red-700 mb-1">Alasan Penolakan: <span class="text-red-500">*</span></label>
            <textarea name="admin_notes" rows="3" required class="w-full px-3 py-2 border border-red-300 rounded-lg" placeholder="Jelaskan alasan penolakan..."></textarea>
            <div class="mt-3 flex gap-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">Konfirmasi Tolak</button>
                <button type="button" onclick="this.closest('form').style.display='none'; this.closest('.bg-yellow-50').querySelector('.flex.gap-3').style.display='flex';"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold">Batal</button>
            </div>
        </form>
    </div>
    @endif

    @if($cloudStorageRequest->status === 'proses')
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-blue-800 mb-3">Berikan Akses Cloud Storage</h3>
        <form action="{{ route('admin.datacenter.cloud-storage.complete', $cloudStorageRequest->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-blue-700 mb-1">Kapasitas Disetujui (GB): <span class="text-red-500">*</span></label>
                        <input type="number" name="kapasitas_disetujui_gb" required min="1"
                               value="{{ old('kapasitas_disetujui_gb', $cloudStorageRequest->kapasitas_gb) }}"
                               class="w-full px-3 py-2 border border-blue-300 rounded-lg">
                        <p class="mt-1 text-xs text-gray-500">Diminta: {{ $cloudStorageRequest->kapasitas_gb }} GB</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-blue-700 mb-1">Alasan Revisi Kapasitas:</label>
                        <textarea name="alasan_kapasitas" rows="2"
                                  class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                                  placeholder="Wajib diisi jika kapasitas disetujui berbeda dari yang diminta. Ditampilkan kepada pemohon.">{{ old('alasan_kapasitas') }}</textarea>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-1">URL Akses: <span class="text-red-500">*</span></label>
                    <input type="text" name="akses_url" required value="{{ old('akses_url') }}"
                           class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                           placeholder="Contoh: https://storage.kaltaraprov.go.id/...">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-blue-700 mb-1">Username: <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required value="{{ old('username') }}" autocomplete="off"
                               class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                               placeholder="Username akun cloud storage">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-blue-700 mb-1">Password: <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required autocomplete="new-password"
                               class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                               placeholder="Password akun cloud storage">
                        <p class="mt-1 text-xs text-gray-500">Disimpan terenkripsi dan hanya ditampilkan kepada pemohon.</p>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-blue-700 mb-1">Keterangan untuk User:</label>
                <textarea name="keterangan_admin" rows="3"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Informasi cara akses, dll (opsional)">{{ old('keterangan_admin', $cloudStorageRequest->keterangan_admin) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-blue-700 mb-1">Catatan untuk Pemohon (opsional):</label>
                <textarea name="admin_notes" rows="2"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg"
                          placeholder="Ditampilkan sebagai &quot;Catatan Admin&quot; di halaman pemohon">{{ old('admin_notes', $cloudStorageRequest->admin_notes) }}</textarea>
            </div>
            <button type="submit" class="mt-3 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                Selesaikan & Berikan Akses
            </button>
        </form>
    </div>
    @endif

    @if($cloudStorageRequest->status === 'selesai')
    <div class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-orange-800 mb-1">Revisi Kapasitas Disetujui</h3>
        <p class="text-xs text-gray-600 mb-3">
            Berlaku saat ini: <span class="font-semibold">{{ $cloudStorageRequest->kapasitas_berlaku_gb }} GB</span>
            (diminta {{ $cloudStorageRequest->kapasitas_gb }} GB). Revisi dan alasannya ditampilkan kepada pemohon.
        </p>
        <form action="{{ route('admin.datacenter.cloud-storage.revise-kapasitas', $cloudStorageRequest->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                <div>
                    <label class="block text-sm font-semibold text-orange-700 mb-1">Kapasitas Baru (GB): <span class="text-red-500">*</span></label>
                    <input type="number" name="kapasitas_disetujui_gb" required min="1"
                           value="{{ old('kapasitas_disetujui_gb') }}"
                           class="w-full px-3 py-2 border border-orange-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-orange-700 mb-1">Alasan Revisi: <span class="text-red-500">*</span></label>
                    <textarea name="alasan_kapasitas" rows="2" required
                              class="w-full px-3 py-2 border border-orange-300 rounded-lg"
                              placeholder="Contoh: Penambahan kuota sesuai kebutuhan arsip tahun 2027">{{ old('alasan_kapasitas') }}</textarea>
                </div>
            </div>
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded font-semibold">
                Simpan Revisi Kapasitas
            </button>
        </form>

        @if($cloudStorageRequest->kapasitasRevisis->isNotEmpty())
        <div class="mt-5 pt-4 border-t border-orange-200">
            <h4 class="text-sm font-semibold text-orange-800 mb-2">Riwayat Revisi</h4>
            <ul class="space-y-2">
                @foreach($cloudStorageRequest->kapasitasRevisis as $revisi)
                <li class="bg-white rounded border border-orange-200 p-3 text-sm">
                    <div class="flex flex-wrap justify-between gap-2">
                        <span class="font-semibold text-gray-800">{{ $revisi->kapasitas_lama_gb }} GB → {{ $revisi->kapasitas_baru_gb }} GB</span>
                        <span class="text-xs text-gray-500">{{ $revisi->created_at->format('d/m/Y H:i') }} WITA · {{ $revisi->revisedBy->name ?? '-' }}</span>
                    </div>
                    <p class="text-gray-700 whitespace-pre-wrap mt-1">{{ $revisi->alasan }}</p>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-green-800 mb-3">Kredensial Akses yang Diberikan</h3>
        <form action="{{ route('admin.datacenter.cloud-storage.update-credentials', $cloudStorageRequest->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-green-700 mb-1">URL Akses: <span class="text-red-500">*</span></label>
                    <input type="text" name="akses_url" required value="{{ old('akses_url', $cloudStorageRequest->akses_url) }}"
                           class="w-full px-3 py-2 border border-green-300 rounded-lg">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-green-700 mb-1">Username: <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required value="{{ old('username', $cloudStorageRequest->username) }}" autocomplete="off"
                               class="w-full px-3 py-2 border border-green-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-green-700 mb-1">Password Baru:</label>
                        <input type="password" name="password" autocomplete="new-password"
                               class="w-full px-3 py-2 border border-green-300 rounded-lg"
                               placeholder="{{ $cloudStorageRequest->password ? 'Kosongkan jika tidak diubah' : 'Belum ada password' }}">
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $cloudStorageRequest->password ? 'Password sudah tersimpan (terenkripsi).' : 'Belum ada password tersimpan.' }}
                        </p>
                    </div>
                </div>
            </div>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                Simpan Kredensial
            </button>
        </form>
    </div>
    @endif

    @if(in_array($cloudStorageRequest->status, ['proses', 'selesai']))
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">Catatan Admin</h3>
        <p class="text-xs text-gray-500 mb-4">Catatan ini ditampilkan kepada pemohon.</p>
        <form action="{{ route('admin.datacenter.cloud-storage.update-notes', $cloudStorageRequest->id) }}" method="POST">
            @csrf
            <textarea name="admin_notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Tambahkan catatan...">{{ $cloudStorageRequest->admin_notes }}</textarea>
            <div class="mt-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold">
                    Update Catatan
                </button>
            </div>
        </form>

        @if($cloudStorageRequest->processedBy)
        <div class="mt-4 pt-4 border-t">
            <p class="text-sm text-gray-600">Diproses oleh: <span class="font-semibold">{{ $cloudStorageRequest->processedBy->name }}</span></p>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
