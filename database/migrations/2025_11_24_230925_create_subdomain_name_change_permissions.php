<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create permissions for subdomain name change feature
        $permissions = [
            // User Permissions
            [
                'name' => 'user.subdomain.name-change.index',
                'display_name' => 'Daftar Perubahan Nama Subdomain',
                'description' => 'Akses untuk melihat daftar permohonan perubahan nama subdomain milik user',
                'group' => 'User - Layanan Digital',
                'order' => 11,
            ],
            [
                'name' => 'user.subdomain.name-change.create',
                'display_name' => 'Ajukan Perubahan Nama Subdomain',
                'description' => 'Akses untuk mengajukan permohonan perubahan nama subdomain',
                'group' => 'User - Layanan Digital',
                'order' => 11,
            ],
            [
                'name' => 'user.subdomain.name-change.show',
                'display_name' => 'Detail Perubahan Nama Subdomain',
                'description' => 'Akses untuk melihat detail permohonan perubahan nama subdomain',
                'group' => 'User - Layanan Digital',
                'order' => 11,
            ],

            // Admin Permissions
            [
                'name' => 'admin.subdomain.name-change.index',
                'display_name' => 'Kelola Perubahan Nama Subdomain',
                'description' => 'Akses untuk melihat dan mengelola semua permohonan perubahan nama subdomain',
                'group' => 'Admin - Layanan Digital',
                'order' => 2,
            ],
            [
                'name' => 'admin.subdomain.name-change.show',
                'display_name' => 'Detail Perubahan Nama Subdomain',
                'description' => 'Akses untuk melihat detail permohonan perubahan nama subdomain',
                'group' => 'Admin - Layanan Digital',
                'order' => 2,
            ],
            [
                'name' => 'admin.subdomain.name-change.approve',
                'display_name' => 'Setujui Perubahan Nama Subdomain',
                'description' => 'Akses untuk menyetujui permohonan perubahan nama subdomain',
                'group' => 'Admin - Layanan Digital',
                'order' => 2,
            ],
            [
                'name' => 'admin.subdomain.name-change.reject',
                'display_name' => 'Tolak Perubahan Nama Subdomain',
                'description' => 'Akses untuk menolak permohonan perubahan nama subdomain',
                'group' => 'Admin - Layanan Digital',
                'order' => 2,
            ],
            [
                'name' => 'admin.subdomain.name-change.complete',
                'display_name' => 'Eksekusi Perubahan Nama Subdomain',
                'description' => 'Akses untuk mengeksekusi perubahan nama subdomain di Cloudflare',
                'group' => 'Admin - Layanan Digital',
                'order' => 2,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign permissions to appropriate roles
     */
    private function assignPermissionsToRoles(): void
    {
        // Admin gets all permissions
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminPermissions = Permission::where('name', 'like', 'admin.subdomain.name-change.%')->pluck('id');
            $adminRole->permissions()->syncWithoutDetaching($adminPermissions);
        }

        // User-OPD gets user permissions
        $userOpdRole = Role::where('name', 'User-OPD')->first();
        if ($userOpdRole) {
            $userPermissions = Permission::where('name', 'like', 'user.subdomain.name-change.%')->pluck('id');
            $userOpdRole->permissions()->syncWithoutDetaching($userPermissions);
        }

        // Operator-OPD also gets user permissions for subdomain name change
        $operatorOpdRole = Role::where('name', 'Operator-OPD')->first();
        if ($operatorOpdRole) {
            $userPermissions = Permission::where('name', 'like', 'user.subdomain.name-change.%')->pluck('id');
            $operatorOpdRole->permissions()->syncWithoutDetaching($userPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permissions
        Permission::where('name', 'like', '%subdomain.name-change%')->delete();
    }
};
