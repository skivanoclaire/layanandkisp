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
            <select name="unit_kerja_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">-- Pilih Instansi --</option>
                @foreach ($unitKerjaList as $uk)
                    <option value="{{ $uk->id }}" @selected(old('unit_kerja_id', $item->unit_kerja_id ?? $user->unit_kerja_id) == $uk->id)>{{ $uk->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No. HP <span class="text-red-500">*</span></label>
            <input type="text" name="no_hp" value="{{ old('no_hp', $item->no_hp ?? $user->phone) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border p-6 mb-6" x-data="{ target: '{{ old('target_type', $item->target_type ?? 'service') }}' }">
    <h2 class="text-lg font-bold text-gray-800 mb-4">2. Objek & Tindakan</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Objek <span class="text-red-500">*</span></label>
            <select name="target_type" x-model="target" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="service">Layanan / Endpoint</option>
                <option value="consumer">Akses Konsumen</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tindakan <span class="text-red-500">*</span></label>
            <select name="jenis_tindakan" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="nonaktif" @selected(old('jenis_tindakan', $item->jenis_tindakan ?? 'nonaktif') === 'nonaktif')>Nonaktifkan (sementara)</option>
                <option value="cabut" @selected(old('jenis_tindakan', $item->jenis_tindakan ?? '') === 'cabut')>Cabut (permanen)</option>
            </select>
        </div>

        <div class="md:col-span-2" x-show="target === 'service'" x-cloak>
            <label class="block text-sm font-medium text-gray-700 mb-1">Layanan Target</label>
            <select name="splp_service_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">-- Pilih Layanan --</option>
                @foreach ($services as $svc)
                    <option value="{{ $svc->id }}" @selected(old('splp_service_id', $item->splp_service_id ?? '') == $svc->id)>{{ $svc->nama_layanan }} ({{ $svc->kode_layanan }}) — {{ ucfirst($svc->status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2" x-show="target === 'consumer'" x-cloak>
            <label class="block text-sm font-medium text-gray-700 mb-1">Konsumen Target</label>
            <select name="splp_consumer_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">-- Pilih Konsumen --</option>
                @foreach ($consumers as $c)
                    <option value="{{ $c->id }}" @selected(old('splp_consumer_id', $item->splp_consumer_id ?? '') == $c->id)>{{ $c->nama_konsumen }} → {{ $c->service->nama_layanan ?? '-' }} ({{ ucfirst($c->status) }})</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan <span class="text-red-500">*</span></label>
            <textarea name="alasan" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('alasan', $item->alasan ?? '') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_darurat" value="0">
                <input type="checkbox" name="is_darurat" value="1" @checked(old('is_darurat', $item->is_darurat ?? false))>
                <span class="text-sm text-gray-700">Insiden keamanan — jalur darurat (penonaktifan dapat dieksekusi lebih dahulu)</span>
            </label>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Surat / Rekomendasi (opsional)</label>
            <input type="file" name="surat" accept=".pdf,.doc,.docx" class="w-full text-sm border border-gray-300 rounded-lg p-2">
            @if ($item && $item->surat_path)<p class="text-xs text-green-600 mt-1">Terlampir: {{ basename($item->surat_path) }}</p>@endif
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <label class="flex items-start gap-2">
        <input type="checkbox" name="consent_true" value="1" @checked(old('consent_true', $item->consent_true ?? false)) class="mt-1">
        <span class="text-sm text-gray-700">Saya memahami penonaktifan/pencabutan dapat berdampak pada konsumen lain dan akan diarsipkan. <span class="text-red-500">*</span></span>
    </label>
    @error('consent_true')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
</div>

<div class="flex flex-wrap gap-3">
    <button type="submit" name="action" value="draft" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2.5 rounded-lg font-semibold">Simpan sebagai Draft</button>
    <button type="submit" name="action" value="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Ajukan Permohonan</button>
    <a href="{{ route('user.splp.deactivation.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
</div>
