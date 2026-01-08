<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Framework extends Model
{
    protected $fillable = ['name', 'programming_language_id'];

    /**
     * Get the programming language this framework belongs to
     */
    public function programmingLanguage(): BelongsTo
    {
        return $this->belongsTo(ProgrammingLanguage::class);
    }

    /**
     * Get subdomain requests using this framework
     */
    public function subdomainRequests(): HasMany
    {
        return $this->hasMany(SubdomainRequest::class);
    }

    /**
     * Get web monitors using this framework
     */
    public function webMonitors(): HasMany
    {
        return $this->hasMany(WebMonitor::class);
    }
}
