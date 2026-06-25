@php($service = $service ?? null)

@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Layanan</label>
        <input type="text" name="kode_layanan" value="{{ old('kode_layanan', $service->kode_layanan ?? '') }}" placeholder="Otomatis bila kosong"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Layanan/Nama API <span class="text-red-500">*</span></label>
        <input type="text" name="nama_layanan" value="{{ old('nama_layanan', $service->nama_layanan ?? '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">OPD Pemilik</label>
        <select name="opd_pemilik_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">-- Pilih OPD --</option>
            @foreach ($unitKerjaList as $uk)
                <option value="{{ $uk->id }}" @selected(old('opd_pemilik_id', $service->opd_pemilik_id ?? '') == $uk->id)>{{ $uk->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
        <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            @foreach (['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'dicabut' => 'Dicabut'] as $val => $label)
                <option value="{{ $val }}" @selected(old('status', $service->status ?? 'aktif') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
        <textarea name="deskripsi" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('deskripsi', $service->deskripsi ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Backend URL</label>
        <input type="text" name="backend_url" value="{{ old('backend_url', $service->backend_url ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Route Path</label>
        <input type="text" name="route_path" value="{{ old('route_path', $service->route_path ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Environment <span class="text-red-500">*</span></label>
        <select name="environment" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            @foreach (['produksi' => 'Produksi', 'sandbox' => 'Sandbox'] as $val => $label)
                <option value="{{ $val }}" @selected(old('environment', $service->environment ?? 'produksi') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Autentikasi <span class="text-red-500">*</span></label>
        <select name="auth_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            @foreach (['apikey' => 'API Key', 'oauth2' => 'OAuth2', 'none' => 'Tanpa Autentikasi'] as $val => $label)
                <option value="{{ $val }}" @selected(old('auth_type', $service->auth_type ?? 'apikey') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Klasifikasi Data <span class="text-red-500">*</span></label>
        <select name="klasifikasi_data" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            @foreach (['publik' => 'Publik', 'terbatas' => 'Terbatas', 'rahasia' => 'Rahasia'] as $val => $label)
                <option value="{{ $val }}" @selected(old('klasifikasi_data', $service->klasifikasi_data ?? 'publik') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Aktif</label>
        <input type="date" name="tgl_aktif" value="{{ old('tgl_aktif', optional($service->tgl_aktif ?? null)->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Gateway Service ID</label>
        <input type="text" name="gateway_service_id" value="{{ old('gateway_service_id', $service->gateway_service_id ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Gateway Route ID</label>
        <input type="text" name="gateway_route_id" value="{{ old('gateway_route_id', $service->gateway_route_id ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
    </div>
</div>

<div class="flex gap-3 mt-6">
    <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Simpan</button>
    <a href="{{ route('admin.splp.services.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
</div>
