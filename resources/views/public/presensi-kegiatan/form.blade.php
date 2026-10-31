@extends('public.presensi-kegiatan.layout')

@section('title', $mode === 'konfirmasi' ? 'Konfirmasi Kehadiran' : 'Presensi Kegiatan')

@section('content')
@php
    $kunci = fn ($field) => $akun && !empty($akun[$field]) && in_array($field, ['nama', 'nip', 'instansi'], true);
    $nilai = fn ($field) => old($field, $akun[$field] ?? ($sudah?->{$field} ?? ''));
    $input = 'w-full rounded-lg border-gray-300 border px-3 py-2.5 text-base focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none';

    // Pilihan instansi awal: isian sebelumnya, lalu data peserta yang sudah tercatat.
    $idPemprov = $unitKerjas->flatten()->pluck('id')->all();
    $pilihanAwal = old('pilihan_instansi');
    if ($pilihanAwal === null && $sudah) {
        $pilihanAwal = in_array($sudah->unit_kerja_id, $idPemprov, true) ? (string) $sudah->unit_kerja_id : 'lainnya';
    }
    $lainnyaAwal = old('instansi_lainnya', $pilihanAwal === 'lainnya' ? $sudah?->instansi : '');
@endphp

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">{{ session('error') }}</div>
    @endif

    @if(!$dibuka)
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <div class="text-4xl">🔒</div>
            <h2 class="font-bold text-lg mt-3">
                {{ $mode === 'konfirmasi' ? 'Konfirmasi kehadiran sedang ditutup' : 'Presensi belum dibuka' }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                {{ $mode === 'konfirmasi'
                    ? 'Silakan hubungi panitia bila Anda belum sempat mengonfirmasi kehadiran.'
                    : 'Presensi dibuka oleh panitia pada saat kegiatan berlangsung. Silakan coba beberapa saat lagi.' }}
            </p>
            <a href="{{ route('kegiatan.show', $kegiatan) }}" class="inline-block mt-5 text-sm font-semibold text-green-700 hover:underline">← Informasi kegiatan</a>
        </div>
    @else
        @if($sudah && $mode === 'presensi' && $sudah->hadir_at)
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 mb-4 text-sm">
                Anda sudah tercatat hadir pada {{ $sudah->hadir_at->format('d/m/Y H:i') }} WITA.
            </div>
        @elseif($sudah && $mode === 'konfirmasi' && $sudah->konfirmasi)
            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg px-4 py-3 mb-4 text-sm">
                Anda sudah mengonfirmasi <strong>{{ strtolower($sudah->konfirmasi_label) }}</strong>.
                Kirim ulang formulir ini bila ingin mengubahnya.
            </div>
        @endif

        @guest
            <div class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1 text-sm">
                    <div class="font-semibold text-gray-800">Punya akun E-Layanan DKISP / SSO Kaltara?</div>
                    <div class="text-gray-600">Masuk agar nama, NIP, dan instansi terisi otomatis tanpa mengetik ulang.</div>
                </div>
                <a href="{{ route('kegiatan.masuk', [$kegiatan, $mode]) }}"
                   class="shrink-0 text-center bg-green-600 hover:bg-green-700 text-white font-semibold text-sm px-4 py-2.5 rounded-lg">
                    Masuk dengan Akun
                </a>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500 mb-4">
                <span class="flex-1 h-px bg-gray-300"></span>atau isi manual<span class="flex-1 h-px bg-gray-300"></span>
            </div>
        @endguest

        <form method="POST" id="form-presensi"
              action="{{ route($mode === 'konfirmasi' ? 'kegiatan.konfirmasi.simpan' : 'kegiatan.presensi.simpan', $kegiatan) }}"
              class="bg-white rounded-xl shadow-sm p-5 space-y-4">
            @csrf
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

            @if($akun)
                <div class="rounded-lg bg-green-50 border border-green-200 px-3 py-2 text-xs text-green-800">
                    Data bertanda 🔒 diambil dari akun Anda. Untuk mengubahnya, perbarui profil di E-Layanan.
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label class="block text-sm font-semibold mb-1" for="nama">Nama Lengkap (beserta gelar) @if($kunci('nama'))🔒@endif <span class="text-red-500">*</span></label>
                <input id="nama" name="nama" value="{{ $nilai('nama') }}" required maxlength="255"
                       class="{{ $input }} {{ $kunci('nama') ? 'bg-gray-100' : '' }}" @readonly($kunci('nama'))>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" for="nip">NIP @if($kunci('nip'))🔒@endif <span class="text-gray-400 font-normal">(kosongkan bila non-ASN)</span></label>
                <input id="nip" name="nip" value="{{ $nilai('nip') }}" inputmode="numeric" maxlength="25"
                       class="{{ $input }} {{ $kunci('nip') ? 'bg-gray-100' : '' }}" @readonly($kunci('nip'))>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" for="jabatan">Jabatan</label>
                <input id="jabatan" name="jabatan" value="{{ $nilai('jabatan') }}" maxlength="255" class="{{ $input }}">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1" for="instansi">Instansi / Perangkat Daerah @if($kunci('instansi'))🔒@endif <span class="text-red-500">*</span></label>
                @if($kunci('instansi'))
                    <input id="instansi" name="instansi" value="{{ $nilai('instansi') }}" readonly class="{{ $input }} bg-gray-100">
                @else
                    <select id="instansi" name="pilihan_instansi" required class="{{ $input }} bg-white">
                        <option value="">— Pilih unit kerja —</option>
                        @foreach($unitKerjas as $tipe => $daftar)
                            <optgroup label="{{ $tipe }}">
                                @foreach($daftar as $uk)
                                    <option value="{{ $uk->id }}" @selected((string) $pilihanAwal === (string) $uk->id)>{{ $uk->nama }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                        <option value="lainnya" @selected($pilihanAwal === 'lainnya')>Lainnya (tulis manual)</option>
                    </select>
                    <input id="instansi_lainnya" name="instansi_lainnya" value="{{ $lainnyaAwal }}" maxlength="255"
                           placeholder="Tuliskan nama instansi"
                           class="{{ $input }} mt-2 {{ $pilihanAwal === 'lainnya' ? '' : 'hidden' }}">
                    <script>
                        (function () {
                            const pilih = document.getElementById('instansi');
                            const lainnya = document.getElementById('instansi_lainnya');
                            pilih.addEventListener('change', function () {
                                const manual = this.value === 'lainnya';
                                lainnya.classList.toggle('hidden', !manual);
                                lainnya.required = manual;
                                if (manual) lainnya.focus();
                            });
                            lainnya.required = pilih.value === 'lainnya';
                        })();
                    </script>
                @endif
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1" for="no_hp">No. HP / WhatsApp <span class="text-red-500">*</span></label>
                    <input id="no_hp" name="no_hp" value="{{ $nilai('no_hp') }}" required inputmode="tel" maxlength="20" placeholder="08xxxxxxxxxx" class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ $nilai('email') }}" maxlength="255" class="{{ $input }}">
                </div>
            </div>

            @if($mode === 'konfirmasi')
                <fieldset>
                    <legend class="block text-sm font-semibold mb-2">Konfirmasi <span class="text-red-500">*</span></legend>
                    <div class="grid sm:grid-cols-2 gap-3">
                        @foreach([
                            'hadir' => ['🏢 Hadir di lokasi', $kegiatan->tempat],
                            'online' => ['💻 Hadir online', 'Khusus unit kerja yang berlokasi di luar Tanjung Selor. Tautan rapat dikirim ke WhatsApp Anda.'],
                        ] as $val => [$label, $ket])
                            <label class="flex items-start gap-2 border rounded-lg px-3 py-3 cursor-pointer has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                <input type="radio" name="konfirmasi" value="{{ $val }}" required
                                       @checked(old('konfirmasi', $sudah?->konfirmasi) === $val) class="text-green-600 mt-1">
                                <span>
                                    <span class="block text-sm font-medium">{{ $label }}</span>
                                    <span class="block text-xs text-gray-500">{{ $ket }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <div>
                    <label class="block text-sm font-semibold mb-1" for="catatan">Catatan <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <textarea id="catatan" name="catatan" rows="2" maxlength="500" class="{{ $input }}">{{ old('catatan', $sudah?->catatan) }}</textarea>
                </div>
            @else
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-sm font-semibold">Tanda Tangan @if($kegiatan->wajib_tanda_tangan)<span class="text-red-500">*</span>@endif</label>
                        <button type="button" id="ttd-hapus" class="text-xs font-semibold text-red-600 hover:underline">Ulangi</button>
                    </div>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                        <canvas id="ttd-canvas" class="w-full h-44 touch-none rounded-lg"></canvas>
                        <span id="ttd-petunjuk" class="pointer-events-none absolute inset-0 flex items-center justify-center text-sm text-gray-400">Tanda tangan di sini</span>
                    </div>
                    <input type="hidden" name="tanda_tangan" id="tanda_tangan">
                </div>
            @endif

            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg text-base">
                {{ $mode === 'konfirmasi' ? 'Kirim Konfirmasi' : 'Simpan Presensi' }}
            </button>
        </form>
    @endif
@endsection

@if($dibuka && $mode === 'presensi')
@push('scripts')
<script>
(function () {
    const canvas = document.getElementById('ttd-canvas');
    const ctx = canvas.getContext('2d');
    const petunjuk = document.getElementById('ttd-petunjuk');
    const wajib = @json((bool) $kegiatan->wajib_tanda_tangan);
    let menggambar = false, terisi = false;

    function ukuran() {
        const rasio = Math.max(window.devicePixelRatio || 1, 1);
        const kotak = canvas.getBoundingClientRect();
        canvas.width = kotak.width * rasio;
        canvas.height = kotak.height * rasio;
        ctx.scale(rasio, rasio);
        ctx.lineWidth = 2.2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#111827';
        terisi = false;
        petunjuk.style.display = '';
    }

    function titik(e) {
        const kotak = canvas.getBoundingClientRect();
        return { x: e.clientX - kotak.left, y: e.clientY - kotak.top };
    }

    canvas.addEventListener('pointerdown', function (e) {
        menggambar = true;
        canvas.setPointerCapture(e.pointerId);
        const p = titik(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        ctx.lineTo(p.x + 0.1, p.y + 0.1);
        ctx.stroke();
        terisi = true;
        petunjuk.style.display = 'none';
    });
    canvas.addEventListener('pointermove', function (e) {
        if (!menggambar) return;
        const p = titik(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
    });
    ['pointerup', 'pointercancel', 'pointerleave'].forEach(ev => canvas.addEventListener(ev, () => menggambar = false));

    document.getElementById('ttd-hapus').addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        terisi = false;
        petunjuk.style.display = '';
    });

    document.getElementById('form-presensi').addEventListener('submit', function (e) {
        if (!terisi) {
            if (wajib) {
                e.preventDefault();
                canvas.parentElement.classList.add('border-red-500');
                canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }
        document.getElementById('tanda_tangan').value = canvas.toDataURL('image/png');
        this.querySelector('button[type=submit]').disabled = true;
    });

    ukuran();
    let lebar = canvas.getBoundingClientRect().width;
    window.addEventListener('resize', function () {
        // Abaikan resize vertikal (keyboard ponsel muncul) agar tanda tangan tidak terhapus.
        const baru = canvas.getBoundingClientRect().width;
        if (baru !== lebar) { lebar = baru; ukuran(); }
    });
})();
</script>
@endpush
@endif
