{{-- Field bersama form tambah & edit running text. --}}
@php
    $rt = $runningText ?? null;
@endphp

@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-5 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-4">
    <div>
        <label for="isi" class="block text-sm font-medium text-gray-700 mb-1">
            Isi Teks <span class="text-red-500">*</span>
        </label>
        <textarea id="isi" name="isi" rows="3" maxlength="500" required
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
            placeholder="Contoh: Layanan permohonan email akan dijeda pada 1 September 2026 pukul 08.00-12.00 WITA.">{{ old('isi', $rt->isi ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">
            Maksimal 500 karakter. Teks polos saja — tag HTML dan skrip akan dibuang otomatis demi keamanan.
        </p>
    </div>

    <div>
        <label for="tautan" class="block text-sm font-medium text-gray-700 mb-1">Tautan (opsional)</label>
        <input type="url" id="tautan" name="tautan" maxlength="255"
            value="{{ old('tautan', $rt->tautan ?? '') }}"
            placeholder="https://contoh.kaltaraprov.go.id/pengumuman"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
        <p class="mt-1 text-xs text-gray-500">Hanya menerima URL http:// atau https://.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="urutan" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
            <input type="number" id="urutan" name="urutan" min="0" max="9999"
                value="{{ old('urutan', $rt->urutan ?? 0) }}"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            <p class="mt-1 text-xs text-gray-500">Angka kecil tampil lebih dulu.</p>
        </div>

        <div>
            <label for="mulai_at" class="block text-sm font-medium text-gray-700 mb-1">Mulai Tayang (opsional)</label>
            <input type="datetime-local" id="mulai_at" name="mulai_at"
                value="{{ old('mulai_at', $rt?->mulai_at?->format('Y-m-d\TH:i')) }}"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
        </div>

        <div>
            <label for="selesai_at" class="block text-sm font-medium text-gray-700 mb-1">Selesai Tayang (opsional)</label>
            <input type="datetime-local" id="selesai_at" name="selesai_at"
                value="{{ old('selesai_at', $rt?->selesai_at?->format('Y-m-d\TH:i')) }}"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1"
            {{ old('is_active', $rt->is_active ?? true) ? 'checked' : '' }}>
        Aktifkan running text ini
    </label>
</div>
