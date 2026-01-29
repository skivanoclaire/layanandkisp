<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveiKepuasanLayanan extends Model
{
    protected $table = 'survei_kepuasan_layanan';

    protected $fillable = [
        'user_id',
        'web_monitor_id',
        'rating_kecepatan',
        'rating_kemudahan',
        'rating_kualitas',
        'rating_responsif',
        'rating_keamanan',
        'rating_keseluruhan',
        'saran',
        'kelebihan',
        'kekurangan',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'rating_kecepatan' => 'integer',
        'rating_kemudahan' => 'integer',
        'rating_kualitas' => 'integer',
        'rating_responsif' => 'integer',
        'rating_keamanan' => 'integer',
        'rating_keseluruhan' => 'integer',
    ];

    /**
     * Get the user who submitted the survey
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the web monitor (subdomain) being surveyed
     */
    public function webMonitor(): BelongsTo
    {
        return $this->belongsTo(WebMonitor::class);
    }

    /**
     * Get average rating for this survey
     */
    public function getAverageRatingAttribute(): float
    {
        return round(($this->rating_kecepatan + $this->rating_kemudahan +
                     $this->rating_kualitas + $this->rating_responsif +
                     $this->rating_keamanan + $this->rating_keseluruhan) / 6, 2);
    }

    /**
     * Scope to filter by subdomain
     */
    public function scopeForWebMonitor($query, $webMonitorId)
    {
        return $query->where('web_monitor_id', $webMonitorId);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
