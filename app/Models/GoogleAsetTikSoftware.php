<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoogleAsetTikSoftware extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'google_aset_tik_software';

    protected $fillable = [
        'no', 'nama_opd', 'nama_aset', 'kode_barang', 'tahun', 'judul', 'harga',
        'is_aktif', 'keterangan_software', 'jenis_perangkat_lunak',
        'data_output', 'pengembangan', 'sewa', 'software_berjalan',
        'fitur_sesuai', 'url', 'integrasi', 'platform', 'database',
        'script', 'framework', 'status', 'terotorisasi', 'aset_vital',
        'keterangan_utilisasi', 'asal_usul', 'spreadsheet_row',
        'synced_at', 'sync_status', 'sync_notes'
    ];

    protected $casts = [
        'no' => 'integer',
        'tahun' => 'integer',
        'harga' => 'decimal:2',
        'synced_at' => 'datetime',
        'spreadsheet_row' => 'integer',
    ];

    public function scopeForOpd($query, string $namaOpd)
    {
        return $query->where('nama_opd', $namaOpd);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', 'Aktif');
    }

    public function scopePendingExport($query)
    {
        return $query->where('sync_status', 'pending_export');
    }

    public function scopeJenis($query, string $jenis)
    {
        return $query->where('jenis_perangkat_lunak', $jenis);
    }

    public function getHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    public function markPendingExport(string $notes = null): void
    {
        $this->update([
            'sync_status' => 'pending_export',
            'sync_notes' => $notes,
        ]);
    }

    public function markSynced(): void
    {
        $this->update([
            'sync_status' => 'synced',
            'synced_at' => now(),
            'sync_notes' => null,
        ]);
    }

    public function needsSync(): bool
    {
        return in_array($this->sync_status, ['pending_export', 'conflict']);
    }
}
