@php($item = $item ?? null)
@php($user = auth()->user())

@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <p class="font-semibold mb-1">Periksa kembali isian berikut:</p>
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Data Pemohon --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">1. Data Pemohon</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" value="{{ $user->name }}" disabled
                   class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
            <input type="text" value="{{ $user->nip ?? '-' }}" disabled
                   class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Instansi <span class="text-red-500">*</span></label>
            @php($selectedUnitKerjaId = old('unit_kerja_id', $item->unit_kerja_id ?? $user->unit_kerja_id))
            <input type="text" value="{{ optional($unitKerjaList->firstWhere('id', $selectedUnitKerjaId))->nama ?? ($user->unitKerja?->nama ?? '-') }}" disabled
                   class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600">
            <input type="hidden" name="unit_kerja_id" value="{{ $selectedUnitKerjaId }}">
            <p class="text-xs text-gray-500 mt-1">Instansi diambil dari data akun Anda</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No. HP <span class="text-red-500">*</span></label>
            <input type="text" name="no_hp" value="{{ old('no_hp', $item->no_hp ?? $user->phone) }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('no_hp') border-red-500 @enderror">
        </div>
    </div>
</div>

{{-- Detail Layanan --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">2. Detail Endpoint Penyedia Layanan</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Layanan <span class="text-red-500">*</span></label>
            <input type="text" name="nama_layanan" value="{{ old('nama_layanan', $item->nama_layanan ?? '') }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('nama_layanan') border-red-500 @enderror">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Layanan</label>
            <textarea name="deskripsi" rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('deskripsi', $item->deskripsi ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Backend URL <span class="text-red-500">*</span></label>
            <input type="url" name="backend_url" placeholder="https://api.instansi.go.id" value="{{ old('backend_url', $item->backend_url ?? '') }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('backend_url') border-red-500 @enderror">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Route Path (opsional)</label>
            <input type="text" name="route_path" placeholder="/api/v1/layanan" value="{{ old('route_path', $item->route_path ?? '') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Autentikasi <span class="text-red-500">*</span></label>
            <select name="auth_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                @foreach (['apikey' => 'API Key', 'oauth2' => 'OAuth2', 'none' => 'Tanpa Autentikasi'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('auth_type', $item->auth_type ?? 'apikey') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Klasifikasi Data <span class="text-red-500">*</span></label>
            <select name="klasifikasi_data" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                @foreach (['publik' => 'Publik', 'terbatas' => 'Terbatas', 'rahasia' => 'Rahasia'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('klasifikasi_data', $item->klasifikasi_data ?? 'publik') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- Klasifikasi Keamanan Data (opsional) --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-1">3. Klasifikasi Keamanan Data (opsional)</h2>
    <p class="text-sm text-gray-500 mb-4">Tingkat Kerahasiaan, Integritas, dan Ketersediaan data layanan.</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach (['dc_confidentiality' => 'Kerahasiaan', 'dc_integrity' => 'Integritas', 'dc_availability' => 'Ketersediaan'] as $field => $label)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                <select name="{{ $field }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">-- Pilih --</option>
                    @foreach (['Rendah', 'Sedang', 'Tinggi'] as $opt)
                        <option value="{{ $opt }}" @selected(old($field, $item->{$field} ?? '') === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</div>

{{-- Lampiran --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">4. Lampiran</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Surat Permohonan (PDF/DOC, maks 10MB)</label>
            <input type="file" name="surat_permohonan" accept=".pdf,.doc,.docx"
                   class="w-full text-sm border border-gray-300 rounded-lg p-2">
            @if ($item && $item->surat_permohonan_path)
                <p class="text-xs text-green-600 mt-1">Terlampir: {{ basename($item->surat_permohonan_path) }}</p>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dokumentasi API — OpenAPI/Swagger (maks 10MB)</label>
            <input type="file" name="openapi_doc" accept=".pdf,.doc,.docx,.json,.yaml,.yml,.zip"
                   class="w-full text-sm border border-gray-300 rounded-lg p-2">
            @if ($item && $item->openapi_doc_path)
                <p class="text-xs text-green-600 mt-1">Terlampir: {{ basename($item->openapi_doc_path) }}</p>
            @endif
        </div>
    </div>
</div>

{{-- Persetujuan --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <label class="flex items-start gap-2">
        <input type="checkbox" name="consent_true" value="1" @checked(old('consent_true', $item->consent_true ?? false)) class="mt-1">
        <span class="text-sm text-gray-700">Saya menyatakan data yang diisi benar dan menyetujui ketentuan layanan integrasi SPLP. <span class="text-red-500">*</span></span>
    </label>
    @error('consent_true')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
</div>

<div class="flex flex-wrap gap-3">
    <button type="submit" name="action" value="draft"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2.5 rounded-lg font-semibold">
        Simpan sebagai Draft
    </button>
    <button type="submit" name="action" value="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">
        Ajukan Permohonan
    </button>
    <a href="{{ route('user.splp.provider.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
</div>
