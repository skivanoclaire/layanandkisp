<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SplpRequestLog extends Model
{
    protected $fillable = [
        'request_type', 'request_id', 'actor_id', 'action', 'note',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Catat aktivitas untuk sebuah permohonan SPLP.
     */
    public static function record(string $requestType, int $requestId, string $action, ?string $note = null): self
    {
        return static::create([
            'request_type' => $requestType,
            'request_id' => $requestId,
            'actor_id' => auth()->id(),
            'action' => $action,
            'note' => $note,
        ]);
    }
}
