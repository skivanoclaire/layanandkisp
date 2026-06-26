@php($item = $item ?? null)
@php($user = auth()->user())

@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <p class="font-semibold mb-1">Periksa kembali isian berikut:</p>
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

{{-- Data Pemohon --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">1. Data Pemohon</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" value="{{ $user->name }}" disabled class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
            <input type="text" value="{{ $user->nip ?? '-' }}" disabled class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Instansi Pemohon <span class="text-red-500">*</span></label>
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

{{-- Layanan Tujuan --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-1">2. Layanan Tujuan</h2>
    <p class="text-sm text-gray-500 mb-4">Pilih layanan terdaftar yang ingin Anda akses.</p>
    @if ($services->isEmpty())
        <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded text-sm">
            Belum ada layanan produksi aktif yang dapat diakses. Silakan hubungi Admin.
        </div>
    @else
        <label class="block text-sm font-medium text-gray-700 mb-1">Layanan <span class="text-red-500">*</span></label>
        <select name="splp_service_id" id="splp_service_id" required onchange="renderServiceCard()"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('splp_service_id') border-red-500 @enderror">
            <option value="">-- Pilih Layanan Tujuan --</option>
            @foreach ($services as $svc)
                <option value="{{ $svc->id }}"
                        data-opd="{{ $svc->opdPemilik->nama ?? '-' }}"
                        data-klasifikasi="{{ ucfirst($svc->klasifikasi_data) }}"
                        data-auth="{{ strtoupper($svc->auth_type) }}"
                        data-deskripsi="{{ $svc->deskripsi }}"
                        @selected(old('splp_service_id', $item->splp_service_id ?? '') == $svc->id)>
                    {{ $svc->nama_layanan }} — {{ $svc->opdPemilik->nama ?? 'OPD' }} [{{ ucfirst($svc->klasifikasi_data) }}]
                </option>
            @endforeach
        </select>

        <div id="serviceCard" class="mt-4 p-4 rounded-lg border border-gray-200 bg-gray-50 hidden">
            <p class="text-sm text-gray-600"><span class="font-semibold">OPD Pemilik:</span> <span id="cardOpd"></span></p>
            <p class="text-sm text-gray-600"><span class="font-semibold">Klasifikasi Data:</span> <span id="cardKlasifikasi"></span></p>
            <p class="text-sm text-gray-600"><span class="font-semibold">Autentikasi:</span> <span id="cardAuth"></span></p>
            <p class="text-sm text-gray-600 mt-1" id="cardDeskripsiWrap"><span class="font-semibold">Deskripsi:</span> <span id="cardDeskripsi"></span></p>
            <p id="cardRahasiaWarn" class="text-sm text-red-600 mt-2 hidden">⚠ Layanan berklasifikasi Rahasia: lampirkan PKS / persetujuan OPD penyedia.</p>
        </div>
    @endif
</div>

{{-- Instansi Konsumen --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">3. Instansi Konsumen</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Instansi yang akan mengakses</label>
            <select name="instansi_konsumen_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">-- Sama dengan instansi pemohon --</option>
                @foreach ($unitKerjaList as $uk)
                    <option value="{{ $uk->id }}" @selected(old('instansi_konsumen_id', $item->instansi_konsumen_id ?? '') == $uk->id)>{{ $uk->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_eksternal" value="0">
                <input type="checkbox" name="is_eksternal" value="1" @checked(old('is_eksternal', $item->is_eksternal ?? false))>
                <span class="text-sm text-gray-700">Pemohon eksternal (lampirkan PKS/surat kuasa)</span>
            </label>
        </div>
    </div>
</div>

{{-- Detail Akses --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">4. Detail Akses</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">IP / Domain Sumber Akses <span class="text-red-500">*</span></label>
            <textarea name="ip_domain" rows="2" required placeholder="103.10.20.30, api.instansi.go.id"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('ip_domain') border-red-500 @enderror">{{ old('ip_domain', $item->ip_domain ?? '') }}</textarea>
            <p class="text-xs text-gray-500 mt-1">Pisahkan dengan koma jika lebih dari satu.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estimasi Volume</label>
            <div class="flex gap-2">
                <input type="text" name="estimasi_volume" value="{{ old('estimasi_volume', $item->estimasi_volume ?? '') }}" placeholder="1000"
                       class="w-2/3 px-4 py-2 border border-gray-300 rounded-lg">
                <select name="volume_satuan" class="w-1/3 px-2 py-2 border border-gray-300 rounded-lg">
                    <option value="">satuan</option>
                    <option value="per_hari" @selected(old('volume_satuan', $item->volume_satuan ?? '') === 'per_hari')>per hari</option>
                    <option value="per_bulan" @selected(old('volume_satuan', $item->volume_satuan ?? '') === 'per_bulan')>per bulan</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kredensial Diminta</label>
            <select name="credential_pref" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                @foreach (['mengikuti_layanan' => 'Mengikuti ketentuan layanan', 'apikey' => 'API Key', 'oauth2' => 'OAuth2'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('credential_pref', $item->credential_pref ?? 'mengikuti_layanan') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan Penggunaan <span class="text-red-500">*</span></label>
            <textarea name="tujuan_penggunaan" rows="3" required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('tujuan_penggunaan') border-red-500 @enderror">{{ old('tujuan_penggunaan', $item->tujuan_penggunaan ?? '') }}</textarea>
        </div>
    </div>
</div>

{{-- Lampiran --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">5. Lampiran</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @php($files = ['surat_permohonan' => ['Surat Permohonan', $item->surat_permohonan_path ?? null], 'pks' => ['PKS / Surat Kuasa', $item->pks_path ?? null], 'hasil_uji' => ['Hasil Uji Sandbox (opsional)', $item->hasil_uji_path ?? null]])
        @foreach ($files as $name => $meta)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $meta[0] }}</label>
                <input type="file" name="{{ $name }}" class="w-full text-sm border border-gray-300 rounded-lg p-2">
                @if ($meta[1])<p class="text-xs text-green-600 mt-1">Terlampir: {{ basename($meta[1]) }}</p>@endif
            </div>
        @endforeach
    </div>
</div>

{{-- Persetujuan --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <label class="flex items-start gap-2">
        <input type="checkbox" name="consent_true" value="1" @checked(old('consent_true', $item->consent_true ?? false)) class="mt-1">
        <span class="text-sm text-gray-700">Saya menyatakan data yang diisi benar dan menyetujui ketentuan akses layanan SPLP. <span class="text-red-500">*</span></span>
    </label>
    @error('consent_true')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
</div>

<div class="flex flex-wrap gap-3">
    <button type="submit" name="action" value="draft" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2.5 rounded-lg font-semibold">Simpan sebagai Draft</button>
    <button type="submit" name="action" value="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Ajukan Permohonan</button>
    <a href="{{ route('user.splp.consumer.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
</div>

@push('scripts')
<script>
    function renderServiceCard() {
        const sel = document.getElementById('splp_service_id');
        const card = document.getElementById('serviceCard');
        if (!sel || !card) return;
        const opt = sel.options[sel.selectedIndex];
        if (!sel.value) { card.classList.add('hidden'); return; }
        document.getElementById('cardOpd').textContent = opt.dataset.opd || '-';
        document.getElementById('cardKlasifikasi').textContent = opt.dataset.klasifikasi || '-';
        document.getElementById('cardAuth').textContent = opt.dataset.auth || '-';
        const desk = opt.dataset.deskripsi || '';
        document.getElementById('cardDeskripsi').textContent = desk;
        document.getElementById('cardDeskripsiWrap').style.display = desk ? '' : 'none';
        document.getElementById('cardRahasiaWarn').classList.toggle('hidden', (opt.dataset.klasifikasi || '').toLowerCase() !== 'rahasia');
        card.classList.remove('hidden');
    }
    document.addEventListener('DOMContentLoaded', renderServiceCard);
</script>
@endpush
