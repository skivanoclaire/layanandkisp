# Vidcon Data Seeder

## Cara Menggunakan Seeder

1. Letakkan file CSV Anda dengan nama `vidcon_data.csv` di folder ini
2. File CSV harus memiliki struktur kolom berikut (dengan header di baris pertama):
   - No
   - Nama Instansi
   - Nomor Surat
   - Judul Kegiatan
   - Lokasi
   - Tanggal Mulai (format: dd/mm/yyyy atau yyyy-mm-dd)
   - Tanggal Selesai (format: dd/mm/yyyy atau yyyy-mm-dd)
   - Jam Mulai (format: HH:mm)
   - Jam Selesai (format: HH:mm)
   - Platform
   - Operator
   - Dokumentasi
   - Akun Zoom
   - Informasi Pimpinan
   - Keterangan

3. Jalankan seeder dengan perintah:
   ```
   php artisan db:seed --class=VidconDataSeeder
   ```

## Catatan

- File CSV yang digunakan: `Data Fasilitasi Vidcon - 2025.csv`
- Total data: 235 baris
- Format tanggal yang didukung: Y-m-d, d/m/Y, d-m-Y, m/d/Y
- Format waktu yang didukung: H:i, H:i:s, h:i A, h:i:s A
