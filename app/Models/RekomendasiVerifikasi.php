<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiVerifikasi extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_verifikasi';

    protected $fillable = [
        'rekomendasi_aplikasi_form_id',
        'verifikator_id',
        'status',
        'checklist_analisis_kebutuhan',
        'checklist_perencanaan',
        'checklist_manajemen_risiko',
        'checklist_kelengkapan_data',
        'checklist_kesesuaian_peraturan',
        'checklist_anggaran',
        'checklist_timeline',
        'catatan_verifikasi',
        'catatan_internal',
        'tanggal_verifikasi',
    ];

    protected $casts = [
        'checklist_analisis_kebutuhan' => 'boolean',
        'checklist_perencanaan' => 'boolean',
        'checklist_manajemen_risiko' => 'boolean',
        'checklist_kelengkapan_data' => 'boolean',
        'checklist_kesesuaian_peraturan' => 'boolean',
        'checklist_anggaran' => 'boolean',
        'checklist_timeline' => 'boolean',
        'tanggal_verifikasi' => 'datetime',
    ];

    /**
     * Get the rekomendasi aplikasi form that owns this verifikasi.
     */
    public function rekomendasiAplikasiForm(): BelongsTo
    {
        return $this->belongsTo(RekomendasiAplikasiForm::class, 'rekomendasi_aplikasi_form_id');
    }

    /**
     * Get the verifikator (user) who verified this.
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    /**
     * Check if all checklists are completed.
     */
    public function isAllChecklistCompleted(): bool
    {
        return $this->checklist_analisis_kebutuhan
            && $this->checklist_perencanaan
            && $this->checklist_manajemen_risiko
            && $this->checklist_kelengkapan_data
            && $this->checklist_kesesuaian_peraturan;
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'menunggu' => 'warning',
            'sedang_diverifikasi' => 'info',
            'disetujui' => 'success',
            'ditolak' => 'danger',
            'perlu_revisi' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'menunggu' => 'Menunggu Verifikasi',
            'sedang_diverifikasi' => 'Sedang Diverifikasi',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'perlu_revisi' => 'Perlu Revisi',
            default => $this->status,
        };
    }
}
