# Portal Layanan TIK (E-Layanan) — DKISP Kaltara

<p align="center">
  <img src="public/kaltara.svg" alt="Logo DKISP" width="120">
</p>

<p align="center">
  <strong>Portal Layanan TIK (E-Layanan)</strong><br>
  Dinas Komunikasi Informatika Statistik dan Persandian<br>
  Provinsi Kalimantan Utara
</p>

<p align="center">
  🌐 <a href="https://layanan.diskominfo.kaltaraprov.go.id"><strong>layanan.diskominfo.kaltaraprov.go.id</strong></a>
</p>

---

## Tentang Aplikasi

Portal Layanan TIK (E-Layanan) adalah sistem pelayanan berbasis web yang menyediakan berbagai layanan digital untuk memudahkan ASN dan masyarakat dalam mengakses layanan Dinas Komunikasi Informatika Statistik dan Persandian Provinsi Kalimantan Utara.

## Fitur Layanan Digital

### 📧 Email Dinas
- Permohonan pembuatan email dinas baru
- Reset password email
- Tracking status permohonan

### 🌐 Subdomain
- Pendaftaran subdomain baru (*.kaltaraprov.go.id)
- Perubahan IP/pointing
- Perubahan nama subdomain
- **Pembaruan Data Aplikasi** — pemilik (per unit kerja) memperbarui data aplikasi/teknologi/server miliknya, melalui alur persetujuan admin (Setujui / Revisi / Tolak)
- **Usulan non-aktif (pensiun)** aplikasi — menandai aplikasi yang akan dipensiunkan tanpa menghapus data, sebagai bahan evaluasi
- Monitoring status website

### 🗂️ Pendaftaran Sistem Elektronik (PSE)
- Pendaftaran & pembaruan data Sistem Elektronik (Permenkominfo/Permenkomdigi)
- Kategori Sistem Elektronik (KSE/ESC) dan Klasifikasi Data
- Pengajuan pembaruan data PSE per subdomain dengan persetujuan admin

### 😊 Survei Kepuasan Layanan
- Pengisian survei kepuasan terhadap layanan/website yang dikelola
- Rekapitulasi hasil survei untuk admin

