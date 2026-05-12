# Portal E-Layanan DKISP Kaltara

<p align="center">
  <img src="public/kaltara.svg" alt="Logo DKISP" width="120">
</p>

<p align="center">
  Portal layanan digital untuk Dinas Komunikasi, Informatika dan Statistik<br>
  Provinsi Kalimantan Utara
</p>

<p align="center">
  🌐 <a href="https://layanan.diskominfo.kaltaraprov.go.id"><strong>layanan.diskominfo.kaltaraprov.go.id</strong></a>
</p>

---

## Tentang Aplikasi

Portal E-Layanan DKISP adalah sistem pelayanan berbasis web yang menyediakan berbagai layanan digital untuk memudahkan ASN dan masyarakat dalam mengakses layanan Dinas Komunikasi, Informatika dan Statistik Provinsi Kalimantan Utara.

## Pengembang Awal

Dibangun oleh: Bayu Adi Hartanto, S.Kom. (pengembang awal)

## Hak Cipta & Lisensi

Copyright (c) 2026 Bayu Adi Hartanto.

Aplikasi ini dirilis dengan Lisensi MIT. Anda bebas menggunakan, menyalin, memodifikasi, menggabungkan, menerbitkan, mendistribusikan, mensublisensikan, dan/atau menjual salinan perangkat lunak ini, dengan syarat tetap menyertakan pemberitahuan hak cipta dan teks lisensi ini pada setiap salinan atau bagian substansial dari perangkat lunak.

Lihat file `LICENSE` untuk detail.

## Fitur Layanan Digital

### 📧 Email Dinas
- Permohonan pembuatan email dinas baru (`@kaltaraprov.go.id`)
- Reset password email
- Tracking status permohonan (menunggu, proses, selesai, ditolak)
- Notifikasi WhatsApp otomatis ke pengguna setiap perubahan status (token APTIKA)

### 🌐 Subdomain
- Pendaftaran subdomain baru (`*.kaltaraprov.go.id`)
- Perubahan IP/pointing
- Perubahan nama subdomain
- Monitoring status website

### 📋 Rekomendasi Aplikasi
- Pengajuan rekomendasi aplikasi (sesuai Permenkomdigi 6/2025)
- Analisis kebutuhan, perencanaan, dan manajemen risiko
- Penilaian kelayakan sistem

### 🤖 Konsultasi SPBE Berbasis AI
- Konsultasi terkait Sistem Pemerintahan Berbasis Elektronik
- Panduan implementasi SPBE

### ✍️ Tanda Tangan Elektronik (TTE)
- Pendampingan aktivasi dan penggunaan TTE
- Pendaftaran akun baru TTE
- Permohonan reset passphrase TTE
- Pembaruan sertifikat TTE
- **Auto-fill** Instansi & Jabatan dari profil pengguna (data Unit Kerja & Jabatan)
- Notifikasi WhatsApp ke pengguna saat submit (dengan nomor tiket) & saat status diperbarui (token SANDI)
- Notifikasi WhatsApp ke admin saat permohonan baru masuk

### 📃 Aset TIK
- Akses Data Aset TIK Hardware/Software Seluruh OPD
- Rekomendasi Aset TIK

### 🎥 Video Conference
- Layanan video konferensi
- Penjadwalan meeting online
- Pembagian tugas operator otomatis

### 🌍 Internet
- Lapor gangguan internet
- Starlink Jelajah

### 🔐 Jaringan Privat/VPN
- Permohonan akses VPN
- Konfigurasi jaringan privat
- **Kredensial VPN terenkripsi** di database (Laravel Crypt)
- Tampilan kredensial untuk pengguna: tombol mata (show/hide) + tombol copy username/password
- Disclaimer tanggung jawab pengguna otomatis ditampilkan
- **Revisi bandwidth oleh admin** dengan riwayat audit (siapa & kapan revisi)

### 🖥️ Pusat Data/Komputasi
- Kunjungan/Colocation
- VPS/VM
- Backup
- Cloud Storage

### 📃 PSE
- Pengisian Kategori Sistem Elektronik dan Klasifikasi Data

## Fitur Admin

### Dashboard
- Statistik permohonan layanan
- Monitoring subdomain aktif
- Deteksi website down
- Grafik pertumbuhan layanan
- Pelaporan per layanan

