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
        'nip',
        'phone',
        'unit_kerja_id',
        'is_verified',
        'is_sso_user',
        'verified_at',
        'verified_by',
    ];

    protected $hidden = ['password','remember_token'];

    // PASTIKAN HANYA ADA INI (hapus method casts(): array kalau masih ada)
    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified_at'       => 'datetime',
        'is_verified'       => 'boolean',
        'is_sso_user'       => 'boolean',
        'password'          => 'hashed',
        'nik'               => 'encrypted',
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

    /**
     * Get the jabatan (position/job title) for this user
     */
    public function jabatan()
    {
        return $this->hasOne(\App\Models\Jabatan::class);
    }

    /**
     * Get the unit kerja (work unit/institution) for this user
     */
    public function unitKerja()
    {
        return $this->belongsTo(\App\Models\UnitKerja::class);
    }

    /**
     * Get the roles that belong to this user
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withTimestamps();
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Get role names as array
     */
    public function getRoleNamesAttribute(): array
    {
        return $this->roles->pluck('name')->toArray();
    }

    /**
     * Get role display names as string
     */
    public function getRoleDisplayAttribute(): string
    {
        return $this->roles->pluck('display_name')->join(', ');
    }

    /**
     * Get all permissions from user's roles
     * Eager loads relationships to prevent N+1 query problem
     */
    public function getPermissionsAttribute()
    {
        // Eager load jika belum dimuat untuk mencegah N+1 query
        if (!$this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        } elseif ($this->roles->isNotEmpty() && !$this->roles->first()->relationLoaded('permissions')) {
            $this->load('roles.permissions');
        }

        return $this->roles->flatMap(function($role) {
            return $role->permissions;
        })->unique('id');
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions->contains('name', $permissionName);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions->whereIn('name', $permissions)->isNotEmpty();
    }

    /**
     * Relationship to vidcon requests where user is assigned as operator
     */
    public function vidconRequests()
    {
        return $this->belongsToMany(\App\Models\VidconRequest::class, 'vidcon_request_operators', 'user_id', 'vidcon_request_id')
                    ->withTimestamps();
    }

    /**
     * Get operator workload statistics for vidcon
     * Returns count of completed and in-progress vidcon requests
     */
    public function getVidconWorkloadAttribute()
    {
        return $this->vidconRequests()
                    ->whereIn('status', ['proses', 'selesai'])
                    ->count();
    }

    /**
     * Get active vidcon workload (currently in progress)
     */
    public function getActiveVidconWorkloadAttribute()
    {
        return $this->vidconRequests()
                    ->where('status', 'proses')
                    ->count();
    }
}
