@php($consumer = $consumer ?? null)

@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Layanan <span class="text-red-500">*</span></label>
        <select name="splp_service_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">-- Pilih Layanan --</option>
            @foreach ($services as $svc)
                <option value="{{ $svc->id }}" @selected(old('splp_service_id', $consumer->splp_service_id ?? '') == $svc->id)>{{ $svc->nama_layanan }} ({{ $svc->kode_layanan }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Konsumen <span class="text-red-500">*</span></label>
        <input type="text" name="nama_konsumen" value="{{ old('nama_konsumen', $consumer->nama_konsumen ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Instansi Konsumen</label>
        <select name="instansi_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">-- Pilih Instansi --</option>
            @foreach ($unitKerjaList as $uk)
                <option value="{{ $uk->id }}" @selected(old('instansi_id', $consumer->instansi_id ?? '') == $uk->id)>{{ $uk->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kredensial <span class="text-red-500">*</span></label>
        <select name="credential_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            @foreach (['apikey' => 'API Key', 'oauth2' => 'OAuth2'] as $val => $label)
                <option value="{{ $val }}" @selected(old('credential_type', $consumer->credential_type ?? 'apikey') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Referensi Kredensial</label>
        <input type="text" name="credential_ref" value="{{ old('credential_ref', $consumer->credential_ref ?? '') }}" placeholder="ID/ref (bukan secret)" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Environment <span class="text-red-500">*</span></label>
        <select name="environment" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            @foreach (['produksi' => 'Produksi', 'sandbox' => 'Sandbox'] as $val => $label)
                <option value="{{ $val }}" @selected(old('environment', $consumer->environment ?? 'produksi') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Rate Limit</label>
        <input type="text" name="rate_limit" value="{{ old('rate_limit', $consumer->rate_limit ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">ACL</label>
        <input type="text" name="acl" value="{{ old('acl', $consumer->acl ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">IP Whitelist</label>
        <textarea name="ip_whitelist" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('ip_whitelist', $consumer->ip_whitelist ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Masa Berlaku</label>
        <input type="date" name="expires_at" value="{{ old('expires_at', optional($consumer->expires_at ?? null)->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
        <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            @foreach (['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'dicabut' => 'Dicabut', 'kadaluarsa' => 'Kadaluarsa'] as $val => $label)
                <option value="{{ $val }}" @selected(old('status', $consumer->status ?? 'aktif') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="flex gap-3 mt-6">
    <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Simpan</button>
    <a href="{{ route('admin.splp.consumers.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
</div>
