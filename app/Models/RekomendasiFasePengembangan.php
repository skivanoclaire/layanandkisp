<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekomendasiFasePengembangan extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_fase_pengembangan';

    protected $fillable = [
        'rekomendasi_aplikasi_form_id',
        'fase',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
        'progress_persen',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'progress_persen' => 'integer',
    ];

    /**
     * Get the rekomendasi aplikasi form that owns this fase.
     */
    public function rekomendasiAplikasiForm(): BelongsTo
    {
        return $this->belongsTo(RekomendasiAplikasiForm::class, 'rekomendasi_aplikasi_form_id');
    }

    /**
     * Get all documents for this fase.
     */
    public function dokumen(): HasMany
    {
        return $this->hasMany(RekomendasiDokumenPengembangan::class, 'rekomendasi_fase_pengembangan_id');
    }

    /**
     * Get all milestones for this fase.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(RekomendasiMilestone::class, 'rekomendasi_fase_pengembangan_id');
    }

    /**
     * Get fase display name.
     */
    public function getFaseDisplayAttribute(): string
    {
        return match($this->fase) {
            'rancang_bangun' => 'Rancang Bangun (Design)',
            'implementasi' => 'Implementasi',
            'uji_kelaikan' => 'Uji Kelaikan (Testing)',
            'pemeliharaan' => 'Pemeliharaan (Maintenance)',
            'evaluasi' => 'Evaluasi',
            default => $this->fase,
        };
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'belum_mulai' => 'Belum Mulai',
            'sedang_berjalan' => 'Sedang Berjalan',
            'selesai' => 'Selesai',
            default => $this->status,
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'belum_mulai' => 'secondary',
            'sedang_berjalan' => 'primary',
            'selesai' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get progress bar color based on percentage.
     */
    public function getProgressBarColorAttribute(): string
    {
        if ($this->progress_persen < 30) {
            return 'danger';
        } elseif ($this->progress_persen < 70) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    /**
     * Calculate milestone completion percentage.
     */
    public function getMilestoneCompletionAttribute(): int
    {
        $total = $this->milestones()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->milestones()->where('status', 'completed')->count();
        return (int) round(($completed / $total) * 100);
    }
}
