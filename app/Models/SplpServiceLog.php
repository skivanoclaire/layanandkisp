<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SplpServiceLog extends Model
{
    public const UPDATED_AT = null; // hanya created_at

    protected $fillable = [
        'splp_service_id', 'splp_consumer_id', 'action',
        'config_lama', 'config_baru', 'actor_id', 'ip_address', 'keterangan',
    ];

    protected $casts = [
        'config_lama' => 'array',
        'config_baru' => 'array',
        'created_at' => 'datetime',
    ];

    public function service(): BelongsTo { return $this->belongsTo(SplpService::class, 'splp_service_id'); }
    public function consumer(): BelongsTo { return $this->belongsTo(SplpConsumer::class, 'splp_consumer_id'); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_id'); }

    /**
     * Helper pencatatan audit (pola RekomendasiHistoriAktivitas::log).
     */
    public static function record(array $attributes): self
    {
        return static::create(array_merge([
            'actor_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ], $attributes));
    }
}
