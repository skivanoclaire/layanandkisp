<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveiDigitalSetting extends Model
{
    protected $table = 'survei_digital_settings';

    protected $fillable = [
        'embed_base_url',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Admin yang terakhir memperbarui pengaturan.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Ambil (atau buat) baris pengaturan tunggal.
     */
    public static function current(): self
    {
        return self::first() ?? self::create([
            'embed_base_url' => null,
            'is_active' => true,
        ]);
    }
}
