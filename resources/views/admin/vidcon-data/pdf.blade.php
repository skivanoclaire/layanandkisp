<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Fasilitasi Video Konferensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Data Fasilitasi Video Konferensi</h1>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Instansi</th>
                <th>Judul Kegiatan</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Platform</th>
                <th>Operator</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vidconData as $data)
            <tr>
                <td class="text-center">{{ $data->no }}</td>
                <td>{{ $data->nama_instansi }}</td>
                <td>{{ Str::limit($data->judul_kegiatan, 80) }}</td>
                <td>
                    @if($data->tanggal_mulai)
                        {{ $data->tanggal_mulai->format('d/m/Y') }}
                        @if($data->tanggal_selesai && $data->tanggal_selesai != $data->tanggal_mulai)
                            - {{ $data->tanggal_selesai->format('d/m/Y') }}
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($data->jam_mulai)
                        {{ $data->jam_mulai->format('H:i') }}
                        @if($data->jam_selesai)
                            - {{ $data->jam_selesai->format('H:i') }}
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td>{{ $data->platform ?? '-' }}</td>
                <td>{{ $data->operator ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 20px; font-size: 9px;">
        Dicetak pada: {{ date('d F Y H:i') }}
    </p>
</body>
</html>
