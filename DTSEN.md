# Identifikasi Kebutuhan Form Aplikasi Permohonan DTSEN
**Berdasarkan Draft Juknis Berbagi Pakai Data DTSEN Provinsi Kalimantan Utara (adopsi Permen PPN/Bappenas No. 7 Tahun 2025)**

---

## A. Gambaran Umum Alur & Aktor

Aplikasi melayani 5 tahapan utama (Bab III Juknis):

1. **Pembuatan Akun** → 2. **Pengajuan Permintaan Data** → 3. **Pengecekan Permintaan (Verifikasi Administrasi & Substansi)** → 4. **Pengiriman/Pemberian Hak Akses (BAST & Token)** → 5. **Pemanfaatan Data & Pelaporan**

**Aktor/role yang perlu didukung sistem:**

| Role | Pihak | Fungsi di aplikasi |
|---|---|---|
| Pemohon (OPD Pengguna) | Kepala OPD (penanggung jawab) + petugas/pelaksana teknis yang ditunjuk | Registrasi akun, ajukan permohonan, unduh data, lapor pemanfaatan |
| Admin/Prosesor | DKISP | Kelola akun, verifikasi administrasi, verifikasi teknis-keamanan, pemrosesan data & QA, terbitkan token/tautan unduh, rekap laporan |
| Verifikator Substansi | Koordinator Forum Satu Data Daerah | Verifikasi substansi KAK, setujui/tolak, undang klarifikasi |
| Koordinator | Bapperida | Sediakan daftar variabel/indikator DTSEN, himpun permintaan, koordinator pelaporan |
| Penandatangan BAST | Kepala DKISP / Koordinator Tim Pelaksana & Kepala OPD | TTE pada BAST |

**Level hak akses (Bab IV) — menentukan kelengkapan dokumen:**

| Level | Jenis Data | Dokumen Wajib |
|---|---|---|
| 1 | Agregat (Terbuka) | Tanpa persyaratan, akses publik — tidak lewat alur permohonan |
| 2 | Kustomisasi (Terbatas) | Surat Permohonan Data |
| 3 | Mikro tanpa nama & alamat (Terbatas) | Surat Permohonan + KAK |
| 4 | BNBA (Terbatas) | Surat Permohonan + KAK + Dokumen Pendukung + BAST |

> Jika permohonan mencakup lebih dari satu level, sistem wajib memberlakukan persyaratan level tertinggi yang dimohonkan.

---

## B. Tahap 1 — Pembuatan Akun

### Form 1.1: Registrasi Akun Layanan DTSEN (diisi OPD)
Mengacu Lampiran I & Bab V huruf A:

**Data Surat Permohonan Akun**
- Nomor surat
- Sifat surat
- Jumlah lampiran
- Tanggal surat
- Nama Perangkat Daerah (dropdown master OPD)
- Upload berkas surat permohonan bertanda tangan Kepala Perangkat Daerah (PDF; dukung TTE)

**Data Calon Pengguna Akun** (bisa lebih dari satu personel — repeatable)
- Nama lengkap
- NIP
- Jabatan
- Unit kerja
- Nomor telepon/HP
- Alamat surel (validasi + peringatan/anjuran domain resmi pemerintah, mis. *.go.id)

**Narahubung Teknis OPD**
- Nama narahubung
- Nomor kontak (HP/WA)
- Surel narahubung

### Form 1.2: Verifikasi Akun (internal — Admin DKISP)
- Checklist kelengkapan dokumen (surat lengkap, TTD kepala OPD, data personel valid)
- Status: Disetujui / Dikembalikan untuk perbaikan
- Catatan perbaikan (wajib jika dikembalikan)
- Tanggal verifikasi & nama verifikator (auto)

**Kebutuhan sistem pendukung tahap 1:**
- SLA timer: target 1 hari kerja sejak dokumen lengkap
- Auto-nonaktif akun setelah 30 hari kalender tidak digunakan + notifikasi peringatan sebelum penonaktifan
- Form aktivasi ulang akun (permintaan reaktivasi ke DKISP)

---

## C. Tahap 2 — Pengajuan Permintaan Data

