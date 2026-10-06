# Panduan Deploy — Revisi Modul DTSEN (Katalog Variabel BNBA & KAK Unggah)

Branch: `staging/revisi-dtsen`. Uji di staging dulu; merge ke `main` setelah lolos.

## Isi perubahan

- Katalog variabel diganti dengan **Katalog Variabel BNBA/DTSEN Kaltara** (100 variabel:
  set Keluarga 52, set Anggota 48). Kategori Agregat tidak dimuat.
- Level akses mengikuti sensitivitas: terbuka → L2, quasi-identifier → L3, data pribadi → L4.
  Gabungan RT/RW KTP dengan jenis kelamin/tanggal lahir dihitung sebagai L4.
- KAK cukup diunggah (PDF bertanda tangan Kepala OPD) + centang pernyataan, wajib untuk L3/L4.
  Isian KAK, bagian Dokumen Pendukung, dan Kesiapan Teknis dihapus dari formulir.

Tidak ada variabel `.env` baru. Tidak ada aset front-end baru (tidak perlu `npm run build`).

## Langkah

1. **Backup database** (migration menghapus variabel katalog lama yang belum pernah dimohonkan):

   ```bash
   mysqldump -u <user> -p <nama_db> > backup-sebelum-revisi-dtsen-$(date +%F).sql
   ```

2. **Ambil kode**:

   ```bash
   git fetch origin
   git checkout staging/revisi-dtsen
   git pull origin staging/revisi-dtsen
   ```

3. **Migrasi & bersihkan cache**:

   ```bash
   php artisan migrate --force
   php artisan view:clear
   php artisan config:clear
   ```

   Migration yang berjalan: `2026_10_06_000001_ganti_katalog_variabel_dtsen_bnba`.
   Variabel lama (`DTSEN-0xx`) yang sudah dipakai permohonan hanya dinonaktifkan agar
   riwayat permohonan tetap utuh; sisanya dihapus.

## Verifikasi

```bash
php artisan tinker --execute="echo App\Models\DtsenVariable::active()->count();"
# harus 100
```

- Buka **Berbagi Pakai Data (DTSEN) › Permintaan Data › Ajukan**: tampil Set Keluarga & Set Anggota,
  tidak ada kategori Agregat; pilih `rt_ktp` + `jenis_kelamin` → level berubah ke Level 4.
- Pilih variabel L3 lalu ajukan tanpa unggah KAK → muncul pesan KAK wajib diunggah.
- **Master Data › Variabel DTSEN**: kolom Set/Kategori dan Sensitivitas terisi.

## Rollback

Pulihkan dari backup langkah 1. `php artisan migrate:rollback` hanya menghapus katalog baru
dan mengaktifkan kembali variabel lama yang dinonaktifkan; variabel lama yang sudah dihapus
tidak kembali.
