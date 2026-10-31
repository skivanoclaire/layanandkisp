<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $jenis === 'hadir' ? 'Daftar Hadir' : 'Rekap Konfirmasi Kehadiran' }}</title>
    <style>
        @page { margin: 28px 32px 40px 32px; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #111; }
        .kop { width: 100%; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 10px; }
        .kop td { border: 0; vertical-align: middle; }
        .kop .nama { text-align: center; font-size: 12px; line-height: 1.35; }
        h1 { text-align: center; font-size: 13px; margin: 6px 0 8px; text-decoration: underline; }
        .info { margin-bottom: 10px; }
        .info td { border: 0; padding: 1px 4px; font-size: 10px; vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #333; padding: 4px; vertical-align: middle; }
        table.data th { background: #e5e7eb; font-size: 9.5px; text-align: center; }
        table.data td { font-size: 9.5px; }
        table.data tr { page-break-inside: avoid; }
        .c { text-align: center; }
        .kecil { font-size: 8.5px; color: #444; }
        .ttd { height: 34px; position: relative; }
        .ttd .no { font-size: 8.5px; color: #555; }
        .ttd img { height: 32px; max-width: 110px; }
        .kanan { padding-left: 50%; }
        .pengesahan { width: 100%; margin-top: 18px; page-break-inside: avoid; }
        .pengesahan td { border: 0; font-size: 10px; }
        .footer { position: fixed; bottom: -24px; left: 0; right: 0; font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <table class="kop">
        <tr>
            <td style="width: 60px;">@if($logo)<img src="{{ $logo }}" style="width: 52px;">@endif</td>
            <td class="nama">
                <strong>PEMERINTAH PROVINSI KALIMANTAN UTARA</strong><br>
                <strong>{{ strtoupper($kegiatan->penyelenggara ?: 'Dinas Komunikasi, Informatika, Statistik dan Persandian') }}</strong>
            </td>
            <td style="width: 60px;"></td>
        </tr>
    </table>

    <h1>{{ $jenis === 'hadir' ? 'DAFTAR HADIR PESERTA' : 'REKAP KONFIRMASI KEHADIRAN' }}</h1>

    <table class="info">
        <tr><td style="width: 90px;">Kegiatan</td><td>:</td><td><strong>{{ $kegiatan->judul }}</strong></td></tr>
        <tr><td>Hari, Tanggal</td><td>:</td><td>{{ $kegiatan->tanggal_label }}</td></tr>
        @if($kegiatan->waktu)<tr><td>Waktu</td><td>:</td><td>{{ $kegiatan->waktu }}</td></tr>@endif
        <tr><td>Tempat</td><td>:</td><td>{{ $kegiatan->tempat }}</td></tr>
        @if($kegiatan->nomor_surat)<tr><td>Dasar</td><td>:</td><td>Surat Undangan Nomor {{ $kegiatan->nomor_surat }}</td></tr>@endif
    </table>

    @if($jenis === 'hadir')
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 25%;">Nama / NIP</th>
                    <th style="width: 18%;">Jabatan</th>
                    <th style="width: 23%;">Instansi</th>
                    <th style="width: 11%;">No. HP</th>
                    <th style="width: 19%;" colspan="2">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peserta as $i => $p)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>
                            {{ $p->nama }}
                            @if($p->nip)<br><span class="kecil">NIP. {{ $p->nip }}</span>@endif
                        </td>
                        <td>{{ $p->jabatan ?: '-' }}</td>
                        <td>
                            {{ $p->instansi }}
                            @if($p->konfirmasi === 'online')<br><span class="kecil">(hadir online)</span>@endif
                        </td>
                        <td>{{ $p->no_hp }}</td>
                        {{-- Tanda tangan zig-zag: nomor ganjil di kiri, genap di kanan --}}
                        @if($i % 2 === 0)
                            <td class="ttd" style="width: 9.5%; border-right: 0;">
                                <span class="no">{{ $i + 1 }}.</span>
                                @if($p->tanda_tangan)<img src="{{ $p->tanda_tangan }}">@endif
                            </td>
                            <td style="width: 9.5%; border-left: 0;"></td>
                        @else
                            <td style="width: 9.5%; border-right: 0;"></td>
                            <td class="ttd" style="width: 9.5%; border-left: 0;">
                                <span class="no">{{ $i + 1 }}.</span>
                                @if($p->tanda_tangan)<img src="{{ $p->tanda_tangan }}">@endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="7" class="c" style="padding: 14px;">Belum ada peserta yang melakukan presensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 26%;">Nama / NIP</th>
                    <th style="width: 28%;">Instansi</th>
                    <th style="width: 12%;">No. HP</th>
                    <th style="width: 11%;">Konfirmasi</th>
                    <th style="width: 19%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peserta as $i => $p)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>
                            {{ $p->nama }}
                            @if($p->nip)<br><span class="kecil">NIP. {{ $p->nip }}</span>@endif
                        </td>
                        <td>{{ $p->instansi }}</td>
                        <td>{{ $p->no_hp }}</td>
                        <td class="c">{{ $p->konfirmasi_label }}</td>
                        <td>
                            {{ $p->catatan }}
                            @if($p->hadir_at)<br><span class="kecil">Presensi {{ $p->hadir_at->format('d/m/Y H:i') }}</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="c" style="padding: 14px;">Belum ada konfirmasi kehadiran.</td></tr>
                @endforelse
            </tbody>
        </table>
        <p class="kecil" style="margin-top: 6px;">
            Hadir di lokasi: {{ $peserta->where('konfirmasi', 'hadir')->count() }} orang &middot;
            Hadir online: {{ $peserta->where('konfirmasi', 'online')->count() }} orang
        </p>
    @endif

    @if($kegiatan->pj_nama)
        <table class="pengesahan">
            <tr>
                <td style="width: 55%;"></td>
                <td>
                    Tanjung Selor, {{ $kegiatan->tanggal_mulai->locale('id')->isoFormat('D MMMM YYYY') }}<br>
                    {{ $kegiatan->pj_jabatan }}
                    <br><br><br><br><br>
                    <strong><u>{{ $kegiatan->pj_nama }}</u></strong>
                    @if($kegiatan->pj_nip)<br>NIP. {{ $kegiatan->pj_nip }}@endif
                </td>
            </tr>
        </table>
    @endif

    <div class="footer">Dicetak dari E-Layanan DKISP pada {{ now()->locale('id')->isoFormat('D MMMM YYYY HH:mm') }} WITA</div>
</body>
</html>