### Form 2.1: Registrasi Pemohon (per pengajuan)
Sesuai Bab III huruf D (data untuk konfirmasi perbaikan dokumen oleh DKISP):
- Nama pemohon
- NIP
- Jabatan
- Nama dinas/OPD (auto dari akun)
- Nomor telepon

### Form 2.2: Formulir Permintaan Data (inti)
Mengacu Lampiran II:
- Nomor surat, sifat, lampiran, tanggal
- Nama Perangkat Daerah (auto)
- Nama program/kegiatan yang diusung
- Data lengkap BNBA / Pemadanan Data
- **Cakupan wilayah** (dropdown berjenjang: provinsi/kabupaten-kota/kecamatan/desa-kelurahan)
- **Tujuan penggunaan** (uraian)
- Upload Surat Permohonan Data bertanda tangan Kepala OPD (PDF/TTE)

### Form 2.3: Pemilihan Variabel Data
Mengacu Bab III huruf D (daftar variabel dari Bapperida) & tabel KAK:
- Katalog/daftar indikator DTSEN (checklist per variabel — sumber: master data dari Bapperida)
- Per variabel terpilih: kolom **Kegunaan/Alasan Kebutuhan** (wajib diisi)

### Form 2.4: Kerangka Acuan Kerja (KAK) — wajib Level 3 & 4
Mengacu Lampiran IV; idealnya form digital terstruktur (bukan sekadar upload):
1. Nama Perangkat Daerah pemohon (auto)
2. Program/kegiatan terkait
3. **Latar belakang** (uraian permasalahan & keterkaitan tusi OPD)
4. **Dasar hukum** (repeatable: daftar peraturan yang melandasi tusi OPD)
5. **Maksud dan tujuan** (spesifik & terukur, termasuk output/outcome)
6. **Ruang lingkup & variabel data** (auto-tarik dari Form 2.2 & 2.3: level akses, cakupan wilayah, tabel variabel + kegunaan)
7. **Rencana pemanfaatan data/metodologi** (rencana pengolahan/analisis, keluaran: dashboard/laporan/basis sasaran, unit yang mengakses)
8. **Jangka waktu pemanfaatan** (tanggal mulai = sejak BAST; tanggal akhir — validasi ≤ masa berlaku token)
9. **Mekanisme keamanan & PDP:**
   - Infrastruktur/media penyimpanan data
   - Personel/unit yang diberi akses (repeatable: nama, NIP, jabatan)
   - Teknik pelindungan data yang diterapkan (checklist: anonimisasi / pseudonim / masking / minimasi / access control / lainnya)
10. **Rencana retensi & pemusnahan:** batas waktu pemusnahan + metode pemusnahan
11. Pernyataan tanggung jawab (checkbox persetujuan) + TTD Kepala OPD

### Form 2.5: Dokumen Pendukung — wajib Level 4 (BNBA)
- Upload dokumen perencanaan program / proposal kegiatan / dokumen sejenis (multi-file)
- Keterangan setiap dokumen

### Form 2.6: Kesiapan Teknis & Keamanan
Dicek pada verifikasi administrasi (Bab III huruf E), sebaiknya diisi pemohon di awal:
- Metode akses/infrastruktur yang diinginkan: API/JSON/SQL, Excel terenkripsi, atau VPN
- Metode enkripsi/kanal penyaluran yang tersedia di OPD
- Kapasitas teknis SDM OPD (untuk kesepakatan infrastruktur dengan DKISP)

---

## D. Tahap 3 — Pengecekan Permintaan Data

### Form 3.1: Verifikasi Administrasi (internal — DKISP (BIDANG STATISTIK); SLA 1 hari kerja)
- Checklist kelengkapan & kesesuaian dokumen per level akses (surat, KAK, dok. pendukung)
- Checklist kesiapan teknis & keamanan (metode akses, enkripsi, kanal penyaluran)
- Hasil: **Lengkap/Sesuai** → lanjut verifikasi substansi; **Tidak lengkap/Tidak sesuai** → dikembalikan
- Catatan perbaikan (wajib jika dikembalikan)
- *Sistem: pemohon dapat memperbaiki/melengkapi dan mengunggah ulang (loop revisi)*

