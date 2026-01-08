<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Permohonan Subdomain</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0 0 5px 0;
        }
        .header p {
            font-size: 10px;
            margin: 2px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 9px;
        }
        td {
            font-size: 8px;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 15px;
            font-size: 8px;
            color: #666;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-menunggu { background-color: #fef3c7; color: #92400e; }
        .badge-proses { background-color: #dbeafe; color: #1e40af; }
        .badge-selesai { background-color: #d1fae5; color: #065f46; }
        .badge-ditolak { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Data Permohonan Subdomain</h1>
        @if($filterInfo['dari_tanggal'] || $filterInfo['sampai_tanggal'])
            <p>
                Periode:
                @if($filterInfo['dari_tanggal'])
                    {{ \Carbon\Carbon::parse($filterInfo['dari_tanggal'])->format('d/m/Y') }}
                @else
                    Awal
                @endif
                s/d
                @if($filterInfo['sampai_tanggal'])
                    {{ \Carbon\Carbon::parse($filterInfo['sampai_tanggal'])->format('d/m/Y') }}
                @else
                    Sekarang
                @endif
            </p>
        @endif
        @if($filterInfo['status'])
            <p>Status: {{ ucfirst($filterInfo['status']) }}</p>
        @endif
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>No. Tiket</th>
                <th>Tanggal Diajukan</th>
                <th>Nama</th>
                <th>Subdomain</th>
                <th>Instansi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $index => $request)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $request->ticket_no }}</td>
                <td>{{ $request->submitted_at ? $request->submitted_at->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $request->user->name ?? '-' }}</td>
                <td>{{ $request->subdomain_name }}.jogjakota.go.id</td>
                <td>{{ Str::limit($request->instansi, 40) }}</td>
                <td>
                    <span class="badge badge-{{ $request->status }}">
                        {{ ucfirst($request->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total: {{ $requests->count() }} permohonan</p>
    </div>
</body>
</html>
