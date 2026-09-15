<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Form 4.4 - Permohonan perpanjangan masa akses token/tautan unduh.
 */
class DtsenExtensionRequest extends Model
{
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    /** Batas maksimum durasi perpanjangan sekali ajukan (hari kalender). */
    public const MAX_DURASI_HARI = 90;

    protected $fillable = [
        'dtsen_data_request_id', 'dtsen_access_token_id', 'user_id',
        'alasan', 'durasi_hari', 'status', 'catatan', 'decided_by', 'decided_at',
    ];

    protected $casts = [
        'durasi_hari' => 'integer',
        'decided_at' => 'datetime',
    ];

    public function dataRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenDataRequest::class, 'dtsen_data_request_id');
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(DtsenAccessToken::class, 'dtsen_access_token_id');
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

    public static function statusBadgeClasses(): array
    {
        return [
            self::STATUS_DIAJUKAN => 'bg-yellow-100 text-yellow-800',
            self::STATUS_DISETUJUI => 'bg-green-100 text-green-800',
            self::STATUS_DITOLAK => 'bg-red-100 text-red-800',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return self::statusBadgeClasses()[$this->status] ?? 'bg-gray-100 text-gray-700';
    }
}
