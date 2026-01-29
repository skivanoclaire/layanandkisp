<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiStatusKementerian extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_status_kementerian';

    protected $fillable = [
        'rekomendasi_aplikasi_form_id',
        'rekomendasi_surat_id',
        'status',
        'file_respons_path',
        'nomor_surat_respons',
        'tanggal_surat_respons',
        'tanggal_diterima',
        'alasan_ditolak',
        'catatan_revisi',
        'catatan_internal',
        'updated_by',
    ];

    protected $casts = [
        'catatan_revisi' => 'array',
        'tanggal_surat_respons' => 'date',
        'tanggal_diterima' => 'date',
    ];

    /**
     * Get the rekomendasi aplikasi form.
     */
    public function rekomendasiAplikasiForm(): BelongsTo
    {
        return $this->belongsTo(RekomendasiAplikasiForm::class, 'rekomendasi_aplikasi_form_id');
    }

    /**
     * Get the surat that owns this status (legacy relation).
     */
    public function surat(): BelongsTo
    {
        return $this->belongsTo(RekomendasiSurat::class, 'rekomendasi_surat_id');
    }

    /**
     * Get the user who updated this status.
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'menunggu' => 'warning',
            'disetujui' => 'success',
            'ditolak' => 'danger',
            'revisi_diminta' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'menunggu' => 'Menunggu Respons Kementerian',
            'disetujui' => 'Disetujui Kementerian',
            'ditolak' => 'Ditolak Kementerian',
            'revisi_diminta' => 'Kementerian Meminta Revisi',
            default => $this->status,
        };
    }

    /**
     * Get duration waiting in days.
     */
    public function getDaysWaitingAttribute(): int
    {
        if ($this->status === 'menunggu') {
            return now()->diffInDays($this->created_at);
        }

        if ($this->tanggal_diterima) {
            return $this->tanggal_diterima->diffInDays($this->created_at);
        }

        return 0;
    }
}
