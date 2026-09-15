<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jejak unduhan data DTSEN (siapa, kapan, dari mana) - Bab VIII huruf B.
 */
class DtsenDownloadLog extends Model
{
    protected $fillable = [
        'dtsen_access_token_id', 'dtsen_data_request_id', 'user_id',
        'ip_address', 'user_agent', 'downloaded_at',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(DtsenAccessToken::class, 'dtsen_access_token_id');
    }

    public function dataRequest(): BelongsTo
    {
        return $this->belongsTo(DtsenDataRequest::class, 'dtsen_data_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