### Form 3.2: Verifikasi Substansi (internal — Koordinator Forum SDD (ADMIN BAPERIDA); SLA 2 hari kerja)
- Penilaian kesesuaian KAK dengan dasar hukum/tusi OPD terhadap variabel yang dimohonkan
- Hasil: **Diterima** / **Ditolak** / **Perlu Klarifikasi**
- Alasan penolakan (wajib jika ditolak) — *jika ditolak, proses selesai & tidak dapat dilanjutkan (status final)*
- *Catatan: pada tahap ini pemohon TIDAK dapat memperbaiki dokumen*

### Form 3.3: Berita Acara Klarifikasi (opsional, jika diundang klarifikasi)
- Tanggal & tempat/media klarifikasi
- Peserta (Tim Pelaksana & perwakilan OPD)
- Pokok klarifikasi & hasil/kesimpulan
- Upload BA bertanda tangan

### Form 3.4: Pemrosesan Data & QA (internal — DKISP; SLA 2 hari kerja)
- Checklist: pemilahan ✓, agregasi ✓, penjaminan mutu/QA ✓
- Konfirmasi kesesuaian data dengan variabel yang dimohonkan
- Catatan pemrosesan

---

## E. Tahap 4 — Pengiriman/Pemberian Hak Akses



### Form 4.1: Berita Acara Serah Terima (BAST) — generate otomatis
pemohon melakukan upload Berita acara, sebelum mendapatkan akses ke data yang disiapkan

### Form 4.2: Kesepakatan Infrastruktur Pengiriman (DKISP ↔ OPD)
- Pilihan infrastruktur final: API/JSON/SQL | Excel terenkripsi | VPN
- Parameter teknis (endpoint/kredensial/kanal — sesuai pilihan)

### Fitur 4.3: Penerbitan Token/Tautan Unduh (internal — DKISP; SLA 1 hari kerja)
- Generate token/tautan unduh dengan masa aktif 30 hari kalender (countdown terlihat oleh OPD)
- Notifikasi otomatis ke OPD (portal + narahubung teknis) bahwa data siap diunduh
- Log unduhan (audit trail: siapa, kapan, dari mana)

### Form 4.4: Permohonan Perpanjangan Masa Akses
- ID permohonan/BAST terkait (auto)
- Alasan perpanjangan
- Durasi diminta
- Persetujuan narahubung layanan DTSEN/DKISP

### Form 4.5: Permintaan Ulang/Pembaruan Data
- Referensi ke permohonan sebelumnya (auto-link)
- KAK baru yang merujuk permohonan sebelumnya
- Deklarasi: ada/tidak perubahan signifikan pada tujuan & variabel (jika tidak ada → DKISP dapat jalur cepat verifikasi administrasi)
- *Tanpa mengulang Tahap 1 selama akun aktif*

---

## F. Tahap 5 — Pemanfaatan Data & Pelaporan

### Form 5.1: Laporan Pemanfaatan DTSEN (periodik min. 1×/6 bulan & sewaktu-waktu)
Mengacu Bab VIII huruf A:
- Referensi permohonan/BAST (auto)
- Nama program/kegiatan
- Variabel data yang dimanfaatkan (checklist dari variabel yang diterima)
- Hasil pemanfaatan (uraian)
- Kendala yang dihadapi (opsional)
- Upload dokumentasi: berkas laporan / tangkapan layar / rekap Excel
- Alur berjenjang: terkirim ke **Bapperida** (koordinator Forum SDD) & **DKISP** (prosesor) untuk rekapitulasi

### Form 5.2: Berita Acara Pemusnahan Data (Bab VII huruf C)
- Identitas DTSEN yang dimusnahkan: level, variabel, cakupan wilayah (auto dari BAST)
- Dasar/alasan pemusnahan (pilihan: habis retensi / permintaan Pengendali atau Subjek Data / digantikan rilis terbaru / pemanfaatan selesai)
- Metode pemusnahan (elektronik permanen + backup, fisik, dll.)
- Waktu pelaksanaan
- Petugas pelaksana (yang ditunjuk Kepala OPD) & saksi unit keamanan informasi/persandian (jika ada)
- Upload BA bertanda tangan
- *Validasi sistem: salinan BA wajib disampaikan ke DKISP maks. 14 hari kalender sejak pemusnahan — perlu reminder otomatis*

