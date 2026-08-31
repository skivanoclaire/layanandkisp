<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get the users that belong to this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
                    ->withTimestamps();
    }

    /**
     * Terjemahkan daftar nama role ke kolom legacy `users.role`.
     * Prioritas mengikuti urutan array (indeks lebih tinggi = lebih kuat).
     */
    public static function determineLegacyRole(array $roleNames): string
    {
        $roleMapping = [
            'User-Individual' => 'user',
            'User-OPD' => 'user',
            'Operator-Vidcon' => 'operator-vidcon',
            'Admin-Vidcon' => 'admin-vidcon',
            'Admin' => 'admin',
        ];

        $legacyRole = 'user';
        $highestPriority = -1;

        foreach ($roleNames as $roleName) {
            if (isset($roleMapping[$roleName])) {
                $legacyValue = $roleMapping[$roleName];
                $priority = array_search($legacyValue, ['user', 'operator-vidcon', 'admin-vidcon', 'admin']);

                if ($priority !== false && $priority > $highestPriority) {
                    $highestPriority = $priority;
                    $legacyRole = $legacyValue;
                }
            }
        }

        return $legacyRole;
    }

    /**
     * Get the permissions for this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
                    ->withTimestamps()
                    ->orderBy('order');
    }
}
