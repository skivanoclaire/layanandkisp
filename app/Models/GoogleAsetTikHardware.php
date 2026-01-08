<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoogleAsetTikHardware extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'google_aset_tik_hardware';

    protected $fillable = [
        'no', 'nama_opd', 'nama_aset', 'kode_gab_barang', 'no_register',
        'total', 'merk_type', 'tahun', 'nilai_perolehan', 'jenis_aset_tik',
        'sumber_pendanaan', 'keadaan_barang', 'tanggal_perolehan',
        'tanggal_penyerahan', 'asal_usul', 'status', 'terotorisasi',
        'aset_vital', 'keterangan', 'spreadsheet_row', 'synced_at',
        'sync_status', 'sync_notes'
    ];

    protected $casts = [
        'no' => 'integer',
        'tahun' => 'integer',
        'total' => 'integer',
        'nilai_perolehan' => 'decimal:2',
        'tanggal_perolehan' => 'integer',
        'tanggal_penyerahan' => 'date',
        'synced_at' => 'datetime',
        'spreadsheet_row' => 'integer',
    ];

    public function scopeForOpd($query, string $namaOpd)
    {
        return $query->where('nama_opd', $namaOpd);
    }

    public function scopePendingExport($query)
    {
        return $query->where('sync_status', 'pending_export');
    }

    public function scopeHasConflict($query)
    {
        return $query->where('sync_status', 'conflict');
    }

    public function scopeKondisi($query, string $kondisi)
    {
        return $query->where('keadaan_barang', $kondisi);
    }

    public function scopeTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeJenis($query, string $jenis)
    {
        return $query->where('jenis_aset_tik', $jenis);
    }

    public function getNilaiPerolehanFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->nilai_perolehan, 0, ',', '.');
    }

    public function getKategoriAttribute(): ?string
    {
        $kategori = GoogleAsetTikKategori::where('nama_perangkat', $this->nama_aset)->first();
        return $kategori ? $kategori->kategori_perangkat : null;
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