### Form 5.3: Laporan Insiden Keamanan Data (Bab VI huruf C)
- Referensi permohonan/BAST terkait
- Jenis insiden: indikasi kebocoran / penyalahgunaan / insiden keamanan lain
- Kronologi & waktu diketahuinya insiden
- Dampak/dugaan data terdampak
- Tindakan awal yang sudah dilakukan
- *Validasi SLA: wajib dilaporkan maks. 3×24 jam hari kerja sejak diketahui — timer & eskalasi ke Petugas Pelindung DTSEN (Kepala DKISP) dan Prosesor*

---

## G. Modul Pendukung (di luar 5 tahapan inti)

### Form G.1: Pengaduan, Saran & Masukan (Bab IX)
- Kategori: pengaduan / saran / masukan
- Identitas pelapor (dengan jaminan kerahasiaan identitas)
- Uraian + lampiran
- Tindak lanjut oleh Prosesor DTSEN (status & riwayat penanganan)

### Master Data & Konfigurasi
- Master OPD (nama, kepala OPD + NIP, alamat)
- Master daftar variabel/indikator DTSEN (dikelola Bapperida, berversi mengikuti rilis DTSEN)
- Master wilayah (provinsi s.d. desa/kelurahan)
- Master rilis DTSEN (nomor rilis, tanggal) — pemicu notifikasi "data lama wajib dimusnahkan" ke semua OPD pemegang data
- Template surat/KAK/BAST/BA (sesuai Lampiran I–IV)
- Parameter SLA per tahapan (default: akun 1 hr, verif adm 1 hr, verif substansi 2 hr, pemrosesan & QA 2 hr, BAST & akses 1 hr — total ±7 hari kerja)

### Fitur Lintas-Tahapan
- **Tracking status permohonan** (timeline: Draft → Diajukan → Verif Administrasi → Revisi → Verif Substansi → Klarifikasi → Diterima/Ditolak → Pemrosesan & QA → BAST → Data Tersedia → Selesai/Kedaluwarsa)
- **Notifikasi** (portal/email/WA narahubung): dokumen dikembalikan, undangan klarifikasi, data siap unduh, token akan/kadaluarsa, jadwal lapor, rilis baru, peringatan nonaktif akun
- **Audit trail & log akses** (mendukung pemantauan traffic & audit hak akses Bab VIII huruf B)
- **RBAC** (role-based access control — Bab VI huruf B angka 5)
- **Dashboard monitoring** untuk Tim Pelaksana: rekap permohonan, SLA, pemanfaatan, insiden (bahan evaluasi tahunan Bab VIII huruf C)
- **Integrasi TTE tersertifikasi** untuk surat & BAST

---

## H. Ringkasan Matriks Form per Tahapan

| Tahap | Form Pemohon (OPD) | Form Internal (DKISP/Verifikator) |
|---|---|---|
| 1. Pembuatan Akun | 1.1 Registrasi Akun | 1.2 Verifikasi Akun |
| 2. Pengajuan | 2.1 Registrasi Pemohon; 2.2 Permintaan Data; 2.3 Pemilihan Variabel; 2.4 KAK (L3–4); 2.5 Dok. Pendukung (L4); 2.6 Kesiapan Teknis | — |
| 3. Pengecekan | (revisi dokumen bila dikembalikan) | 3.1 Verif Administrasi; 3.2 Verif Substansi; 3.3 BA Klarifikasi; 3.4 Pemrosesan & QA |
| 4. Akses Data | 4.4 Perpanjangan; 4.5 Permintaan Ulang | 4.1 BAST (+NDA); 4.2 Kesepakatan Infrastruktur; 4.3 Token/Tautan |
| 5. Pemanfaatan | 5.1 Laporan Pemanfaatan; 5.2 BA Pemusnahan; 5.3 Laporan Insiden | Rekapitulasi laporan (Bapperida + DKISP) |
| Pendukung | G.1 Pengaduan | Master data, dashboard, audit |
