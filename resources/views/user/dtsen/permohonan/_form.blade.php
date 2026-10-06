@php
    // Catatan: seluruh perhitungan awal disatukan dalam satu blok PHP ini.
    // Bentuk PHP inline tidak boleh dipakai sebelum blok PHP mana pun, karena
    // pola raw-block Blade akan menelan seluruh isi di antara keduanya.
    $item = $item ?? null;
    $user = auth()->user();
    $parent = $parent ?? null;

    // Identitas pemohon mengikuti profil; kolom yang profilnya sudah terisi dikunci
    // di sini dan ditimpa lagi saat validasi di controller.
    $profil = \App\Services\Dtsen\ProfilPemohon::untuk($user);

    // Nilai awal untuk state Alpine: variabel terpilih beserta kegunaannya.
    // Permintaan ulang (Form 4.5) mewarisi pilihan variabel permohonan sebelumnya.
    $sumber = $item ?? $parent;
    $selectedInit = old('variabel', $sumber?->requestVariables->pluck('dtsen_variable_id')->all() ?? []);
    $kegunaanInit = old('kegunaan', $sumber?->requestVariables->pluck('kegunaan', 'dtsen_variable_id')->all() ?? []);
    $levelMap = \App\Models\DtsenVariable::active()->pluck('level_minimal', 'id');
    $kombinasiDataPribadi = \App\Models\DtsenVariable::kombinasiDataPribadiIds();
    $selectedIds = array_map('intval', (array) $selectedInit);
    $setLabels = \App\Models\DtsenVariable::setLabels();
    $setDescriptions = \App\Models\DtsenVariable::setDescriptions();
    $sensitivitasLabels = \App\Models\DtsenVariable::sensitivitasLabels();
    $sensitivitasBadge = [
        'data_pribadi' => 'bg-red-100 text-red-700',
        'quasi_identifier' => 'bg-amber-100 text-amber-700',
        'terbuka' => 'bg-blue-100 text-blue-700',
    ];
    $wilayahInit = old('cakupan_wilayah_ids', $sumber?->cakupan_wilayah_ids ?? []);
@endphp

@include('partials.dtsen.errors')

@if ($parent)
    <div class="mb-6 rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
        <p class="font-semibold">Permintaan ulang / pembaruan data</p>
        <p class="mt-1">Merujuk permohonan <span class="font-mono">{{ $parent->ticket_no }}</span> ({{ $parent->nama_program }}).
            Variabel dan isian KAK disalin dari permohonan tersebut — sesuaikan seperlunya.</p>
        <input type="hidden" name="parent_request_id" value="{{ $parent->id }}">
        <label class="mt-3 flex items-start gap-2">
            <input type="checkbox" name="ada_perubahan_signifikan" value="1" class="mt-1"
                   @checked(old('ada_perubahan_signifikan', true))>
            <span>Terdapat perubahan signifikan pada tujuan penggunaan dan/atau variabel yang dimohonkan.
                Bila tidak dicentang, DKISP dapat menempuh jalur cepat verifikasi administrasi.</span>
        </label>
    </div>
@endif

