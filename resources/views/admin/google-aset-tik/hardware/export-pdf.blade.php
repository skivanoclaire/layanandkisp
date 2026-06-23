<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Aset TIK - Hardware</title>
    <style>
        @page { margin: 20px 18px 30px 18px; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #222; }
        h1 { font-size: 14px; margin: 0 0 4px 0; text-align: center; color: #1e3a5f; }
        .subtitle { text-align: center; font-size: 10px; margin-bottom: 8px; color: #555; }
        .meta { margin-bottom: 8px; font-size: 9px; }
        .meta strong { color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 4px 5px; vertical-align: top; }
        th { background: #dbeafe; color: #1e3a5f; text-align: left; font-size: 8.5px; }
        td { font-size: 8.5px; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>DATA ASET TIK &mdash; PERANGKAT KERAS (HARDWARE)</h1>
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
                <th style="width: 3%;" class="text-center">No</th>
                <th style="width: 18%;">Nama OPD</th>
                <th style="width: 20%;">Nama Aset</th>
                <th style="width: 14%;">Merk/Type</th>
                <th style="width: 13%;">Jenis Aset TIK</th>
                <th style="width: 6%;" class="text-center">Tahun</th>
                <th style="width: 10%;">Kondisi</th>
                <th style="width: 7%;" class="text-center">Aset Vital</th>
                <th style="width: 9%;" class="text-center">Tahun Pengadaan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->nama_opd ?: '-' }}</td>
                    <td>{{ $item->nama_aset ?: '-' }}</td>
                    <td>{{ $item->merk_type ?: '-' }}</td>
                    <td>{{ $item->jenis_aset_tik ?: '-' }}</td>
                    <td class="text-center">{{ $item->tahun ?: '-' }}</td>
                    <td>{{ $item->keadaan_barang ?: '-' }}</td>
                    <td class="text-center">{{ $item->aset_vital ?: '-' }}</td>
                    <td class="text-center">{{ $item->tanggal_perolehan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
