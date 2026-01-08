<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubdomainTechHistory extends Model
{
    protected $fillable = [
        'web_monitor_id',
        'changed_field',
        'old_value',
        'new_value',
        'changed_by',
        'notes'
    ];

    /**
     * Get the web monitor this history belongs to
     */
    public function webMonitor(): BelongsTo
    {
        return $this->belongsTo(WebMonitor::class);
    }

    /**
     * Get the user who made this change
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
