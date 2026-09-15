@extends('layouts.authenticated')

@section('title', '- Proses Permintaan Data DTSEN')
@section('header-title', 'Proses Permintaan Data DTSEN')

@section('content')
@php
    $sla = app(\App\Services\Dtsen\DtsenSlaService::class);
    $activeToken = $item->activeToken();
    $readyForToken = $item->pemrosesan_at !== null
        && (! $item->requiresBast() || $item->bast_verified)
        && in_array($item->status, [
            \App\Models\DtsenDataRequest::STATUS_PEMROSESAN_QA,
            \App\Models\DtsenDataRequest::STATUS_MENUNGGU_BAST,
            \App\Models\DtsenDataRequest::STATUS_DATA_TERSEDIA,
        ], true);

    // Checklist verifikasi administrasi menyesuaikan level yang dimohonkan.
    // Sengaja dihitung di blok ini, bukan di blok PHP kedua di bawah: pola raw-block
    // Blade membuat bentuk PHP inline menelan isi sampai penutup blok berikutnya.
    $checklistAdministrasi = ['adm_check_surat' => 'Surat permohonan lengkap & sesuai'];
    if ($item->requiresKak()) {
        $checklistAdministrasi['adm_check_kak'] = 'KAK lengkap sesuai Lampiran IV';
    }
    if ($item->requiresDokumenPendukung()) {
        $checklistAdministrasi['adm_check_dokumen_pendukung'] = 'Dokumen pendukung terlampir';
    }
    $checklistAdministrasi['adm_check_metode_akses'] = 'Metode akses/infrastruktur memadai';
    $checklistAdministrasi['adm_check_enkripsi'] = 'Enkripsi & kanal penyaluran memadai';
@endphp

