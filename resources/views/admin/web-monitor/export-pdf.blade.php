<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master Data Subdomain</title>
    <style>
        @page { margin: 20px 20px 30px 20px; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #222; }
        h1 { font-size: 14px; margin: 0 0 4px 0; text-align: center; color: #5b21b6; }
        .subtitle { text-align: center; font-size: 10px; margin-bottom: 8px; color: #555; }
        .meta { margin-bottom: 8px; font-size: 9px; }
        .meta strong { color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 4px 5px; vertical-align: top; }
        th { background: #ede9fe; color: #4c1d95; text-align: left; font-size: 9px; }
        td { font-size: 8.5px; }
        tr.footer-row td { background: #f3f4f6; font-weight: bold; }
        .text-center { text-align: center; }
        .badge-active { color: #065f46; font-weight: bold; }
        .badge-inactive { color: #991b1b; font-weight: bold; }
    </style>
</head>
<body>
    <h1>MASTER DATA SUBDOMAIN</h1>
    <div class="subtitle">Dokumen ini digenerate secara otomatis oleh Sistem E-Layanan DKISP</div>

    <div class="meta">
        <strong>Tanggal Generate:</strong> {{ $tanggal }}
        &nbsp;|&nbsp;
        <strong>Total Data:</strong> {{ $data->count() }}
        @foreach($filterLabels as $label => $value)
            &nbsp;|&nbsp; <strong>{{ $label }}:</strong> {{ $value }}
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 18%;">Instansi</th>
                <th style="width: 18%;">Nama Sistem</th>
                <th style="width: 22%;">Subdomain</th>
                <th style="width: 18%;">Jenis</th>
                <th style="width: 12%;">IP Address</th>
                <th style="width: 9%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->instansi->nama ?? '-' }}</td>
                    <td>{{ $item->nama_aplikasi ?: $item->nama_sistem }}</td>
                    <td>{{ $item->subdomain ?: '-' }}</td>
                    <td>{{ $item->jenis ?? '-' }}</td>
                    <td>{{ $item->ip_address ?? '-' }}</td>
                    <td class="text-center">
                        @if($item->status === 'active')
                            <span class="badge-active">Aktif</span>
                        @else
                            <span class="badge-inactive">Tidak Aktif</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
