<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsultasiSpbeAiAccess extends Model
{
    protected $table = 'konsultasi_spbe_ai_accesses';

    protected $fillable = [
        'user_id',
        'access_count',
        'last_accessed_at',
    ];

    protected $casts = [
        'last_accessed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Apakah pengguna tertentu pernah mengakses layanan Konsultasi SPBE AI.
     */
    public static function hasAccessed(int $userId): bool
    {
        return self::where('user_id', $userId)->exists();
    }
}
