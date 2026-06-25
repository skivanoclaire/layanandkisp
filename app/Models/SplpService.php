<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SplpService extends Model
{
    protected $fillable = [
        'kode_layanan', 'nama_layanan', 'opd_pemilik_id', 'deskripsi',
        'backend_url', 'route_path', 'environment', 'auth_type', 'klasifikasi_data',
        'gateway_service_id', 'gateway_route_id', 'status', 'tgl_aktif',
        'source_request_type', 'source_request_id',
    ];

    protected $casts = [
        'tgl_aktif' => 'date',
    ];

    public const ENVIRONMENTS = ['produksi', 'sandbox'];
    public const AUTH_TYPES = ['apikey', 'oauth2', 'none'];
    public const KLASIFIKASI = ['publik', 'terbatas', 'rahasia'];
    public const STATUSES = ['aktif', 'nonaktif', 'dicabut'];

    // Relations
    public function opdPemilik(): BelongsTo { return $this->belongsTo(UnitKerja::class, 'opd_pemilik_id'); }
    public function consumers(): HasMany { return $this->hasMany(SplpConsumer::class); }
    public function logs(): HasMany { return $this->hasMany(SplpServiceLog::class); }

    // Scopes
    public function scopeAktif($query) { return $query->where('status', 'aktif'); }
    public function scopeProduksi($query) { return $query->where('environment', 'produksi'); }

    /**
     * Layanan yang bisa dipilih pemohon V2 (akses konsumen).
     */
    public function scopeSelectableForConsumer($query)
    {
        return $query->where('status', 'aktif')->where('environment', 'produksi');
    }

    public function getKlasifikasiBadgeClassAttribute(): string
    {
        return [
            'publik' => 'bg-green-100 text-green-800',
            'terbatas' => 'bg-yellow-100 text-yellow-800',
            'rahasia' => 'bg-red-100 text-red-800',
        ][$this->klasifikasi_data] ?? 'bg-gray-100 text-gray-700';
    }

    /**
     * Generator kode layanan unik: SPLP-SVC-0001
     */
    public static function nextKode(): string
    {
        return DB::transaction(function () {
            $last = static::where('kode_layanan', 'like', 'SPLP-SVC-%')
                ->orderByDesc('kode_layanan')
                ->lockForUpdate()
                ->first();

            $lastNumber = $last ? intval(substr($last->kode_layanan, -4)) : 0;

            return 'SPLP-SVC-' . str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        }, 1);
    }
}
