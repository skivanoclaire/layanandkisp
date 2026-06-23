@extends('layouts.authenticated')
@section('title', '- Permohonan Video Conference')
@section('header-title', 'Permohonan Video Conference')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 text-purple-700">Formulir Permohonan Video Conference</h1>

    @php
        $profileMissing = [];
        if (empty(auth()->user()->unit_kerja_id)) $profileMissing[] = 'Instansi';
        if (empty(auth()->user()->phone)) $profileMissing[] = 'No. HP/WhatsApp';
    @endphp
    @if(count($profileMissing) > 0)
        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm">
                    <p class="text-yellow-800 font-semibold mb-1">Data profil belum lengkap</p>
                    <p class="text-yellow-700">
                        {{ implode(' dan ', $profileMissing) }} belum diisi di profil Anda. Permohonan tidak bisa dikirim sampai data ini lengkap.
                        <a href="{{ route('profile.edit') }}" class="underline font-semibold">Lengkapi profil sekarang</a>.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Operating Hours -->
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm">
                <p class="text-blue-700 font-semibold mb-1">Jam Layanan:</p>
                <p class="text-blue-700">
                    Senin s.d. Kamis: 07.30 - 16.00 WITA<br>
                    Jumat: 07.30 - 16.30 WITA
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('user.vidcon.store') }}" method="POST">
            @csrf

            <!-- Informasi Pemohon -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Pemohon</h2>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text"
                           value="{{ auth()->user()->name }}"
                           disabled
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">NIP</label>
                    <input type="text"
                           value="{{ auth()->user()->nip }}"
                           disabled
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="text"
                           value="{{ auth()->user()->email }}"
                           disabled
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instansi</label>
                    <input type="text"
                           value="{{ auth()->user()->unitKerja->nama ?? '-' }}"
                           readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500">Diambil dari profil. Hubungi admin jika perlu perubahan.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">No. HP/WhatsApp</label>
                    <input type="text"
                           value="{{ auth()->user()->phone }}"
                           readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500">Diambil dari profil. Update di halaman Profil jika berbeda.</p>
                </div>
            </div>

            <!-- Detail Kegiatan -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Detail Kegiatan</h2>

                <div class="mb-4">
                    <label for="judul_kegiatan" class="block text-sm font-semibold text-gray-700 mb-2">
                        Judul Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="judul_kegiatan"
                           name="judul_kegiatan"
                           value="{{ old('judul_kegiatan') }}"
                           required
                           placeholder="Contoh: Rapat Koordinasi Tahunan"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('judul_kegiatan') border-red-500 @enderror">
                    @error('judul_kegiatan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="deskripsi_kegiatan" class="block text-sm font-semibold text-gray-700 mb-2">
                        Deskripsi Kegiatan
                    </label>
                    <textarea id="deskripsi_kegiatan"
                              name="deskripsi_kegiatan"
                              rows="3"
                              placeholder="Jelaskan detail kegiatan secara singkat"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('deskripsi_kegiatan') border-red-500 @enderror">{{ old('deskripsi_kegiatan') }}</textarea>
                    @error('deskripsi_kegiatan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="lokasi_kegiatan" class="block text-sm font-semibold text-gray-700 mb-2">
                        Lokasi Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="lokasi_kegiatan"
                           name="lokasi_kegiatan"
                           value="{{ old('lokasi_kegiatan') }}"
                           required
                           placeholder="Contoh: Ruang Rapat Lantai 3, Kantor Diskominfo Kaltara"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('lokasi_kegiatan') border-red-500 @enderror">
                    @error('lokasi_kegiatan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="tanggal_mulai"
                               name="tanggal_mulai"
                               value="{{ old('tanggal_mulai') }}"
                               min="{{ date('Y-m-d') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('tanggal_mulai') border-red-500 @enderror">
                        @error('tanggal_mulai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="tanggal_selesai"
                               name="tanggal_selesai"
                               value="{{ old('tanggal_selesai') }}"
                               min="{{ date('Y-m-d') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('tanggal_selesai') border-red-500 @enderror">
                        @error('tanggal_selesai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="jam_mulai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time"
                               id="jam_mulai"
                               name="jam_mulai"
                               value="{{ old('jam_mulai') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('jam_mulai') border-red-500 @enderror">
                        @error('jam_mulai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_selesai" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time"
                               id="jam_selesai"
                               name="jam_selesai"
                               value="{{ old('jam_selesai') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('jam_selesai') border-red-500 @enderror">
                        @error('jam_selesai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="platform" class="block text-sm font-semibold text-gray-700 mb-2">
                        Platform <span class="text-red-500">*</span>
                    </label>
                    <select id="platform"
                            name="platform"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('platform') border-red-500 @enderror">
                        <option value="">-- Pilih Platform --</option>
                        <option value="Zoom" {{ old('platform') == 'Zoom' ? 'selected' : '' }}>Zoom</option>
                        <option value="Google Meet" {{ old('platform') == 'Google Meet' ? 'selected' : '' }}>Google Meet</option>
                        <option value="Microsoft Teams" {{ old('platform') == 'Microsoft Teams' ? 'selected' : '' }}>Microsoft Teams</option>
                        <option value="YouTube Live" {{ old('platform') == 'YouTube Live' ? 'selected' : '' }}>YouTube Live</option>
                        <option value="Lainnya" {{ old('platform') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('platform')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="platform_lainnya_wrapper" style="display: none;" class="mb-4">
                    <label for="platform_lainnya" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Platform Lain <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="platform_lainnya"
                           name="platform_lainnya"
                           value="{{ old('platform_lainnya') }}"
                           placeholder="Contoh: Webex, Jitsi, dll"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('platform_lainnya') border-red-500 @enderror">
                    @error('platform_lainnya')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Jenis Layanan <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        @php
                            $jenisLayananOptions = [
                                'link_host'          => 'Link Host saja',
                                'link_host_operator' => 'Link Host + Operator',
                                'operator'           => 'Operator saja',
                            ];
                        @endphp
                        @foreach($jenisLayananOptions as $value => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio"
                                       name="jenis_layanan"
                                       value="{{ $value }}"
                                       required
                                       {{ old('jenis_layanan') == $value ? 'checked' : '' }}
                                       class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500">
                                <span class="text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('jenis_layanan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="pemohon_meeting_wrapper" style="display: none;" class="mb-4 bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <p class="text-sm text-purple-700 font-semibold mb-3">
                        Informasi Meeting dari Pemohon
                    </p>
                    <p class="text-xs text-purple-600 mb-4">
                        Karena Anda memilih <strong>Operator saja</strong>, masukkan link/ID meeting yang sudah Anda peroleh (misalnya dari Pusat). Informasi ini akan digunakan oleh operator.
                    </p>

                    <div class="mb-4">
                        <label for="pemohon_link_meeting" class="block text-sm font-semibold text-gray-700 mb-2">
                            Link Meeting <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="pemohon_link_meeting"
                               name="pemohon_link_meeting"
                               value="{{ old('pemohon_link_meeting') }}"
                               placeholder="Contoh: https://zoom.us/j/123456789"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('pemohon_link_meeting') border-red-500 @enderror">
                        @error('pemohon_link_meeting')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="pemohon_meeting_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Meeting ID
                            </label>
                            <input type="text"
                                   id="pemohon_meeting_id"
                                   name="pemohon_meeting_id"
                                   value="{{ old('pemohon_meeting_id') }}"
                                   placeholder="Contoh: 123 456 789"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('pemohon_meeting_id') border-red-500 @enderror">
                            @error('pemohon_meeting_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="pemohon_meeting_password" class="block text-sm font-semibold text-gray-700 mb-2">
                                Passcode
                            </label>
                            <input type="text"
                                   id="pemohon_meeting_password"
                                   name="pemohon_meeting_password"
                                   value="{{ old('pemohon_meeting_password') }}"
                                   placeholder="Contoh: abc123"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('pemohon_meeting_password') border-red-500 @enderror">
                            @error('pemohon_meeting_password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="jumlah_peserta" class="block text-sm font-semibold text-gray-700 mb-2">
                        Estimasi Jumlah Peserta
                    </label>
                    <input type="number"
                           id="jumlah_peserta"
                           name="jumlah_peserta"
                           value="{{ old('jumlah_peserta') }}"
                           min="1"
                           placeholder="Contoh: 50"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('jumlah_peserta') border-red-500 @enderror">
                    @error('jumlah_peserta')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="keperluan_khusus" class="block text-sm font-semibold text-gray-700 mb-2">
                        Keperluan Khusus
                    </label>
                    <textarea id="keperluan_khusus"
                              name="keperluan_khusus"
                              rows="3"
                              placeholder="Contoh: Memerlukan recording, breakout rooms, dll"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 @error('keperluan_khusus') border-red-500 @enderror">{{ old('keperluan_khusus') }}</textarea>
                    @error('keperluan_khusus')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold">
                    Kirim Permohonan
                </button>
                <a href="{{ route('user.vidcon.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const platformSelect = document.getElementById('platform');
    const platformLainnyaWrapper = document.getElementById('platform_lainnya_wrapper');
    const platformLainnyaInput = document.getElementById('platform_lainnya');

    function togglePlatformLainnya() {
        if (platformSelect.value === 'Lainnya') {
            platformLainnyaWrapper.style.display = 'block';
            platformLainnyaInput.required = true;
        } else {
            platformLainnyaWrapper.style.display = 'none';
            platformLainnyaInput.required = false;
        }
    }

    platformSelect.addEventListener('change', togglePlatformLainnya);

    // Check on page load (for old input)
    togglePlatformLainnya();

    // Toggle "Informasi Meeting dari Pemohon" when "Operator saja" is chosen
    const jenisLayananRadios = document.querySelectorAll('input[name="jenis_layanan"]');
    const pemohonMeetingWrapper = document.getElementById('pemohon_meeting_wrapper');
    const pemohonLinkMeeting = document.getElementById('pemohon_link_meeting');

    function togglePemohonMeeting() {
        const selected = document.querySelector('input[name="jenis_layanan"]:checked');
        const isOperatorOnly = selected && selected.value === 'operator';
        pemohonMeetingWrapper.style.display = isOperatorOnly ? 'block' : 'none';
        pemohonLinkMeeting.required = isOperatorOnly;
    }

    jenisLayananRadios.forEach(radio => radio.addEventListener('change', togglePemohonMeeting));
    togglePemohonMeeting();

    // Auto-set tanggal_selesai to match tanggal_mulai
    const tanggalMulai = document.getElementById('tanggal_mulai');
    const tanggalSelesai = document.getElementById('tanggal_selesai');

    tanggalMulai.addEventListener('change', function() {
        if (!tanggalSelesai.value || tanggalSelesai.value < tanggalMulai.value) {
            tanggalSelesai.value = tanggalMulai.value;
        }
        tanggalSelesai.min = tanggalMulai.value;
    });
});
</script>
@endsection