### Master Data
- Master Data Subdomain dan PSE
- Master Data Instansi
- Master Data Email
- Master Data IP Public
- Master Data Aset TIK
- Master Data Video Conference (Vidcon)
   - Inventaris Digital Aset Vidcon
   - Peminjaman dan Pengembalian Aset Vidcon
   - Laporan Peminjaman
   - Jadwal Vidcon
   - Pembagian Tugas Vidcon Otomatis

### Pengguna & Akses
- Kelola Pengguna (CRUD, verifikasi, role assignment)
- Kelola Peran (Role) — CRUD role
- Kelola Kewenangan (Role-Permission Matrix)
- **Log Audit** — riwayat lengkap aktivitas autentikasi (login, logout, gagal, lockout, ganti password) dengan filter event/tanggal/IP/user; eksklusif untuk Admin
- Cek via SIMPEG

## Keamanan

### Autentikasi
- **SSO Keycloak** terintegrasi dengan portal ASN Kaltara
- Login lokal dengan email + password (Laravel Breeze)
- **Rate Limiting** otomatis: 5 percobaan per kombinasi email+IP, lockout dengan timer
- **Progressive Image CAPTCHA** (`mews/captcha`, self-hosted GD-based) — muncul otomatis setelah 3x percobaan login gagal dari IP yang sama
- Counter CAPTCHA reset otomatis setelah login berhasil

### Audit Trail
Sistem mencatat seluruh event autentikasi ke tabel `audit_logs`:
- ✅ **Login berhasil** — user, IP, user-agent
- 🚪 **Logout**
- ⚠️ **Login gagal** — termasuk attempt dengan email tidak terdaftar
- 🔒 **Lockout** — saat rate limiter trigger
- 🔑 **Ganti password**

Halaman audit dilengkapi filter range tanggal, jenis event, dan pencarian email/nama/IP.

### Kredensial Sensitif
- Password VPN, password email, NIK, dan kredensial lain dienkripsi dengan Laravel Crypt
- Decrypt only-on-display untuk authorized user
- Backfill otomatis untuk data legacy

## Notifikasi WhatsApp (Fonnte)

Aplikasi terintegrasi dengan API Fonnte untuk pengiriman notifikasi WA otomatis menggunakan **2 channel terpisah**:

| Channel | Token Env | Digunakan Untuk |
|---------|-----------|-----------------|
| **SANDI** | `FONNTE_SANDI_TOKEN` | Layanan TTE (4 jenis): notifikasi submit ke pengguna + status update ke pengguna + alert permohonan baru ke admin (`WA_ADMIN_SANDI`) |
| **APTIKA** | `FONNTE_APTIKA_TOKEN` | Layanan Email & Reset Password Email: notifikasi status update ke pengguna |

Header pesan otomatis disesuaikan dengan channel ("Helpdesk Bidang Persandian" / "Helpdesk Bidang Aptika"). Kegagalan pengiriman WA tidak menghalangi proses utama (graceful fail dengan logging).

## Integrasi Sistem

