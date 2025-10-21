# E-Layanan DKISP Provinsi Kalimantan Utara

Portal **E-Layanan DKISP Provinsi Kalimantan Utara** adalah aplikasi layanan digital untuk memfasilitasi berbagai permohonan layanan TIK bagi Perangkat Daerah dan stakeholder Pemprov Kaltara—termasuk **Email Pemprov**, **Subdomain \*.kaltaraprov.go.id**, **Cloud Storage**, dan **Rekomendasi Aplikasi SPBE**. :contentReference[oaicite:0]{index=0}

> **URL produksi:** https://layanan.diskominfo.kaltaraprov.go.id/ :contentReference[oaicite:1]{index=1}

---

## ✨ Fitur Utama

- **Permohonan Subdomain \*.kaltaraprov.go.id**  
  Subdomain dipakai sebagai nama unik pengganti alamat IP untuk Sistem Elektronik yang dikelola Pemprov Kaltara. :contentReference[oaicite:2]{index=2}
- **Permohonan Email Pemerintah Provinsi** (akun email resmi instansi). :contentReference[oaicite:3]{index=3}
- **Permohonan Cloud Storage** (penyimpanan berbagi internal Pemprov). :contentReference[oaicite:4]{index=4}
- **Rekomendasi Aplikasi SPBE** (sesuai ketentuan Permenkomdigi No. 5 Tahun 2025). :contentReference[oaicite:5]{index=5}
- **Autentikasi Pengguna**: akses menggunakan username & password yang diberikan admin DKISP. :contentReference[oaicite:6]{index=6}

---

## 🧭 Cara Akses

1. Kunjungi **https://layanan.diskominfo.kaltaraprov.go.id**.  
2. Login menggunakan kredensial yang diberikan admin DKISP. :contentReference[oaicite:7]{index=7}

**Helpdesk & Pengaduan:** WhatsApp **+62 822-5373-1353**. :contentReference[oaicite:8]{index=8}

---

## 🧱 Arsitektur Aplikasi (Repo Ini)

> Bagian ini mendokumentasikan implementasi aplikasi pada repositori ini (stack dapat disesuaikan oleh tim pengembang).

- **Framework**: Laravel 12 (PHP 8.2)  
- **UI**: Tailwind CSS, Blade  
- **Auth**: Laravel Breeze  
- **Database**: MySQL/MariaDB  
- **Queue/Jobs**: database/redis (opsional)  
- **Cache**: file/redis (opsional)

> Catatan: sesuaikan dengan `composer.json` dan kebutuhan instansi.

---

## 📦 Persiapan Lingkungan

### Dependensi
- PHP 8.2 + ekstensi: `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `bcmath`, `ctype`, `json`, `tokenizer`, `xml`, `curl`, `fileinfo`
- Composer
- Node.js + npm
- MySQL/MariaDB
- (Opsional) Redis