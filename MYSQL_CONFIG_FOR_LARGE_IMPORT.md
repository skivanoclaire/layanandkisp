# Konfigurasi MySQL untuk Import Data Besar

## Masalah
Ketika melakukan import data besar dari Google Sheets, muncul error:
```
SQLSTATE[HY000]: General error: 2006 MySQL server has gone away
```

## Penyebab
1. **MySQL timeout** - Koneksi terputus karena query terlalu lama
2. **Max packet size** - Data terlalu besar untuk dikirim
3. **PHP timeout** - Script PHP timeout sebelum selesai
4. **Memory limit** - Kehabisan memory PHP

## Solusi yang Sudah Diterapkan di Code

### 1. PHP Settings (Sudah ada di Controller)
```php
set_time_limit(600); // 10 menit
ini_set('memory_limit', '1024M'); // 1GB untuk data sangat besar
DB::reconnect(); // Reconnect database sebelum import
```

### 2. Database Reconnect per Batch (Sudah ada di Service)
```php
foreach ($chunks as $chunkIndex => $chunk) {
    DB::reconnect(); // Reconnect setiap batch
    // ... process batch

    // Free memory setelah setiap batch
    unset($chunk);
    gc_collect_cycles();
}
```

### 3. PDO Timeout (Sudah ada di config/database.php)
```php
'options' => [
    PDO::ATTR_TIMEOUT => 600, // 10 menit
    PDO::ATTR_PERSISTENT => false,
]
```

## Konfigurasi yang Perlu Ditambahkan

### 1. PHP Configuration (php.ini)

**File:** `C:\xampp82\php\php.ini`

Cari dan ubah nilai berikut:
```ini
memory_limit = 1024M
max_execution_time = 600
post_max_size = 100M
upload_max_filesize = 100M
```

**Cara mengubah php.ini:**
1. Stop Apache di XAMPP Control Panel
2. Buka file `C:\xampp82\php\php.ini` dengan text editor
3. Cari baris `memory_limit`, `max_execution_time`, dll
4. Ubah nilainya sesuai di atas
5. Save file
6. Start Apache kembali

### 2. XAMPP MySQL Configuration

Edit file: `C:\xampp82\mysql\bin\my.ini`

Tambahkan atau ubah nilai berikut di section `[mysqld]`:

```ini
[mysqld]
# Timeout settings untuk operasi lama
wait_timeout = 600
interactive_timeout = 600
net_read_timeout = 300
net_write_timeout = 300

# Max packet size untuk data besar
max_allowed_packet = 64M

# Connection pool
max_connections = 200
```

### Cara Mengubah my.ini:

1. **Stop MySQL Server** melalui XAMPP Control Panel
2. **Buka file** `C:\xampp82\mysql\bin\my.ini` dengan text editor (run as administrator)
3. **Cari section** `[mysqld]` (biasanya di baris awal)
4. **Tambahkan** atau **ubah** konfigurasi di atas
5. **Save** file my.ini
6. **Start MySQL Server** kembali melalui XAMPP Control Panel

### Verifikasi Konfigurasi

Setelah restart MySQL, cek apakah konfigurasi sudah diterapkan:

```sql
SHOW VARIABLES LIKE 'wait_timeout';
SHOW VARIABLES LIKE 'interactive_timeout';
SHOW VARIABLES LIKE 'max_allowed_packet';
SHOW VARIABLES LIKE 'net_read_timeout';
SHOW VARIABLES LIKE 'net_write_timeout';
```

Atau via PHP artisan tinker:
```bash
php artisan tinker
DB::select("SHOW VARIABLES LIKE 'wait_timeout'");
DB::select("SHOW VARIABLES LIKE 'max_allowed_packet'");
```

## Alternatif: Set Via Query (Temporary)

Jika tidak bisa edit my.ini, bisa set temporary per session:

```sql
SET SESSION wait_timeout = 600;
SET SESSION interactive_timeout = 600;
SET SESSION net_read_timeout = 300;
SET SESSION net_write_timeout = 300;
SET GLOBAL max_allowed_packet = 67108864; -- 64MB
```

**Note:** Setting via query hanya berlaku untuk session saat itu, akan reset setelah restart MySQL.

## Monitoring Import Progress

Lihat log real-time di `storage/logs/laravel.log`:

```bash
tail -f storage/logs/laravel.log
```

Akan menampilkan progress batch:
```
Processing HAM batch {"batch":1,"total_batches":10}
Processing HAM batch {"batch":2,"total_batches":10}
...
```

## Tips Tambahan

### 1. Import Per Jenis (Bukan "All")
Untuk data sangat besar, import satu per satu:
- Import HAM saja dulu
- Kemudian SAM
- Terakhir Kategori

### 2. Gunakan Dry Run Dulu
Centang "Preview Mode (Dry Run)" untuk estimasi:
- Berapa banyak data yang akan di-import
- Apakah ada error di data
- Tidak menyimpan ke database

### 3. Kurangi Batch Size
Edit `config/google-aset-tik.php`:
```php
'sync' => [
    'batch_size' => 200, // Turunkan dari 500 ke 200
],
```

Batch size lebih kecil = lebih lambat tapi lebih stabil.

## Troubleshooting

### Jika Masih Error Timeout:
1. Naikkan `wait_timeout` dan `interactive_timeout` di my.ini menjadi 1200 (20 menit)
2. Naikkan `set_time_limit(1200)` di controller
3. Kurangi batch_size menjadi 100

### Jika Error Memory:
1. Naikkan PHP memory_limit di php.ini: `memory_limit = 1024M`
2. Restart Apache

### Jika Data Terlalu Besar:
Pertimbangkan menggunakan **Queue/Job system** untuk background processing:
- Data di-process di background
- User tidak perlu menunggu
- Bisa monitor progress via database log

---

**Last Updated:** 2026-01-08
