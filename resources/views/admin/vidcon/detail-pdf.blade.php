<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Permohonan Video Conference - {{ $item->ticket_no }}</title>
    <style>
        @page { margin: 25px 30px; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #111;
        }
        .header {
            border-bottom: 2px solid #6b21a8;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header td { border: none; padding: 0; vertical-align: middle; }
        .header h1 {
            font-size: 15px;
            margin: 0;
            color: #6b21a8;
        }
        .header p {
            font-size: 9px;
            margin: 2px 0 0 0;
            color: #555;
        }
        h2 {
            font-size: 11px;
            margin: 14px 0 5px 0;
            padding: 4px 6px;
            background-color: #f3e8ff;
            color: #6b21a8;
            border-left: 3px solid #6b21a8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table.data th, table.data td {
            border: 1px solid #bbb;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        table.data th {
            background-color: #f5f5f5;
            width: 28%;
            font-weight: bold;
        }
        table.list th, table.list td {
            border: 1px solid #bbb;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        table.list th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .muted { color: #777; }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-menunggu { background-color: #fef3c7; color: #92400e; }
        .badge-proses   { background-color: #dbeafe; color: #1e40af; }
        .badge-selesai  { background-color: #d1fae5; color: #065f46; }
        .badge-ditolak  { background-color: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 18px;
            padding-top: 6px;
            border-top: 1px solid #ccc;
            font-size: 8px;
            color: #666;
        }
        .changes {
            margin-top: 4px;
            font-size: 8px;
        }
        .changes th, .changes td {
            border: 1px solid #ddd;
            padding: 2px 4px;
        }
        .changes th { background-color: #fafafa; }
        .break-all { word-wrap: break-word; }
    </style>
</head>
<body>
    @php
        $statusLabel = match($item->status) {
            'menunggu' => 'Menunggu',
            'proses'   => 'Diproses',
            'selesai'  => 'Selesai',
            'ditolak'  => 'Ditolak',
            default    => ucfirst((string) $item->status),
        };
        $dash = fn ($v) => ($v === null || $v === '') ? '-' : $v;
    @endphp

    <div class="header">
        <table>
            <tr>
                @if($logoBase64)
                    <td style="width: 55px;">
                        <img src="data:image/png;base64,{{ $logoBase64 }}" style="width: 45px;">
                    </td>
                @endif
                <td>
                    <h1>Detail Permohonan Video Conference</h1>
                    <p>Dinas Komunikasi, Informatika, Statistik dan Persandian Provinsi Kalimantan Utara</p>
                    <p>No. Tiket: <strong>{{ $item->ticket_no }}</strong></p>
                </td>
            </tr>
        </table>
    </div>

    <h2>Ringkasan Permohonan</h2>
    <table class="data">
        <tr>
            <th>No. Tiket</th>
            <td>{{ $item->ticket_no }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span class="badge badge-{{ $item->status }}">{{ $statusLabel }}</span></td>
        </tr>
        <tr>
            <th>Diajukan</th>
            <td>{{ $item->submitted_at ? $item->submitted_at->format('d/m/Y H:i') . ' WITA' : '-' }}</td>
        </tr>
        <tr>
            <th>Kelengkapan Informasi Meeting</th>
            <td>
                {{ $item->getProgressPercentage() }}%
                &mdash; Link Meeting: {{ $item->link_meeting ? 'terisi' : 'belum diisi' }},
                Meeting ID: {{ $item->meeting_id ? 'terisi' : 'belum diisi' }},
                Passcode: {{ $item->meeting_password ? 'terisi' : 'belum diisi' }}
                @if($item->isStale())
                    <br><em>Stale ({{ $item->daysSinceLastUpdate() }} hari tanpa pembaruan)</em>
                @endif
            </td>
        </tr>
    </table>

    <h2>Informasi Pemohon</h2>
    <table class="data">
        <tr><th>Nama</th><td>{{ $dash($item->nama) }}</td></tr>
        <tr><th>NIP</th><td>{{ $dash($item->nip) }}</td></tr>
        <tr><th>Instansi / Unit Kerja</th><td>{{ $item->unitKerja->nama ?? '-' }}</td></tr>
        <tr><th>Email</th><td>{{ $dash($item->email_pemohon) }}</td></tr>
        <tr><th>No. HP</th><td>{{ $dash($item->no_hp) }}</td></tr>
        <tr><th>Akun Pengaju</th><td>{{ $item->user->name ?? '-' }}{{ $item->user?->email ? ' (' . $item->user->email . ')' : '' }}</td></tr>
    </table>

    <h2>Detail Kegiatan</h2>
    <table class="data">
        <tr><th>Judul Kegiatan</th><td>{{ $dash($item->judul_kegiatan) }}</td></tr>
        <tr><th>Deskripsi</th><td>{{ $dash($item->deskripsi_kegiatan) }}</td></tr>
        <tr><th>Lokasi Kegiatan</th><td>{{ $dash($item->lokasi_kegiatan) }}</td></tr>
        <tr>
            <th>Tanggal Pelaksanaan</th>
            <td>
                {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }}
                s/d
                {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-' }}
            </td>
        </tr>
        <tr><th>Jam</th><td>{{ $dash($item->jam_mulai) }} - {{ $dash($item->jam_selesai) }} WITA</td></tr>
        <tr><th>Platform</th><td>{{ $dash($item->platform_display) }}</td></tr>
        <tr><th>Jenis Layanan</th><td>{{ $item->jenis_layanan_display }}</td></tr>
        <tr><th>Jumlah Peserta</th><td>{{ $item->jumlah_peserta ? $item->jumlah_peserta . ' orang' : '-' }}</td></tr>
        <tr><th>Keperluan Khusus</th><td>{{ $dash($item->keperluan_khusus) }}</td></tr>
    </table>

    @if($item->pemohon_link_meeting || $item->pemohon_meeting_id || $item->pemohon_meeting_password)
        <h2>Informasi Meeting dari Pemohon</h2>
        <table class="data">
            <tr><th>Link Meeting</th><td class="break-all">{{ $dash($item->pemohon_link_meeting) }}</td></tr>
            <tr><th>Meeting ID</th><td>{{ $dash($item->pemohon_meeting_id) }}</td></tr>
            <tr><th>Passcode</th><td>{{ $dash($item->pemohon_meeting_password) }}</td></tr>
        </table>
    @endif

    <h2>Informasi Meeting (Hasil Proses Admin)</h2>
    <table class="data">
        <tr><th>Link Meeting</th><td class="break-all">{{ $dash($item->link_meeting) }}</td></tr>
        <tr><th>Meeting ID</th><td>{{ $dash($item->meeting_id) }}</td></tr>
        <tr><th>Password</th><td>{{ $dash($item->meeting_password) }}</td></tr>
        <tr><th>Akun Zoom</th><td>{{ $item->akun_zoom ? 'Akun ' . $item->akun_zoom : '-' }}</td></tr>
        <tr><th>Informasi Tambahan</th><td>{{ $dash($item->informasi_tambahan) }}</td></tr>
        <tr>
            <th>Operator Ditugaskan</th>
            <td>
                @if($item->operators->count() > 0)
                    @foreach($item->operators as $operator)
                        {{ $loop->iteration }}. {{ $operator->name }} ({{ $operator->phone ?: '-' }})<br>
                    @endforeach
                @else
                    -
                @endif
            </td>
        </tr>
        <tr><th>Catatan Admin</th><td>{{ $dash($item->admin_notes) }}</td></tr>
    </table>

    <h2>Riwayat Pemrosesan</h2>
    <table class="data">
        <tr><th>Diproses Oleh</th><td>{{ $item->processedBy->name ?? '-' }}</td></tr>
        <tr><th>Mulai Diproses</th><td>{{ $item->processing_at ? $item->processing_at->format('d/m/Y H:i') : '-' }}</td></tr>
        <tr><th>Selesai</th><td>{{ $item->completed_at ? $item->completed_at->format('d/m/Y H:i') : '-' }}</td></tr>
        <tr><th>Ditolak</th><td>{{ $item->rejected_at ? $item->rejected_at->format('d/m/Y H:i') : '-' }}</td></tr>
        <tr><th>Jumlah Pembaruan Informasi</th><td>{{ $item->info_update_count ?? 0 }} kali</td></tr>
        <tr>
            <th>Pembaruan Terakhir</th>
            <td>
                {{ $item->last_info_updated_at ? $item->last_info_updated_at->format('d/m/Y H:i') : '-' }}
                @if($item->lastUpdatedBy) oleh {{ $item->lastUpdatedBy->name }} @endif
            </td>
        </tr>
    </table>

    @if($vidconData)
        <h2>Data Fasilitasi Vidcon Terkait</h2>
        <table class="data">
            <tr><th>ID Data Fasilitasi</th><td>{{ $vidconData->id }}</td></tr>
            <tr><th>Nomor Surat</th><td>{{ $dash($vidconData->nomor_surat) }}</td></tr>
            <tr><th>Lokasi</th><td>{{ $dash($vidconData->lokasi) }}</td></tr>
            <tr><th>Operator</th><td>{{ $dash($vidconData->operator) }}</td></tr>
            <tr><th>Akun Zoom</th><td>{{ $vidconData->akun_zoom ? 'Akun ' . $vidconData->akun_zoom : '-' }}</td></tr>
            <tr><th>Keterangan</th><td>{{ $dash($vidconData->keterangan) }}</td></tr>
        </table>
    @endif

    <h2>Riwayat Aktivitas</h2>
    @if($activities->count() > 0)
        <table class="list">
            <thead>
                <tr>
                    <th style="width: 15%;">Waktu</th>
                    <th style="width: 20%;">Aktivitas</th>
                    <th style="width: 18%;">Oleh</th>
                    <th>Keterangan &amp; Perubahan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities as $activity)
                    @php
                        $actionLabel = match($activity->action) {
                            'info_updated'   => 'Informasi Meeting Diupdate',
                            'status_changed' => 'Status Berubah',
                            'approved'       => 'Permohonan Disetujui',
                            default          => $activity->action,
                        };
                        $keys = array_unique(array_merge(
                            array_keys($activity->old_values ?? []),
                            array_keys($activity->new_values ?? [])
                        ));
                    @endphp
                    <tr>
                        <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $actionLabel }}</td>
                        <td>{{ $activity->user->name ?? 'Unknown' }}</td>
                        <td>
                            {{ $activity->notes ?: '-' }}
                            @if(count($keys) > 0)
                                <table class="changes">
                                    <thead>
                                        <tr>
                                            <th style="width: 25%;">Field</th>
                                            <th style="width: 37%;">Sebelum</th>
                                            <th style="width: 38%;">Sesudah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($keys as $key)
                                            @php
                                                $old = $activity->old_values[$key] ?? null;
                                                $new = $activity->new_values[$key] ?? null;
                                                $fmt = fn ($v) => is_array($v)
                                                    ? json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                                                    : (($v === null || $v === '') ? '-' : $v);
                                            @endphp
                                            <tr>
                                                <td>{{ $key }}</td>
                                                <td class="break-all">{{ $fmt($old) }}</td>
                                                <td class="break-all">{{ $fmt($new) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="muted">Belum ada aktivitas tercatat.</p>
    @endif

    <div class="footer">
        <p>
            Dicetak pada {{ now()->format('d F Y H:i') }} WITA oleh {{ $printedBy }}.
            Dokumen ini dihasilkan otomatis dari Sistem E-Layanan DKISP Provinsi Kalimantan Utara.
        </p>
    </div>
</body>
</html>
