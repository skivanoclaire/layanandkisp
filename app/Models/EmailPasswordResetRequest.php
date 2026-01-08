<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class EmailPasswordResetRequest extends Model
{
    protected $fillable = [
        'user_id',
        'email_address',
        'nip',
        'encrypted_password',
        'status',
        'admin_notes',
        'processed_by',
        'processed_at',
        'reset_method',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    /**
     * Get the user who requested the password reset
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who processed the request
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Set encrypted password
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['encrypted_password'] = Crypt::encryptString($value);
    }

    /**
     * Get decrypted password (only for admin viewing, use with caution)
     */
    public function getDecryptedPassword(): string
    {
        return Crypt::decryptString($this->encrypted_password);
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processed requests
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}
