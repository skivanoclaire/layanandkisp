<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Permintaan aktivasi ulang akun DTSEN yang nonaktif karena tidak digunakan.
 */
class DtsenAccountReactivation extends Model
{
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'dtsen_account_request_id', 'user_id', 'alasan',
        'status', 'catatan', 'decided_by', 'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function accountRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenAccountRequest::class, 'dtsen_account_request_id');
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function decidedBy(): BelongsTo { return $this->belongsTo(User::class, 'decided_by'); }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst((string) $this->status);
    }
}
