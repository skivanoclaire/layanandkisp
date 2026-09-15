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
    $dasarHukumInit = old('kak_dasar_hukum', $sumber?->kak_dasar_hukum ?? ['']);
    $personelInit = old('kak_personel_akses', $sumber?->kak_personel_akses ?? [['nama' => '', 'nip' => '', 'jabatan' => '']]);
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

    {{-- Form 2.3 - Pemilihan variabel data --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-2 mb-1">
            <h2 class="text-lg font-bold text-gray-800">3. Pemilihan Variabel Data</h2>
            <span class="text-sm text-gray-600">Terpilih: <strong x-text="count"></strong> variabel</span>
        </div>
        <p class="text-sm text-gray-500 mb-4">
            Katalog dari Bapperida{{ $release ? ' — rilis ' . $release->nomor_rilis : '' }}.
            Setiap variabel yang dipilih wajib disertai kegunaan/alasan kebutuhannya.
        </p>

        @forelse ($variables as $kategori => $daftar)
            <div class="mb-4 border rounded-lg" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-2.5 bg-gray-50 hover:bg-gray-100 rounded-t-lg text-left">
                    <span class="font-semibold text-sm text-gray-800">{{ $kategori ?: 'Lainnya' }} <span class="font-normal text-gray-500">({{ $daftar->count() }})</span></span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div x-show="open" class="divide-y">
                    @foreach ($daftar as $variable)
                        <div class="p-3">
                            <label class="flex items-start gap-2">
                                <input type="checkbox" name="variabel[]" value="{{ $variable->id }}" class="mt-1"
                                       @checked(in_array($variable->id, array_map('intval', (array) $selectedInit)))
                                       @change="toggle({{ $variable->id }}, $event.target.checked)">
                                <span class="min-w-0">
                                    <span class="text-sm font-medium text-gray-800">{{ $variable->nama }}</span>
                                    <span class="ml-2 px-1.5 py-0.5 rounded text-[10px] font-semibold
                                        {{ $variable->level_minimal >= 4 ? 'bg-red-100 text-red-700' : ($variable->level_minimal === 3 ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                        L{{ $variable->level_minimal }}
                                    </span>
                                    <span class="block font-mono text-[11px] text-gray-400">{{ $variable->kode }}</span>
                                    @if ($variable->deskripsi)
                                        <span class="block text-xs text-gray-500 mt-0.5">{{ $variable->deskripsi }}</span>
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
        @empty
            <p class="text-sm text-gray-500">Katalog variabel DTSEN belum tersedia. Hubungi Bapperida/Admin untuk melengkapinya.</p>
        @endforelse
    </div>

    {{-- Form 2.4 - Kerangka Acuan Kerja (wajib level 3 & 4) --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6" :class="level >= 3 ? '' : 'opacity-60'">
        <h2 class="text-lg font-bold text-gray-800 mb-1">4. Kerangka Acuan Kerja (KAK)</h2>
        <p class="text-sm mb-4" :class="level >= 3 ? 'text-red-600 font-medium' : 'text-gray-500'">
            <span x-show="level >= 3" x-cloak>Wajib diisi untuk permohonan level 3 dan 4.</span>
            <span x-show="level < 3" x-cloak>Tidak wajib untuk level 2, namun boleh diisi bila diperlukan.</span>
        </p>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Latar Belakang</label>
                <textarea name="kak_latar_belakang" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                          placeholder="Uraian permasalahan dan keterkaitannya dengan tugas & fungsi OPD.">{{ old('kak_latar_belakang', $sumber->kak_latar_belakang ?? '') }}</textarea>
            </div>

            {{-- Dasar hukum (repeatable) --}}
            <div x-data="{ items: {{ Js::from(array_values((array) $dasarHukumInit) ?: ['']) }} }">
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Dasar Hukum</label>
                    <button type="button" @click="items.push('')" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded font-semibold">+ Tambah</button>
                </div>
                <template x-for="(d, i) in items" :key="i">
                    <div class="flex gap-2 mb-2">
                        <input type="text" :name="`kak_dasar_hukum[${i}]`" x-model="items[i]"
                               placeholder="mis. Perda No. ... tentang ... / Pergub No. ... tentang SOTK"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <button type="button" @click="items.length > 1 && items.splice(i, 1)"
                                class="px-3 text-red-600 hover:bg-red-50 rounded-lg text-sm">Hapus</button>
                    </div>
                </template>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maksud dan Tujuan</label>
                <textarea name="kak_maksud_tujuan" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                          placeholder="Spesifik dan terukur, termasuk output/outcome yang diharapkan.">{{ old('kak_maksud_tujuan', $sumber->kak_maksud_tujuan ?? '') }}</textarea>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-600">
                <p class="font-medium text-gray-700">Ruang lingkup &amp; variabel data</p>
                <p class="mt-1">Bagian ini terisi otomatis dari level hak akses, cakupan wilayah, dan tabel variabel beserta
                    kegunaannya yang Anda isi di atas — tidak perlu diketik ulang.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rencana Pemanfaatan Data / Metodologi</label>
                <textarea name="kak_metodologi" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                          placeholder="Rencana pengolahan/analisis data.">{{ old('kak_metodologi', $sumber->kak_metodologi ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keluaran yang Dihasilkan</label>
                    <textarea name="kak_keluaran" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                              placeholder="mis. dashboard, laporan, basis data sasaran">{{ old('kak_keluaran', $sumber->kak_keluaran ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit yang Mengakses Keluaran</label>
                    <input type="text" name="kak_unit_akses" value="{{ old('kak_unit_akses', $sumber->kak_unit_akses ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jangka Waktu Pemanfaatan — Mulai</label>
                    <input type="date" name="kak_jangka_mulai" value="{{ old('kak_jangka_mulai', optional($sumber->kak_jangka_mulai ?? null)->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Terhitung sejak BAST ditandatangani.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jangka Waktu Pemanfaatan — Akhir</label>
                    <input type="date" name="kak_jangka_akhir" value="{{ old('kak_jangka_akhir', optional($sumber->kak_jangka_akhir ?? null)->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <div class="border-t pt-4">
                <p class="text-sm font-semibold text-gray-800 mb-3">Mekanisme Keamanan &amp; Pelindungan Data Pribadi</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Infrastruktur / Media Penyimpanan Data</label>
                        <textarea name="kak_infrastruktur_penyimpanan" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                  placeholder="mis. server OPD di Pusat Data Provinsi, akses terbatas VPN">{{ old('kak_infrastruktur_penyimpanan', $sumber->kak_infrastruktur_penyimpanan ?? '') }}</textarea>
                    </div>

                    {{-- Personel yang diberi akses (repeatable) --}}
                    <div x-data="{ rows: {{ Js::from(array_values((array) $personelInit) ?: [['nama' => '', 'nip' => '', 'jabatan' => '']]) }} }">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">Personel / Unit yang Diberi Akses</label>
                            <button type="button" @click="rows.push({ nama: '', nip: '', jabatan: '' })"
                                    class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded font-semibold">+ Tambah</button>
                        </div>
                        <template x-for="(r, i) in rows" :key="i">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-2 mb-2">
                                <input type="text" :name="`kak_personel_akses[${i}][nama]`" x-model="r.nama" placeholder="Nama"
                                       class="md:col-span-4 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input type="text" :name="`kak_personel_akses[${i}][nip]`" x-model="r.nip" placeholder="NIP"
                                       class="md:col-span-3 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input type="text" :name="`kak_personel_akses[${i}][jabatan]`" x-model="r.jabatan" placeholder="Jabatan"
                                       class="md:col-span-4 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <button type="button" @click="rows.length > 1 && rows.splice(i, 1)"
                                        class="md:col-span-1 px-2 text-red-600 hover:bg-red-50 rounded-lg text-sm">×</button>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Teknik Pelindungan Data yang Diterapkan</label>
                        @php($teknikInit = old('kak_teknik_pelindungan', $sumber->kak_teknik_pelindungan ?? []))
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach (\App\Models\DtsenDataRequest::teknikPelindunganOptions() as $val => $label)
                                <label class="flex items-center gap-2 text-sm border rounded-lg px-3 py-2">
                                    <input type="checkbox" name="kak_teknik_pelindungan[]" value="{{ $val }}"
                                           @checked(in_array($val, (array) $teknikInit))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batas Waktu Pemusnahan Data</label>
                            <input type="date" name="kak_retensi_batas_waktu" value="{{ old('kak_retensi_batas_waktu', optional($sumber->kak_retensi_batas_waktu ?? null)->format('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pemusnahan</label>
                            <textarea name="kak_metode_pemusnahan" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                      placeholder="mis. penghapusan permanen berkas beserta salinan cadangan">{{ old('kak_metode_pemusnahan', $sumber->kak_metode_pemusnahan ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t pt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Unggah KAK Bertanda Tangan Kepala OPD (opsional, PDF)</label>
                <input type="file" name="kak_file" accept=".pdf" class="w-full text-sm border border-gray-300 rounded-lg p-2">
                @if ($item && $item->kak_file_path)
                    <p class="text-xs text-green-600 mt-1">
                        Terlampir: <a href="{{ Storage::url($item->kak_file_path) }}" target="_blank" class="underline">{{ basename($item->kak_file_path) }}</a>
                    </p>
                @endif
                <label class="flex items-start gap-2 mt-3">
                    <input type="checkbox" name="kak_pernyataan" value="1" class="mt-1"
                           @checked(old('kak_pernyataan', $sumber->kak_pernyataan ?? false))>
                    <span class="text-sm text-gray-700">
                        Kepala Perangkat Daerah menyatakan bertanggung jawab atas penggunaan, penyimpanan, pelindungan,
                        dan pemusnahan data DTSEN sesuai KAK ini.
                    </span>
                </label>
            </div>
        </div>
    </div>

    {{-- Form 2.5 - Dokumen pendukung (wajib level 4) --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6" x-show="level >= 4" x-cloak>
        <h2 class="text-lg font-bold text-gray-800 mb-1">5. Dokumen Pendukung</h2>
        <p class="text-sm text-red-600 font-medium mb-4">Wajib untuk permohonan level 4 (BNBA): dokumen perencanaan program, proposal kegiatan, atau dokumen sejenis.</p>

        @if ($item && $item->documents->where('jenis', 'pendukung')->isNotEmpty())
            <div class="mb-4 space-y-2">
                @foreach ($item->documents->where('jenis', 'pendukung') as $doc)
                    <div class="flex items-center justify-between border rounded-lg px-3 py-2 text-sm bg-gray-50">
                        <div class="min-w-0">
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 {{ $doc->nama_dokumen }}</a>
                            @if ($doc->keterangan)<p class="text-xs text-gray-500">{{ $doc->keterangan }}</p>@endif
                        </div>
                        <button type="button"
                                onclick="if (confirm('Hapus dokumen ini?')) document.getElementById('hapus-dok-{{ $doc->id }}').submit()"
                                class="text-red-600 hover:underline text-xs ml-3">Hapus</button>
                    </div>
                @endforeach
            </div>
        @endif

        <div x-data="{ slots: 1 }">
            <template x-for="i in slots" :key="i">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <input type="file" :name="`dokumen_pendukung[${i - 1}]`" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip"
                           class="w-full text-sm border border-gray-300 rounded-lg p-2">
                    <input type="text" :name="`dokumen_keterangan[${i - 1}]`" placeholder="Keterangan dokumen"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </template>
            <button type="button" @click="slots < 10 && slots++"
                    class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg font-semibold">+ Tambah Berkas</button>
        </div>
    </div>

    {{-- Form 2.6 - Kesiapan teknis & keamanan --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-1">6. Kesiapan Teknis &amp; Keamanan</h2>
        <p class="text-sm text-gray-500 mb-4">Menjadi bahan kesepakatan infrastruktur pengiriman data dengan DKISP.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Metode Akses yang Diinginkan</label>
                <select name="metode_akses" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">-- Pilih --</option>
                    @foreach (\App\Models\DtsenDataRequest::metodeAksesLabels() as $val => $label)
                        <option value="{{ $val }}" @selected(old('metode_akses', $sumber->metode_akses ?? '') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Metode Enkripsi / Kanal Penyaluran</label>
                <input type="text" name="metode_enkripsi" value="{{ old('metode_enkripsi', $sumber->metode_enkripsi ?? '') }}"
                       placeholder="mis. AES-256 pada berkas, kanal HTTPS/VPN"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Teknis SDM OPD</label>
                <textarea name="kapasitas_sdm" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                          placeholder="mis. 2 pranata komputer, mampu mengonsumsi API dan mengelola basis data">{{ old('kapasitas_sdm', $sumber->kapasitas_sdm ?? '') }}</textarea>
            </div>
        </div>
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

    function dtsenForm({ selected, kegunaan, levels }) {
        return {
            selected: selected.map(Number),
            kegunaan,
            levels,
            get count() { return this.selected.length },
            get level() {
                if (this.selected.length === 0) return 2;
                const max = Math.max(...this.selected.map((id) => Number(this.levels[id] ?? 2)));
                return Math.min(4, Math.max(2, max));
            },
            get levelLabel() { return DTSEN_LEVEL_LABELS[this.level] ?? ('Level ' + this.level) },
            get requiredDocs() { return DTSEN_LEVEL_DOCS[this.level] ?? [] },
            isSelected(id) { return this.selected.includes(Number(id)) },
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
