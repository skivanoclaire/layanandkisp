<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SubdomainNameChangeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'ticket_number',
        'web_monitor_id',
        'old_subdomain_name',
        'new_subdomain_name',
        'reason',
        'dns_propagation_acknowledged',
        'status',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'dns_propagation_acknowledged' => 'boolean',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function webMonitor(): BelongsTo
    {
        return $this->belongsTo(WebMonitor::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // Ticket Generator
    public static function nextTicket(string $prefix = 'SUBNAME'): string
    {
        $ym = now()->format('ym');
        $base = $prefix . '-' . $ym . '-';

        return DB::transaction(function () use ($base, $prefix) {
            $last = self::where('ticket_number', 'like', $prefix . '-' . now()->format('ym') . '-%')
                ->orderByDesc('ticket_number')
                ->lockForUpdate()
                ->first();

            $lastNumber = $last ? intval(explode('-', $last->ticket_number)[2] ?? 0) : 0;
            $nextNumber = $lastNumber + 1;
            return $base . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
        }, 1);
    }

    // Auto-generate ticket on create
    protected static function booted(): void
    {
        static::creating(function (SubdomainNameChangeRequest $model) {
            if (empty($model->ticket_number)) {
                $model->ticket_number = self::nextTicket();
            }
        });
    }
}
