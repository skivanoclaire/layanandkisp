<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Survei Kepuasan Layanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 10px;
            color: #666;
        }
        .info-box {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .info-box p {
            margin: 3px 0;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stats-item {
            display: table-cell;
            width: 16.66%;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .stats-item .label {
            font-size: 8px;
            color: #666;
            margin-bottom: 3px;
        }
        .stats-item .value {
            font-size: 14px;
            font-weight: bold;
            color: #16a34a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #333;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
        }
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 8px;
            color: #666;
        }
        .rating-stars {
            color: #fbbf24;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN SURVEI KEPUASAN LAYANAN DIGITAL</h1>
        <p>Pemerintah Provinsi Kalimantan Utara</p>
        <p>Dinas Komunikasi dan Informatika</p>
    </div>

    <div class="info-box">
        <p><strong>Filter Laporan:</strong></p>
        <p>Subdomain: {{ $filters['subdomain'] }}</p>
        <p>Periode: {{ $filters['date_from'] }} s/d {{ $filters['date_to'] }}</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
        <p>Total Data: {{ $stats['total_surveys'] }} survei</p>
    </div>

    <h3 style="margin-bottom: 10px;">Ringkasan Statistik</h3>
    <div class="stats-grid">
        <div class="stats-item">
            <div class="label">Kecepatan</div>
            <div class="value">{{ $stats['avg_kecepatan'] }}</div>
        </div>
        <div class="stats-item">
            <div class="label">Kemudahan</div>
            <div class="value">{{ $stats['avg_kemudahan'] }}</div>
        </div>
        <div class="stats-item">
            <div class="label">Kualitas</div>
            <div class="value">{{ $stats['avg_kualitas'] }}</div>
        </div>
        <div class="stats-item">
            <div class="label">Responsif</div>
            <div class="value">{{ $stats['avg_responsif'] }}</div>
        </div>
        <div class="stats-item">
            <div class="label">Keamanan</div>
            <div class="value">{{ $stats['avg_keamanan'] }}</div>
        </div>
        <div class="stats-item">
            <div class="label">Keseluruhan</div>
            <div class="value">{{ $stats['avg_keseluruhan'] }}</div>
        </div>
    </div>

    <h3 style="margin-bottom: 10px;">Detail Data Survei</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 8%;">Tanggal</th>
                <th style="width: 12%;">User</th>
                <th style="width: 15%;">Subdomain</th>
                <th style="width: 12%;">Aplikasi</th>
                <th style="width: 5%;">Kec</th>
                <th style="width: 5%;">Mud</th>
                <th style="width: 5%;">Kua</th>
                <th style="width: 5%;">Res</th>
                <th style="width: 5%;">Amn</th>
                <th style="width: 5%;">Sel</th>
                <th style="width: 5%;">Avg</th>
                <th style="width: 15%;">Feedback</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($surveys as $index => $survey)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $survey->created_at->format('d/m/Y') }}</td>
                    <td>{{ $survey->user->name }}</td>
                    <td>{{ $survey->webMonitor->subdomain }}</td>
                    <td>{{ $survey->webMonitor->nama_aplikasi ?? $survey->webMonitor->nama_instansi ?? '-' }}</td>
                    <td style="text-align: center;">{{ $survey->rating_kecepatan }}</td>
                    <td style="text-align: center;">{{ $survey->rating_kemudahan }}</td>
                    <td style="text-align: center;">{{ $survey->rating_kualitas }}</td>
                    <td style="text-align: center;">{{ $survey->rating_responsif }}</td>
                    <td style="text-align: center;">{{ $survey->rating_keamanan }}</td>
                    <td style="text-align: center;">{{ $survey->rating_keseluruhan }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $survey->average_rating }}</td>
                    <td>
                        @if($survey->saran || $survey->kelebihan || $survey->kekurangan)
                            Ada
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($surveys->isEmpty())
        <p style="text-align: center; padding: 20px; color: #666;">Tidak ada data survei untuk periode yang dipilih</p>
    @endif

    <div class="footer">
        <p>Dicetak oleh: {{ auth()->user()->name }}</p>
        <p>Dokumen ini dihasilkan secara otomatis oleh sistem E-Layanan DKISP Prov. Kaltara</p>
    </div>
</body>
</html>