<div x-data="dtsenForm({
        selected: {{ Js::from(array_map('intval', (array) $selectedInit)) }},
        kegunaan: {{ Js::from((array) $kegunaanInit) }},
        levels: {{ Js::from($levelMap) }},
        kombinasi: {{ Js::from($kombinasiDataPribadi) }},
     })">

    {{-- Ringkasan level hak akses yang terbentuk dari pilihan variabel --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-1">Level Hak Akses</h2>
        <p class="text-sm text-gray-500 mb-4">
            Level ditentukan otomatis dari variabel yang Anda pilih. Bila permohonan mencakup lebih dari satu level,
            berlaku persyaratan level tertinggi (Bab IV Juknis).
        </p>

        <div class="rounded-lg border-2 p-4"
             :class="level >= 4 ? 'border-red-300 bg-red-50' : (level === 3 ? 'border-amber-300 bg-amber-50' : 'border-blue-300 bg-blue-50')">
            <p class="font-bold text-gray-800" x-text="levelLabel"></p>
            <p class="text-sm text-gray-700 mt-2">Dokumen wajib:</p>
            <ul class="mt-1 space-y-1 text-sm">
                <template x-for="doc in requiredDocs" :key="doc">
                    <li class="flex items-center gap-2"><span>•</span><span x-text="doc"></span></li>
                </template>
            </ul>
            <p class="text-xs text-gray-600 mt-3" x-show="count === 0" x-cloak>
                Belum ada variabel dipilih — level ditetapkan minimal Level 2.
            </p>
        </div>
    </div>

    {{-- Form 2.1 - Registrasi pemohon --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-1">1. Data Pemohon</h2>
        <p class="text-sm text-gray-500 mb-4">Diambil dari profil Anda — tidak perlu diketik ulang setiap mengajukan.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-dtsen.profil-field :profil="$profil" field="nama" name="pemohon_nama"
                                  label="Nama Pemohon" :value="$item->pemohon_nama ?? null" required />

            <x-dtsen.profil-field :profil="$profil" field="nip" name="pemohon_nip"
                                  label="NIP" :value="$item->pemohon_nip ?? null" />

            <x-dtsen.profil-field :profil="$profil" field="jabatan" name="pemohon_jabatan"
                                  label="Jabatan" :value="$item->pemohon_jabatan ?? null" />

            <x-dtsen.profil-field :profil="$profil" field="telepon" name="pemohon_telepon"
                                  label="Nomor Telepon" :value="$item->pemohon_telepon ?? null" required />

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Dinas/OPD</label>
                <input type="text" value="{{ $account->unitKerja->nama ?? $profil->namaUnitKerja() ?? '-' }}" disabled
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600">
                <p class="text-xs text-gray-500 mt-1">Diambil dari akun layanan DTSEN {{ $account->ticket_no }}.</p>
            </div>
        </div>
    </div>

    {{-- Form 2.2 - Formulir permintaan data --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-1">2. Formulir Permintaan Data</h2>
        <p class="text-sm text-gray-500 mb-4">Mengacu Lampiran II Juknis.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Surat</label>
                <input type="text" name="nomor_surat" value="{{ old('nomor_surat', $item->nomor_surat ?? '') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Surat</label>
                <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', optional($item->tanggal_surat ?? null)->format('Y-m-d')) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sifat Surat <span class="text-red-500">*</span></label>
                <select name="sifat_surat" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @foreach (\App\Models\DtsenAccountRequest::sifatSuratLabels() as $val => $label)
                        <option value="{{ $val }}" @selected(old('sifat_surat', $item->sifat_surat ?? 'biasa') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Lampiran</label>
                <input type="text" name="jumlah_lampiran" value="{{ old('jumlah_lampiran', $item->jumlah_lampiran ?? '') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Program/Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_program" value="{{ old('nama_program', $item->nama_program ?? $parent?->nama_program) }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('nama_program') border-red-500 @enderror">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Data yang Diminta <span class="text-red-500">*</span></label>
                <select name="jenis_permintaan" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @foreach (\App\Models\DtsenDataRequest::jenisPermintaanLabels() as $val => $label)
                        <option value="{{ $val }}" @selected(old('jenis_permintaan', $item->jenis_permintaan ?? $parent?->jenis_permintaan ?? 'bnba') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Cakupan wilayah berjenjang --}}
        <div class="mt-5">
            <label class="block text-sm font-medium text-gray-700 mb-2">Cakupan Wilayah</label>
            <div class="border rounded-lg divide-y max-h-80 overflow-y-auto">
                @foreach ($wilayahTree->where('tingkat', 'provinsi') as $provinsi)
                    <div class="p-3" x-data="{ open: true }">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 font-semibold text-sm">
                                <input type="checkbox" name="cakupan_wilayah_ids[]" value="{{ $provinsi->id }}"
                                       @checked(in_array($provinsi->id, (array) $wilayahInit))>
                                <span>{{ $provinsi->nama }} <span class="text-xs font-normal text-gray-500">(seluruh provinsi)</span></span>
                            </label>
                            <button type="button" @click="open = !open" class="text-xs text-blue-600 hover:underline"
                                    x-text="open ? 'Sembunyikan' : 'Tampilkan'"></button>
                        </div>
                        <div x-show="open" class="ml-6 mt-2 grid grid-cols-1 md:grid-cols-2 gap-1">
                            @foreach ($provinsi->children as $kab)
                                <label class="flex items-center gap-2 text-sm py-0.5">
                                    <input type="checkbox" name="cakupan_wilayah_ids[]" value="{{ $kab->id }}"
                                           @checked(in_array($kab->id, (array) $wilayahInit))>
                                    <span>{{ $kab->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                @if ($wilayahTree->where('tingkat', 'provinsi')->isEmpty())
                    <p class="p-4 text-sm text-gray-500">Master wilayah belum tersedia. Hubungi admin untuk melengkapinya.</p>
                @endif
            </div>
            <div class="mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Cakupan Wilayah</label>
                <textarea name="cakupan_wilayah_catatan" rows="2" placeholder="mis. khusus 12 kecamatan prioritas program"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('cakupan_wilayah_catatan', $item->cakupan_wilayah_catatan ?? '') }}</textarea>
            </div>
        </div>

        <div class="mt-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan Penggunaan <span class="text-red-500">*</span></label>
            <textarea name="tujuan_penggunaan" rows="4" required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('tujuan_penggunaan') border-red-500 @enderror"
                      placeholder="Uraikan tujuan pemanfaatan data serta keterkaitannya dengan program/kegiatan.">{{ old('tujuan_penggunaan', $item->tujuan_penggunaan ?? $parent?->tujuan_penggunaan) }}</textarea>
        </div>

        <div class="mt-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Surat Permohonan Data <span class="text-red-500">*</span>
                <span class="font-normal text-gray-500">(PDF, ditandatangani Kepala OPD, maks 10MB)</span>
            </label>
            <input type="file" name="surat_permohonan" accept=".pdf" class="w-full text-sm border border-gray-300 rounded-lg p-2">
            @if ($item && $item->surat_permohonan_path)
                <p class="text-xs text-green-600 mt-1">
                    Terlampir: <a href="{{ Storage::url($item->surat_permohonan_path) }}" target="_blank" class="underline">{{ basename($item->surat_permohonan_path) }}</a>
                </p>
            @endif
        </div>
    </div>

    {{-- Form 2.3 - Pemilihan variabel data (Katalog Variabel BNBA/DTSEN Kaltara) --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-2 mb-1">
            <h2 class="text-lg font-bold text-gray-800">3. Pemilihan Variabel Data</h2>
            <span class="text-sm text-gray-600">Terpilih: <strong x-text="count"></strong> variabel</span>
        </div>
        <p class="text-sm text-gray-500">
            Katalog Variabel BNBA/DTSEN Kaltara{{ $release ? ' — rilis ' . $release->nomor_rilis : '' }}.
            Set Keluarga dan set Anggota terhubung lewat nomor kartu keluarga.
            Setiap variabel yang dipilih wajib disertai kegunaan/alasan kebutuhannya.
        </p>
        <div class="mt-3 mb-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600">
            <span><span class="px-1.5 py-0.5 rounded font-semibold bg-blue-100 text-blue-700">Terbuka · L2</span> baris tanpa nama, NIK, atau alamat</span>
            <span><span class="px-1.5 py-0.5 rounded font-semibold bg-amber-100 text-amber-700">Quasi-identifier · L3</span> dapat mengidentifikasi orang bila digabung</span>
            <span><span class="px-1.5 py-0.5 rounded font-semibold bg-red-100 text-red-700">Data pribadi · L4</span> hanya untuk permintaan yang disetujui</span>
            <span><span class="px-1.5 py-0.5 rounded font-semibold bg-gray-100 text-gray-600">filter</span> dapat dipakai membatasi baris</span>
        </div>
        <p class="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800" x-show="kombinasiAktif" x-cloak>
            Gabungan RT/RW KTP dengan jenis kelamin atau tanggal lahir diperlakukan sebagai permintaan data pribadi (Level 4).
        </p>

        @forelse ($variables as $set => $kategoriList)
            <div class="mb-6">
                <h3 class="font-bold text-gray-800">
                    {{ $setLabels[$set] ?? 'Lainnya' }}
                    <span class="font-normal text-sm text-gray-500">({{ $kategoriList->flatten()->count() }} variabel)</span>
                </h3>
                @if (isset($setDescriptions[$set]))
                    <p class="text-xs text-gray-500 mb-2">{{ $setDescriptions[$set] }}</p>
                @endif

                @foreach ($kategoriList as $kategori => $daftar)
                    @php
                        $idsKategori = $daftar->pluck('id')->all();
                        $adaTerpilih = count(array_intersect($idsKategori, $selectedIds)) > 0;
                    @endphp
                    <div class="mb-3 border rounded-lg" x-data="{ open: {{ $adaTerpilih ? 'true' : 'false' }} }">
                        <div class="flex items-center justify-between gap-2 px-4 py-2.5 bg-gray-50 rounded-t-lg">
                            <button type="button" @click="open = !open" class="flex-1 flex items-center gap-2 text-left">
                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold text-sm text-gray-800">{{ $kategori }}</span>
                                <span class="text-xs text-gray-500">(<span x-text="countOf({{ Js::from($idsKategori) }})"></span>/{{ $daftar->count() }})</span>
                            </button>
                            <button type="button" class="text-xs text-blue-600 hover:underline whitespace-nowrap"
                                    @click="open = true; setMany({{ Js::from($idsKategori) }}, !allSelected({{ Js::from($idsKategori) }}))"
                                    x-text="allSelected({{ Js::from($idsKategori) }}) ? 'Batal pilih semua' : 'Pilih semua'"></button>
                        </div>
                        <div x-show="open" x-cloak class="divide-y">
                            @foreach ($daftar as $variable)
                                <div class="p-3">
                                    <label class="flex items-start gap-2">
                                        <input type="checkbox" name="variabel[]" value="{{ $variable->id }}" class="mt-1"
                                               @checked(in_array($variable->id, $selectedIds))
                                               :checked="isSelected({{ $variable->id }})"
                                               @change="toggle({{ $variable->id }}, $event.target.checked)">
                                        <span class="min-w-0">
                                            <span class="font-mono text-sm font-medium text-gray-800 break-all">{{ $variable->nama }}</span>
                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold whitespace-nowrap {{ $sensitivitasBadge[$variable->sensitivitas] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $sensitivitasLabels[$variable->sensitivitas] ?? 'Level' }} · L{{ $variable->level_minimal }}
                                            </span>
                                            @if ($variable->bisa_filter)
                                                <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">filter</span>
                                            @endif
                                            @if ($variable->deskripsi)
                                                <span class="block text-xs text-gray-600 mt-0.5">{{ $variable->deskripsi }}</span>
                                            @endif
                                            @if ($variable->nilai_kode)
                                                <span class="block text-[11px] text-gray-400 mt-0.5">Nilai/kode: {{ $variable->nilai_kode }}</span>
                                            @endif
                                        </span>
                                    </label>
                                    <div class="ml-6 mt-2" x-show="isSelected({{ $variable->id }})" x-cloak>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Kegunaan / Alasan Kebutuhan <span class="text-red-500">*</span></label>
                                        <input type="text" name="kegunaan[{{ $variable->id }}]"
                                               value="{{ $kegunaanInit[$variable->id] ?? '' }}"
                                               placeholder="mis. dasar penetapan sasaran penerima bantuan"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <p class="text-sm text-gray-500">Katalog variabel DTSEN belum tersedia. Hubungi Bapperida/Admin untuk melengkapinya.</p>
        @endforelse
    </div>

    {{-- Form 2.4 - Kerangka Acuan Kerja (wajib level 3 & 4), diunggah sebagai PDF bertanda tangan --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6" :class="level >= 3 ? '' : 'opacity-60'">
        <h2 class="text-lg font-bold text-gray-800 mb-1">4. Kerangka Acuan Kerja (KAK)</h2>
        <p class="text-sm mb-4" :class="level >= 3 ? 'text-red-600 font-medium' : 'text-gray-500'">
            <span x-show="level >= 3" x-cloak>Wajib diunggah untuk permohonan level 3 dan 4.</span>
            <span x-show="level < 3" x-cloak>Tidak wajib untuk level 2, namun boleh diunggah bila diperlukan.</span>
        </p>

        <label class="block text-sm font-medium text-gray-700 mb-1">
            KAK Bertanda Tangan Kepala OPD <span class="font-normal text-gray-500">(PDF, maks 10MB)</span>
        </label>
        <input type="file" name="kak_file" accept=".pdf" class="w-full text-sm border border-gray-300 rounded-lg p-2">
        <p class="text-xs text-gray-500 mt-1">
            Susun KAK mengacu Lampiran IV Juknis: latar belakang, dasar hukum, maksud dan tujuan, rencana pemanfaatan,
            jangka waktu, serta mekanisme keamanan dan pemusnahan data.
        </p>
        @if ($item && $item->kak_file_path)
            <p class="text-xs text-green-600 mt-1">
                Terlampir: <a href="{{ Storage::url($item->kak_file_path) }}" target="_blank" class="underline">{{ basename($item->kak_file_path) }}</a>
            </p>
        @endif
        @error('kak_file')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror

        <label class="flex items-start gap-2 mt-3">
            <input type="checkbox" name="kak_pernyataan" value="1" class="mt-1"
                   @checked(old('kak_pernyataan', $sumber->kak_pernyataan ?? false))>
            <span class="text-sm text-gray-700">
                Kepala Perangkat Daerah menyatakan bertanggung jawab atas penggunaan, penyimpanan, pelindungan,
                dan pemusnahan data DTSEN sesuai KAK ini.
            </span>
        </label>
        @error('kak_pernyataan')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <label class="flex items-start gap-2">
            <input type="checkbox" name="consent_true" value="1" @checked(old('consent_true', $item->consent_true ?? false)) class="mt-1">
            <span class="text-sm text-gray-700">
                Saya menyatakan data yang diisi benar dan menyetujui ketentuan pemanfaatan, pelindungan, serta pemusnahan
                data DTSEN sebagaimana diatur dalam Juknis. <span class="text-red-500">*</span>
            </span>
        </label>
        @error('consent_true')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" name="action" value="draft"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2.5 rounded-lg font-semibold">Simpan sebagai Draft</button>
        <button type="submit" name="action" value="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Ajukan Permohonan</button>
        <a href="{{ route('user.dtsen.permohonan.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
    </div>
</div>

@push('scripts')
<script>
    // Level hak akses mengikuti variabel dengan level_minimal tertinggi yang dipilih;
    // dari situ ditentukan dokumen apa saja yang wajib dilampirkan (Bab IV Juknis).
    const DTSEN_LEVEL_LABELS = @json(\App\Models\DtsenDataRequest::levelLabels());
    const DTSEN_LEVEL_DOCS = @json(\App\Models\DtsenDataRequest::levelRequirements());

    function dtsenForm({ selected, kegunaan, levels, kombinasi }) {
        return {
            selected: selected.map(Number),
            kegunaan,
            levels,
            kombinasi,
            get count() { return this.selected.length },
            // Sama dengan DtsenDataRequest::highestLevelFor(): gabungan RT/RW KTP dengan
            // jenis kelamin/tanggal lahir diperlakukan sebagai data pribadi.
            get kombinasiAktif() {
                return this.kombinasi.some(([a, b]) => a.some((id) => this.isSelected(id)) && b.some((id) => this.isSelected(id)));
            },
            get level() {
                if (this.selected.length === 0) return 2;
                if (this.kombinasiAktif) return 4;
                const max = Math.max(...this.selected.map((id) => Number(this.levels[id] ?? 2)));
                return Math.min(4, Math.max(2, max));
            },
            get levelLabel() { return DTSEN_LEVEL_LABELS[this.level] ?? ('Level ' + this.level) },
            get requiredDocs() { return DTSEN_LEVEL_DOCS[this.level] ?? [] },
            isSelected(id) { return this.selected.includes(Number(id)) },
            countOf(ids) { return ids.filter((id) => this.isSelected(id)).length },
            allSelected(ids) { return ids.every((id) => this.isSelected(id)) },
            setMany(ids, checked) { ids.forEach((id) => this.toggle(id, checked)) },
            toggle(id, checked) {
                id = Number(id);
                if (checked) {
                    if (!this.selected.includes(id)) this.selected.push(id);
                } else {
                    this.selected = this.selected.filter((v) => v !== id);
                }
            },
        }
    }
</script>
@endpush
