<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit trail lintas-tahapan DTSEN. Satu tabel untuk seluruh jenis berkas,
 * dibedakan oleh `request_type` (pola sama dengan SplpRequestLog).
 */
class DtsenRequestLog extends Model
{
    public const TYPE_AKUN = 'akun';
    public const TYPE_PERMOHONAN = 'permohonan';
    public const TYPE_PERPANJANGAN = 'perpanjangan';
    public const TYPE_PEMANFAATAN = 'pemanfaatan';
    public const TYPE_PEMUSNAHAN = 'pemusnahan';
    public const TYPE_INSIDEN = 'insiden';
    public const TYPE_PENGADUAN = 'pengaduan';

    protected $fillable = [
        'request_type', 'request_id', 'actor_id', 'action', 'note', 'ip_address',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function record(string $requestType, int $requestId, string $action, ?string $note = null): self
    {
        return static::create([
            'request_type' => $requestType,
            'request_id' => $requestId,
            'actor_id' => auth()->id(),
            'action' => $action,
            'note' => $note,
            'ip_address' => request()?->ip(),
        ]);
    }

    /** Riwayat satu berkas, terbaru di atas. */
    public static function forRequest(string $requestType, int $requestId)
    {
        return static::with('actor')
            ->where('request_type', $requestType)
            ->where('request_id', $requestId)
            ->orderByDesc('created_at')
            ->get();
    }
}
