@php
    // Catatan: semua perhitungan awal disatukan di satu blok PHP ini. Bentuk PHP
    // inline tidak boleh dipakai sebelum blok PHP mana pun, karena pola raw-block
    // Blade akan menelan seluruh isi di antara keduanya.
    $item = $item ?? null;
    $user = auth()->user();
    $profil = \App\Services\Dtsen\ProfilPemohon::untuk($user);

    // Baris personel pertama selalu pengaju sendiri, terisi dari profil.
    $personelPengaju = [
        'nama' => $profil->nilai('nama'),
        'nip' => $profil->nilai('nip'),
        'jabatan' => $profil->nilai('jabatan'),
        'unit_kerja' => $profil->namaUnitKerja(),
        'no_hp' => $profil->nilai('telepon'),
        'email' => $profil->nilai('email'),
    ];

    $oldMembers = old('members', $item?->members->map->only(['nama','nip','jabatan','unit_kerja','no_hp','email'])->values()->all() ?? []);
    $oldMembers = count($oldMembers) ? array_values($oldMembers) : [$personelPengaju];
    // Kolom pengaju yang sudah ada di profil dikunci, sisanya tetap bisa diisi.
    $oldMembers[0] = array_merge($oldMembers[0], array_filter($personelPengaju, fn ($v) => filled($v)));

    // Dipakai Alpine untuk menentukan kolom mana pada baris pertama yang readonly.
    $kunciPengaju = [
        'nama' => $profil->terisi('nama'),
        'nip' => $profil->terisi('nip'),
        'jabatan' => $profil->terisi('jabatan'),
        'unit_kerja' => filled($profil->namaUnitKerja()),
        'no_hp' => $profil->terisi('telepon'),
        'email' => $profil->terisi('email'),
    ];

    // Centang "narahubung = saya" hanya berarti bila profil punya nama, telepon, dan surel.
    $profilNarahubungLengkap = $profil->terisi('nama') && $profil->terisi('telepon') && $profil->terisi('email');

    // Checkbox yang tidak dicentang tidak ikut terkirim, jadi keberadaan old input
    // dipakai untuk membedakan "sengaja dilepas" dari "form baru dibuka".
    $narahubungSamaDefault = $profilNarahubungLengkap && (
        filled(old())
            ? (bool) old('narahubung_sama_profil')
            : ($item ? $item->narahubung_email === $profil->nilai('email') : true)
    );
@endphp

@include('partials.dtsen.errors')

@php($belumLengkap = $profil->yangBelumTerisi(['nama', 'nip', 'jabatan', 'telepon', 'email', 'unit_kerja_id']))
@if ($belumLengkap)
    <div class="mb-6 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
        <p class="font-semibold">Lengkapi profil agar tidak mengetik ulang</p>
        <p class="mt-1">
            Data berikut belum ada di profil Anda: <strong>{{ implode(', ', $belumLengkap) }}</strong>.
            Isi sekarang di formulir ini, lalu lengkapi di
            <a href="{{ route('profile.edit') }}" class="underline font-semibold">halaman Profil</a>
            supaya terisi otomatis pada permohonan berikutnya.
        </p>
    </div>
@endif

