<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>QR {{ $mode === 'konfirmasi' ? 'Konfirmasi Kehadiran' : 'Presensi' }} - {{ $kegiatan->judul }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; color: #111827; background: #e5e7eb; }
        .lembar { width: 186mm; min-height: 271mm; margin: 10mm auto; background: #fff; padding: 12mm 14mm;
                  border: 3mm solid #15803d; border-radius: 6mm; display: flex; flex-direction: column; align-items: center; text-align: center; }
        .kop { display: flex; align-items: center; gap: 4mm; }
        .kop img { width: 18mm; height: 18mm; }
        .kop div { text-align: left; font-size: 11pt; line-height: 1.3; }
        .judul-besar { margin: 9mm 0 2mm; font-size: 30pt; font-weight: 800; letter-spacing: .5pt; color: #15803d; }
        .sub { font-size: 13pt; color: #374151; margin-bottom: 6mm; }
        .kegiatan { font-size: 15pt; font-weight: 700; line-height: 1.35; max-width: 160mm; }
        .info { margin-top: 3mm; font-size: 11.5pt; color: #374151; }
        .qr { margin: 8mm 0 5mm; width: 110mm; height: 110mm; }
        .qr svg { width: 100%; height: 100%; }
        .langkah { text-align: left; font-size: 11.5pt; line-height: 1.6; background: #f0fdf4; border-radius: 3mm; padding: 4mm 6mm; width: 150mm; }
        .langkah ol { margin: 0; padding-left: 6mm; }
        .url { margin-top: auto; padding-top: 5mm; font-size: 10.5pt; color: #4b5563; word-break: break-all; }
        .aksi { text-align: center; margin: 6mm 0 0; }
        .aksi button, .aksi a { font-size: 11pt; padding: 8px 18px; border-radius: 6px; border: 0; cursor: pointer; text-decoration: none; margin: 0 4px; }
        .aksi button { background: #15803d; color: #fff; }
        .aksi a { background: #fff; color: #15803d; border: 1px solid #15803d; }
        @media print {
            body { background: #fff; }
            .aksi { display: none; }
            .lembar { margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="aksi">
        <button type="button" onclick="window.print()">🖨️ Cetak</button>
        <a href="{{ route('admin.presensi-kegiatan.qr', [$kegiatan, 'mode' => $mode === 'konfirmasi' ? 'presensi' : 'konfirmasi']) }}">
            Lihat QR {{ $mode === 'konfirmasi' ? 'Presensi' : 'Konfirmasi' }}
        </a>
    </div>

    <div class="lembar">
        <div class="kop">
            <img src="{{ asset('logokaltarafix.png') }}" alt="">
            <div>
                <strong>PEMERINTAH PROVINSI KALIMANTAN UTARA</strong><br>
                {{ $kegiatan->penyelenggara ?: 'Dinas Komunikasi, Informatika, Statistik dan Persandian' }}
            </div>
        </div>

        <div class="judul-besar">{{ $mode === 'konfirmasi' ? 'KONFIRMASI KEHADIRAN' : 'PRESENSI PESERTA' }}</div>
        <div class="sub">Pindai QR Code di bawah ini dengan kamera ponsel</div>

        <div class="kegiatan">{{ $kegiatan->judul }}</div>
        <div class="info">{{ $kegiatan->tanggal_label }}@if($kegiatan->waktu) &middot; {{ $kegiatan->waktu }}@endif<br>{{ $kegiatan->tempat }}</div>

        <div class="qr">{!! $svg !!}</div>

        <div class="langkah">
            <ol>
                <li>Buka kamera ponsel lalu arahkan ke QR Code.</li>
                <li>Masuk dengan akun <strong>E-Layanan / SSO Kaltara</strong> agar data terisi otomatis, atau isi data secara manual.</li>
                @if($mode === 'presensi')
                    <li>Bubuhkan tanda tangan, lalu tekan <strong>Simpan Presensi</strong>.</li>
                @else
                    <li>Pilih <strong>Hadir di lokasi</strong> atau <strong>Hadir online</strong> (khusus unit kerja di luar Tanjung Selor), lalu kirim.</li>
                @endif
            </ol>
        </div>

        <div class="url">{{ $url }}</div>
    </div>
</body>
</html>
