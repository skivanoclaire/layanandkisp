<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class KuesionerPortalResponse extends Model
{
    public const STATUS_PERSETUJU = 'persetuju';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_MENOLAK = 'menolak';

    protected $table = 'kuesioner_portal_responses';

    protected $fillable = [
        'periode_id',
        'user_id',
        'kode_responden',
        'status',
        'persetujuan_at',
        'submitted_at',
        'durasi_detik',
        'unit_kerja_id',
        'status_kepegawaian',
        'peran',
        'lama_penggunaan',
        'frekuensi',
        'layanan_diajukan',
        'layanan_lainnya',
        'pernah_hubungi_petugas',
        'kelebihan',
        'kekurangan',
        'saran',
        'dikecualikan',
        'alasan_dikecualikan',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'persetujuan_at' => 'datetime',
        'submitted_at' => 'datetime',
        'durasi_detik' => 'integer',
        'layanan_diajukan' => 'array',
        'pernah_hubungi_petugas' => 'boolean',
        'dikecualikan' => 'boolean',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(KuesionerPortalPeriode::class, 'periode_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(KuesionerPortalAnswer::class, 'response_id');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    public static function kodeBaru(): string
    {
        do {
            $kode = 'R-' . Str::upper(Str::random(6));
        } while (static::where('kode_responden', $kode)->exists());

        return $kode;
    }
}
