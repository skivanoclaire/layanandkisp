<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaWorkingHourSetting extends Model
{
    protected $table = 'sla_working_hour_settings';

    protected $fillable = [
        'jam_mulai',
        'jam_selesai',
        'hari_kerja',
        'updated_by',
    ];

    protected $casts = [
        'hari_kerja' => 'array',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Ambil (atau buat) baris pengaturan jam kerja tunggal.
     */
    public static function current(): self
    {
        return self::first() ?? self::create([
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '16:00:00',
            'hari_kerja' => [1, 2, 3, 4, 5],
        ]);
    }
}
