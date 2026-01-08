<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Collection;

trait ManagesRolePermissions
{
    /**
     * Get all permissions that are allowed for a specific role based on the permission matrix
     *
     * @param Role $role
     * @return Collection
     */
    public function getAllowedPermissionsForRole(Role $role): Collection
    {
        $matrix = config('role_permissions.role_permission_matrix');

        if (!isset($matrix[$role->name])) {
            // If role not in matrix, return empty collection (safest default)
            return collect();
        }

        $roleConfig = $matrix[$role->name];
        $allPermissions = Permission::all();

        return $allPermissions->filter(function ($permission) use ($roleConfig) {
            return $this->canRoleHavePermission($permission, $roleConfig);
        });
    }

    /**
     * Check if a role can have a specific permission based on the matrix configuration
     *
     * @param Permission $permission
     * @param array $roleConfig
     * @return bool
     */
    private function canRoleHavePermission(Permission $permission, array $roleConfig): bool
    {
        // Check blocked specific permissions first
        if (isset($roleConfig['blocked_specific'])) {
            foreach ($roleConfig['blocked_specific'] as $blockedPattern) {
                if ($this->matchesPattern($permission->name, $blockedPattern)) {
                    return false;
                }
            }
        }

        // Check blocked groups
        if (isset($roleConfig['blocked_groups'])) {
            foreach ($roleConfig['blocked_groups'] as $blockedPattern) {
                if ($this->matchesPattern($permission->group, $blockedPattern)) {
                    return false;
                }
            }
        }

        // Check allowed specific permissions
        if (isset($roleConfig['allowed_specific'])) {
            foreach ($roleConfig['allowed_specific'] as $allowedPattern) {
                if ($this->matchesPattern($permission->name, $allowedPattern)) {
                    return true;
                }
            }
        }

        // Check allowed groups
        if (isset($roleConfig['allowed_groups'])) {
            foreach ($roleConfig['allowed_groups'] as $allowedPattern) {
                if ($this->matchesPattern($permission->group, $allowedPattern)) {
                    return true;
                }
            }
        }

        // Default: not allowed
        return false;
    }

    /**
     * Check if a string matches a pattern (supports wildcards)
     *
     * @param string $string
     * @param string $pattern
     * @return bool
     */
    private function matchesPattern(string $string, string $pattern): bool
    {
        // Exact match
        if ($string === $pattern) {
            return true;
        }

        // Wildcard match: convert pattern to regex
        if (strpos($pattern, '*') !== false) {
            $regexPattern = '/^' . str_replace(
                ['*', '.'],
                ['.*', '\\.'],
                $pattern
            ) . '$/';

            return preg_match($regexPattern, $string) === 1;
        }

        return false;
    }

    /**
     * Check if a role can have a specific permission (public method for views)
     *
     * @param Role $role
     * @param Permission $permission
     * @return bool
     */
    public function roleCanHavePermission(Role $role, Permission $permission): bool
    {
        $matrix = config('role_permissions.role_permission_matrix');

        if (!isset($matrix[$role->name])) {
            return false;
        }

        return $this->canRoleHavePermission($permission, $matrix[$role->name]);
    }

    /**
     * Get permissions grouped by category, filtered by what the role can have
     *
     * @param Role $role
     * @return Collection
     */
    public function getFilteredPermissionsForRole(Role $role): Collection
    {
        $allowedPermissions = $this->getAllowedPermissionsForRole($role);

        return $allowedPermissions
            ->sortBy(['order', 'display_name'])
            ->groupBy('group');
    }

    /**
     * Validate that all provided permission IDs are allowed for the role
     *
     * @param Role $role
     * @param array $permissionIds
     * @return array [valid_ids, invalid_ids]
     */
    public function validatePermissionsForRole(Role $role, array $permissionIds): array
    {
        $allowedPermissions = $this->getAllowedPermissionsForRole($role);
        $allowedIds = $allowedPermissions->pluck('id')->toArray();

        $validIds = array_intersect($permissionIds, $allowedIds);
        $invalidIds = array_diff($permissionIds, $allowedIds);

        return [
            'valid' => array_values($validIds),
            'invalid' => array_values($invalidIds),
        ];
    }

    /**
     * Get role configuration from matrix
     *
     * @param Role $role
     * @return array|null
     */
    public function getRoleMatrixConfig(Role $role): ?array
    {
        $matrix = config('role_permissions.role_permission_matrix');
        return $matrix[$role->name] ?? null;
    }

    /**
     * Get reason why a permission is not allowed for a role
     *
     * @param Role $role
     * @param Permission $permission
     * @return string
     */
    public function getDisallowedReason(Role $role, Permission $permission): string
    {
        $matrix = config('role_permissions.role_permission_matrix');

        if (!isset($matrix[$role->name])) {
            return "Role '{$role->display_name}' tidak terdaftar dalam permission matrix.";
        }

        $roleConfig = $matrix[$role->name];

        // Check blocked specific
        if (isset($roleConfig['blocked_specific'])) {
            foreach ($roleConfig['blocked_specific'] as $blockedPattern) {
                if ($this->matchesPattern($permission->name, $blockedPattern)) {
                    return "Permission '{$permission->display_name}' diblokir secara spesifik untuk role ini.";
                }
            }
        }

        // Check blocked groups
        if (isset($roleConfig['blocked_groups'])) {
            foreach ($roleConfig['blocked_groups'] as $blockedPattern) {
                if ($this->matchesPattern($permission->group, $blockedPattern)) {
                    return "Group '{$permission->group}' tidak diizinkan untuk role ini.";
                }
            }
        }

        return "Permission ini tidak termasuk dalam scope role '{$role->display_name}'.";
    }
}
