{{--
    Pilihan Akun Zoom (001-004) pada halaman permohonan vidcon.
    Nilainya ikut tersalin ke Data Fasilitasi Vidcon saat permohonan disetujui,
    sehingga admin tidak perlu mengeditnya manual di /admin/vidcon-data/{id}/edit.

    Variabel: $item (VidconRequest), $labelClass, $borderClass, $note
--}}
@php
    $isZoomPlatform = str_contains(strtolower($item->platform_display ?? $item->platform ?? ''), 'zoom');
@endphp
<div class="mb-3">
    <label class="block text-sm font-semibold {{ $labelClass }} mb-1">Akun Zoom:</label>
    <select name="akun_zoom"
            class="akun-zoom-select w-full px-3 py-2 border {{ $borderClass }} rounded-lg focus:ring-2">
        <option value="">-- Pilih Akun Zoom --</option>
        @foreach (['001', '002', '003', '004'] as $akun)
            <option value="{{ $akun }}" {{ old('akun_zoom', $item->akun_zoom) === $akun ? 'selected' : '' }}>
                Akun {{ $akun }}
            </option>
        @endforeach
    </select>
    @error('akun_zoom')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
    <p class="text-xs text-gray-600 mt-1">
        {{ $note }}
        @unless ($isZoomPlatform)
            <span class="text-gray-500">Platform permohonan ini <strong>{{ $item->platform_display }}</strong> — isi hanya bila memang memakai akun Zoom Diskominfo.</span>
        @endunless
    </p>

    {{-- Peringatan bentrok jadwal akun Zoom --}}
    <div class="zoom-conflict-warning hidden mt-2 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-yellow-800">Peringatan: Akun Zoom sedang digunakan</p>
                <div class="zoom-conflict-details text-sm text-yellow-700 mt-1"></div>
            </div>
        </div>
    </div>
</div>
