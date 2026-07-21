<?php

namespace App\Models;

use App\Services\Sla\SlaServiceRegistry;
use Illuminate\Database\Eloquent\Model;

class SlaSetting extends Model
{
    protected $table = 'sla_settings';

    protected $fillable = [
        'service_key',
        'label',
        'target_value',
        'target_unit',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'target_value' => 'integer',
        'is_active' => 'boolean',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Pastikan setiap layanan di registry punya baris target SLA (default 3 hari kerja).
     * Dipanggil dari halaman dashboard/pengaturan SLA sehingga tidak perlu seeder terpisah.
     */
    public static function ensureDefaults(): void
    {
        $existing = self::pluck('id', 'service_key');

        foreach (SlaServiceRegistry::all() as $key => $meta) {
            if (! $existing->has($key)) {
                self::create([
                    'service_key' => $key,
                    'label' => $meta['label'],
                    'target_value' => 3,
                    'target_unit' => 'hari_kerja',
                    'is_active' => true,
                ]);
            }
        }
    }
}
