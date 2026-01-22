<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Pertimbangan - {{ $proposal->ticket_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.6;
            color: #333;
            padding: 40px 50px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
            position: relative;
        }

        .logo-section {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 25px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .logo-section img {
            width: 300px;
            height: auto;
            display: block;
            margin: 0 auto 15px auto;
        }

        .logo-section .province-name {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header h1 {
            font-size: 16pt;
            color: #1e40af;
            margin-bottom: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 13pt;
            color: #3b82f6;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .header h3 {
            font-size: 11pt;
            color: #1e40af;
            margin-bottom: 10px;
            font-weight: normal;
        }

        .header .subtitle {
            font-size: 9pt;
            color: #666;
        }

        .info-box {
            background-color: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }

        .info-box h3 {
            font-size: 12pt;
            color: #1e40af;
            margin-bottom: 8px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 4px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }

        .info-label {
            display: table-cell;
            width: 35%;
            font-weight: bold;
            color: #555;
            padding-right: 10px;
        }

        .info-value {
            display: table-cell;
            width: 65%;
        }

        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 13pt;
            font-weight: bold;
            color: #1e40af;
            background-color: #dbeafe;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-left: 4px solid #2563eb;
        }

        .subsection {
            margin-bottom: 12px;
        }

        .subsection-title {
            font-size: 11pt;
            font-weight: bold;
            color: #374151;
            margin-bottom: 6px;
        }

        .content {
            text-align: justify;
            margin-bottom: 8px;
            padding-left: 5px;
        }

        .risk-item {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .risk-header {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 8px;
            font-size: 11pt;
        }

        .risk-detail {
            margin-bottom: 6px;
            font-size: 9pt;
        }

        .risk-label {
            font-weight: bold;
            color: #78350f;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-high {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-medium {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-low {
            background-color: #d1fae5;
            color: #065f46;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #666;
            padding: 10px;
            border-top: 1px solid #e5e7eb;
        }

        .page-break {
            page-break-after: always;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table td {
            padding: 4px;
            vertical-align: top;
        }

        ul {
            margin-left: 20px;
            margin-bottom: 8px;
        }

        li {
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Permohonan Pertimbangan Pembangunan Aplikasi</h1>
        <h2>{{ $proposal->nama_aplikasi }}</h2>
        <h3>Pemerintah Provinsi Kalimantan Utara</h3>
        <div class="subtitle">
            {{ $proposal->ticket_number }} | {{ $proposal->created_at->format('d F Y') }}
        </div>
    </div>

    <!-- Logo -->
    <div class="logo-section">
        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo Kaltara">
        <div class="province-name">PEMERINTAH PROVINSI KALIMANTAN UTARA</div>
    </div>

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
                <div class="info-value">{{ ucfirst($proposal->jenis_layanan) }}</div>
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
            <div class="info-row">
                <div class="info-label">Lingkup Aplikasi</div>
                <div class="info-value">{{ ucfirst($proposal->lingkup_aplikasi) }}</div>
            </div>
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
            <div class="content">{!! strip_tags($proposal->deskripsi) !!}</div>
        </div>

        <div class="subsection">
            <div class="subsection-title">Tujuan</div>
            <div class="content">{!! strip_tags($proposal->tujuan) !!}</div>
        </div>

        <div class="subsection">
            <div class="subsection-title">Manfaat</div>
            <div class="content">{!! strip_tags($proposal->manfaat) !!}</div>
        </div>

        @if($proposal->integrasi_sistem_lain === 'ya' && $proposal->detail_integrasi)
        <div class="subsection">
            <div class="subsection-title">Detail Integrasi</div>
            <div class="content">{!! strip_tags($proposal->detail_integrasi) !!}</div>
        </div>
        @endif

        @if($proposal->kebutuhan_khusus)
        <div class="subsection">
            <div class="subsection-title">Kebutuhan Khusus</div>
            <div class="content">{!! strip_tags($proposal->kebutuhan_khusus) !!}</div>
        </div>
        @endif

        @if($proposal->dampak_tidak_dibangun)
        <div class="subsection">
            <div class="subsection-title">Dampak Jika Tidak Dibangun</div>
            <div class="content">{!! strip_tags($proposal->dampak_tidak_dibangun) !!}</div>
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
            <div class="content">{!! strip_tags($proposal->dasar_hukum) !!}</div>
        </div>
        @endif

        @if($proposal->uraian_permasalahan)
        <div class="subsection">
            <div class="subsection-title">Uraian Permasalahan</div>
            <div class="content">{!! strip_tags($proposal->uraian_permasalahan) !!}</div>
        </div>
        @endif

        @if($proposal->pihak_terkait)
        <div class="subsection">
            <div class="subsection-title">Pihak Terkait</div>
            <div class="content">{!! strip_tags($proposal->pihak_terkait) !!}</div>
        </div>
        @endif

        @if($proposal->ruang_lingkup)
        <div class="subsection">
            <div class="subsection-title">Ruang Lingkup</div>
            <div class="content">{!! strip_tags($proposal->ruang_lingkup) !!}</div>
        </div>
        @endif

        @if($proposal->analisis_biaya_manfaat)
        <div class="subsection">
            <div class="subsection-title">Analisis Biaya Manfaat</div>
            <div class="content">{!! strip_tags($proposal->analisis_biaya_manfaat) !!}</div>
        </div>
        @endif

        @if($proposal->lokasi_implementasi)
        <div class="subsection">
            <div class="subsection-title">Lokasi Implementasi</div>
            <div class="content">{!! strip_tags($proposal->lokasi_implementasi) !!}</div>
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
            <div class="content">{!! strip_tags($proposal->uraian_ruang_lingkup) !!}</div>
        </div>
        @endif

        @if($proposal->proses_bisnis)
        <div class="subsection">
            <div class="subsection-title">Proses Bisnis</div>
            <div class="content">{!! strip_tags($proposal->proses_bisnis) !!}</div>
        </div>
        @endif

        @if($proposal->kerangka_kerja)
        <div class="subsection">
            <div class="subsection-title">Kerangka Kerja</div>
            <div class="content">{!! strip_tags($proposal->kerangka_kerja) !!}</div>
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
            <div class="content">{!! strip_tags($proposal->peran_tanggung_jawab) !!}</div>
        </div>
        @endif

        @if($proposal->jadwal_pelaksanaan)
        <div class="subsection">
            <div class="subsection-title">Jadwal Pelaksanaan</div>
            <div class="content">{!! strip_tags($proposal->jadwal_pelaksanaan) !!}</div>
        </div>
        @endif

        @if($proposal->rencana_aksi)
        <div class="subsection">
            <div class="subsection-title">Rencana Aksi</div>
            <div class="content">{!! strip_tags($proposal->rencana_aksi) !!}</div>
        </div>
        @endif

        @if($proposal->keamanan_informasi)
        <div class="subsection">
            <div class="subsection-title">Keamanan Informasi</div>
            <div class="content">{!! strip_tags($proposal->keamanan_informasi) !!}</div>
        </div>
        @endif

        @if($proposal->sumber_daya_manusia)
        <div class="subsection">
            <div class="subsection-title">Sumber Daya Manusia</div>
            <div class="content">{!! strip_tags($proposal->sumber_daya_manusia) !!}</div>
        </div>
        @endif

        @if($proposal->sumber_daya_anggaran)
        <div class="subsection">
            <div class="subsection-title">Sumber Daya Anggaran</div>
            <div class="content">{!! strip_tags($proposal->sumber_daya_anggaran) !!}</div>
        </div>
        @endif

        @if($proposal->sumber_daya_sarana)
        <div class="subsection">
            <div class="subsection-title">Sumber Daya Sarana</div>
            <div class="content">{!! strip_tags($proposal->sumber_daya_sarana) !!}</div>
        </div>
        @endif

        @if($proposal->indikator_keberhasilan)
        <div class="subsection">
            <div class="subsection-title">Indikator Keberhasilan</div>
            <div class="content">{!! strip_tags($proposal->indikator_keberhasilan) !!}</div>
        </div>
        @endif

        @if($proposal->alih_pengetahuan)
        <div class="subsection">
            <div class="subsection-title">Alih Pengetahuan</div>
            <div class="content">{!! strip_tags($proposal->alih_pengetahuan) !!}</div>
        </div>
        @endif

        @if($proposal->pemantauan_pelaporan)
        <div class="subsection">
            <div class="subsection-title">Pemantauan dan Pelaporan</div>
            <div class="content">{!! strip_tags($proposal->pemantauan_pelaporan) !!}</div>
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
                    {{ $risiko['uraian_kejadian'] }}
                </div>
                @endif

                @if(isset($risiko['penyebab']) && $risiko['penyebab'])
                <div class="risk-detail">
                    <span class="risk-label">Penyebab:</span> {{ $risiko['penyebab'] }}
                </div>
                @endif

                @if(isset($risiko['dampak']) && $risiko['dampak'])
                <div class="risk-detail">
                    <span class="risk-label">Dampak:</span> {{ $risiko['dampak'] }}
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
                        - Rencana Aksi: {{ $risiko['rencana_aksi'] }}
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

    <!-- Footer -->
    <div class="footer">
        Dokumen ini digenerate secara otomatis oleh Sistem E-Layanan DKISP<br>
        {{ now()->format('d F Y H:i:s') }}
    </div>
</body>
</html>
