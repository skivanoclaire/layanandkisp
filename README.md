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
- Monitoring status website

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
- Permohonan akses VPN
- Konfigurasi jaringan privat

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

### Master Data
- Master Data Subdomain
- Master Data Instansi
- Master Data Email
- Master Data IP
- Master Data Aset TIK

### Manajemen
- User Management
- Kelola Role
- Kelola Kewenangan
- Cek via SIMPEG

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

## Tech Stack

- **Framework:** Laravel 12
- **PHP Version:** 8.2+
- **Database:** MySQL 8
- **Frontend:** Blade Template, Tailwind CSS, Alpine.js, Vite
- **Authentication:** SSO Keycloak
- **DNS Management:** Cloudflare API
- **Email Management:** WHM/cPanel API
- **URL Shortener:** YOURLS API (link.kaltaraprov.go.id)
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

4. **Konfigurasi database**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=layanan_dkisp
   DB_USERNAME=root
   DB_PASSWORD=
   ```

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
│   │   │   ├── Operator/       # Controller operator
│   │   │   └── User/           # Controller user
│   │   └── Middleware/
│   ├── Models/
│   └── Services/
│       ├── SimpegClient.php          # Service integrasi SIMPEG
│       ├── YourlsClient.php          # Service integrasi YOURLS (pemendek tautan)
│       ├── CloudflareService.php     # Service integrasi Cloudflare DNS
│       ├── WhmApiService.php         # Service integrasi WHM/cPanel
│       └── FonnteWhatsappService.php # Service notifikasi WhatsApp
├── database/
│   └── migrations/
├── resources/
│   └── views/
├── routes/
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