### ⭐ Survei Digital SPBE
- Embed survei kepuasan resmi [surveidigital.spbe.go.id](https://surveidigital.spbe.go.id) langsung di dalam portal (iframe)
- Tombol **"Beri Penilaian"** muncul otomatis pada permohonan yang telah **selesai** (Email, TTE, Video Conference, Jaringan Intra/VPN, Pusat Data)
- Layanan **Konsultasi SPBE Berbasis AI** — tombol penilaian tampil setelah pengguna mengakses layanan (klik "Akses Disini")
- Token embed dipakai bersama seluruh layanan & dikelola terpusat oleh admin (lihat *Manajemen Survei Digital*)

### ✂️ Pemendek Tautan (URL Shortener)
- Permohonan pemendek tautan resmi pada domain [link.kaltaraprov.go.id](https://link.kaltaraprov.go.id)
- Pengusulan kode pendek kustom (opsional)
- Approval admin → short link otomatis dibuat di YOURLS
- Pengelolaan link: ubah URL tujuan, pantau statistik klik, nonaktifkan/aktifkan kembali
- Riwayat (log) setiap permohonan & perubahan

### 📋 Rekomendasi Aplikasi
- Pengajuan rekomendasi aplikasi
- Analisis risiko keamanan
- Penilaian kelayakan sistem

### 🤖 Konsultasi SPBE Berbasis AI
- Konsultasi terkait Sistem Pemerintahan Berbasis Elektronik
- Panduan implementasi SPBE

### ✍️ Tanda Tangan Elektronik (TTE)
- Pendampingan aktivasi dan penggunaan TTE
- Pendaftaran akun baru TTE
- Permohonan reset passphrase TTE
- Pembaruan sertifikat TTE

### 🎥 Video Conference
- Layanan video konferensi
- Penjadwalan meeting online

### 🌍 Internet
- Lapor gangguan internet
- Starlink Jelajah

### 🔐 Jaringan Privat/VPN
- Pendaftaran akses VPN baru
- Reset akun VPN
- Akses JIP PDNS (Jaringan Intra Pemerintah / Pusat Data Nasional)

### 🖥️ Pusat Data/Komputasi
- Kunjungan/Colocation
- VPS/VM
- Backup
- Cloud Storage

## Fitur Admin

### Dashboard
- Statistik permohonan layanan
- Monitoring subdomain aktif
- Deteksi website down
- Grafik pertumbuhan layanan

### Kelola Permohonan (Approval)
- Pusat persetujuan seluruh layanan (Email, Subdomain, PSE, TTE, VPN, Internet, Pusat Data, Vidcon, dll.)
- Badge jumlah permohonan menunggu per kategori pada menu
- Alur Setujui / Revisi / Tolak dengan catatan admin (mis. Pembaruan Data Subdomain)
- Notifikasi status ke pemohon via WhatsApp (Fonnte)

### Web Monitor & Keamanan Sistem Elektronik
- Monitoring status website (cek otomatis terjadwal) & sinkronisasi DNS Cloudflare
- Data aplikasi lengkap: teknologi, server, developer, kontak
- **Kategori Sistem Elektronik (KSE/ESC)** — kuesioner & skoring kategori (Strategis/Tinggi/Rendah)
- **Klasifikasi Data** — penilaian Kerahasiaan/Integritas/Ketersediaan (CIA)
- **Laporan Trafik** website berbasis Cloudflare Analytics + ekspor PDF
- **Penanda Pensiun (decommission)** dengan badge & filter — menandai aplikasi non-aktif tanpa menghapus data
- Generate & unduh dokumen TTE (PDF)

### Master Data
- Master Data Subdomain (Web Monitor)
- Master Data Instansi/Unit Kerja
- Master Data Email & Akun Email (WHM/cPanel)
- Master Data IP (cek IP terpakai pada rentang 103.156.110.0/24)
- Master Data Aset TIK (perangkat keras, perangkat lunak, kategori, peminjaman & pengembalian)

### Rekomendasi Aplikasi & Fase Pengembangan
- Verifikasi & penilaian kelayakan usulan aplikasi (checklist, kajian, ekspor PDF)
- Pemantauan fase pengembangan: milestone, tim, dokumen, dan catatan

### Manajemen API (SPLP)
- Pengelolaan **API Key** (buat, aktif/nonaktif, hapus)
- **IP Whitelist** untuk akses API publik
- Daftar endpoint untuk integrasi Sistem Penghubung Layanan Pemerintah (SPLP)

### Manajemen Survei Digital
- Kelola **token/URL embed** survei SPBE dari satu tempat — rotasi token cukup sekali dan berlaku untuk seluruh layanan
- Aktif/nonaktifkan survei secara global
- Pratinjau URL final per layanan (base URL + `jenis_layanan`) untuk verifikasi sebelum disebarkan

### Manajemen Sistem
- User Management & verifikasi via SIMPEG
- Kelola Role & Kewenangan (permission)
- Audit Log aktivitas
- Kelola Operator (Vidcon/Sandi)

## Integrasi Sistem

| Sistem | Fungsi |
|--------|--------|
| **SSO Keycloak** | Single Sign-On terintegrasi dengan [sso.kaltaraprov.go.id](https://sso.kaltaraprov.go.id) |
| **Cloudflare** | Manajemen DNS dan subdomain otomatis |
| **WHM/cPanel** | Manajemen email dinas otomatis |
| **SIMPEG** | Verifikasi dan sinkronisasi data ASN |
| **Google API** | Integrasi data aset TIK |
| **YOURLS** | Pembuatan & pengelolaan short link otomatis di [link.kaltaraprov.go.id](https://link.kaltaraprov.go.id) (API signature passwordless) |
| **Fonnte WhatsApp** | Notifikasi status permohonan via WhatsApp |
| **Survei Digital SPBE** | Embed survei kepuasan resmi [surveidigital.spbe.go.id](https://surveidigital.spbe.go.id) per layanan |

## API Publik (SPLP)

Aplikasi menyediakan API untuk integrasi dengan **SPLP (Sistem Penghubung Layanan Pemerintah)**, diamankan **dua lapis**: IP Whitelist + API Key. Pengelolaan key dan whitelist dilakukan melalui menu **Manajemen API** pada panel admin.

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `GET` | `/api/v1/master/instansi` | Data master instansi/unit kerja |
| `GET` | `/api/v1/master/subdomain` | Data master subdomain (Web Monitor) |

Autentikasi API key via header `X-API-Key: <api-key>` (alternatif: `Authorization: Bearer <api-key>`). Permintaan dari IP di luar daftar whitelist atau tanpa API key yang valid akan ditolak.

## Tech Stack

- **Framework:** Laravel 12
- **PHP Version:** 8.2+
- **Database:** MySQL 8
- **Frontend:** Blade Template, Tailwind CSS, Alpine.js, Vite
- **Authentication:** SSO Keycloak
- **DNS Management:** Cloudflare API
- **Email Management:** WHM/cPanel API
- **URL Shortener:** YOURLS API (link.kaltaraprov.go.id)
- **API Publik:** REST `/api/v1/*` untuk SPLP (pengamanan API Key + IP Whitelist)
- **Notifikasi:** Fonnte WhatsApp API
- **Penjadwalan:** Laravel Scheduler (cek status website, sinkronisasi Cloudflare per jam)
- **Containerization:** Docker / Docker Compose

## Persyaratan Sistem

- PHP >= 8.2
- Composer
- MySQL >= 5.7
- Node.js & NPM
- Web Server (Apache/Nginx)

## Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/skivanoclaire/layanandkisp.git
   cd layanandkisp
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi database** — sesuaikan kredensial database pada file `.env`.

5. **Konfigurasi integrasi** (sesuaikan di `.env`)
   ```env
   # SSO Keycloak
   KEYCLOAK_BASE_URL=https://sso.kaltaraprov.go.id
   KEYCLOAK_REALM=kaltara
   KEYCLOAK_CLIENT_ID=
   KEYCLOAK_CLIENT_SECRET=

   # Cloudflare
   CLOUDFLARE_API_TOKEN=
   CLOUDFLARE_ZONE_ID=

   # WHM/cPanel
   WHM_HOST=
   WHM_USERNAME=
   WHM_API_TOKEN=

   # SIMPEG
   SIMPEG_API_URL=
   SIMPEG_API_KEY=

   # Google API
   GOOGLE_API_CREDENTIALS=

   # YOURLS (Pemendek Tautan)
   YOURLS_API_URL=https://link.kaltaraprov.go.id/yourls-api.php
   YOURLS_BASE_URL=https://link.kaltaraprov.go.id
   YOURLS_API_SIGNATURE=

   # Fonnte WhatsApp
   FONNTE_SANDI_TOKEN=
   FONNTE_APTIKA_TOKEN=
   ```

6. **Jalankan migrasi**
   ```bash
   php artisan migrate
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

8. **Jalankan aplikasi**
   ```bash
   php artisan serve
   ```

## Struktur Direktori

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Controller admin
│   │   │   ├── Api/            # Controller API publik (SPLP)
│   │   │   ├── Operator/       # Controller operator
│   │   │   └── User/           # Controller user
│   │   ├── Middleware/         # Termasuk ApiKeyAuth & ApiIpWhitelist
│   │   └── Resources/          # API Resources (transformasi respons API)
│   ├── Models/                 # Termasuk ApiKey, ApiWhitelist, WebMonitor, dll.
│   └── Services/
│       ├── SimpegClient.php             # Service integrasi SIMPEG
│       ├── YourlsClient.php             # Service integrasi YOURLS (pemendek tautan)
│       ├── CloudflareService.php        # Service integrasi Cloudflare DNS
│       ├── WhmApiService.php            # Service integrasi WHM/cPanel
│       ├── AdminPendingCountsService.php# Hitung badge permohonan menunggu
│       └── FonnteWhatsappService.php    # Service notifikasi WhatsApp
├── database/
│   └── migrations/
├── resources/
│   └── views/
├── routes/
│   ├── web.php                 # Rute aplikasi (user, admin, operator)
│   ├── api.php                 # Rute API publik /api/v1/* (SPLP)
│   └── console.php             # Scheduler (cek status website, sync Cloudflare)
└── public/
```

## Role & Akses

| Role | Akses |
|------|-------|
| **Admin** | Full access, konfigurasi sistem manajemen permohonan, approval, master data |
| **Operator-Vidcon** | Kelola Layanan Vidcon |
| **Operator-Sandi** | Kelola Layanan Sandi |
| **User** | Akses Permohonan Layanan secara Digital |

## Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b fitur/FiturBaru`)
3. Commit perubahan (`git commit -m 'Menambah fitur baru'`)
4. Push ke branch (`git push origin fitur/FiturBaru`)
5. Buat Pull Request

## Lisensi

© 2026 Dinas Komunikasi Informatika Statistik dan Persandian Provinsi Kalimantan Utara - Bidang Aplikasi Informatika. All rights reserved.

## Pengembang

**Bayu Adi H.**
**Dinas Komunikasi Informatika Statistik dan Persandian**  
**Provinsi Kalimantan Utara**

[![LinkedIn](https://img.shields.io/badge/LinkedIn-0077B5?style=flat&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/noclaire/)
[![GitHub](https://img.shields.io/badge/GitHub-100000?style=flat&logo=github&logoColor=white)](https://github.com/skivanoclaire)



---

<p align="center">
  <i>Melayani dengan Teknologi untuk Kalimantan Utara yang Lebih Baik</i>
</p>