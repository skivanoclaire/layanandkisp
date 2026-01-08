<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class VidconRequest extends Model
{
    protected $fillable = [
        'ticket_no', 'user_id', 'nama', 'nip', 'unit_kerja_id', 'email_pemohon', 'no_hp',
        'judul_kegiatan', 'deskripsi_kegiatan', 'tanggal_mulai', 'tanggal_selesai',
        'jam_mulai', 'jam_selesai', 'platform', 'platform_lainnya', 'jumlah_peserta', 'keperluan_khusus',
        'status', 'submitted_at', 'processing_at', 'completed_at', 'rejected_at',
        'processed_by', 'admin_notes',
        'link_meeting', 'meeting_id', 'meeting_password', 'informasi_tambahan', 'operator_assigned',
        'last_info_updated_at', 'info_update_count', 'last_updated_by'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'processing_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'jumlah_peserta' => 'integer',
        'last_info_updated_at' => 'datetime',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function operatorAssigned(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_assigned');
    }

    // Many-to-many relationship for multiple operators
    public function operators()
    {
        return $this->belongsToMany(User::class, 'vidcon_request_operators', 'vidcon_request_id', 'user_id')
                    ->withTimestamps();
    }

    public function activities()
    {
        return $this->hasMany(VidconRequestActivity::class);
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeInProcess($query)
    {
        return $query->where('status', 'proses');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'selesai');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'ditolak');
    }

    // Accessors
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'menunggu' => '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 font-semibold">Menunggu</span>',
            'proses' => '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-semibold">Diproses</span>',
            'selesai' => '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">Selesai</span>',
            'ditolak' => '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-semibold">Ditolak</span>',
            default => '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 font-semibold">Unknown</span>',
        };
    }

    public function getPlatformDisplayAttribute(): string
    {
        if ($this->platform === 'Lainnya' && $this->platform_lainnya) {
            return $this->platform_lainnya;
        }
        return $this->platform;
    }

    // Ticket Generator
    public static function nextTicket(string $prefix = 'VID'): string
    {
        $ym = now()->format('ym');
        $base = $prefix . '-' . $ym . '-';

        return DB::transaction(function () use ($base, $prefix) {
            $last = self::where('ticket_no', 'like', $prefix . '-' . now()->format('ym') . '-%')
                ->orderByDesc('ticket_no')
                ->lockForUpdate()
                ->first();

            $lastNumber = $last ? intval(explode('-', $last->ticket_no)[2] ?? 0) : 0;
            $nextNumber = $lastNumber + 1;
            return $base . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
        }, 1);
    }

    /**
     * AI Recommendation for fair operator distribution
     * Uses weighted scoring algorithm to balance workload
     *
     * @param \Illuminate\Support\Collection $operators Collection of User models with Operator-Vidcon role
     * @param int $count Number of operators to recommend
     * @return array Array of recommended operator IDs
     */
    public static function recommendOperators($operators, int $count = 1): array
    {
        if ($operators->isEmpty()) {
            return [];
        }

        // Calculate workload score for each operator
        $operatorsWithScore = $operators->map(function ($operator) {
            // Active workload has higher weight (2x) - currently in progress
            $activeWorkload = $operator->active_vidcon_workload ?? 0;

            // Total workload has lower weight (0.5x) - historical distribution
            $totalWorkload = $operator->vidcon_workload ?? 0;

            // Calculate weighted score (lower is better)
            $score = ($activeWorkload * 2) + ($totalWorkload * 0.5);

            return [
                'id' => $operator->id,
                'name' => $operator->name,
                'active_workload' => $activeWorkload,
                'total_workload' => $totalWorkload,
                'score' => $score,
            ];
        });

        // Sort by score ascending (least busy first)
        $sorted = $operatorsWithScore->sortBy('score');

        // Return top N operator IDs
        return $sorted->take($count)->pluck('id')->toArray();
    }

    /**
     * Check if meeting info is complete enough to approve
     */
    public function isInfoComplete(): bool
    {
        return !empty($this->link_meeting)
            && !empty($this->meeting_id)
            && !empty($this->meeting_password);
    }

    /**
     * Get days since last info update
     */
    public function daysSinceLastUpdate(): ?int
    {
        if (!$this->last_info_updated_at) {
            return null;
        }
        return now()->diffInDays($this->last_info_updated_at);
    }

    /**
     * Check if request is stale (no update in X days while in proses)
     */
    public function isStale(int $days = 3): bool
    {
        if ($this->status !== 'proses') {
            return false;
        }

        $lastUpdate = $this->last_info_updated_at ?? $this->processing_at;
        if (!$lastUpdate) {
            return false;
        }

        return now()->diffInDays($lastUpdate) >= $days;
    }

    /**
     * Get progress percentage based on filled fields
     */
    public function getProgressPercentage(): int
    {
        $requiredFields = ['link_meeting', 'meeting_id', 'meeting_password'];
        $filledCount = 0;

        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $filledCount++;
            }
        }

        return (int) (($filledCount / count($requiredFields)) * 100);
    }

    protected static function booted(): void
    {
        static::creating(function (VidconRequest $model) {
            if (empty($model->ticket_no)) {
                $model->ticket_no = self::nextTicket();
            }
            if (empty($model->submitted_at)) {
                $model->submitted_at = now();
            }
        });
    }
}
