<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Pertimbangan - {{ $proposal->ticket_number }}</title>
    <style>
        /* Dokumen resmi: hitam, putih, abu-abu. Tanpa warna. */
        @page {
            margin: 2.6cm 2.4cm 3cm 2.4cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.5;
            color: #111;
            margin: 2.6cm 2.4cm 3cm 2.4cm;
        }

        /* Kop surat */
        table.kop {
            width: 100%;
            border-collapse: collapse;
        }

        .kop td {
            border: none;
            vertical-align: middle;
            padding: 0;
        }

        .kop-logo {
            width: 70px;
        }

        .kop-logo img {
            width: 58px;
            height: auto;
        }

        .kop-teks {
            text-align: center;
            padding-right: 70px;
        }

        .kop-1 {
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .kop-2 {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 2px;
        }

        .kop-3 {
            font-size: 8.5pt;
            color: #444;
            margin-top: 2px;
        }

        .kop-garis {
            border-top: 2.5px solid #000;
            border-bottom: 0.8px solid #000;
            height: 3px;
            margin: 8px 0 14px 0;
        }

        /* Judul dokumen */
        .judul-dokumen {
            text-align: center;
            margin-bottom: 14px;
        }

        .judul-1 {
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .judul-2 {
            font-size: 10pt;
            margin-top: 3px;
            font-style: italic;
        }

        table.meta {
            width: 100%;
            margin-top: 10px;
            font-size: 9pt;
            border-collapse: collapse;
        }

        table.meta td {
            padding: 2px 4px;
            border: none;
            text-align: left;
        }

        /* Seksi */
        .section {
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #e6e6e6;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 8px;
            margin-bottom: 10px;
        }

        .subsection {
            margin-bottom: 10px;
        }

        .subsection-title {
            page-break-after: avoid;
            font-size: 9.5pt;
            font-weight: bold;
            margin-bottom: 4px;
            border-bottom: 0.5px solid #888;
            padding-bottom: 2px;
        }

        .content {
            text-align: justify;
            margin-bottom: 6px;
            padding-left: 4px;
        }

        /* Info dasar: tabel label-nilai dengan garis tipis */
        .info-box {
            border: 0.8px solid #888;
            padding: 8px 10px;
            margin-bottom: 10px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .info-label {
            display: table-cell;
            width: 32%;
            font-weight: bold;
            color: #333;
            padding-right: 10px;
        }

        .info-value {
            display: table-cell;
            width: 68%;
        }

        /* Tabel umum dan isi rich text */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        .rich table th,
        .rich table td {
            border: 0.6px solid #888;
            padding: 4px 6px;
            font-size: 9pt;
            vertical-align: top;
        }

        .rich table th {
            background-color: #e6e6e6;
            font-weight: bold;
        }

        .rich p {
            margin-bottom: 4px;
        }

        .rich ul,
        .rich ol {
            margin: 4px 0 6px 18px;
        }

        .rich h1,
        .rich h2,
        .rich h3,
        .rich h4 {
            font-size: 10pt;
            margin: 6px 0 4px 0;
        }

        /* Manajemen risiko */
        .risk-item {
            border: 0.8px solid #888;
            padding: 8px 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .risk-header {
            font-weight: bold;
            border-bottom: 0.5px solid #888;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .risk-detail {
            margin-bottom: 4px;
        }

        .risk-label {
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 1px 7px;
            border: 0.8px solid #000;
            font-size: 8.5pt;
            font-weight: bold;
            background-color: #fff;
            color: #000;
        }

        .badge-high {
            background-color: #000;
            color: #fff;
        }

        .badge-medium {
            background-color: #777;
            color: #fff;
            border-color: #777;
        }

        .badge-low {
            background-color: #e6e6e6;
            color: #000;
        }

        .page-break {
            page-break-after: always;
        }

        /* Pengesahan */
        table.pengesahan {
            margin-top: 24px;
            page-break-inside: avoid;
        }

        table.pengesahan td {
            width: 50%;
            border: none;
            vertical-align: top;
            text-align: center;
            font-size: 9.5pt;
        }

        .ttd-ruang {
            height: 62px;
        }

        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }

        .ttd-nip {
            font-size: 8.5pt;
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Kop surat -->
    <table class="kop">
        <tr>
            <td class="kop-logo">@if($logoBase64)<img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo">@endif</td>
            <td class="kop-teks">
                <div class="kop-1">PEMERINTAH PROVINSI KALIMANTAN UTARA</div>
                <div class="kop-2">DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN</div>
                <div class="kop-3">E-Layanan Digital DKISP Kalimantan Utara</div>
            </td>
        </tr>
    </table>
    <div class="kop-garis"></div>

    <!-- Judul dokumen -->
    <div class="judul-dokumen">
        <div class="judul-1">Permohonan Pertimbangan Pembangunan Aplikasi</div>
        <div class="judul-2">{{ $proposal->nama_aplikasi }}</div>
        <table class="meta">
            <tr>
                <td style="width: 16%">Nomor Tiket</td>
                <td style="width: 2%">:</td>
                <td style="width: 34%">{{ $proposal->ticket_number }}</td>
                <td style="width: 26%; white-space: nowrap;">Tanggal Pengajuan</td>
                <td style="width: 2%">:</td>
                <td>{{ $proposal->created_at->translatedFormat('d F Y') }}</td>
            </tr>
        </table>
    </div>

    {{-- Halaman pertama khusus sampul --}}

    <!-- Informasi Dasar -->
    <div class="section">
        <div class="section-title">I. INFORMASI DASAR</div>

        <div class="info-box">
            <div class="info-row">
                <div class="info-label">Nama Aplikasi</div>
                <div class="info-value">{{ $proposal->nama_aplikasi }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Prioritas</div>
                <div class="info-value">{{ ucfirst(str_replace('_', ' ', $proposal->prioritas)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Jenis Layanan</div>
                <div class="info-value">{{ ['publik' => 'Layanan Publik', 'internal' => 'Layanan Administrasi Pemerintahan'][$proposal->jenis_layanan] ?? ucfirst($proposal->jenis_layanan ?? '-') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Target Pengguna</div>
                <div class="info-value">{{ $proposal->target_pengguna }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Estimasi Pengguna</div>
                <div class="info-value">{{ number_format($proposal->estimasi_pengguna, 0, ',', '.') }} pengguna</div>
            </div>
            <div class="info-row">
                <div class="info-label">Pemilik Proses Bisnis</div>
                <div class="info-value">{{ $proposal->pemilikProsesBisnis?->nama ?? '-' }}</div>
            </div>
            {{-- Lingkup Aplikasi dinonaktifkan sementara
            <div class="info-row">
                <div class="info-label">Lingkup Aplikasi</div>
                <div class="info-value">{{ ucfirst($proposal->lingkup_aplikasi) }}</div>
            </div>
            --}}
            <div class="info-row">
                <div class="info-label">Platform</div>
                <div class="info-value">
                    @forelse(($proposal->platform ?? []) as $platform)
                        {{ ucfirst($platform) }}@if(!$loop->last), @endif
                    @empty
                        -
                    @endforelse
                </div>
            </div>
            @if($proposal->teknologi_diusulkan)
            <div class="info-row">
                <div class="info-label">Teknologi yang Diusulkan</div>
                <div class="info-value">{{ $proposal->teknologi_diusulkan }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Estimasi Waktu Pengembangan</div>
                <div class="info-value">{{ $proposal->estimasi_waktu_pengembangan }} bulan</div>
            </div>
            <div class="info-row">
                <div class="info-label">Estimasi Biaya</div>
                <div class="info-value">Rp {{ number_format($proposal->estimasi_biaya, 0, ',', '.') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Sumber Pendanaan</div>
                <div class="info-value">{{ strtoupper($proposal->sumber_pendanaan) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Integrasi dengan Sistem Lain</div>
                <div class="info-value">{{ $proposal->integrasi_sistem_lain === 'ya' ? 'Ya' : 'Tidak' }}</div>
            </div>
        </div>

        <div class="subsection">
            <div class="subsection-title">Deskripsi Aplikasi</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->deskripsi) !!}</div>
        </div>

        <div class="subsection">
            <div class="subsection-title">Tujuan</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->tujuan) !!}</div>
        </div>

        <div class="subsection">
            <div class="subsection-title">Manfaat</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->manfaat) !!}</div>
        </div>

        @if($proposal->integrasi_sistem_lain === 'ya' && $proposal->detail_integrasi)
        <div class="subsection">
            <div class="subsection-title">Detail Integrasi</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->detail_integrasi) !!}</div>
        </div>
        @endif

        @if($proposal->kebutuhan_khusus)
        <div class="subsection">
            <div class="subsection-title">Kebutuhan Khusus</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->kebutuhan_khusus) !!}</div>
        </div>
        @endif

        @if($proposal->dampak_tidak_dibangun)
        <div class="subsection">
            <div class="subsection-title">Dampak Jika Tidak Dibangun</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->dampak_tidak_dibangun) !!}</div>
        </div>
        @endif
    </div>

    <!-- Analisis Kebutuhan (Permenkomdigi No. 6 Tahun 2025) -->
    @if($proposal->dasar_hukum || $proposal->uraian_permasalahan || $proposal->pihak_terkait || $proposal->ruang_lingkup || $proposal->analisis_biaya_manfaat || $proposal->lokasi_implementasi)
    <div class="page-break"></div>
    <div class="section">
        <div class="section-title">II. ANALISIS KEBUTUHAN (Permenkomdigi No. 6 Tahun 2025)</div>

        @if($proposal->dasar_hukum)
        <div class="subsection">
            <div class="subsection-title">Dasar Hukum</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->dasar_hukum) !!}</div>
        </div>
        @endif

        @if($proposal->uraian_permasalahan)
        <div class="subsection">
            <div class="subsection-title">Uraian Permasalahan</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->uraian_permasalahan) !!}</div>
        </div>
        @endif

        @if($proposal->pihak_terkait)
        <div class="subsection">
            <div class="subsection-title">Pihak Terkait</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->pihak_terkait) !!}</div>
        </div>
        @endif

        @if($proposal->ruang_lingkup)
        <div class="subsection">
            <div class="subsection-title">Ruang Lingkup</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->ruang_lingkup) !!}</div>
        </div>
        @endif

        @if($proposal->analisis_biaya_manfaat)
        <div class="subsection">
            <div class="subsection-title">Analisis Biaya Manfaat</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->analisis_biaya_manfaat) !!}</div>
        </div>
        @endif

        @if($proposal->lokasi_implementasi)
        <div class="subsection">
            <div class="subsection-title">Lokasi Implementasi</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->lokasi_implementasi) !!}</div>
        </div>
        @endif
    </div>
    @endif

    <!-- Perencanaan (Permenkomdigi No. 6 Tahun 2025) -->
    @if($proposal->uraian_ruang_lingkup || $proposal->proses_bisnis || $proposal->kerangka_kerja || $proposal->pelaksana_pembangunan || $proposal->peran_tanggung_jawab || $proposal->jadwal_pelaksanaan || $proposal->rencana_aksi || $proposal->keamanan_informasi || $proposal->sumber_daya_manusia || $proposal->sumber_daya_anggaran || $proposal->sumber_daya_sarana || $proposal->indikator_keberhasilan || $proposal->alih_pengetahuan || $proposal->pemantauan_pelaporan)
    <div class="page-break"></div>
    <div class="section">
        <div class="section-title">III. PERENCANAAN (Permenkomdigi No. 6 Tahun 2025)</div>

        @if($proposal->uraian_ruang_lingkup)
        <div class="subsection">
            <div class="subsection-title">Uraian Ruang Lingkup</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->uraian_ruang_lingkup) !!}</div>
        </div>
        @endif

        @if($proposal->proses_bisnis)
        <div class="subsection">
            <div class="subsection-title">Proses Bisnis</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->proses_bisnis) !!}</div>
        </div>
        @endif

        @if($proposal->kerangka_kerja)
        <div class="subsection">
            <div class="subsection-title">Kerangka Kerja</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->kerangka_kerja) !!}</div>
        </div>
        @endif

        @if($proposal->pelaksana_pembangunan)
        <div class="subsection">
            <div class="subsection-title">Pelaksana Pembangunan</div>
            <div class="content">
                @php
                    $pelaksanaLabels = [
                        'menteri' => 'Menteri',
                        'swakelola' => 'Swakelola',
                        'pihak_ketiga' => 'Pihak Ketiga',
                    ];
                @endphp
                {{ $pelaksanaLabels[$proposal->pelaksana_pembangunan] ?? ucfirst(str_replace('_', ' ', $proposal->pelaksana_pembangunan)) }}
            </div>
        </div>
        @endif

        @if($proposal->peran_tanggung_jawab)
        <div class="subsection">
            <div class="subsection-title">Peran dan Tanggung Jawab</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->peran_tanggung_jawab) !!}</div>
        </div>
        @endif

        @if($proposal->jadwal_pelaksanaan)
        <div class="subsection">
            <div class="subsection-title">Jadwal Pelaksanaan</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->jadwal_pelaksanaan) !!}</div>
        </div>
        @endif

        @if($proposal->rencana_aksi)
        <div class="subsection">
            <div class="subsection-title">Rencana Aksi</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->rencana_aksi) !!}</div>
        </div>
        @endif

        @if($proposal->keamanan_informasi)
        <div class="subsection">
            <div class="subsection-title">Keamanan Informasi</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->keamanan_informasi) !!}</div>
        </div>
        @endif

        @if($proposal->sumber_daya_manusia)
        <div class="subsection">
            <div class="subsection-title">Sumber Daya Manusia</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->sumber_daya_manusia) !!}</div>
        </div>
        @endif

        @if($proposal->sumber_daya_anggaran)
        <div class="subsection">
            <div class="subsection-title">Sumber Daya Anggaran</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->sumber_daya_anggaran) !!}</div>
        </div>
        @endif

        @if($proposal->sumber_daya_sarana)
        <div class="subsection">
            <div class="subsection-title">Sumber Daya Sarana</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->sumber_daya_sarana) !!}</div>
        </div>
        @endif

        @if($proposal->indikator_keberhasilan)
        <div class="subsection">
            <div class="subsection-title">Indikator Keberhasilan</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->indikator_keberhasilan) !!}</div>
        </div>
        @endif

        @if($proposal->alih_pengetahuan)
        <div class="subsection">
            <div class="subsection-title">Alih Pengetahuan</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->alih_pengetahuan) !!}</div>
        </div>
        @endif

        @if($proposal->pemantauan_pelaporan)
        <div class="subsection">
            <div class="subsection-title">Pemantauan dan Pelaporan</div>
            <div class="content rich">{!! \App\Services\RichTextPdfFormatter::format($proposal->pemantauan_pelaporan) !!}</div>
        </div>
        @endif
    </div>
    @endif

    <!-- Manajemen Risiko (Permenkomdigi No. 6 Tahun 2025) -->
    @if(!empty($proposal->risiko_items) && count($proposal->risiko_items) > 0)
    <div class="page-break"></div>
    <div class="section">
        <div class="section-title">IV. MANAJEMEN RISIKO (Permenkomdigi No. 6 Tahun 2025)</div>

        @forelse($proposal->risiko_items ?? [] as $index => $risiko)
            @php
                // Get risk level based on besaran_risiko_nilai
                $besaranNilai = $risiko['besaran_risiko_nilai'] ?? 0;
                $riskLevelText = 'Tidak Diketahui';
                $badgeClass = 'badge';

                if ($besaranNilai >= 15) {
                    $riskLevelText = 'Sangat Tinggi';
                    $badgeClass = 'badge badge-high';
                } elseif ($besaranNilai >= 10) {
                    $riskLevelText = 'Tinggi';
                    $badgeClass = 'badge badge-high';
                } elseif ($besaranNilai >= 5) {
                    $riskLevelText = 'Sedang';
                    $badgeClass = 'badge badge-medium';
                } elseif ($besaranNilai >= 1) {
                    $riskLevelText = 'Rendah';
                    $badgeClass = 'badge badge-low';
                }

                // Get jenis risiko text
                $jenisText = 'Risiko ' . ($index + 1);
                if (isset($risiko['jenis_risiko'])) {
                    $jenisText = $risiko['jenis_risiko'] === 'positif' ? 'Positif (Peluang)' : 'Negatif (Ancaman)';
                }
            @endphp

            <div class="risk-item">
                <div class="risk-header">
                    Risiko SPBE #{{ $index + 1 }} - <span class="{{ $badgeClass }}">{{ $riskLevelText }}</span>
                </div>

                @if(isset($risiko['jenis_risiko']) && $risiko['jenis_risiko'])
                <div class="risk-detail">
                    <span class="risk-label">Jenis:</span> {{ $jenisText }}
                </div>
                @endif

                @if(isset($risiko['kategori_risiko']) && $risiko['kategori_risiko'])
                <div class="risk-detail">
                    <span class="risk-label">Kategori:</span> {{ ucwords(str_replace('_', ' ', $risiko['kategori_risiko'])) }}
                </div>
                @endif

                @if(isset($risiko['area_dampak']) && $risiko['area_dampak'])
                <div class="risk-detail">
                    <span class="risk-label">Area Dampak:</span> {{ ucwords(str_replace('_', ' ', $risiko['area_dampak'])) }}
                </div>
                @endif

                @if(isset($risiko['uraian_kejadian']) && $risiko['uraian_kejadian'])
                <div class="risk-detail">
                    <span class="risk-label">Uraian Kejadian:</span><br>
                    {!! nl2br(e($risiko['uraian_kejadian'])) !!}
                </div>
                @endif

                @if(isset($risiko['penyebab']) && $risiko['penyebab'])
                <div class="risk-detail">
                    <span class="risk-label">Penyebab:</span> {!! nl2br(e($risiko['penyebab'])) !!}
                </div>
                @endif

                @if(isset($risiko['dampak']) && $risiko['dampak'])
                <div class="risk-detail">
                    <span class="risk-label">Dampak:</span> {!! nl2br(e($risiko['dampak'])) !!}
                </div>
                @endif

                @if(isset($risiko['level_kemungkinan']) && isset($risiko['level_dampak']))
                <div class="risk-detail">
                    <span class="risk-label">Penilaian:</span>
                    Level Kemungkinan: {{ $risiko['level_kemungkinan'] }} ×
                    Level Dampak: {{ $risiko['level_dampak'] }} =
                    Besaran Risiko: {{ $besaranNilai }} ({{ $riskLevelText }})
                </div>
                @endif

                @if(isset($risiko['perlu_penanganan']) && $risiko['perlu_penanganan'] === 'ya')
                    <div class="risk-detail">
                        <span class="risk-label">Penanganan Risiko:</span>
                    </div>

                    @if(isset($risiko['opsi_penanganan']) && $risiko['opsi_penanganan'])
                    <div class="risk-detail">
                        - Opsi: {{ ucwords(str_replace('_', ' ', $risiko['opsi_penanganan'])) }}
                    </div>
                    @endif

                    @if(isset($risiko['rencana_aksi']) && $risiko['rencana_aksi'])
                    <div class="risk-detail">
                        - Rencana Aksi: {!! nl2br(e($risiko['rencana_aksi'])) !!}
                    </div>
                    @endif

                    @if(isset($risiko['jadwal_implementasi']) && $risiko['jadwal_implementasi'])
                    <div class="risk-detail">
                        - Jadwal: {{ $risiko['jadwal_implementasi'] }}
                    </div>
                    @endif

                    @if(isset($risiko['penanggung_jawab']) && $risiko['penanggung_jawab'])
                    <div class="risk-detail">
                        - Penanggung Jawab: {{ $risiko['penanggung_jawab'] }}
                    </div>
                    @endif
                @endif
            </div>
        @empty
            <div class="content">Tidak ada risiko teridentifikasi untuk usulan ini.</div>
        @endforelse
    </div>
    @endif

    <!-- Pengesahan -->
    <table class="pengesahan">
        <tr>
            <td>
                Pemohon,
                <div class="ttd-ruang"></div>
                <div class="ttd-nama">{{ $proposal->user?->name ?: '................................' }}</div>
                <div class="ttd-nip">NIP. {{ $proposal->user?->nip ?: '................................' }}</div>
            </td>
            <td>
                Diverifikasi oleh,
                <div class="ttd-ruang"></div>
                <div class="ttd-nama">{{ $proposal->verifikasi?->verifikator?->name ?: '................................' }}</div>
                <div class="ttd-nip">Tanggal: ........................</div>
            </td>
        </tr>
    </table>
</body>
</html>
