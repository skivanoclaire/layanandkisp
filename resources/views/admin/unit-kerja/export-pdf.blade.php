<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master Data Instansi</title>
    <style>
        @page { margin: 20px 20px 30px 20px; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 14px; margin: 0 0 4px 0; text-align: center; color: #065f46; }
        .subtitle { text-align: center; font-size: 10px; margin-bottom: 8px; color: #555; }
        .meta { margin-bottom: 8px; font-size: 9px; }
        .meta strong { color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 4px 5px; vertical-align: top; }
        th { background: #d1fae5; color: #065f46; text-align: left; font-size: 9px; }
        td { font-size: 9px; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: bold; }
        .badge-induk { background: #dbeafe; color: #1e40af; }
        .badge-cabang { background: #ede9fe; color: #6b21a8; }
        .badge-sekolah { background: #d1fae5; color: #065f46; }
        .badge-pusat { background: #ffedd5; color: #c2410c; }
    </style>
</head>
<body>
    <h1>MASTER DATA INSTANSI</h1>
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
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 65%;">Nama Instansi</th>
                <th style="width: 30%;">Tipe</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>
                        @if($item->tipe === 'Induk Perangkat Daerah')
                            <span class="badge badge-induk">{{ $item->tipe }}</span>
                        @elseif($item->tipe === 'Cabang Perangkat Daerah')
                            <span class="badge badge-cabang">{{ $item->tipe }}</span>
                        @elseif($item->tipe === 'Sekolah')
                            <span class="badge badge-sekolah">{{ $item->tipe }}</span>
                        @elseif($item->tipe === 'Instansi Pusat/Lainnya')
                            <span class="badge badge-pusat">{{ $item->tipe }}</span>
                        @else
                            {{ $item->tipe }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
