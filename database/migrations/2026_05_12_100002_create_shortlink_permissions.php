<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            [
                'name' => 'user.shortlink.index',
                'display_name' => 'Daftar Permohonan Pemendek Tautan',
                'description' => 'Akses untuk melihat daftar permohonan pemendek tautan (link.kaltaraprov.go.id) milik user',
                'group' => 'User - Layanan Digital',
                'order' => 12,
            ],
            [
                'name' => 'user.shortlink.create',
                'display_name' => 'Ajukan Permohonan Pemendek Tautan',
                'description' => 'Akses untuk mengajukan permohonan pemendek tautan',
                'group' => 'User - Layanan Digital',
                'order' => 12,
            ],
            [
                'name' => 'user.shortlink.show',
                'display_name' => 'Detail Permohonan Pemendek Tautan',
                'description' => 'Akses untuk melihat detail permohonan pemendek tautan',
                'group' => 'User - Layanan Digital',
                'order' => 12,
            ],
            [
                'name' => 'admin.shortlink.index',
                'display_name' => 'Kelola Permohonan Pemendek Tautan',
                'description' => 'Akses untuk melihat dan mengelola semua permohonan pemendek tautan',
                'group' => 'Admin - Layanan Digital',
                'order' => 2,
            ],
            [
                'name' => 'admin.shortlink.show',
                'display_name' => 'Detail Permohonan Pemendek Tautan',
                'description' => 'Akses untuk melihat detail permohonan pemendek tautan',
                'group' => 'Admin - Layanan Digital',
                'order' => 2,
            ],
            [
                'name' => 'admin.shortlink.manage',
                'display_name' => 'Proses Permohonan Pemendek Tautan',
                'description' => 'Akses untuk menyetujui/menolak permohonan, membuat & mengelola short link di YOURLS',
                'group' => 'Admin - Layanan Digital',
                'order' => 2,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }

        // Admin -> semua permission admin.shortlink.*
        if ($adminRole = Role::where('name', 'Admin')->first()) {
            $adminRole->permissions()->syncWithoutDetaching(
                Permission::where('name', 'like', 'admin.shortlink.%')->pluck('id')
            );
        }

        // Role pengguna -> semua permission user.shortlink.*
        $userPermissionIds = Permission::where('name', 'like', 'user.shortlink.%')->pluck('id');
        foreach (['User-Individual', 'User-OPD', 'Operator-OPD'] as $roleName) {
            if ($role = Role::where('name', $roleName)->first()) {
                $role->permissions()->syncWithoutDetaching($userPermissionIds);
            }
        }
    }

    public function down(): void
    {
        Permission::where('name', 'like', '%shortlink.%')->delete();
    }
};
