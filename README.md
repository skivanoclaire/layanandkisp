# E-Layanan DKISP Provinsi Kalimantan Utara

Portal **E-Layanan DKISP** mendigitalisasi berbagai layanan TIK bagi Perangkat Daerah dan masyarakat, mulai dari permohonan subdomain/email resmi hingga dukungan publikasi, SPBE, jaringan, dan pusat data.

**URL produksi:** `https://layanan.diskominfo.kaltaraprov.go.id`

---

## 🌟 Fitur Utama (Modul)

> Ringkasan fungsi tiap kartu/menu pada beranda.

1. **Rekomendasi**  
   Layanan penerbitan surat rekomendasi teknis (mis. aplikasi/layanan TIK) sesuai kebijakan SPBE. Mendukung unggah dokumen, validasi, dan nomor tiket.

2. **AI SPBE Kaltara**  
   Asisten berbasis AI untuk membantu konsultasi/penelusuran referensi SPBE (kebijakan, standar, dan praktik terbaik) serta menjawab pertanyaan umum seputar layanan.

3. **Manajemen Risiko SPBE**  
   Pencatatan, penilaian, dan pemantauan risiko SPBE (dampak/kemungkinan, rencana mitigasi, dan penanggung jawab) untuk setiap program/layanan.

4. **Peliputan**  
   Permohonan peliputan/dukungan dokumentasi untuk kegiatan pemerintah. Termasuk jadwal kru, lokasi, dan kebutuhan konten.

5. **Publikasi**  
   Permohonan rilis/unggah konten ke kanal resmi Pemprov (website, media sosial, dsb.) lengkap dengan draft materi, foto/video, dan tenggat tayang.

6. **Konten Multimedia**  
   Produksi/penyuntingan materi grafis, foto, dan video. Pengguna bisa mengajukan kebutuhan desain dengan spesifikasi ukuran/media.

7. **Pengaduan**  
   Kanal pelaporan gangguan layanan TIK atau masukan publik. Memiliki nomor tiket, status penanganan, dan riwayat komunikasi.

8. **Subdomain dan PSE**  
   Permohonan subdomain `*.kaltaraprov.go.id` dan pendaftaran **Penyelenggara Sistem Elektronik (PSE)**. Termasuk validasi nama, DNS, dan penanggung jawab.

9. **Pusat Data**  
   Layanan resource pusat data: penempatan VM/server, alokasi storage, dan jaringan internal. Mendukung penjadwalan provisioning dan monitoring dasar.

10. **Email**  
    Pembuatan akun email instansi, reset kata sandi, dan manajemen kuota untuk domain resmi pemerintah daerah.

11. **TTE (Tanda Tangan Elektronik)**  
    Fasilitas pendaftaran/aktivasi TTE pejabat/ASN, pengelolaan sertifikat, dan verifikasi dokumen bertanda tangan elektronik.

12. **Portal Data**  
    Pengelolaan dan publikasi data—sinkronisasi dengan portal data terbuka/internal, termasuk metadata dan lisensi penggunaan.

13. **SPLP**  
    (Sarana/Prasarana Layanan Publik) Permohonan sarana telekomunikasi/penunjang gelaran acara (mis. perangkat, koneksi, dan dukungan teknis).

14. **SPBE**  
    Menu referensi/administrasi SPBE: evaluasi mandiri, indikator, pemetaan layanan, dan dokumen pendukung kebijakan SPBE daerah.

15. **PPID**  
    Dukungan permohonan informasi publik (Pejabat Pengelola Informasi dan Dokumentasi): registrasi, klasifikasi, tanggapan, dan keberatan.

16. **Keamanan Informasi**  
    Penilaian keamanan, laporan insiden siber, permintaan pemeriksaan kerentanan, serta edukasi keamanan informasi.

17. **Jaringan Internet**  
    Permohonan akses jaringan, VLAN, IP publik/internal, dan penarikan link antar kantor/UPT. Termasuk jadwal instalasi dan status koneksi.

18. **VPN**  
    Pengajuan akun akses VPN untuk ASN/instansi, pengaturan profil/izin, dan tata cara penggunaan.

19. **WiFi Publik**  
    Penempatan/aktivasi WiFi publik di titik layanan pemerintah. Mencakup survei lokasi, kapasitas, dan pemeliharaan.

20. **Cloud Storage**  
    Penyimpanan berbagi untuk unit kerja—pembuatan ruang, quota, izin akses, dan integrasi sinkronisasi.

21. **Helpdesk TIK**  
    Pusat bantuan terpadu untuk semua layanan TIK. Tiket insiden/permintaan, SLA, dan notifikasi progres.

22. **Zoom/YouTube Live Streaming**  
    Permohonan dukungan rapat/siaran langsung (akun Zoom, setup RTMP YouTube, uji coba, dan operator saat acara).

---

## 🧱 Stack & Arsitektur (Repo)

- **Backend:** Laravel 12 (PHP 8.2)  
- **Frontend:** Blade + Tailwind CSS  
- **Auth:** Laravel Breeze (login/registrasi/role dasar)  
- **Database:** MySQL/MariaDB  
- **Cache/Queue:** file/redis (opsional)  
- **Build Frontend:** npm (Vite)

> Sesuaikan dengan `composer.json` dan `package.json` pada repo ini.

---

## 📦 Persiapan Lingkungan

**Dependensi:** PHP 8.2 + ekstensi umum (`mbstring`, `openssl`, `pdo_mysql`, `xml`, `curl`, `fileinfo`, …), Composer, Node.js + npm, MySQL/MariaDB, (opsional) Redis.

**Konfigurasi `.env` (contoh ringkas):**
```dotenv
APP_NAME="E-Layanan DKISP"
APP_ENV=production
APP_KEY=base64:***isi-key-saat-deploy***
APP_URL=https://layanan.diskominfo.kaltaraprov.go.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=******
DB_USERNAME=******
DB_PASSWORD=********

QUEUE_CONNECTION=database
CACHE_STORE=file

MAIL_MAILER=smtp
MAIL_HOST=smtp.kaltaraprov.go.id
MAIL_PORT=587
MAIL_USERNAME=no-reply@kaltaraprov.go.id
MAIL_PASSWORD=********
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@kaltaraprov.go.id
MAIL_FROM_NAME="${APP_NAME}"
