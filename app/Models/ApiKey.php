<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key_prefix',
        'key_hash',
        'is_active',
        'last_used_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'key_hash',
    ];

    /**
     * Buat API key baru. Kembalikan model + plaintext key (hanya ditampilkan sekali).
     *
     * @return array{model: self, plain: string}
     */
    public static function generate(string $name, ?int $userId = null): array
    {
        $plain = Str::random(48);

        $model = self::create([
            'name' => $name,
            'key_prefix' => substr($plain, 0, 8),
            'key_hash' => hash('sha256', $plain),
            'is_active' => true,
            'created_by' => $userId,
        ]);

        return ['model' => $model, 'plain' => $plain];
    }

    /**
     * Cari API key aktif yang cocok dengan plaintext yang diberikan.
     */
    public static function findValid(string $plain): ?self
    {
        return self::where('key_hash', hash('sha256', $plain))
            ->where('is_active', true)
            ->first();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
