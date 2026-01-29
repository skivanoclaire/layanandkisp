<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PseUpdateRequestLog extends Model
{
    protected $fillable = [
        'pse_update_request_id',
        'actor_id',
        'action',
        'note',
    ];

    // Relationships

    public function request(): BelongsTo
    {
        return $this->belongsTo(PseUpdateRequest::class, 'pse_update_request_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
