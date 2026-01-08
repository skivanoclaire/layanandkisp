<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Rekomendasi Aplikasi - {{ $form->ticket_number }}</title>
    <style>
        @page {
            margin: 1.5cm 2cm 2cm 2cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo-container {
            margin-bottom: 10px;
        }

        .header h3 {
            margin: 5px 0;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0;
            font-size: 11pt;
        }

        .letter-info {
            margin-bottom: 20px;
        }

        .letter-info table {
            width: 100%;
        }

        .letter-info td {
            padding: 3px 0;
            vertical-align: top;
        }

        .letter-info td:first-child {
            width: 120px;
        }

        .letter-info td:nth-child(2) {
            width: 10px;
        }

        .content {
            text-align: justify;
            margin-bottom: 30px;
        }

        .content p {
            margin-bottom: 12px;
        }

        .signature-section {
            margin-top: 50px;
            text-align: right;
        }

        .signature-box {
            display: inline-block;
            text-align: center;
            min-width: 250px;
        }

        .signature-space {
            height: 80px;
        }

        .underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 250px;
            font-weight: bold;
        }

        .section-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .detail-table {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }

        .detail-table td {
            padding: 5px;
            vertical-align: top;
        }

        .detail-table td:first-child {
            width: 200px;
            font-weight: bold;
        }

        .detail-table td:nth-child(2) {
            width: 20px;
        }

        ul {
            margin-left: 20px;
        }

        ul li {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    {{-- KOP SURAT --}}
    <div class="header">
        <table width="100%" style="border:none;">
            <tr>
                <td width="100" style="text-align:center;vertical-align:middle;">
                    @if($logoBase64)
                        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo Kaltara" style="width:80px;height:auto;">
                    @endif
                </td>
                <td style="text-align:center;vertical-align:middle;">
                    <h3 style="margin:2px 0;">PEMERINTAH PROVINSI KALIMANTAN UTARA</h3>
                    <h3 style="margin:2px 0;">DINAS KOMUNIKASI, INFORMATIKA, STATISTIK DAN PERSANDIAN</h3>
                    <p style="margin:5px 0;font-size:10pt;">Jalan Rambutan Gedung Gabungan Dinas Lantai V, Kode Pos 77212</p>
                    <p style="margin:2px 0;font-size:10pt;">E-mail: diskominfo@kaltaraprov.go.id Website: diskominfo.kaltaraprov.go.id</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- INFO SURAT --}}
    <div class="letter-info">
        <table>
            <tr>
                <td>Nomor</td>
                <td>:</td>
                <td><strong>{{ $letterNumber }}</strong></td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>:</td>
                <td>-</td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td><strong>Rekomendasi Pengembangan Aplikasi</strong></td>
            </tr>
        </table>
    </div>

    {{-- ISI SURAT --}}
    <div class="content">
        <p>Kepada Yth,<br>
            <strong>Pengguna Aplikasi e-Layanan DKISP</strong><br>
            Di Tempat
        </p>

        <p>Dengan hormat,</p>

        <p>
            Berdasarkan hasil kajian dan analisis terhadap usulan pengembangan aplikasi yang disampaikan dengan
            nomor tiket <strong>{{ $form->ticket_number }}</strong>, dengan ini kami menyampaikan rekomendasi sebagai
            berikut:
        </p>

        <div class="section-title">I. DATA APLIKASI</div>
        <table class="detail-table">
            <tr>
                <td>Judul Aplikasi</td>
                <td>:</td>
                <td>{{ $form->judul_aplikasi }}</td>
            </tr>
            <tr>
                <td>Instansi</td>
                <td>:</td>
                <td>{{ $form->pemilikProsesBisnis ? $form->pemilikProsesBisnis->nama : '-' }}</td>
            </tr>
            <tr>
                <td>Target Waktu Implementasi</td>
                <td>:</td>
                <td>{{ $form->target_waktu }}</td>
            </tr>
            <tr>
                <td>Sasaran Pengguna</td>
                <td>:</td>
                <td>{{ $form->sasaran_pengguna }}</td>
            </tr>
            <tr>
                <td>Lokasi Implementasi</td>
                <td>:</td>
                <td>{{ $form->lokasi_implementasi }}</td>
            </tr>
        </table>

        <div class="section-title">II. DASAR PERTIMBANGAN</div>
        <p>
            Berdasarkan dokumen analisis kebutuhan dan perencanaan yang telah disampaikan, aplikasi ini dikembangkan
            dengan pertimbangan:
        </p>
        <ul>
            <li><strong>Maksud dan Tujuan:</strong> {{ Str::limit(strip_tags($form->maksud_tujuan), 200) }}</li>
            <li><strong>Ruang Lingkup:</strong> {{ Str::limit(strip_tags($form->ruang_lingkup), 200) }}</li>
            <li><strong>Manfaat:</strong> Sesuai dengan analisis biaya-manfaat yang telah dilakukan</li>
        </ul>

        <div class="section-title">III. REKOMENDASI</div>
        <p>
            Berdasarkan hasil kajian teknis dan analisis risiko, kami merekomendasikan usulan pengembangan aplikasi
            <strong>"{{ $form->judul_aplikasi }}"</strong> untuk dapat dilaksanakan dengan catatan:
        </p>
        <ul>
            <li>Pengembangan dilakukan sesuai dengan kerangka kerja: <strong>{{ strip_tags($form->kerangka_kerja) }}</strong></li>
            <li>Pelaksanaan pembangunan oleh: <strong>{{ strip_tags($form->pelaksana_pembangunan) }}</strong></li>
            <li>Jadwal pelaksanaan sesuai rencana: <strong>{{ strip_tags($form->jadwal_pelaksanaan) }}</strong></li>
            <li>Memperhatikan aspek keamanan informasi dan kepatuhan terhadap regulasi yang berlaku</li>
            <li>Melaksanakan manajemen risiko secara berkelanjutan sesuai dokumen yang telah disusun</li>
        </ul>

        <p>
            Demikian surat rekomendasi ini kami sampaikan untuk dapat digunakan sebagaimana mestinya.
        </p>
    </div>

    {{-- TANDA TANGAN --}}
    <div class="signature-section" style="page-break-inside: avoid;">
        <div class="signature-box" style="text-align: left;">
            <div style="margin-bottom: 5px;">
                Tanjung Selor, {{ $date }}
            </div>
            <div style="margin-bottom: 10px;">
                Kepala Dinas Komunikasi, Informatika,<br>
                Statistik dan Persandian
            </div>
            <div class="signature-space" style="margin:10px 0;">
                {{-- QR Code sebagai pengganti tanda tangan basah --}}
                <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code Verifikasi" style="width:120px;height:120px;">
            </div>
            <div style="margin-top: 40px;">
                <div style="font-weight: bold;">{{ $signerName }}</div>
                <div style="margin-top: 3px; font-weight: bold;">{{ $signerRank }}</div>
                <div style="margin-top: 3px; font-weight: bold;">NIP. {{ $nipKepalaDinas }}</div>
            </div>
        </div>
    </div>

    {{-- TEMBUSAN --}}
    <div style="margin-top: 30px; font-size: 11pt; page-break-inside: avoid;">
        <p style="margin-bottom: 5px;"><strong>Tembusan:</strong></p>
        <ol style="margin-left: 20px; margin-top: 5px;">
            <li>Yth. Gubernur Kalimantan Utara (sebagai laporan)</li>
            <li>Arsip</li>
        </ol>
    </div>

</body>

</html>
