@extends('layouts.authenticated')

@section('title', '- Detail Permintaan Data DTSEN')
@section('header-title', 'Detail Permintaan Data DTSEN')

@section('content')
<div class="container mx-auto p-6">
    <a href="{{ route('user.dtsen.permohonan.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali ke daftar</a>

    <div class="mt-4">@include('partials.dtsen.errors')</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Ringkasan permohonan --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $item->nama_program }}</h2>
                        <p class="font-mono text-sm text-gray-500">{{ $item->ticket_no }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                        <p class="text-xs text-gray-500 mt-1">{{ $item->level_label }}</p>
                    </div>
                </div>

                @if ($item->parentRequest)
                    <div class="mb-4 rounded-lg border border-indigo-200 bg-indigo-50 p-3 text-sm text-indigo-900">
                        Permintaan ulang dari
                        <a href="{{ route('user.dtsen.permohonan.show', $item->parentRequest->id) }}" class="font-mono font-semibold underline">{{ $item->parentRequest->ticket_no }}</a>
                        — {{ $item->ada_perubahan_signifikan ? 'terdapat perubahan signifikan pada tujuan/variabel.' : 'tanpa perubahan signifikan (dapat ditempuh jalur cepat verifikasi administrasi).' }}
                    </div>
                @endif

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500">Pemohon</dt><dd class="font-medium">{{ $item->pemohon_nama }} @if($item->pemohon_nip)({{ $item->pemohon_nip }})@endif</dd></div>
                    <div><dt class="text-gray-500">Jabatan</dt><dd class="font-medium">{{ $item->pemohon_jabatan ?: '-' }}</dd></div>
                    <div><dt class="text-gray-500">Perangkat Daerah</dt><dd class="font-medium">{{ $item->unitKerja->nama ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Telepon</dt><dd class="font-medium">{{ $item->pemohon_telepon }}</dd></div>
                    <div><dt class="text-gray-500">Nomor Surat</dt><dd class="font-medium">{{ $item->nomor_surat ?: '-' }}</dd></div>
                    <div><dt class="text-gray-500">Tanggal Surat</dt><dd class="font-medium">{{ $item->tanggal_surat?->format('d/m/Y') ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Jenis Permintaan</dt><dd class="font-medium">{{ \App\Models\DtsenDataRequest::jenisPermintaanLabels()[$item->jenis_permintaan] ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Rilis DTSEN</dt><dd class="font-medium">{{ $item->release->nomor_rilis ?? '-' }}</dd></div>
                    <div class="md:col-span-2">
                        <dt class="text-gray-500">Cakupan Wilayah</dt>
                        <dd class="font-medium">
                            @forelse ($wilayahLabels as $label)
                                <span class="inline-block bg-gray-100 rounded px-2 py-0.5 text-xs mr-1 mb-1">{{ $label }}</span>
                            @empty
                                -
                            @endforelse
                            @if ($item->cakupan_wilayah_catatan)
                                <p class="text-xs text-gray-600 mt-1">{{ $item->cakupan_wilayah_catatan }}</p>
                            @endif
                        </dd>
                    </div>
                    <div class="md:col-span-2"><dt class="text-gray-500">Tujuan Penggunaan</dt><dd class="whitespace-pre-line">{{ $item->tujuan_penggunaan }}</dd></div>
                </dl>

                <div class="mt-4 flex flex-wrap gap-3 text-sm">
                    @if ($item->surat_permohonan_path)
                        <a href="{{ Storage::url($item->surat_permohonan_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 Surat Permohonan</a>
                    @endif
                    @if ($item->kak_file_path)
                        <a href="{{ Storage::url($item->kak_file_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 KAK Bertanda Tangan</a>
                    @endif
                    @if ($item->bast_file_path)
                        <a href="{{ Storage::url($item->bast_file_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 BAST</a>
                    @endif
                </div>
            </div>

            {{-- Variabel yang dimohonkan --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-3">Variabel yang Dimohonkan</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">Kode</th>
                                <th class="px-3 py-2 text-left">Variabel</th>
                                <th class="px-3 py-2 text-left">Kegunaan</th>
                                <th class="px-3 py-2 text-center">Hasil Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($item->requestVariables as $rv)
                                <tr>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $rv->variable->kode ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $rv->variable->nama ?? '-' }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $rv->kegunaan }}</td>
                                    <td class="px-3 py-2 text-center">
                                        @if ($item->verif_substansi_at)
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $rv->disetujui ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $rv->disetujui ? 'Disetujui' : 'Tidak disetujui' }}
                                            </span>
                                            @if ($rv->catatan_verifikator)
                                                <p class="text-xs text-gray-500 mt-1">{{ $rv->catatan_verifikator }}</p>
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-xs">Belum diverifikasi</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">Belum ada variabel dipilih.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Ringkasan KAK --}}
            @if ($item->requiresKak())
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-3">Kerangka Acuan Kerja</h3>
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-gray-500">Latar Belakang</dt><dd class="whitespace-pre-line">{{ $item->kak_latar_belakang ?: '-' }}</dd></div>
                        <div>
                            <dt class="text-gray-500">Dasar Hukum</dt>
                            <dd>
                                @forelse ((array) $item->kak_dasar_hukum as $dasar)
                                    <p>• {{ $dasar }}</p>
                                @empty
                                    -
                                @endforelse
                            </dd>
                        </div>
                        <div><dt class="text-gray-500">Maksud dan Tujuan</dt><dd class="whitespace-pre-line">{{ $item->kak_maksud_tujuan ?: '-' }}</dd></div>
                        <div><dt class="text-gray-500">Rencana Pemanfaatan / Metodologi</dt><dd class="whitespace-pre-line">{{ $item->kak_metodologi ?: '-' }}</dd></div>
                        <div><dt class="text-gray-500">Keluaran</dt><dd>{{ $item->kak_keluaran ?: '-' }} @if($item->kak_unit_akses)<span class="text-gray-500">— diakses {{ $item->kak_unit_akses }}</span>@endif</dd></div>
                        <div><dt class="text-gray-500">Jangka Waktu Pemanfaatan</dt><dd>{{ $item->kak_jangka_mulai?->format('d/m/Y') ?? '-' }} s.d. {{ $item->kak_jangka_akhir?->format('d/m/Y') ?? '-' }}</dd></div>
                        <div><dt class="text-gray-500">Infrastruktur Penyimpanan</dt><dd class="whitespace-pre-line">{{ $item->kak_infrastruktur_penyimpanan ?: '-' }}</dd></div>
                        <div>
                            <dt class="text-gray-500">Personel yang Diberi Akses</dt>
                            <dd>
                                @forelse ((array) $item->kak_personel_akses as $p)
                                    <p>• {{ $p['nama'] ?? '-' }} @if(!empty($p['nip']))({{ $p['nip'] }})@endif @if(!empty($p['jabatan']))— {{ $p['jabatan'] }}@endif</p>
                                @empty
                                    -
                                @endforelse
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Teknik Pelindungan Data</dt>
                            <dd>
                                @forelse ((array) $item->kak_teknik_pelindungan as $teknik)
                                    <span class="inline-block bg-gray-100 rounded px-2 py-0.5 text-xs mr-1">{{ \App\Models\DtsenDataRequest::teknikPelindunganOptions()[$teknik] ?? $teknik }}</span>
                                @empty
                                    -
                                @endforelse
                            </dd>
                        </div>
                        <div><dt class="text-gray-500">Retensi &amp; Pemusnahan</dt><dd>Batas {{ $item->kak_retensi_batas_waktu?->format('d/m/Y') ?? '-' }} — {{ $item->kak_metode_pemusnahan ?: '-' }}</dd></div>
                    </dl>
                </div>
            @endif

            {{-- Dokumen pendukung --}}
            @if ($item->documents->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-3">Dokumen Pendukung</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach ($item->documents as $doc)
                            <li>
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 {{ $doc->nama_dokumen }}</a>
                                <span class="text-xs text-gray-500">({{ $doc->jenis_label }})</span>
                                @if ($doc->keterangan)<p class="text-xs text-gray-500">{{ $doc->keterangan }}</p>@endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Berita acara klarifikasi --}}
            @if ($item->clarifications->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-3">Berita Acara Klarifikasi</h3>
                    @foreach ($item->clarifications as $k)
                        <div class="border-l-2 border-amber-300 pl-3 py-2 mb-3 text-sm">
                            <p class="font-medium">{{ $k->tanggal->format('d/m/Y') }} — {{ $k->tempat_media }}</p>
                            <p class="text-gray-600 mt-1"><span class="text-gray-500">Peserta:</span> {{ $k->peserta }}</p>
                            <p class="text-gray-600 mt-1"><span class="text-gray-500">Pokok:</span> {{ $k->pokok_klarifikasi }}</p>
                            @if ($k->hasil)<p class="text-gray-600 mt-1"><span class="text-gray-500">Hasil:</span> {{ $k->hasil }}</p>@endif
                            @if ($k->file_path)
                                <a href="{{ Storage::url($k->file_path) }}" target="_blank" class="text-blue-600 hover:underline text-xs">📎 Unduh berita acara</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Riwayat --}}
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="font-bold text-gray-800 mb-3">Riwayat &amp; Pemberitahuan</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($logs as $log)
                        <li class="flex flex-wrap gap-x-3 border-l-2 border-gray-200 pl-3 py-1">
                            <span class="text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                            <span class="font-medium">{{ $log->action }}</span>
                            <span class="text-gray-600">{{ $log->note }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500">Belum ada aktivitas.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Kolom kanan: timeline & tindakan --}}
        <div class="space-y-6">
            @include('partials.dtsen.timeline', ['item' => $item])

            @if ($item->status === \App\Models\DtsenDataRequest::STATUS_PERLU_PERBAIKAN)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-2">Perbaikan Diperlukan</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $item->adm_catatan }}</p>
                    <a href="{{ route('user.dtsen.permohonan.edit', $item->id) }}"
                       class="mt-3 block text-center bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">
                        Perbaiki &amp; Ajukan Ulang
                    </a>
                </div>
            @endif

            {{-- Form 4.1 — unggah BAST --}}
            @if ($item->canUploadBast())
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-1">Unggah BAST</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        Data Anda telah lolos pemrosesan &amp; QA. Unggah Berita Acara Serah Terima bertanda tangan
                        untuk memperoleh hak akses.
                    </p>
                    @if ($item->bast_file_path && ! $item->bast_verified && $item->bast_catatan)
                        <div class="mb-3 rounded-lg border border-orange-300 bg-orange-50 p-3 text-sm text-orange-900">
                            <p class="font-semibold">BAST dikembalikan</p>
                            <p class="mt-1">{{ $item->bast_catatan }}</p>
                        </div>
                    @elseif ($item->bast_file_path && ! $item->bast_verified)
                        <div class="mb-3 rounded-lg border border-yellow-300 bg-yellow-50 p-3 text-sm text-yellow-900">
                            BAST sudah diunggah dan menunggu verifikasi DKISP.
                        </div>
                    @endif
                    <form action="{{ route('user.dtsen.permohonan.bast', $item->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor BAST <span class="text-red-500">*</span></label>
                            <input type="text" name="bast_nomor" value="{{ old('bast_nomor', $item->bast_nomor) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal BAST <span class="text-red-500">*</span></label>
                            <input type="date" name="bast_tanggal" value="{{ old('bast_tanggal', $item->bast_tanggal?->format('Y-m-d')) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Berkas BAST (PDF) <span class="text-red-500">*</span></label>
                            <input type="file" name="bast_file" accept=".pdf" required
                                   class="w-full text-sm border border-gray-300 rounded-lg p-2">
                        </div>
                        <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Unggah BAST</button>
                    </form>
                </div>
            @endif

            {{-- Fitur 4.3 — akses data & countdown token --}}
            @if ($activeToken)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-1">Akses Data</h3>
                    @php($sisa = $activeToken->daysLeft())
                    <div class="rounded-lg border p-3 text-sm mb-3
                        {{ $sisa !== null && $sisa <= \App\Models\DtsenAccessToken::WARN_BEFORE_DAYS ? 'border-orange-300 bg-orange-50 text-orange-900' : 'border-green-300 bg-green-50 text-green-900' }}">
                        <p class="font-semibold">Berlaku {{ $sisa !== null ? max($sisa, 0) . ' hari lagi' : 'tanpa batas' }}</p>
                        <p class="text-xs mt-0.5">Sampai {{ $activeToken->expires_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">
                        Metode: <strong>{{ \App\Models\DtsenDataRequest::metodeAksesLabels()[$activeToken->metode] ?? $activeToken->metode }}</strong>
                        · Terunduh {{ $activeToken->download_count }}x
                        @if ($activeToken->max_download) dari maks. {{ $activeToken->max_download }}x @endif
                    </p>
                    <a href="{{ route('user.dtsen.akses.show', $activeToken->token) }}"
                       class="block text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">
                        Buka Halaman Akses Data
                    </a>
                </div>
            @endif

            {{-- Form 4.4 — perpanjangan masa akses --}}
            @if ($item->tokens->isNotEmpty())
                @php($pendingExt = $item->extensionRequests->firstWhere('status', \App\Models\DtsenExtensionRequest::STATUS_DIAJUKAN))
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-bold text-gray-800 mb-1">Perpanjangan Masa Akses</h3>
                    @if ($pendingExt)
                        <p class="text-sm text-gray-600">Permohonan perpanjangan {{ $pendingExt->durasi_hari }} hari sedang menunggu keputusan DKISP.</p>
                    @else
                        <form action="{{ route('user.dtsen.permohonan.perpanjangan.store', $item->id) }}" method="POST" class="space-y-3 mt-2">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Diminta (hari) <span class="text-red-500">*</span></label>
                                <input type="number" name="durasi_hari" min="1" max="{{ \App\Models\DtsenExtensionRequest::MAX_DURASI_HARI }}" value="30" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Perpanjangan <span class="text-red-500">*</span></label>
                                <textarea name="alasan" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                            </div>
                            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">Ajukan Perpanjangan</button>
                        </form>
                    @endif

                    @if ($item->extensionRequests->isNotEmpty())
                        <ul class="mt-4 space-y-2 text-xs text-gray-600 border-t pt-3">
                            @foreach ($item->extensionRequests->take(5) as $ext)
                                <li>
                                    <span class="px-1.5 py-0.5 rounded font-semibold {{ $ext->status_badge_class }}">{{ $ext->status_label }}</span>
                                    {{ $ext->durasi_hari }} hari — {{ $ext->created_at->format('d/m/Y') }}
                                    @if ($ext->catatan)<p class="text-gray-500">{{ $ext->catatan }}</p>@endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            {{-- Form 4.5 & Tahap 5 — tindak lanjut --}}
            @if ($item->canReport())
                <div class="bg-white rounded-lg shadow-sm border p-6 space-y-2">
                    <h3 class="font-bold text-gray-800 mb-2">Tindak Lanjut</h3>
                    <a href="{{ route('user.dtsen.pemanfaatan.create', ['permohonan' => $item->id]) }}"
                       class="block text-center bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg font-semibold text-sm">Lapor Pemanfaatan Data</a>
                    <a href="{{ route('user.dtsen.pemusnahan.create', ['permohonan' => $item->id]) }}"
                       class="block text-center bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg font-semibold text-sm">Berita Acara Pemusnahan</a>
                    <a href="{{ route('user.dtsen.permohonan.create', ['ulang_dari' => $item->id]) }}"
                       class="block text-center bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg font-semibold text-sm">Permintaan Ulang / Pembaruan</a>
                    <a href="{{ route('user.dtsen.insiden.create') }}"
                       class="block text-center bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-semibold text-sm">Lapor Insiden Keamanan</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
