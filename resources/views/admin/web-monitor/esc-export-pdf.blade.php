<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Kategori Sistem Elektronik</title>
    <style>
        @page { margin: 18px 14px 26px 14px; }
        body { font-family: Arial, sans-serif; font-size: 8px; color: #222; }
        h1 { font-size: 13px; margin: 0 0 4px 0; text-align: center; color: #1e3a5f; }
        .subtitle { text-align: center; font-size: 9px; margin-bottom: 6px; color: #555; }
        .meta { margin-bottom: 6px; font-size: 8px; }
        .meta strong { color: #000; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #999; padding: 3px 3px; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }
        th { background: #dbeafe; color: #1e3a5f; text-align: center; font-size: 6.5px; line-height: 1.1; }
        th.left, td.left { text-align: left; }
        td { font-size: 7.5px; text-align: center; }
        .kat-strategis { color: #991b1b; font-weight: bold; }
        .kat-tinggi { color: #c2410c; font-weight: bold; }
        .kat-rendah { color: #065f46; font-weight: bold; }
        .note { margin-top: 8px; font-size: 7.5px; color: #444; line-height: 1.4; }
        .note strong { color: #000; }
    </style>
</head>
<body>
@php
    $questions = [
        '1_1'  => '1.1. Nilai investasi sistem elektronik yang terpasang',
        '1_2'  => '1.2. Total anggaran operasional tahunan yang dialokasikan untuk pengelolaan Sistem Elektronik',
        '1_3'  => '1.3. Memiliki kewajiban kepatuhan terhadap Peraturan atau Standar tertentu',
        '1_4'  => '1.4. Menggunakan teknik kriptografi khusus untuk keamanan informasi dalam Sistem Elektronik',
        '1_5'  => '1.5. Jumlah pengguna Sistem Elektronik',
        '1_6'  => '1.6. Data pribadi yang dikelola Sistem Elektronik',
        '1_7'  => '1.7. Tingkat klasifikasi/kekritisan Data yang ada dalam Sistem Elektronik',
        '1_8'  => '1.8. Tingkat kekritisan proses yang ada dalam Sistem Elektronik',
        '1_9'  => '1.9. Dampak dari kegagalan Sistem Elektronik',
        '1_10' => '1.10. Potensi kerugian/dampak negatif dari insiden ditembusnya keamanan informasi',
    ];
    $scoreMap = ['A' => 5, 'B' => 2, 'C' => 1];
@endphp

    <h1>REKAPITULASI KATEGORI SISTEM ELEKTRONIK (ESC)</h1>
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
                <th style="width: 2.5%;">No</th>
                <th class="left" style="width: 13%;">Nama Subdomain</th>
                <th class="left" style="width: 13%;">Instansi</th>
                @foreach($questions as $qId => $qText)
                    <th style="width: 5.5%;">{{ $qText }}</th>
                @endforeach
                <th style="width: 5%;">Total Skor</th>
                <th style="width: 6%;">Kategori</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $item)
                @php $answers = $item->esc_answers ?? []; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="left">{{ $item->subdomain ?: '-' }}</td>
                    <td class="left">{{ $item->instansi->nama ?? '-' }}</td>
                    @foreach($questions as $qId => $qText)
                        @php $ans = $answers[$qId] ?? null; @endphp
                        <td>{{ $ans ? $ans . ' (' . ($scoreMap[$ans] ?? 0) . ')' : '-' }}</td>
                    @endforeach
                    <td><strong>{{ $item->esc_total_score ?? '-' }}</strong></td>
                    <td class="kat-{{ \Illuminate\Support\Str::lower($item->esc_category ?? '') }}">{{ $item->esc_category ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15">Tidak ada data ESC yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="note">
        <strong>Keterangan:</strong> Setiap sel pertanyaan menampilkan huruf jawaban beserta poinnya, mis. <strong>A (5)</strong>.
        Poin: <strong>A = 5</strong>, <strong>B = 2</strong>, <strong>C = 1</strong>.
        Kategori berdasarkan Total Skor: <strong>Strategis</strong> (&ge; 36), <strong>Tinggi</strong> (16&ndash;35), <strong>Rendah</strong> (&lt; 16).
    </div>
</body>
</html>
