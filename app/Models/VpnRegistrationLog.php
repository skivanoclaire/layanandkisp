<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VpnRegistrationLog extends Model
{
    protected $fillable = [
        'vpn_registration_id',
        'actor_id',
        'action',
        'old_value',
        'new_value',
        'note',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(VpnRegistration::class, 'vpn_registration_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
