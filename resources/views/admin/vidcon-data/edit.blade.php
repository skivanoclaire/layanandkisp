@extends('layouts.authenticated')

@section('title', '- Edit Data Vidcon')
@section('header-title', 'Edit Data Vidcon')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Data Fasilitasi Video Konferensi</h1>
        <a href="{{ route('admin.vidcon-data.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.vidcon-data.update', $vidconData) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <p class="text-sm text-blue-700">
                    <strong>Nomor urut:</strong> {{ $vidconData->no }} (otomatis digenerate oleh sistem)
                </p>
            </div>

            {{-- INFORMASI PEMOHON --}}
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b-2 border-purple-500">Informasi Pemohon</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nama Pemohon --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pemohon <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_pemohon" value="{{ old('nama_pemohon', $vidconData->nama_pemohon) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('nama_pemohon') border-red-500 @enderror" required>
                        @error('nama_pemohon')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- NIP Pemohon --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NIP Pemohon <span class="text-red-500">*</span></label>
                        <input type="text" name="nip_pemohon" value="{{ old('nip_pemohon', $vidconData->nip_pemohon) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('nip_pemohon') border-red-500 @enderror" required>
                        @error('nip_pemohon')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email Pemohon --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Pemohon <span class="text-red-500">*</span></label>
                        <input type="email" name="email_pemohon" value="{{ old('email_pemohon', $vidconData->email_pemohon) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('email_pemohon') border-red-500 @enderror" required>
                        @error('email_pemohon')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Instansi --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Instansi <span class="text-red-500">*</span></label>
                        <select name="unit_kerja_id" class="w-full border-gray-300 rounded-md shadow-sm @error('unit_kerja_id') border-red-500 @enderror" required>
                            <option value="">-- Pilih Instansi --</option>
                            @foreach($unitKerjas as $uk)
                                <option value="{{ $uk->id }}" {{ old('unit_kerja_id', $vidconData->unit_kerja_id) == $uk->id ? 'selected' : '' }}>
                                    {{ $uk->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_kerja_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- No HP --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. HP <span class="text-red-500">*</span></label>
                        <input type="text" name="no_hp" value="{{ old('no_hp', $vidconData->no_hp) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('no_hp') border-red-500 @enderror" placeholder="08xxxxxxxxxx" required>
                        @error('no_hp')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nomor Surat --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Surat</label>
                        <input type="text" name="nomor_surat" value="{{ old('nomor_surat', $vidconData->nomor_surat) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('nomor_surat') border-red-500 @enderror">
                        @error('nomor_surat')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- DETAIL KEGIATAN --}}
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b-2 border-purple-500">Detail Kegiatan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Judul Kegiatan --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Kegiatan <span class="text-red-500">*</span></label>
                        <input type="text" name="judul_kegiatan" value="{{ old('judul_kegiatan', $vidconData->judul_kegiatan) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('judul_kegiatan') border-red-500 @enderror" required>
                        @error('judul_kegiatan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Deskripsi Kegiatan --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Kegiatan</label>
                        <textarea name="deskripsi_kegiatan" rows="4" class="w-full border-gray-300 rounded-md shadow-sm @error('deskripsi_kegiatan') border-red-500 @enderror" placeholder="Jelaskan detail kegiatan yang akan dilaksanakan">{{ old('deskripsi_kegiatan', $vidconData->deskripsi_kegiatan) }}</textarea>
                        @error('deskripsi_kegiatan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Lokasi --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                        <input type="text" name="lokasi" value="{{ old('lokasi', $vidconData->lokasi) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('lokasi') border-red-500 @enderror">
                        @error('lokasi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jumlah Peserta --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Peserta (estimasi)</label>
                        <input type="number" name="jumlah_peserta" value="{{ old('jumlah_peserta', $vidconData->jumlah_peserta) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('jumlah_peserta') border-red-500 @enderror" min="1">
                        @error('jumlah_peserta')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Mulai --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai', $vidconData->tanggal_mulai?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('tanggal_mulai') border-red-500 @enderror" required>
                        @error('tanggal_mulai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Selesai --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai', $vidconData->tanggal_selesai?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('tanggal_selesai') border-red-500 @enderror" required>
                        @error('tanggal_selesai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jam Mulai --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_mulai" value="{{ old('jam_mulai', $vidconData->jam_mulai?->format('H:i')) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('jam_mulai') border-red-500 @enderror" required>
                        @error('jam_mulai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jam Selesai --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jam Selesai <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_selesai" value="{{ old('jam_selesai', $vidconData->jam_selesai?->format('H:i')) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('jam_selesai') border-red-500 @enderror" required>
                        @error('jam_selesai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @php
                        $currentPlatform = old('platform', $vidconData->platform);
                        $standardPlatforms = ['Zoom', 'Google Meet', 'Microsoft Teams', 'Cisco Webex', 'YouTube Live'];
                        $isLainnya = !in_array($currentPlatform, $standardPlatforms) && !empty($currentPlatform);
                    @endphp

                    {{-- Platform --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Platform <span class="text-red-500">*</span></label>
                        <select name="platform" id="platform" class="w-full border-gray-300 rounded-md shadow-sm @error('platform') border-red-500 @enderror" required>
                            <option value="">-- Pilih Platform --</option>
                            <option value="Zoom" {{ ($isLainnya ? false : $currentPlatform) == 'Zoom' ? 'selected' : '' }}>Zoom</option>
                            <option value="Google Meet" {{ ($isLainnya ? false : $currentPlatform) == 'Google Meet' ? 'selected' : '' }}>Google Meet</option>
                            <option value="Microsoft Teams" {{ ($isLainnya ? false : $currentPlatform) == 'Microsoft Teams' ? 'selected' : '' }}>Microsoft Teams</option>
                            <option value="Cisco Webex" {{ ($isLainnya ? false : $currentPlatform) == 'Cisco Webex' ? 'selected' : '' }}>Cisco Webex</option>
                            <option value="YouTube Live" {{ ($isLainnya ? false : $currentPlatform) == 'YouTube Live' ? 'selected' : '' }}>YouTube Live</option>
                            <option value="Lainnya" {{ $isLainnya ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('platform')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Platform Lainnya (conditional) --}}
                    <div id="platform_lainnya_wrapper" style="display: {{ $isLainnya ? 'block' : 'none' }};">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sebutkan Platform Lainnya</label>
                        <input type="text" name="platform_lainnya" value="{{ old('platform_lainnya', $isLainnya ? $currentPlatform : '') }}" class="w-full border-gray-300 rounded-md shadow-sm @error('platform_lainnya') border-red-500 @enderror" placeholder="Nama platform">
                        @error('platform_lainnya')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Keperluan Khusus --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keperluan Khusus</label>
                        <textarea name="keperluan_khusus" rows="3" class="w-full border-gray-300 rounded-md shadow-sm @error('keperluan_khusus') border-red-500 @enderror" placeholder="Contoh: Breakout rooms, recording, live streaming, dll.">{{ old('keperluan_khusus', $vidconData->keperluan_khusus) }}</textarea>
                        @error('keperluan_khusus')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- INFORMASI MEETING & OPERATOR --}}
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b-2 border-purple-500">Informasi Meeting & Operator</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Link Meeting --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Link Meeting</label>
                        <input type="text" name="link_meeting" value="{{ old('link_meeting', $vidconData->link_meeting) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('link_meeting') border-red-500 @enderror" placeholder="https://...">
                        @error('link_meeting')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Meeting ID --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meeting ID</label>
                        <input type="text" name="meeting_id" value="{{ old('meeting_id', $vidconData->meeting_id) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('meeting_id') border-red-500 @enderror" placeholder="123 456 7890">
                        @error('meeting_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Meeting Password --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meeting Password</label>
                        <input type="text" name="meeting_password" value="{{ old('meeting_password', $vidconData->meeting_password) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('meeting_password') border-red-500 @enderror">
                        @error('meeting_password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dokumentasi (legacy) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dokumentasi</label>
                        <input type="text" name="dokumentasi" value="{{ old('dokumentasi', $vidconData->dokumentasi) }}" class="w-full border-gray-300 rounded-md shadow-sm @error('dokumentasi') border-red-500 @enderror" placeholder="Link dokumentasi">
                        @error('dokumentasi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Informasi Tambahan --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Informasi Tambahan</label>
                        <textarea name="informasi_tambahan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm @error('informasi_tambahan') border-red-500 @enderror" placeholder="Informasi tambahan terkait meeting">{{ old('informasi_tambahan', $vidconData->informasi_tambahan) }}</textarea>
                        @error('informasi_tambahan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Operator (Multi-select) --}}
                    <div class="md:col-span-2">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-gray-700">Operator (bisa pilih lebih dari 1)</label>
                            <button type="button" id="aiRecommendBtn" class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md shadow-sm transition">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Rekomendasi AI
                            </button>
                        </div>
                        <div id="aiNotification" class="hidden mb-2 p-2 bg-purple-100 border border-purple-200 rounded text-sm text-purple-800"></div>
                        <div class="border border-gray-300 rounded-md p-3 max-h-48 overflow-y-auto">
                            @php
                                $selectedOperators = old('operators', $vidconData->operators->pluck('id')->toArray());
                            @endphp
                            @forelse($operators as $operator)
                                <label class="flex items-center py-2 hover:bg-gray-50 cursor-pointer operator-item" data-operator-id="{{ $operator->id }}">
                                    <input type="checkbox" name="operators[]" value="{{ $operator->id }}"
                                        {{ in_array($operator->id, $selectedOperators) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2 operator-checkbox">
                                    <div class="flex-1">
                                        <span class="text-sm font-medium">{{ $operator->name }}</span>
                                        <span class="text-xs text-gray-500">({{ $operator->email }})</span>
                                        <span class="text-xs text-gray-400 ml-2">
                                            Workload: <span class="font-semibold">{{ $operator->active_vidcon_workload ?? 0 }}</span> aktif /
                                            <span class="font-semibold">{{ $operator->vidcon_workload ?? 0 }}</span> total
                                        </span>
                                    </div>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">Belum ada operator tersedia.</p>
                            @endforelse
                        </div>
                        @error('operators')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- CATATAN & INFORMASI LAINNYA --}}
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b-2 border-purple-500">Catatan & Informasi Lainnya</h2>
                <div class="grid grid-cols-1 gap-6">
                    {{-- Akun Zoom --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Akun Zoom</label>
                        <select name="akun_zoom" id="akun_zoom" class="w-full border-gray-300 rounded-md shadow-sm @error('akun_zoom') border-red-500 @enderror">
                            <option value="">-- Pilih Akun Zoom --</option>
                            <option value="001" {{ old('akun_zoom', $vidconData->akun_zoom) == '001' ? 'selected' : '' }}>Akun 001</option>
                            <option value="002" {{ old('akun_zoom', $vidconData->akun_zoom) == '002' ? 'selected' : '' }}>Akun 002</option>
                            <option value="003" {{ old('akun_zoom', $vidconData->akun_zoom) == '003' ? 'selected' : '' }}>Akun 003</option>
                            <option value="004" {{ old('akun_zoom', $vidconData->akun_zoom) == '004' ? 'selected' : '' }}>Akun 004</option>
                        </select>
                        @error('akun_zoom')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror

                        {{-- Warning area for conflicts --}}
                        <div id="zoom_conflict_warning" class="hidden mt-2 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-yellow-800">Peringatan: Akun Zoom sedang digunakan</p>
                                    <div id="conflict_details" class="text-sm text-yellow-700 mt-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Pimpinan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Informasi Pimpinan</label>
                        <textarea name="informasi_pimpinan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm @error('informasi_pimpinan') border-red-500 @enderror">{{ old('informasi_pimpinan', $vidconData->informasi_pimpinan) }}</textarea>
                        @error('informasi_pimpinan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" rows="3" class="w-full border-gray-300 rounded-md shadow-sm @error('keterangan') border-red-500 @enderror">{{ old('keterangan', $vidconData->keterangan) }}</textarea>
                        @error('keterangan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Hidden field for backward compatibility --}}
            <input type="hidden" name="operator" value="{{ old('operator', $vidconData->operator) }}">

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('admin.vidcon-data.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Platform toggle
    document.getElementById('platform').addEventListener('change', function() {
        const wrapper = document.getElementById('platform_lainnya_wrapper');
        if (this.value === 'Lainnya') {
            wrapper.style.display = 'block';
        } else {
            wrapper.style.display = 'none';
        }
    });

    // Date validation - tanggal selesai tidak boleh lebih kecil dari tanggal mulai
    document.getElementById('tanggal_mulai').addEventListener('change', validateDates);
    document.getElementById('tanggal_selesai').addEventListener('change', validateDates);

    function validateDates() {
        const startDate = document.getElementById('tanggal_mulai').value;
        const endDate = document.getElementById('tanggal_selesai').value;

        if (startDate && endDate && endDate < startDate) {
            alert('Tanggal selesai tidak boleh lebih kecil dari tanggal mulai');
            document.getElementById('tanggal_selesai').value = '';
        }
    }

    // AI Recommendation for Operators
    document.getElementById('aiRecommendBtn')?.addEventListener('click', function() {
        const recommendedIds = @json($recommendedOperatorIds ?? []);
        const checkboxes = document.querySelectorAll('.operator-checkbox');
        const notification = document.getElementById('aiNotification');

        if (recommendedIds.length === 0) {
            notification.textContent = 'Tidak ada rekomendasi operator tersedia.';
            notification.classList.remove('hidden', 'bg-purple-100', 'text-purple-800');
            notification.classList.add('bg-yellow-100', 'text-yellow-800');
            setTimeout(() => notification.classList.add('hidden'), 3000);
            return;
        }

        // Uncheck all first
        checkboxes.forEach(cb => cb.checked = false);

        // Check recommended operators
        let selectedCount = 0;
        recommendedIds.forEach(id => {
            const checkbox = document.querySelector(`.operator-checkbox[value="${id}"]`);
            if (checkbox) {
                checkbox.checked = true;
                selectedCount++;

                // Add brief highlight animation
                const item = checkbox.closest('.operator-item');
                if (item) {
                    item.classList.add('bg-purple-50');
                    setTimeout(() => item.classList.remove('bg-purple-50'), 1000);
                }
            }
        });

        // Show notification
        notification.textContent = `✨ ${selectedCount} operator terbaik telah dipilih berdasarkan workload!`;
        notification.classList.remove('hidden');
        setTimeout(() => notification.classList.add('hidden'), 3000);
    });

    // Check Zoom account conflict
    function checkZoomAccountConflict() {
        const platform = document.getElementById('platform')?.value;
        const akunZoomSelect = document.getElementById('akun_zoom');
        const akunZoom = akunZoomSelect?.value;
        const tanggalMulai = document.getElementById('tanggal_mulai')?.value;
        const tanggalSelesai = document.getElementById('tanggal_selesai')?.value;
        const jamMulai = document.querySelector('input[name="jam_mulai"]')?.value;
        const jamSelesai = document.querySelector('input[name="jam_selesai"]')?.value;
        const submitButton = document.querySelector('button[type="submit"]');

        // Clear previous warning and re-enable submit
        const warningDiv = document.getElementById('zoom_conflict_warning');
        const conflictDetails = document.getElementById('conflict_details');
        warningDiv?.classList.add('hidden');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        // Reset akun zoom if platform is not Zoom
        if (platform !== 'Zoom') {
            if (akunZoomSelect) {
                akunZoomSelect.value = '';
            }
            return;
        }

        // Skip if required fields are empty
        if (!akunZoom || !tanggalMulai || !tanggalSelesai || !jamMulai || !jamSelesai) {
            return;
        }

        // AJAX call to check conflict
        fetch('{{ route("admin.vidcon-data.check-zoom-conflict") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                akun_zoom: akunZoom,
                tanggal_mulai: tanggalMulai,
                tanggal_selesai: tanggalSelesai,
                jam_mulai: jamMulai,
                jam_selesai: jamSelesai,
                exclude_id: {{ $vidconData->id }} // For edit mode - exclude current record
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.has_conflict && data.conflicts.length > 0) {
                // Show warning
                warningDiv?.classList.remove('hidden');

                // Disable submit button
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                }

                // Build conflict message
                let message = '<p class="mb-2 font-semibold text-red-700">Tidak dapat menyimpan karena ada konflik jadwal!</p>';
                data.conflicts.forEach((conflict, index) => {
                    message += `<div class="mb-2 ${index > 0 ? 'mt-2 pt-2 border-t border-yellow-300' : ''}">`;
                    message += `<strong>Akun ${conflict.akun_zoom}</strong> telah digunakan pada:<br>`;
                    message += `- Judul: ${conflict.judul_kegiatan}<br>`;
                    message += `- Tanggal: ${conflict.tanggal_mulai} s/d ${conflict.tanggal_selesai}<br>`;
                    message += `- Jam: ${conflict.jam_mulai} - ${conflict.jam_selesai}`;
                    message += `</div>`;
                });
                message += '<p class="mt-2 text-sm italic text-yellow-800">Silakan pilih akun Zoom yang berbeda atau ubah tanggal/jam kegiatan.</p>';

                conflictDetails.innerHTML = message;
            }
        })
        .catch(error => {
            console.error('Error checking conflict:', error);
        });
    }

    // Trigger conflict check on field changes
    document.getElementById('platform')?.addEventListener('change', checkZoomAccountConflict);
    document.getElementById('akun_zoom')?.addEventListener('change', checkZoomAccountConflict);
    document.getElementById('tanggal_mulai')?.addEventListener('change', checkZoomAccountConflict);
    document.getElementById('tanggal_selesai')?.addEventListener('change', checkZoomAccountConflict);
    document.querySelector('input[name="jam_mulai"]')?.addEventListener('change', checkZoomAccountConflict);
    document.querySelector('input[name="jam_selesai"]')?.addEventListener('change', checkZoomAccountConflict);
</script>
@endpush
@endsection