{{-- Form 1.1 bagian A — Data Surat Permohonan Akun (Lampiran I) --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-1">1. Data Surat Permohonan Akun</h2>
    <p class="text-sm text-gray-500 mb-4">Surat ditandatangani Kepala Perangkat Daerah.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Surat <span class="text-red-500">*</span></label>
            <input type="text" name="nomor_surat" value="{{ old('nomor_surat', $item->nomor_surat ?? '') }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('nomor_surat') border-red-500 @enderror">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Surat <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', optional($item->tanggal_surat ?? null)->format('Y-m-d')) }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('tanggal_surat') border-red-500 @enderror">
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
            <input type="text" name="jumlah_lampiran" placeholder="mis. 1 berkas" value="{{ old('jumlah_lampiran', $item->jumlah_lampiran ?? '') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Perangkat Daerah @unless ($profil->terisi('unit_kerja_id'))<span class="text-red-500">*</span>@endunless
            </label>
            @if ($profil->terisi('unit_kerja_id'))
                {{-- Select yang disabled tidak ikut terkirim, jadi nilainya dibawa input tersembunyi. --}}
                <input type="text" value="{{ $profil->namaUnitKerja() }}" disabled
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                <input type="hidden" name="unit_kerja_id" value="{{ $profil->nilai('unit_kerja_id') }}">
                <p class="text-xs text-gray-500 mt-1">
                    Terisi dari profil Anda.
                    <a href="{{ route('profile.edit') }}" class="text-blue-600 hover:underline">Ubah di Profil</a>
                </p>
            @else
                <select name="unit_kerja_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('unit_kerja_id') border-red-500 @enderror">
                    <option value="">-- Pilih Perangkat Daerah --</option>
                    @foreach ($unitKerjaList as $uk)
                        <option value="{{ $uk->id }}" @selected(old('unit_kerja_id', $item->unit_kerja_id ?? null) == $uk->id)>{{ $uk->nama }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-amber-600 mt-1">
                    Perangkat daerah belum diatur di profil Anda — pilih di sini, lalu lengkapi
                    <a href="{{ route('profile.edit') }}" class="underline">Profil</a> agar terisi otomatis nanti.
                </p>
            @endif
            @error('unit_kerja_id')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Berkas Surat Permohonan (PDF, maks 10MB)</label>
            <input type="file" name="surat" accept=".pdf" class="w-full text-sm border border-gray-300 rounded-lg p-2">
            @if ($item && $item->surat_path)
                <p class="text-xs text-green-600 mt-1">
                    Terlampir:
                    <a href="{{ Storage::url($item->surat_path) }}" target="_blank" class="underline">{{ basename($item->surat_path) }}</a>
                    — unggah berkas baru untuk menggantikan.
                </p>
            @endif
        </div>
    </div>
</div>

{{-- Form 1.1 bagian B — Data Calon Pengguna Akun (repeatable) --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6"
     x-data="{
        members: {{ Js::from($oldMembers) }},
        // Kolom pengaju (baris pertama) yang datanya sudah ada di profil: dikunci.
        kunci: {{ Js::from($kunciPengaju) }},
        add() { this.members.push({ nama: '', nip: '', jabatan: '', unit_kerja: '', no_hp: '', email: '' }) },
        remove(i) { if (i > 0) this.members.splice(i, 1) },
        terkunci(i, field) { return i === 0 && this.kunci[field] === true },
        kelas(i, field) {
            return this.terkunci(i, field)
                ? 'border-gray-200 bg-gray-100 text-gray-600 cursor-not-allowed'
                : 'border-gray-300';
        },
        isGovEmail(email) { return /\.go\.id$/i.test(email || '') },
     }">
    <div class="flex items-start justify-between mb-1">
        <h2 class="text-lg font-bold text-gray-800">2. Data Calon Pengguna Akun</h2>
        <button type="button" @click="add()" class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg font-semibold">+ Tambah Personel</button>
    </div>
    <p class="text-sm text-gray-500 mb-4">
        Petugas/pelaksana teknis yang ditunjuk Kepala Perangkat Daerah. Dapat lebih dari satu orang.
        Baris pertama adalah Anda sebagai pengaju dan terisi dari profil.
    </p>

    <template x-for="(m, i) in members" :key="i">
        <div class="border rounded-lg p-4 mb-3" :class="i === 0 ? 'bg-blue-50 border-blue-200' : 'bg-gray-50'">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-gray-700">
                    <span x-show="i === 0">Anda (pengaju)</span>
                    <span x-show="i > 0">Personel <span x-text="i + 1"></span></span>
                </p>
                <button type="button" @click="remove(i)" x-show="i > 0"
                        class="text-xs text-red-600 hover:underline">Hapus</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Nama Lengkap <span class="text-red-500" x-show="!terkunci(i, 'nama')">*</span>
                    </label>
                    <input type="text" :name="`members[${i}][nama]`" x-model="m.nama" required
                           :readonly="terkunci(i, 'nama')" :class="kelas(i, 'nama')"
                           class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">NIP</label>
                    <input type="text" :name="`members[${i}][nip]`" x-model="m.nip"
                           :readonly="terkunci(i, 'nip')" :class="kelas(i, 'nip')"
                           class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jabatan</label>
                    <input type="text" :name="`members[${i}][jabatan]`" x-model="m.jabatan"
                           :readonly="terkunci(i, 'jabatan')" :class="kelas(i, 'jabatan')"
                           class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Unit Kerja</label>
                    <input type="text" :name="`members[${i}][unit_kerja]`" x-model="m.unit_kerja"
                           :readonly="terkunci(i, 'unit_kerja')" :class="kelas(i, 'unit_kerja')"
                           class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">No. Telepon/HP</label>
                    <input type="text" :name="`members[${i}][no_hp]`" x-model="m.no_hp"
                           :readonly="terkunci(i, 'no_hp')" :class="kelas(i, 'no_hp')"
                           class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Alamat Surel <span class="text-red-500" x-show="!terkunci(i, 'email')">*</span>
                    </label>
                    <input type="email" :name="`members[${i}][email]`" x-model="m.email" required
                           :readonly="terkunci(i, 'email')" :class="kelas(i, 'email')"
                           class="w-full px-3 py-2 border rounded-lg text-sm">
                    <p x-show="m.email && !isGovEmail(m.email)" x-cloak class="text-xs text-orange-600 mt-1">
                        Dianjurkan memakai surel resmi pemerintah berakhiran <span class="font-mono">.go.id</span>.
                    </p>
                </div>
            </div>
            <p x-show="i === 0" class="text-xs text-gray-500 mt-3">
                Kolom berlatar abu-abu mengikuti profil Anda.
                <a href="{{ route('profile.edit') }}" class="text-blue-600 hover:underline">Ubah di Profil</a>
            </p>
        </div>
    </template>
</div>

{{-- Form 1.1 bagian C — Narahubung Teknis OPD --}}
<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-800 mb-1">3. Narahubung Teknis OPD</h2>
    <p class="text-sm text-gray-500 mb-3">Kontak yang dihubungi DKISP untuk koordinasi teknis dan pemberitahuan data siap diunduh.</p>

    <div x-data="{
            sama: {{ $narahubungSamaDefault ? 'true' : 'false' }},
            profil: {{ Js::from([
                'nama' => $profil->nilai('nama'),
                'kontak' => $profil->nilai('telepon'),
                'email' => $profil->nilai('email'),
            ]) }},
            manual: {{ Js::from([
                'nama' => old('narahubung_nama', $item->narahubung_nama ?? ''),
                'kontak' => old('narahubung_kontak', $item->narahubung_kontak ?? ''),
                'email' => old('narahubung_email', $item->narahubung_email ?? ''),
            ]) }},
            nilai(field) { return this.sama ? (this.profil[field] ?? '') : this.manual[field] },
            kelas() { return this.sama ? 'border-gray-200 bg-gray-100 text-gray-600 cursor-not-allowed' : 'border-gray-300' },
         }">
        @if ($profilNarahubungLengkap)
            <label class="flex items-start gap-2 mb-4">
                <input type="checkbox" name="narahubung_sama_profil" value="1" x-model="sama" class="mt-1">
                <span class="text-sm text-gray-700">
                    Narahubung teknis adalah saya sendiri — isi otomatis dari profil
                    (<span class="font-medium">{{ $profil->nilai('nama') }}</span>).
                </span>
            </label>
        @else
            <p class="mb-4 text-xs text-amber-600">
                Profil Anda belum memuat nama, nomor telepon, dan surel sekaligus, sehingga narahubung
                harus diisi manual. Lengkapi <a href="{{ route('profile.edit') }}" class="underline">Profil</a>
                agar bisa diisi otomatis.
            </p>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Narahubung <span class="text-red-500">*</span></label>
                <input type="text" name="narahubung_nama" required
                       :value="nilai('nama')" @input="manual.nama = $event.target.value"
                       :readonly="sama" :class="kelas()"
                       class="w-full px-4 py-2 border rounded-lg @error('narahubung_nama') border-red-500 @enderror">
                @error('narahubung_nama')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Kontak (HP/WA) <span class="text-red-500">*</span></label>
                <input type="text" name="narahubung_kontak" required
                       :value="nilai('kontak')" @input="manual.kontak = $event.target.value"
                       :readonly="sama" :class="kelas()"
                       class="w-full px-4 py-2 border rounded-lg @error('narahubung_kontak') border-red-500 @enderror">
                @error('narahubung_kontak')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Surel Narahubung <span class="text-red-500">*</span></label>
                <input type="email" name="narahubung_email" required
                       :value="nilai('email')" @input="manual.email = $event.target.value"
                       :readonly="sama" :class="kelas()"
                       class="w-full px-4 py-2 border rounded-lg @error('narahubung_email') border-red-500 @enderror">
                @error('narahubung_email')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <p x-show="sama" class="text-xs text-gray-500 mt-2">
            Mengikuti profil Anda.
            <a href="{{ route('profile.edit') }}" class="text-blue-600 hover:underline">Ubah di Profil</a>,
            atau hapus centang di atas untuk menunjuk orang lain.
        </p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <label class="flex items-start gap-2">
        <input type="checkbox" name="consent_true" value="1" @checked(old('consent_true', $item->consent_true ?? false)) class="mt-1">
        <span class="text-sm text-gray-700">
            Saya menyatakan data yang diisi benar, personel yang didaftarkan ditunjuk secara resmi, dan menyetujui ketentuan
            layanan berbagi pakai data DTSEN. <span class="text-red-500">*</span>
        </span>
    </label>
    @error('consent_true')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
</div>

<div class="flex flex-wrap gap-3">
    <button type="submit" name="action" value="draft"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2.5 rounded-lg font-semibold">Simpan sebagai Draft</button>
    <button type="submit" name="action" value="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold">Ajukan Permohonan</button>
    <a href="{{ route('user.dtsen.akun.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Batal</a>
</div>
