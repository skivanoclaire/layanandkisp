{{--
    Ringkasan berkas permintaan data DTSEN — dipakai bersama oleh halaman
    verifikasi administrasi (DKISP) dan verifikasi substansi (Bapperida).
    Butuh: $item (DtsenDataRequest, sudah eager-load relasi), $wilayahLabels
--}}
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
            Permintaan ulang dari <span class="font-mono font-semibold">{{ $item->parentRequest->ticket_no }}</span> —
            {{ $item->ada_perubahan_signifikan
                ? 'pemohon menyatakan ADA perubahan signifikan pada tujuan/variabel.'
                : 'pemohon menyatakan TIDAK ada perubahan signifikan; verifikasi administrasi dapat ditempuh lewat jalur cepat.' }}
        </div>
    @endif

    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
        <div><dt class="text-gray-500">Pemohon</dt><dd class="font-medium">{{ $item->pemohon_nama }} @if($item->pemohon_nip)({{ $item->pemohon_nip }})@endif</dd></div>
        <div><dt class="text-gray-500">Jabatan</dt><dd class="font-medium">{{ $item->pemohon_jabatan ?: '-' }}</dd></div>
        <div><dt class="text-gray-500">Perangkat Daerah</dt><dd class="font-medium">{{ $item->unitKerja->nama ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Telepon</dt><dd class="font-medium">{{ $item->pemohon_telepon }}</dd></div>
        <div><dt class="text-gray-500">Nomor Surat</dt><dd class="font-medium">{{ $item->nomor_surat ?: '-' }} ({{ $item->tanggal_surat?->format('d/m/Y') ?? '-' }})</dd></div>
        <div><dt class="text-gray-500">Jenis Permintaan</dt><dd class="font-medium">{{ \App\Models\DtsenDataRequest::jenisPermintaanLabels()[$item->jenis_permintaan] ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Rilis DTSEN</dt><dd class="font-medium">{{ $item->release->nomor_rilis ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Akun Layanan</dt><dd class="font-mono text-xs">{{ $item->accountRequest->ticket_no ?? '-' }}</dd></div>
        <div class="md:col-span-2">
            <dt class="text-gray-500">Cakupan Wilayah</dt>
            <dd>
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

    {{-- Kelengkapan dokumen menurut level yang dimohonkan --}}
    <div class="mt-5 rounded-lg border border-gray-200 bg-gray-50 p-4">
        <p class="text-sm font-semibold text-gray-700 mb-2">Kelengkapan Dokumen ({{ $item->level_label }})</p>
        <ul class="space-y-1 text-sm">
            @php
                $kelengkapan = [
                    'Surat Permohonan Data' => (bool) $item->surat_permohonan_path,
                ];
                if ($item->requiresKak()) {
                    $kelengkapan['Kerangka Acuan Kerja (isian digital)'] = filled($item->kak_latar_belakang) && filled($item->kak_maksud_tujuan);
                    $kelengkapan['KAK bertanda tangan (opsional)'] = (bool) $item->kak_file_path;
                }
                if ($item->requiresDokumenPendukung()) {
                    $kelengkapan['Dokumen Pendukung'] = $item->documents->where('jenis', 'pendukung')->isNotEmpty();
                }
                if ($item->requiresBast()) {
                    $kelengkapan['BAST'] = (bool) $item->bast_file_path;
                }
            @endphp
            @foreach ($kelengkapan as $label => $ada)
                <li class="flex items-center gap-2">
                    <span class="{{ $ada ? 'text-green-600' : 'text-red-600' }} font-bold">{{ $ada ? '✓' : '✗' }}</span>
                    <span class="{{ $ada ? 'text-gray-700' : 'text-red-700' }}">{{ $label }}</span>
                </li>
            @endforeach
        </ul>
    </div>

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
        @foreach ($item->documents as $doc)
            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-blue-600 hover:underline">📎 {{ $doc->nama_dokumen }}</a>
        @endforeach
    </div>
</div>

{{-- Variabel & kegunaannya --}}
<div class="bg-white rounded-lg shadow-sm border p-6">
    <h3 class="font-bold text-gray-800 mb-3">Variabel yang Dimohonkan ({{ $item->requestVariables->count() }})</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">Kode</th>
                    <th class="px-3 py-2 text-left">Variabel</th>
                    <th class="px-3 py-2 text-center">Level</th>
                    <th class="px-3 py-2 text-left">Kegunaan / Alasan Kebutuhan</th>
                    <th class="px-3 py-2 text-center">Hasil</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($item->requestVariables as $rv)
                    <tr>
                        <td class="px-3 py-2 font-mono text-xs">{{ $rv->variable->kode ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $rv->variable->nama ?? '-' }}</td>
                        <td class="px-3 py-2 text-center">L{{ $rv->variable->level_minimal ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $rv->kegunaan }}</td>
                        <td class="px-3 py-2 text-center">
                            @if ($item->verif_substansi_at)
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $rv->disetujui ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $rv->disetujui ? 'Disetujui' : 'Ditolak' }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">Tidak ada variabel dipilih.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- KAK --}}
@if ($item->requiresKak() || filled($item->kak_latar_belakang))
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
                        <span class="text-red-600">Belum dicantumkan</span>
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
                        <span class="text-red-600">Belum dicantumkan</span>
                    @endforelse
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Teknik Pelindungan Data</dt>
                <dd>
                    @forelse ((array) $item->kak_teknik_pelindungan as $teknik)
                        <span class="inline-block bg-gray-100 rounded px-2 py-0.5 text-xs mr-1">{{ \App\Models\DtsenDataRequest::teknikPelindunganOptions()[$teknik] ?? $teknik }}</span>
                    @empty
                        <span class="text-red-600">Belum dicantumkan</span>
                    @endforelse
                </dd>
            </div>
            <div><dt class="text-gray-500">Retensi &amp; Pemusnahan</dt><dd>Batas {{ $item->kak_retensi_batas_waktu?->format('d/m/Y') ?? '-' }} — {{ $item->kak_metode_pemusnahan ?: '-' }}</dd></div>
            <div><dt class="text-gray-500">Pernyataan Tanggung Jawab</dt>
                <dd class="{{ $item->kak_pernyataan ? 'text-green-700' : 'text-red-600' }} font-medium">
                    {{ $item->kak_pernyataan ? 'Disetujui pemohon' : 'Belum disetujui' }}
                </dd>
            </div>
        </dl>
    </div>
@endif

{{-- Kesiapan teknis & keamanan (Form 2.6) --}}
<div class="bg-white rounded-lg shadow-sm border p-6">
    <h3 class="font-bold text-gray-800 mb-3">Kesiapan Teknis &amp; Keamanan</h3>
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
        <div><dt class="text-gray-500">Metode Akses Diinginkan</dt>
            <dd class="font-medium">{{ \App\Models\DtsenDataRequest::metodeAksesLabels()[$item->metode_akses] ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Metode Enkripsi / Kanal</dt><dd class="font-medium">{{ $item->metode_enkripsi ?: '-' }}</dd></div>
        <div class="md:col-span-2"><dt class="text-gray-500">Kapasitas Teknis SDM</dt><dd class="whitespace-pre-line">{{ $item->kapasitas_sdm ?: '-' }}</dd></div>
    </dl>
</div>

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
                <p class="text-xs text-gray-400 mt-1">Dicatat {{ $k->createdBy->name ?? '-' }}</p>
            </div>
        @endforeach
    </div>
@endif