| Sistem | Fungsi |
|--------|--------|
| **SSO Keycloak** | Single Sign-On terintegrasi dengan [sso.kaltaraprov.go.id](https://sso.kaltaraprov.go.id) |
| **Cloudflare** | Manajemen DNS dan subdomain otomatis |
| **WHM/cPanel** | Manajemen email dinas otomatis (create/reset password) |
| **API SIMPEG** | Verifikasi dan sinkronisasi data ASN |
| **Fonnte WhatsApp** | Notifikasi otomatis 2-channel (SANDI/APTIKA) |
| **Google API** | Integrasi data aset TIK |

## Tampilan Halaman Depan

Halaman publik (`/`) menampilkan grid 18 layanan digital dengan:
- **Background animasi cosmic** — solar system orbiting + 80 bintang twinkling + 4 nebula glow (palette hijau)
- **AOS (Animate on Scroll)** untuk efek fade & slide saat scroll
- Hover scale + shadow pada cards layanan
- Tombol **scroll-to-top** muncul otomatis setelah scroll 300px
- File backup `welcome_backup.blade.php` tersedia untuk revert cepat

## Tech Stack

- **Framework:** Laravel 12
- **PHP Version:** 8.2+
- **Database:** MySQL 8+
- **Frontend:** Blade Template, Tailwind CSS 4, Alpine.js
- **Animation:** AOS (Animate on Scroll) + custom CSS keyframes
- **Authentication:** Laravel Breeze + SSO Keycloak (hybrid)
- **CAPTCHA:** mews/captcha (self-hosted, GD-based)
- **DNS Management:** Cloudflare API
- **Email Management:** WHM/cPanel API
- **WA Notification:** Fonnte API (2 channels)

## Persyaratan Sistem

- PHP >= 8.2 dengan ekstensi: `gd` (untuk CAPTCHA), `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- Composer
- MySQL >= 8.0
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
   KEYCLOAK_REALM=asn-kaltara
   KEYCLOAK_CLIENT_ID=
   KEYCLOAK_CLIENT_SECRET=

   # Cloudflare
   CLOUDFLARE_API_TOKEN=
   CLOUDFLARE_ZONE_ID=

   # WHM/cPanel
   WHM_HOST=
   WHM_USERNAME=
   WHM_TOKEN=

   # SIMPEG
   SIMPEG_API_URL=
   SIMPEG_API_KEY=

   # Fonnte WhatsApp (2 channel)
   FONNTE_SANDI_TOKEN=
   FONNTE_APTIKA_TOKEN=
   WA_ADMIN_SANDI=

   # Google API
   GOOGLE_API_CREDENTIALS=
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
│   │   │   ├── Admin/          # Controller admin (Email, VPN, TTE, AuditLog, dll)
│   │   │   ├── Auth/           # Login, register, password (Breeze + SSO)
│   │   │   ├── Operator/       # Controller operator
│   │   │   └── User/           # Controller user (TTE, Email, VPN, dll)
│   │   ├── Middleware/
│   │   └── Requests/Auth/      # LoginRequest dengan rate limit + captcha
│   ├── Listeners/
│   │   └── AuthAuditSubscriber.php   # Listen Login/Logout/Failed/Lockout
│   ├── Models/
│   │   ├── AuditLog.php             # Audit trail autentikasi
│   │   ├── EmailRequestLog.php      # Log per-permohonan email
│   │   ├── VpnRegistrationLog.php   # Log revisi bandwidth, dll
│   │   └── VpnRegistration.php      # Setter/getter Crypt untuk username/password
│   └── Services/
│       ├── FonnteWhatsappService.php  # 2-channel WA notification
│       ├── SimpegClient.php           # Integrasi SIMPEG
│       ├── WhmApiService.php          # Integrasi cPanel
│       └── CpanelEmailService.php
├── config/
│   ├── captcha.php             # Konfigurasi mews/captcha
│   └── services.php            # Token Fonnte, Cloudflare, dll
├── database/
│   └── migrations/             # Termasuk audit_logs, vpn_registration_logs
├── resources/
│   ├── css/app.css             # Custom keyframes (cosmic bg, scroll-to-top)
│   └── views/
│       ├── admin/audit-logs/   # View Log Audit
│       ├── auth/login.blade.php # Form login dengan CAPTCHA conditional
│       └── welcome.blade.php   # Halaman depan dengan animasi
├── routes/
│   ├── web.php
│   └── auth.php
└── public/
```

## Role & Akses

| Role | Akses |
|------|-------|
| **Admin** | Full access, konfigurasi sistem, manajemen permohonan, approval, master data, **Log Audit** |
| **Operator-Vidcon** | Kelola Layanan Vidcon |
| **Operator-Sandi** | Kelola Layanan Sandi |
| **User ASN** | Akses Permohonan Layanan secara Digital |
| **Custom User** | Custom akses untuk jenis User lainnya yang diinginkan |

## Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b fitur/FiturBaru`)
3. Commit perubahan (`git commit -m 'Menambah fitur baru'`)
4. Push ke branch (`git push origin fitur/FiturBaru`)
5. Buat Pull Request

## Lisensi

© 2026 DKISP Kalimantan Utara - Bidang Aplikasi Informatika. All rights reserved.

## Pengembang

**Bayu Adi H.**
**Dinas Komunikasi, Informatika dan Statistik**  
**Provinsi Kalimantan Utara**

[![LinkedIn](https://img.shields.io/badge/LinkedIn-0077B5?style=flat&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/noclaire/)
[![GitHub](https://img.shields.io/badge/GitHub-100000?style=flat&logo=github&logoColor=white)](https://github.com/skivanoclaire)



---

<p align="center">
  <i>Membangun Teknologi untuk Kalimantan Utara yang Lebih Baik</i>
</p>
