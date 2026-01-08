<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubdomainRequestLog extends Model
{
    protected $fillable = [
        'subdomain_request_id',
        'actor_id',
        'action',
        'note'
    ];

    /**
     * Get the subdomain request this log belongs to
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(SubdomainRequest::class, 'subdomain_request_id');
    }

    /**
     * Get the user who performed this action
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
