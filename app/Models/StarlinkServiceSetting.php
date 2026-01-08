<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StarlinkServiceSetting extends Model
{
    protected $table = 'starlink_service_settings';

    protected $fillable = [
        'is_active',
        'inactive_reason',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who updated this setting
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the current service status
     */
    public static function isActive()
    {
        $setting = self::first();
        return $setting ? $setting->is_active : true;
    }
}