<div class="container mx-auto p-6">
    <a href="{{ route('admin.dtsen.permohonan.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>

    <div class="mt-4">@include('partials.dtsen.errors')</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            @include('partials.dtsen.request-detail', ['item' => $item, 'wilayahLabels' => $wilayahLabels])

            {{-- Token akses & log unduhan --}}
            @if ($item->tokens->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-3">Token / Tautan Akses</h3>
                    @foreach ($item->tokens as $token)
                        <div class="border rounded-lg p-4 mb-3">
                            <div class="flex flex-wrap justify-between items-start gap-2">
                                <div>
                                    <p class="font-semibold text-sm">
                                        {{ \App\Models\DtsenDataRequest::metodeAksesLabels()[$token->metode] ?? $token->metode }}
                                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ $token->statusBadgeClass() }}">{{ $token->statusLabel() }}</span>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Terbit {{ $token->issued_at?->format('d/m/Y H:i') }} oleh {{ $token->issuedBy->name ?? '-' }}
                                        · Berakhir {{ $token->expires_at?->format('d/m/Y H:i') ?? '-' }}
                                        · Terunduh {{ $token->download_count }}x
                                    </p>
                                    @if ($token->nama_berkas)
                                        <p class="text-xs text-gray-600 mt-1">Berkas: {{ $token->nama_berkas }}</p>
                                    @endif
                                </div>
                                @unless ($token->isRevoked())
                                    <form action="{{ route('admin.dtsen.permohonan.token.cabut', [$item->id, $token->id]) }}" method="POST"
                                          onsubmit="return confirm('Cabut token akses ini?')" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="alasan" placeholder="Alasan"
                                               class="px-2 py-1 border border-gray-300 rounded text-xs">
                                        <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold">Cabut</button>
                                    </form>
                                @endunless
                            </div>

                            @if ($token->downloadLogs->isNotEmpty())
                                <details class="mt-3">
                                    <summary class="text-xs text-blue-600 cursor-pointer">Log unduhan ({{ $token->downloadLogs->count() }})</summary>
                                    <ul class="mt-2 space-y-1 text-xs text-gray-600">
                                        @foreach ($token->downloadLogs->take(20) as $log)
                                            <li>{{ $log->downloaded_at->format('d/m/Y H:i') }} — {{ $log->user->name ?? '-' }} ({{ $log->ip_address }})</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Riwayat --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-3">Riwayat Aktivitas</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($logs as $log)
                        <li class="flex flex-wrap gap-x-3 border-l-2 border-gray-200 pl-3 py-1">
                            <span class="text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                            <span class="font-medium">{{ $log->action }}</span>
                            <span class="text-gray-600">{{ $log->note }}@if($log->actor) — {{ $log->actor->name }}@endif</span>
                        </li>
                    @empty
                        <li class="text-gray-500">Belum ada aktivitas.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Panel tindakan DKISP --}}
        <div class="space-y-6">
            @include('partials.dtsen.timeline', ['item' => $item])

            {{-- SLA per tahapan --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-3">SLA per Tahapan</h3>
                <ul class="space-y-2 text-sm">
                    @foreach (\App\Services\Dtsen\DtsenSlaService::stages() as $key => $stage)
                        @continue($key === 'akun')
                        @php($hasil = $sla->stageDuration($item, $key))
                        <li class="flex justify-between gap-2">
                            <span class="text-gray-600">{{ $stage['label'] }}</span>
                            @if (! $hasil)
                                <span class="text-gray-400 text-xs">belum mulai</span>
                            @else
                                <span class="text-xs font-semibold {{ $hasil['breached'] ? 'text-red-600' : 'text-green-700' }}">
                                    {{ $hasil['days'] }} / {{ $hasil['target'] }} hari kerja{{ $hasil['running'] ? ' (berjalan)' : '' }}
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <p class="text-xs text-gray-500 mt-3">Target end-to-end: {{ \App\Services\Dtsen\DtsenSlaService::totalTarget() }} hari kerja.</p>
            </div>

            {{-- Bola ada di Bapperida; DKISP tidak punya tindakan sampai substansi diputuskan. --}}
            @if (in_array($item->status, [\App\Models\DtsenDataRequest::STATUS_VERIF_SUBSTANSI, \App\Models\DtsenDataRequest::STATUS_KLARIFIKASI], true))
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-1">Menunggu Verifikasi Substansi</h3>
                    <p class="text-sm text-gray-600">
                        Dokumen sudah dinyatakan lengkap. Penilaian substansi KAK sedang ditangani
                        Koordinator Forum Satu Data Daerah (Bapperida). Pemrosesan &amp; QA terbuka
                        setelah permohonan dinyatakan diterima.
                    </p>
                    @if (auth()->user()?->hasPermission('Verifikasi Substansi DTSEN'))
                        <a href="{{ route('admin.dtsen.substansi.show', $item->id) }}"
                           class="mt-3 block text-center bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg font-semibold text-sm">
                            Buka Halaman Verifikasi Substansi &rarr;
                        </a>
                    @endif
                </div>
            @endif

            {{-- Form 3.1 — Verifikasi administrasi --}}
            @if (in_array($item->status, [\App\Models\DtsenDataRequest::STATUS_DIAJUKAN, \App\Models\DtsenDataRequest::STATUS_VERIF_ADMIN], true))
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-1">Verifikasi Administrasi</h3>
                    <p class="text-xs text-gray-500 mb-4">Form 3.1 — SLA 1 hari kerja.</p>
                    <form action="{{ route('admin.dtsen.permohonan.verifikasi-administrasi', $item->id) }}" method="POST" class="space-y-4"
                          x-data="{ hasil: 'lengkap' }">
                        @csrf
                        <div class="border rounded-lg p-3 bg-gray-50 space-y-2">
                            <p class="text-xs font-semibold text-gray-600">Checklist Kelengkapan &amp; Kesesuaian</p>
                            @foreach ($checklistAdministrasi as $field => $label)
                                <label class="flex items-start gap-2 text-sm">
                                    <input type="checkbox" name="{{ $field }}" value="1" class="mt-0.5" @checked($item->{$field})>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hasil</label>
                            <select name="hasil" x-model="hasil" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="lengkap">Lengkap &amp; sesuai — lanjut verifikasi substansi</option>
                                <option value="dikembalikan">Tidak lengkap — kembalikan ke pemohon</option>
                            </select>
                        </div>

                        <div x-show="hasil === 'dikembalikan'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Perbaikan <span class="text-red-500">*</span></label>
                            <textarea name="adm_catatan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $item->adm_catatan }}</textarea>
                        </div>

                        <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold">Simpan Verifikasi</button>
                    </form>
                </div>
            @endif

            {{-- Status verifikasi substansi (read-only bagi DKISP) --}}
            @if ($item->verif_substansi_at)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-2">Hasil Verifikasi Substansi</h3>
                    <p class="text-sm">
                        <span class="px-2 py-0.5 rounded text-xs font-semibold
                            {{ $item->sub_hasil === 'diterima' ? 'bg-green-100 text-green-700' : ($item->sub_hasil === 'ditolak' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ ucfirst($item->sub_hasil ?? '-') }}
                        </span>
                    </p>
                    @if ($item->sub_catatan)<p class="text-sm text-gray-600 mt-2">{{ $item->sub_catatan }}</p>@endif
                    @if ($item->sub_alasan_penolakan)<p class="text-sm text-red-700 mt-2">{{ $item->sub_alasan_penolakan }}</p>@endif
                    <p class="text-xs text-gray-500 mt-2">{{ $item->subVerifier->name ?? '-' }} — {{ $item->verif_substansi_at->format('d/m/Y H:i') }}</p>
                </div>
            @endif

            {{-- Form 3.4 — Pemrosesan & QA --}}
            @if (in_array($item->status, [\App\Models\DtsenDataRequest::STATUS_DITERIMA, \App\Models\DtsenDataRequest::STATUS_PEMROSESAN_QA], true))
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-1">Pemrosesan Data &amp; QA</h3>
                    <p class="text-xs text-gray-500 mb-4">Form 3.4 — SLA 2 hari kerja.</p>
                    <form action="{{ route('admin.dtsen.permohonan.pemrosesan-qa', $item->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="border rounded-lg p-3 bg-gray-50 space-y-2">
                            @foreach ([
                                'qa_check_pemilahan' => 'Pemilahan data',
                                'qa_check_agregasi' => 'Agregasi sesuai level akses',
                                'qa_check_mutu' => 'Penjaminan mutu (QA)',
                                'qa_check_kesesuaian' => 'Data sesuai variabel yang dimohonkan',
                            ] as $field => $label)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="{{ $field }}" value="1" @checked($item->{$field})>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pemrosesan</label>
                            <textarea name="qa_catatan" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $item->qa_catatan }}</textarea>
                        </div>
                        <label class="flex items-start gap-2 text-sm">
                            <input type="checkbox" name="selesaikan" value="1" class="mt-0.5">
                            <span>Tandai pemrosesan selesai
                                @if ($item->requiresBast())
                                    — pemohon akan diminta mengunggah BAST.
                                @else
                                    — token akses siap diterbitkan.
                                @endif
                            </span>
                        </label>
                        <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold">Simpan Pemrosesan</button>
                    </form>
                </div>
            @endif

            {{-- Form 4.1 — Verifikasi BAST --}}
            @if ($item->requiresBast() && $item->bast_file_path)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-1">Verifikasi BAST</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        {{ $item->bast_nomor }} · {{ $item->bast_tanggal?->format('d/m/Y') }}
                        <a href="{{ Storage::url($item->bast_file_path) }}" target="_blank" class="text-blue-600 hover:underline ml-1">📎 Buka</a>
                    </p>
                    @if ($item->bast_verified)
                        <div class="rounded-lg border border-green-300 bg-green-50 p-3 text-sm text-green-800">
                            BAST disahkan {{ $item->bast_at?->format('d/m/Y H:i') }} oleh {{ $item->bastVerifiedBy->name ?? 'DKISP' }}.
                        </div>
                    @else
                        <form action="{{ route('admin.dtsen.permohonan.verifikasi-bast', $item->id) }}" method="POST" class="space-y-3"
                              x-data="{ hasil: 'sah' }">
                            @csrf
                            <select name="hasil" x-model="hasil" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="sah">Sahkan BAST</option>
                                <option value="tolak">Kembalikan ke pemohon</option>
                            </select>
                            <div x-show="hasil === 'tolak'" x-cloak>
                                <textarea name="bast_catatan" rows="2" placeholder="Catatan perbaikan BAST"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $item->bast_catatan }}</textarea>
                            </div>
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Simpan</button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Form 4.2 — Kesepakatan infrastruktur --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-1">Kesepakatan Infrastruktur</h3>
                <p class="text-xs text-gray-500 mb-3">Form 4.2 — pilihan final penyaluran data.</p>
                <form action="{{ route('admin.dtsen.permohonan.infrastruktur', $item->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <select name="infra_final" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        @foreach (\App\Models\DtsenDataRequest::metodeAksesLabels() as $val => $label)
                            <option value="{{ $val }}" @selected(($item->infra_final ?? $item->metode_akses) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <textarea name="infra_parameter" rows="3" placeholder="Parameter teknis (endpoint / kredensial / kanal)"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $item->infra_parameter }}</textarea>
                    <button class="w-full bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Simpan Kesepakatan</button>
                </form>
            </div>

            {{-- Fitur 4.3 — Penerbitan token --}}
            @if ($readyForToken)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-1">Terbitkan Token / Tautan Unduh</h3>
                    <p class="text-xs text-gray-500 mb-4">
                        Fitur 4.3 — masa aktif bawaan {{ \App\Models\DtsenDataRequest::TOKEN_ACTIVE_DAYS }} hari kalender.
                        Menerbitkan token baru otomatis mencabut token aktif sebelumnya.
                    </p>
                    <form action="{{ route('admin.dtsen.permohonan.token', $item->id) }}" method="POST"
                          enctype="multipart/form-data" class="space-y-3"
                          x-data="{ metode: '{{ $item->infra_final ?? $item->metode_akses ?? 'excel_terenkripsi' }}' }">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Penyaluran</label>
                            <select name="metode" x-model="metode" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                @foreach (\App\Models\DtsenDataRequest::metodeAksesLabels() as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="metode === 'excel_terenkripsi'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Berkas Data (maks 50MB) <span class="text-red-500">*</span></label>
                            <input type="file" name="berkas" class="w-full text-sm border border-gray-300 rounded-lg p-2">
                        </div>

                        <div x-show="metode !== 'excel_terenkripsi'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Parameter Teknis <span class="text-red-500">*</span></label>
                            <textarea name="parameter" rows="3" placeholder="Endpoint, kredensial, atau detail kanal VPN"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $item->infra_parameter }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Masa Aktif (hari)</label>
                                <input type="number" name="masa_aktif_hari" min="1" max="365"
                                       value="{{ \App\Models\DtsenDataRequest::TOKEN_ACTIVE_DAYS }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Maks. Unduh</label>
                                <input type="number" name="max_download" min="1" max="1000" placeholder="tanpa batas"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>

                        <textarea name="catatan" rows="2" placeholder="Catatan untuk pemohon (opsional)"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>

                        <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold">
                            {{ $activeToken ? 'Terbitkan Token Baru' : 'Terbitkan Token' }}
                        </button>
                    </form>
                </div>
            @endif

            {{-- Penutupan berkas --}}
            @if ($item->status === \App\Models\DtsenDataRequest::STATUS_DATA_TERSEDIA)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-2">Selesaikan Permohonan</h3>
                    <form action="{{ route('admin.dtsen.permohonan.selesai', $item->id) }}" method="POST" class="space-y-3"
                          onsubmit="return confirm('Tandai permohonan ini selesai?')">
                        @csrf
                        <input type="text" name="catatan" placeholder="Catatan penutup (opsional)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <button class="w-full bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold text-sm">Tandai Selesai</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
