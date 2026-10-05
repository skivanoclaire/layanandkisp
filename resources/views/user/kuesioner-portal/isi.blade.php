@extends('layouts.authenticated')

@section('title', '- Isi Kuesioner Kualitas Layanan Portal')
@section('header-title', 'Kuesioner Kualitas Layanan Portal')

@php
    $langkah = ['A. Profil', 'B. Kepentingan', 'C. Kinerja', 'D. Kano', 'E. Terbuka'];
    // Langkah pertama yang memuat galat validasi server
    $langkahGalat = 0;
    if ($errors->any()) {
        $peta = ['kepentingan' => 1, 'kinerja' => 2, 'kepuasan' => 2, 'kano' => 3, 'kelebihan' => 4, 'kekurangan' => 4, 'saran' => 4];
        $langkahGalat = collect($errors->keys())
            ->map(fn ($k) => $peta[explode('.', $k)[0]] ?? 0)
            ->min();
    }
    $hubungiAwal = old('pernah_hubungi_petugas');
    $layananLama = old('layanan_diajukan', []);
    $kunciDraf = 'kuesioner-portal-' . ($response?->id ?? 'pratinjau');
    $pulihkanDraf = ! $errors->any() && ! $pratinjau;
@endphp

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-4xl"
         x-data="kuesionerPortal({ langkah: {{ $langkahGalat }}, hubungi: @js($hubungiAwal), kunci: @js($kunciDraf), pulihkan: @js($pulihkanDraf) })">

        @if ($pratinjau)
            <div class="mb-4 p-4 rounded-lg border bg-blue-50 border-blue-200 text-blue-800 text-sm">
                <strong>Mode pratinjau.</strong> Tampilan ini sama dengan yang dilihat responden; jawaban tidak dapat dikirim.
                Gunakan untuk validasi isi oleh ahli dan uji keterbacaan.
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 rounded-lg border bg-red-50 border-red-200 text-red-800 text-sm">
                <p class="font-medium">Masih ada {{ $errors->count() }} isian yang perlu dilengkapi:</p>
                <ul class="list-disc ml-5 mt-1 max-h-32 overflow-y-auto">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Penanda langkah --}}
        <ol class="grid grid-cols-5 gap-1 mb-5 text-[11px] sm:text-xs">
            @foreach ($langkah as $i => $judul)
                <li class="text-center">
                    <div class="h-1.5 rounded-full" :class="langkah >= {{ $i }} ? 'bg-green-600' : 'bg-gray-200'"></div>
                    <span class="block mt-1" :class="langkah === {{ $i }} ? 'font-semibold text-green-700' : 'text-gray-500'">{{ $judul }}</span>
                </li>
            @endforeach
        </ol>

        <form method="POST" action="{{ $pratinjau ? '#' : route('kuesioner-portal.simpan') }}" x-ref="form"
              @change="simpanDraf()" @submit="kirim($event)">
            @csrf

            {{-- ============ BAGIAN A ============ --}}
            <section x-show="langkah === 0" data-langkah="0" class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                <h2 class="text-lg font-semibold text-gray-900">Bagian A. Profil Responden</h2>

                <div>
                    <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700 mb-1">1. Perangkat daerah tempat bekerja <span class="text-red-600">*</span></label>
                    <select id="unit_kerja_id" name="unit_kerja_id" data-wajib-select @disabled($pratinjau)
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">-- Pilih perangkat daerah --</option>
                        @foreach ($unitKerjas as $uk)
                            <option value="{{ $uk->id }}" @selected((string) old('unit_kerja_id', $unitKerjaDefault) === (string) $uk->id)>{{ $uk->nama }}</option>
                        @endforeach
                    </select>
                </div>

                @foreach ([
                    'status_kepegawaian' => '2. Status kepegawaian',
                    'peran' => '3. Peran dalam penggunaan portal',
                    'lama_penggunaan' => '4. Lama menggunakan portal',
                    'frekuensi' => '5. Frekuensi penggunaan',
                ] as $field => $judul)
                    <fieldset data-grup="{{ $field }}">
                        <legend class="block text-sm font-medium text-gray-700 mb-2">{{ $judul }} <span class="text-red-600">*</span></legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($profil[$field] as $k => $label)
                                <label class="px-3 py-2 rounded-lg border border-gray-300 text-sm cursor-pointer hover:border-green-500 has-checked:bg-green-600 has-checked:border-green-600 has-checked:text-white">
                                    <input type="radio" class="sr-only" name="{{ $field }}" value="{{ $k }}" data-wajib @checked(old($field) === $k) @disabled($pratinjau)>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach

                <fieldset data-grup="layanan_diajukan[]">
                    <legend class="block text-sm font-medium text-gray-700 mb-2">6. Layanan yang pernah diajukan (boleh lebih dari satu) <span class="text-red-600">*</span></legend>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach ($profil['layanan'] as $k => $label)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 text-sm cursor-pointer hover:border-green-500 has-checked:bg-green-50 has-checked:border-green-600">
                                <input type="checkbox" name="layanan_diajukan[]" value="{{ $k }}" data-wajib-cek
                                       class="rounded text-green-600" @checked(in_array($k, $layananLama, true)) @disabled($pratinjau)>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <input type="text" name="layanan_lainnya" value="{{ old('layanan_lainnya') }}" maxlength="255" @disabled($pratinjau)
                           placeholder="Bila memilih Lainnya, sebutkan layanannya"
                           class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </fieldset>

                <fieldset data-grup="pernah_hubungi_petugas">
                    <legend class="block text-sm font-medium text-gray-700 mb-2">7. Pernah menghubungi petugas/admin DKISP untuk meminta bantuan dalam 12 bulan terakhir <span class="text-red-600">*</span></legend>
                    <div class="flex gap-2">
                        @foreach (['1' => 'Ya', '0' => 'Tidak'] as $v => $label)
                            <label class="px-4 py-2 rounded-lg border border-gray-300 text-sm cursor-pointer hover:border-green-500 has-checked:bg-green-600 has-checked:border-green-600 has-checked:text-white">
                                <input type="radio" class="sr-only" name="pernah_hubungi_petugas" value="{{ $v }}" data-wajib
                                       x-model="hubungi" @disabled($pratinjau)>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </section>

            {{-- ============ BAGIAN B ============ --}}
            <section x-show="langkah === 1" x-cloak data-langkah="1" class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900">Bagian B. Tingkat Kepentingan</h2>
                <p class="text-sm text-gray-600 mt-1 mb-5">Nilailah seberapa <strong>penting</strong> setiap atribut bagi Bapak/Ibu
                    (1 = sangat tidak penting sampai 5 = sangat penting).</p>
                <div class="divide-y">
                    @foreach ($atribut as $kode => $a)
                        <div class="py-4">
                            <p class="text-sm text-gray-800 mb-2"><span class="text-xs font-mono text-gray-400 mr-1">{{ $loop->iteration }}.</span>
                                Pentingnya: {{ lcfirst($a['kinerja']) }}</p>
                            @include('user.kuesioner-portal._skala', ['nama' => "kepentingan[$kode]", 'pilihan' => $skalaKepentingan, 'nilai' => old("kepentingan.$kode")])
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ============ BAGIAN C ============ --}}
            <section x-show="langkah === 2" x-cloak data-langkah="2" class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900">Bagian C. Tingkat Kinerja dan Kepuasan</h2>
                <p class="text-sm text-gray-600 mt-1 mb-5">Nilailah <strong>kinerja portal saat ini</strong>
                    (1 = sangat tidak setuju sampai 5 = sangat setuju).</p>
                <div class="divide-y">
                    @foreach ($atribut as $kode => $a)
                        @if (in_array($kode, $kinerjaBersyarat, true))
                            <div class="py-4" x-show="hubungi === '1'" x-effect="aturBersyarat($el, hubungi === '1')">
                        @else
                            <div class="py-4">
                        @endif
                            <p class="text-sm text-gray-800 mb-2"><span class="text-xs font-mono text-gray-400 mr-1">{{ $loop->iteration }}.</span>{{ $a['kinerja'] }}</p>
                            @include('user.kuesioner-portal._skala', ['nama' => "kinerja[$kode]", 'pilihan' => $skalaKinerja, 'nilai' => old("kinerja.$kode")])
                        </div>
                    @endforeach
                    <p x-show="hubungi !== '1'" class="py-3 text-xs text-gray-500 italic">
                        Pernyataan tentang petugas tidak ditampilkan karena Anda belum pernah menghubungi petugas dalam 12 bulan terakhir.
                    </p>
                    @foreach ($kepuasan as $kode => $teks)
                        <div class="py-4">
                            <p class="text-sm text-gray-800 mb-2"><span class="text-xs font-mono text-gray-400 mr-1">{{ count($atribut) + $loop->iteration }}.</span>{{ $teks }}</p>
                            @include('user.kuesioner-portal._skala', ['nama' => "kepuasan[$kode]", 'pilihan' => $skalaKinerja, 'nilai' => old("kepuasan.$kode")])
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ============ BAGIAN D ============ --}}
            <section x-show="langkah === 3" x-cloak data-langkah="3" class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900">Bagian D. Reaksi terhadap Kondisi Layanan</h2>
                <p class="text-sm text-gray-600 mt-1">Setiap kondisi ditanyakan dua kali: saat kondisi <strong>terpenuhi</strong> dan saat
                    <strong>tidak terpenuhi</strong>. Pilih satu jawaban pada setiap baris.</p>
                <div class="mt-3 mb-5 grid grid-cols-1 sm:grid-cols-5 gap-1 text-xs text-gray-600">
                    @foreach ($skalaKano as $skor => $label)
                        <span><strong>{{ $skor }}</strong> = {{ $label }}</span>
                    @endforeach
                </div>
                <div class="space-y-4">
                    @foreach ($atribut as $kode => $a)
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-sm text-gray-900 mb-2">
                                <span class="font-semibold mr-1">{{ $loop->iteration }}a.</span>
                                <span class="inline-block px-1.5 py-0.5 mr-1 rounded bg-green-100 text-green-800 text-[11px] font-semibold">Terpenuhi</span>
                                Bagaimana perasaan Anda apabila {{ lcfirst($a['fungsional']) }}?
                            </p>
                            @include('user.kuesioner-portal._skala', ['nama' => "kano[$kode][f]", 'pilihan' => $skalaKano, 'nilai' => old("kano.$kode.f")])
                            <p class="text-sm text-gray-900 mt-4 mb-2">
                                <span class="font-semibold mr-1">{{ $loop->iteration }}b.</span>
                                <span class="inline-block px-1.5 py-0.5 mr-1 rounded bg-red-100 text-red-800 text-[11px] font-semibold">Tidak terpenuhi</span>
                                Bagaimana perasaan Anda apabila {{ lcfirst($a['disfungsional']) }}?
                            </p>
                            @include('user.kuesioner-portal._skala', ['nama' => "kano[$kode][d]", 'pilihan' => $skalaKano, 'nilai' => old("kano.$kode.d")])
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ============ BAGIAN E ============ --}}
            <section x-show="langkah === 4" x-cloak data-langkah="4" class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                <h2 class="text-lg font-semibold text-gray-900">Bagian E. Pertanyaan Terbuka</h2>
                @foreach ([
                    'kelebihan' => '1. Apa kelebihan utama Portal E-Layanan TIK menurut Bapak/Ibu?',
                    'kekurangan' => '2. Apa kekurangan atau kendala yang paling sering Bapak/Ibu alami?',
                    'saran' => '3. Apa saran perbaikan yang paling mendesak?',
                ] as $field => $tanya)
                    <div>
                        <label for="{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $tanya }}</label>
                        <textarea id="{{ $field }}" name="{{ $field }}" rows="3" maxlength="2000" @disabled($pratinjau)
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old($field) }}</textarea>
                    </div>
                @endforeach
            </section>

            <p x-show="pesan" x-text="pesan" class="mt-4 text-sm text-red-700"></p>

            <div class="mt-5 flex items-center justify-between">
                <button type="button" x-show="langkah > 0" @click="pindah(langkah - 1)"
                        class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Sebelumnya
                </button>
                <span x-show="langkah === 0"></span>
                <button type="button" x-show="langkah < 4" @click="lanjut()"
                        class="px-5 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">
                    Berikutnya
                </button>
                @if ($pratinjau)
                    <span x-show="langkah === 4" class="text-sm text-gray-500">Pengiriman dinonaktifkan pada pratinjau</span>
                @else
                    <button type="submit" x-show="langkah === 4"
                            class="px-5 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">
                        Kirim Jawaban
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function kuesionerPortal(opsi) {
            return {
                langkah: opsi.langkah,
                hubungi: opsi.hubungi === null ? null : String(opsi.hubungi),
                pesan: '',
                init() {
                    if (opsi.pulihkan) this.pulihkanDraf();
                },
                // Butir kinerja petugas dinonaktifkan (tidak dikirim, tidak wajib) bila tidak relevan
                aturBersyarat(el, aktif) {
                    el.querySelectorAll('input').forEach(i => {
                        i.disabled = !aktif;
                        if (!aktif) i.checked = false;
                    });
                },
                belumDiisi(nomor) {
                    const bagian = this.$refs.form.querySelector(`[data-langkah="${nomor}"]`);
                    const kosong = [];
                    const grup = new Set();
                    bagian.querySelectorAll('input[data-wajib]:not(:disabled)').forEach(i => grup.add(i.name));
                    grup.forEach(nama => {
                        if (!bagian.querySelector(`input[name="${CSS.escape(nama)}"]:checked`)) kosong.push(nama);
                    });
                    const cek = bagian.querySelectorAll('input[data-wajib-cek]:not(:disabled)');
                    if (cek.length && ![...cek].some(i => i.checked)) kosong.push(cek[0].name);
                    bagian.querySelectorAll('select[data-wajib-select]:not(:disabled)').forEach(s => { if (!s.value) kosong.push(s.name); });
                    return kosong;
                },
                tandai(nomor, kosong) {
                    const bagian = this.$refs.form.querySelector(`[data-langkah="${nomor}"]`);
                    bagian.querySelectorAll('.ring-red-400').forEach(e => e.classList.remove('ring-2', 'ring-red-400'));
                    kosong.forEach(nama => {
                        const el = bagian.querySelector(`[data-grup="${CSS.escape(nama)}"]`) || bagian.querySelector(`[name="${CSS.escape(nama)}"]`);
                        el?.classList.add('ring-2', 'ring-red-400');
                    });
                    bagian.querySelector('.ring-red-400')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                },
                lanjut() {
                    const kosong = this.belumDiisi(this.langkah);
                    this.tandai(this.langkah, kosong);
                    if (kosong.length) {
                        this.pesan = `Masih ada ${kosong.length} pertanyaan wajib yang belum dijawab pada bagian ini.`;
                        return;
                    }
                    this.pindah(this.langkah + 1);
                },
                pindah(n) {
                    this.pesan = '';
                    this.langkah = n;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                kirim(e) {
                    for (let n = 0; n <= 4; n++) {
                        const kosong = this.belumDiisi(n);
                        if (kosong.length) {
                            e.preventDefault();
                            this.langkah = n;
                            this.$nextTick(() => this.tandai(n, kosong));
                            this.pesan = 'Lengkapi dulu pertanyaan wajib pada bagian ini sebelum mengirim.';
                            return;
                        }
                    }
                    try { localStorage.removeItem(opsi.kunci); } catch (_) {}
                },
                simpanDraf() {
                    const data = {};
                    new FormData(this.$refs.form).forEach((v, k) => {
                        if (k === '_token') return;
                        (data[k] ??= []).push(v);
                    });
                    try { localStorage.setItem(opsi.kunci, JSON.stringify(data)); } catch (_) {}
                },
                pulihkanDraf() {
                    let data = null;
                    try { data = JSON.parse(localStorage.getItem(opsi.kunci) || 'null'); } catch (_) {}
                    if (!data) return;
                    if (data.pernah_hubungi_petugas) this.hubungi = data.pernah_hubungi_petugas[0];
                    this.$nextTick(() => {
                        Object.entries(data).forEach(([nama, nilai]) => {
                            if (nama === 'pernah_hubungi_petugas') return;
                            this.$refs.form.querySelectorAll(`[name="${CSS.escape(nama)}"]`).forEach(el => {
                                if (el.type === 'radio' || el.type === 'checkbox') el.checked = nilai.includes(el.value);
                                else el.value = nilai[0];
                            });
                        });
                    });
                },
            };
        }
    </script>
@endpush
