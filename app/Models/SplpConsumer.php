<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SplpConsumer extends Model
{
    protected $fillable = [
        'splp_service_id', 'instansi_id', 'nama_konsumen',
        'credential_type', 'credential_ref', 'acl', 'rate_limit', 'ip_whitelist',
        'environment', 'expires_at', 'status',
        'source_request_type', 'source_request_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public const CREDENTIAL_TYPES = ['apikey', 'oauth2'];
    public const STATUSES = ['aktif', 'nonaktif', 'dicabut', 'kadaluarsa'];

    public function service(): BelongsTo { return $this->belongsTo(SplpService::class, 'splp_service_id'); }
    public function instansi(): BelongsTo { return $this->belongsTo(UnitKerja::class, 'instansi_id'); }

    public function scopeAktif($query) { return $query->where('status', 'aktif'); }

    public function getStatusBadgeClassAttribute(): string
    {
        return [
            'aktif' => 'bg-green-100 text-green-800',
            'nonaktif' => 'bg-gray-100 text-gray-700',
            'dicabut' => 'bg-red-100 text-red-800',
            'kadaluarsa' => 'bg-orange-100 text-orange-800',
        ][$this->status] ?? 'bg-gray-100 text-gray-700';
    }
}
