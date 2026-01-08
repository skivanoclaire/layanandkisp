<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgrammingLanguage extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the frameworks for this programming language
     */
    public function frameworks(): HasMany
    {
        return $this->hasMany(Framework::class);
    }

    /**
     * Get subdomain requests using this language
     */
    public function subdomainRequests(): HasMany
    {
        return $this->hasMany(SubdomainRequest::class);
    }

    /**
     * Get web monitors using this language
     */
    public function webMonitors(): HasMany
    {
        return $this->hasMany(WebMonitor::class);
    }
}
