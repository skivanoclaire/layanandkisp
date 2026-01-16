<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiMilestone extends Model
{
    use HasFactory;

    protected $table = 'rekomendasi_milestone';

    protected $fillable = [
        'rekomendasi_fase_pengembangan_id',
        'nama_milestone',
        'target_tanggal',
        'status',
        'file_bukti',
        'keterangan',
    ];

    protected $casts = [
        'target_tanggal' => 'date',
    ];

    /**
     * Get the fase pengembangan that owns this milestone.
     */
    public function fasePengembangan(): BelongsTo
    {
        return $this->belongsTo(RekomendasiFasePengembangan::class, 'rekomendasi_fase_pengembangan_id');
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'not_started' => 'Belum Dimulai',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            default => $this->status,
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'not_started' => 'secondary',
            'in_progress' => 'primary',
            'completed' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Check if milestone is overdue.
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        return now()->gt($this->target_tanggal);
    }

    /**
     * Get days until deadline.
     */
    public function getDaysUntilDeadlineAttribute(): int
    {
        if ($this->status === 'completed') {
            return 0;
        }

        return now()->diffInDays($this->target_tanggal, false);
    }
}
