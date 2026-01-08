<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAccount extends Model
{
    protected $fillable = [
        'email',
        'domain',
        'user',
        'nip',
        'requester_name',
        'requester_nip',
        'requester_instansi',
        'requester_email',
        'requester_phone',
        'disk_used',
        'disk_quota',
        'diskused_readable',
        'diskquota_readable',
        'suspended',
        'last_synced_at',
    ];

    protected $casts = [
        'disk_used' => 'integer',
        'disk_quota' => 'integer',
        'suspended' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get disk usage percentage
     */
    public function getDiskUsagePercentageAttribute()
    {
        if (!$this->disk_quota || $this->disk_quota == 0) {
            return 0;
        }

        return round(($this->disk_used / $this->disk_quota) * 100, 2);
    }

    /**
     * Check if account is suspended
     */
    public function isSuspended()
    {
        return $this->suspended == 1;
    }

    /**
     * Get the email request that created this account
     * Relation based on matching email address
     */
    public function emailRequest()
    {
        // Extract username from email (before @)
        $username = strstr($this->email, '@', true);

        return $this->hasOne(EmailRequest::class, 'username', 'username')
            ->where('username', $username)
            ->where('status', 'selesai')
            ->latest();
    }

    /**
     * Helper method to get the user who requested this email
     */
    public function getRequestingUser()
    {
        $username = strstr($this->email, '@', true);

        $emailRequest = EmailRequest::where('username', $username)
            ->where('status', 'selesai')
            ->with('user')
            ->latest()
            ->first();

        return $emailRequest ? $emailRequest->user : null;
    }
}
