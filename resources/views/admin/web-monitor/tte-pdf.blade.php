<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pernyataan Kategori Sistem Elektronik</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 40px;
            font-size: 12pt;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .info-label {
            display: inline-block;
            width: 250px;
            font-weight: normal;
        }
        .info-value {
            display: inline-block;
            font-weight: normal;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .signature-section {
            margin-top: 40px;
            text-align: left;
        }
        .statement {
            margin: 30px 0;
            text-align: justify;
        }
        .signature-box {
            margin-top: 10px;
        }
        .signature-name {
            margin-top: 80px;
            font-weight: bold;
        }
        .signature-title {
            font-weight: normal;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PERNYATAAN KATEGORI SISTEM ELEKTRONIK</h1>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Instansi Penyelenggara Sistem Elektronik</span>
            <span class="info-value">: Pemerintah Provinsi Kalimantan Utara</span>
        </div>
        <div class="info-row">
            <span class="info-label">Nama Sistem Elektronik</span>
            <span class="info-value">: {{ $webMonitor->nama_sistem ?: '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Jenis</span>
            <span class="info-value">: {{ $webMonitor->jenis }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Nama Penanggung Jawab</span>
            <span class="info-value">: Deddy Harryady, S.Kom.</span>
        </div>
        <div class="info-row">
            <span class="info-label">Jabatan</span>
            <span class="info-value">: Kepala Bidang Aplikasi dan Informatika</span>
        </div>
    </div>

    <h3>Rincian Pertanyaan dan Jawaban Kategori Sistem Elektronik</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 70%;">Pertanyaan</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 10%;">Bobot</th>
            </tr>
        </thead>
        <tbody>
            @php
                $escAnswers = $webMonitor->esc_answers ?? [];
                $scoreMap = ['A' => 5, 'B' => 2, 'C' => 1];
                $totalScore = 0;

                // Definisi jawaban untuk setiap pertanyaan (spesifik per pertanyaan)
                $answerOptions = [
                    '1_1' => [
                        'A' => 'A. Lebih dari Rp.30 Miliar',
                        'B' => 'B. Lebih dari Rp.3 Miliar s/d Rp.30 Miliar',
                        'C' => 'C. Kurang dari Rp.3 Miliar'
                    ],
                    '1_2' => [
                        'A' => 'A. Lebih dari Rp.10 Miliar',
                        'B' => 'B. Lebih dari Rp.1 Miliar s/d Rp.10 Miliar',
                        'C' => 'C. Kurang dari Rp.1 Miliar'
                    ],
                    '1_3' => [
                        'A' => 'A. Peraturan atau Standar nasional dan internasional',
                        'B' => 'B. Peraturan atau Standar nasional',
                        'C' => 'C. Tidak ada Peraturan khusus'
                    ],
                    '1_4' => [
                        'A' => 'A. Teknik kriptografi khusus yang disertifikasi oleh Negara',
                        'B' => 'B. Teknik kriptografi sesuai standar industri, tersedia secara publik atau dikembangkan sendiri',
                        'C' => 'C. Tidak ada penggunaan teknik kriptografi'
                    ],
                    '1_5' => [
                        'A' => 'A. Lebih dari 5.000 pengguna',
                        'B' => 'B. 1.000 sampai dengan 5.000 pengguna',
                        'C' => 'C. Kurang dari 1.000 pengguna'
                    ],
                    '1_6' => [
                        'A' => 'A. Data pribadi yang memiliki hubungan dengan Data Pribadi lainnya',
                        'B' => 'B. Data pribadi yang bersifat individu dan/atau data pribadi yang terkait dengan kepemilikan badan usaha',
                        'C' => 'C. Tidak ada data pribadi'
                    ],
                    '1_7' => [
                        'A' => 'A. Sangat Rahasia',
                        'B' => 'B. Rahasia dan/ atau Terbatas',
                        'C' => 'C. Biasa'
                    ],
                    '1_8' => [
                        'A' => 'A. Proses yang berisiko mengganggu hajat hidup orang banyak dan memberi dampak langsung pada layanan publik',
                        'B' => 'B. Proses yang berisiko mengganggu hajat hidup orang banyak dan memberi dampak tidak langsung',
                        'C' => 'C. Proses yang hanya berdampak pada bisnis perusahaan'
                    ],
                    '1_9' => [
                        'A' => 'A. Tidak tersedianya layanan publik berskala nasional atau membahayakan pertahanan keamanan negara',
                        'B' => 'B. Tidak tersedianya layanan publik dalam 1 provinsi atau lebih',
                        'C' => 'C. Tidak tersedianya layanan publik dalam 1 kabupaten/kota atau lebih'
                    ],
                    '1_10' => [
                        'A' => 'A. Menimbulkan korban jiwa',
                        'B' => 'B. Terbatas pada kerugian finansial',
                        'C' => 'C. Mengakibatkan gangguan operasional sementara (tidak membahayakan finansial)'
                    ]
                ];
            @endphp
            @foreach($escQuestions as $qId => $question)
                @php
                    $qNum = (int) substr($qId, 2);
                    $answer = $escAnswers[$qId] ?? '-';
                    $score = isset($escAnswers[$qId]) ? ($scoreMap[$escAnswers[$qId]] ?? 0) : 0;
                    $totalScore += $score;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $qNum }}</td>
                    <td>
                        <strong>{{ $question }}</strong><br>
                        <span style="font-size: 10pt; color: #555;">
                            {{ $answerOptions[$qId]['A'] }}<br>
                            {{ $answerOptions[$qId]['B'] }}<br>
                            {{ $answerOptions[$qId]['C'] }}
                        </span>
                    </td>
                    <td style="text-align: center;">{{ $answer }}</td>
                    <td style="text-align: center;">{{ $score }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">Total Skor</td>
                <td style="text-align: center; font-weight: bold;">{{ $totalScore }}</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">Kategori</td>
                <td style="text-align: center; font-weight: bold;">{{ $webMonitor->esc_category ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="statement">
        Saya yang bertanda tangan dibawah ini menyatakan bahwa data diatas diisi sesuai keadaan sebenarnya.
    </div>

    <div class="signature-section">
        <div class="signature-box">
            Bulungan, {{ $tanggal }}
        </div>
        <div class="signature-title">
            Kepala Bidang Aplikasi dan Informatika
        </div>
        <div style="margin-top: 50px;">
            ${ttd_pengirim}
        </div>
        <div class="signature-name">
            ${nama_pengirim}
        </div>
        <div style="font-weight: normal;">
            NIP. ${nip_pengirim}
        </div>
    </div>
</body>
</html>
