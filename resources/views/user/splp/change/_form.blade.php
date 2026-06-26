@php($item = $item ?? null)
@php($user = auth()->user())

@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">1. Data Pemohon</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" value="{{ $user->name }}" disabled class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600">
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
            <input type="text" name="no_hp" value="{{ old('no_hp', $item->no_hp ?? $user->phone) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border p-6 mb-6" x-data="{ kategori: '{{ old('kategori', $item->kategori ?? 'perubahan') }}' }">
    <h2 class="text-lg font-bold text-gray-800 mb-4">2. Detail Perubahan / Perpanjangan</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Layanan / Endpoint Terdaftar <span class="text-red-500">*</span></label>
            <select name="splp_service_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">-- Pilih Layanan Terdaftar --</option>
                @foreach ($services as $svc)
                    <option value="{{ $svc->id }}" @selected(old('splp_service_id', $item->splp_service_id ?? '') == $svc->id)>{{ $svc->nama_layanan }} ({{ $svc->kode_layanan }}) — {{ $svc->opdPemilik->nama ?? 'OPD' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
            <select name="kategori" x-model="kategori" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="perubahan">Perubahan Konfigurasi</option>
                <option value="perpanjangan">Perpanjangan</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Perubahan <span class="text-red-500">*</span></label>
            <select name="jenis_perubahan" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="minor" @selected(old('jenis_perubahan', $item->jenis_perubahan ?? 'minor') === 'minor')>Minor (mis. rotasi key, kuota kecil)</option>
                <option value="signifikan" @selected(old('jenis_perubahan', $item->jenis_perubahan ?? '') === 'signifikan')>Signifikan</option>
            </select>
        </div>
        <div x-show="kategori === 'perpanjangan'" x-cloak>
            <label class="block text-sm font-medium text-gray-700 mb-1">Perpanjangan Sampai</label>
            <input type="date" name="perpanjangan_sampai" value="{{ old('perpanjangan_sampai', optional($item->perpanjangan_sampai ?? null)->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Detail Perubahan <span class="text-red-500">*</span></label>
            <textarea name="detail_perubahan" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('detail_perubahan', $item->detail_perubahan ?? '') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Surat Permohonan (opsional)</label>
            <input type="file" name="surat" accept=".pdf,.doc,.docx" class="w-full text-sm border border-gray-300 rounded-lg p-2">
            @if ($item && $item->surat_path)<p class="text-xs text-green-600 mt-1">Terlampir: {{ basename($item->surat_path) }}</p>@endif
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <label class="flex items-start gap-2">
        <input type="checkbox" name="consent_true" value="1" @checked(old('consent_true', $item->consent_true ?? false)) class="mt-1">
        <span class="text-sm text-gray-700">Saya memahami perubahan konfigurasi dapat berdampak pada konsumen lain dan akan tercatat dalam audit trail. <span class="text-red-500">*</span></span>
    </label>
    @error('consent_true')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
</div>

<div class="flex flex-wrap gap-3">
    <button type="submit" name="action" value="draft" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2.5 rounded-lg font-semibold">Simpan sebagai Draft</button>
    <button type="submit" name="action" value="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Ajukan Permohonan</button>
    <a href="{{ route('user.splp.change.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
</div>
