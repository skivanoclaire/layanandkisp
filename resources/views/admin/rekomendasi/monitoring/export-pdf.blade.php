<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Rekomendasi Aplikasi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.4;
            color: #333;
            padding: 20px 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2563eb;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo-section img {
            width: 100px;
            height: auto;
            display: block;
            margin: 0 auto 10px auto;
        }

        .logo-section .province-name {
            font-size: 12pt;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header h1 {
            font-size: 14pt;
            color: #1e40af;
            margin-bottom: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header .subtitle {
            font-size: 8pt;
            color: #666;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 7pt;
        }

        table thead {
            background-color: #2563eb;
            color: white;
        }

        table thead th {
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1e40af;
        }

        table tbody td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tbody tr:hover {
            background-color: #e7f1ff;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 6pt;
            font-weight: bold;
            text-align: center;
        }

        .status-draft { background-color: #e5e7eb; color: #374151; }
        .status-diajukan { background-color: #dbeafe; color: #1e40af; }
        .status-perlu-revisi { background-color: #fed7aa; color: #9a3412; }
        .status-diproses { background-color: #fef3c7; color: #92400e; }
        .status-disetujui { background-color: #d1fae5; color: #065f46; }
        .status-ditolak { background-color: #fee2e2; color: #991b1b; }

        .fase-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 6pt;
            font-weight: bold;
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 7pt;
            color: #666;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .text-center { text-align: center; }
        .text-nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        @if (!empty($logoBase64))
            <div class="logo-section">
                <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo Kalimantan Utara">
                <div class="province-name">Pemerintah Provinsi Kalimantan Utara</div>
            </div>
        @endif

        <h1>Monitoring Rekomendasi Aplikasi</h1>
        <div class="subtitle">
            Diekspor pada: {{ now()->format('d F Y H:i:s') }} WIB
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th width="3%" class="text-center">No</th>
                <th width="8%">Nomor Tiket</th>
                <th width="15%">Nama Aplikasi</th>
                <th width="12%">Unit Kerja</th>
                <th width="12%">Pemohon</th>
                <th width="8%" class="text-center">Status</th>
                <th width="8%" class="text-center">Fase</th>
                <th width="10%" class="text-center">Tanggal Dibuat</th>
                <th width="10%" class="text-center">Tanggal Update</th>
                <th width="14%">Prioritas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($applications as $index => $app)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-nowrap">{{ $app->ticket_number }}</td>
                    <td>{{ $app->nama_aplikasi }}</td>
                    <td>{{ $app->pemilikProsesBisnis->nama ?? '-' }}</td>
                    <td>
                        {{ $app->user->name ?? '-' }}<br>
                        <small style="color: #666;">{{ $app->user->email ?? '-' }}</small>
                    </td>
                    <td class="text-center">
                        @php
                            $statusClass = 'status-' . str_replace('_', '-', $app->status);
                            $statusLabels = [
                                'draft' => 'Draft',
                                'diajukan' => 'Diajukan',
                                'perlu_revisi' => 'Perlu Revisi',
                                'diproses' => 'Diproses',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ];
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            {{ $statusLabels[$app->status] ?? ucfirst($app->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="fase-badge">
                            {{ $app->fase_saat_ini_display ?? ucfirst(str_replace('_', ' ', $app->fase_saat_ini)) }}
                        </span>
                    </td>
                    <td class="text-center text-nowrap">
                        {{ $app->created_at->format('d/m/Y') }}<br>
                        <small style="color: #666;">{{ $app->created_at->format('H:i') }}</small>
                    </td>
                    <td class="text-center text-nowrap">
                        {{ $app->updated_at->format('d/m/Y') }}<br>
                        <small style="color: #666;">{{ $app->updated_at->format('H:i') }}</small>
                    </td>
                    <td>{{ ucfirst(str_replace('_', ' ', $app->prioritas ?? '-')) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px;">
                        Tidak ada data aplikasi
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary -->
    <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; font-size: 7pt;">
        <strong>Total Aplikasi:</strong> {{ $applications->count() }} aplikasi
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini dicetak otomatis dari Sistem E-Layanan Provinsi Kalimantan Utara</p>
        <p>© {{ date('Y') }} Pemerintah Provinsi Kalimantan Utara. All rights reserved.</p>
    </div>
</body>
</html>
