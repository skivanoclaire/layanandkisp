<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServerLocation extends Model
{
    protected $fillable = ['name'];

    /**
     * Get subdomain requests using this server location
     */
    public function subdomainRequests(): HasMany
    {
        return $this->hasMany(SubdomainRequest::class);
    }

    /**
     * Get web monitors using this server location
     */
    public function webMonitors(): HasMany
    {
        return $this->hasMany(WebMonitor::class);
    }
}
