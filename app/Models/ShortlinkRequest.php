<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShortlinkRequest extends Model
{
    protected $fillable = [
        'ticket_no', 'user_id', 'nama', 'nip', 'instansi',
        'long_url', 'title', 'requested_keyword', 'keperluan',
        'keyword', 'short_url', 'is_active', 'clicks', 'stats_synced_at',
        'status', 'admin_note', 'processed_by',
        'submitted_at', 'processing_at', 'rejected_at', 'completed_at',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'clicks'          => 'integer',
        'stats_synced_at' => 'datetime',
        'submitted_at'    => 'datetime',
        'processing_at'   => 'datetime',
        'rejected_at'     => 'datetime',
        'completed_at'    => 'datetime',
    ];

    // ====== Relations ======
    public function user()        { return $this->belongsTo(User::class); }
    public function processedBy() { return $this->belongsTo(User::class, 'processed_by'); }
    public function logs()        { return $this->hasMany(ShortlinkRequestLog::class); }

    // ====== Ticket number generator (URLYYMMNNNN) ======
    public static function nextTicket(string $prefix = 'URL'): string
    {
        $base = $prefix . now()->format('ym');

        return DB::transaction(function () use ($base) {
            $last = self::where('ticket_no', 'like', $base . '%')
                ->orderByDesc('ticket_no')
                ->lockForUpdate()
                ->first();

            $lastNumber = $last ? intval(substr($last->ticket_no, strlen($base))) : 0;

            return $base . str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        }, 1);
    }

    protected static function booted(): void
    {
        static::creating(function (ShortlinkRequest $model) {
            if (empty($model->ticket_no)) {
                $model->ticket_no = self::nextTicket();
            }
        });
    }
}
