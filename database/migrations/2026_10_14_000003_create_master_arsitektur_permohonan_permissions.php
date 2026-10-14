<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permission permohonan pembaruan Master Data Arsitektur.
     * Pengusulan diberikan ke role User (hanya data instansinya sendiri),
     * persetujuan ke role Admin.
     */
    private array $permissions = [
        [
            'name' => 'Akses Pembaruan Data Arsitektur',
            'display_name' => 'Akses Pembaruan Data Arsitektur',
            'group' => 'User - Layanan Digital',
            'order' => 100,
            'description' => 'Mengusulkan penambahan dan perubahan Master Data Arsitektur milik instansinya',
            'route_name' => 'user.arsitektur.index',
            'role' => 'User',
        ],
        [
            'name' => 'Kelola Permohonan Data Arsitektur',
            'display_name' => 'Kelola Permohonan Pembaruan Data Arsitektur',
            'group' => 'Admin - Layanan Digital',
            'order' => 100,
            'description' => 'Menyetujui atau menolak permohonan pembaruan Master Data Arsitektur dari instansi',
            'route_name' => 'admin.arsitektur-permohonan.index',
            'role' => 'Admin',
        ],
    ];

    public function up(): void
    {
        foreach ($this->permissions as $perm) {
            $role = $perm['role'];
            unset($perm['role']);

            $permId = DB::table('permissions')->where('name', $perm['name'])->value('id');
            if (! $permId) {
                $permId = DB::table('permissions')->insertGetId(array_merge($perm, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            $roleId = DB::table('roles')->where('name', $role)->value('id');
            if ($roleId && ! DB::table('permission_role')->where('role_id', $roleId)->where('permission_id', $permId)->exists()) {
                DB::table('permission_role')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $permIds = DB::table('permissions')->whereIn('name', array_column($this->permissions, 'name'))->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('id', $permIds)->delete();
    }
};
