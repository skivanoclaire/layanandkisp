<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoogleAsetTikSyncLog extends Model
{
    use HasFactory;

    protected $table = 'google_aset_tik_sync_logs';

    protected $fillable = [
        'register_type', 'sync_type', 'status', 'total_rows',
        'rows_created', 'rows_updated', 'rows_failed', 'rows_skipped',
        'error_message', 'error_details', 'started_at', 'completed_at',
        'user_id', 'is_manual'
    ];

    protected $casts = [
        'error_details' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_manual' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->completed_at) {
            return 'Belum selesai';
        }

        return $this->started_at->diffForHumans($this->completed_at, true);
    }

    public function getDurationSecondsAttribute(): ?int
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        $success = $this->rows_created + $this->rows_updated;
        return ($success / $this->total_rows) * 100;
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success' && $this->rows_failed === 0;
    }

    public function isRunning(): bool
    {
        return $this->status === 'running' && !$this->completed_at;
    }
}
