<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',          // penting
        'nik',
        'phone',
        'is_verified',
        'verified_at',
        'verified_by',
    ];

    protected $hidden = ['password','remember_token'];

    // PASTIKAN HANYA ADA INI (hapus method casts(): array kalau masih ada)
    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified_at'       => 'datetime',
        'is_verified'       => 'boolean',
        'password'          => 'hashed',
    ];

    public function markVerified(string $adminIdOrEmail): void
    {
        $this->forceFill([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $adminIdOrEmail,
        ])->save();
    }

    public function requests()
{
    return $this->hasMany(\App\Models\UserRequest::class, 'user_id');
}
}
