<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permission untuk halaman yang sebelumnya hanya dijaga nama role
     * (role:Admin / role:Operator-Vidcon) sehingga tidak bisa diatur lewat
     * Kelola Kewenangan. Role yang dulu punya akses tetap mendapatkannya.
     */
    private array $permissions = [
        [
            'name' => 'admin.survei-digital',
            'display_name' => 'Manajemen Survei Digital',
            'group' => 'Admin - Layanan Digital',
            'order' => 98,
            'description' => 'Mengelola token embed survei SPBE',
            'route_name' => 'admin.survei-digital.index',
            'roles' => ['Admin'],
        ],
        [
            'name' => 'operator.vidcon',
            'display_name' => 'Pelaporan & Dokumentasi Vidcon',
            'group' => 'Operator - 01. Vidcon & TIK',
            'order' => 4,
            'description' => 'Mengunggah dokumentasi kegiatan vidcon yang ditugaskan',
            'route_name' => 'operator.vidcon.index',
            'roles' => ['Admin', 'Operator-Vidcon'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->permissions as $perm) {
            $roles = $perm['roles'];
            unset($perm['roles']);

            $permId = DB::table('permissions')->where('name', $perm['name'])->value('id');
            if (! $permId) {
                $permId = DB::table('permissions')->insertGetId(array_merge($perm, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            foreach ($roles as $role) {
                $roleId = DB::table('roles')->where('name', $role)->value('id');
                if ($roleId) {
                    $this->attach($roleId, $permId);
                }
            }
        }
    }

    private function attach(int $roleId, int $permissionId): void
    {
        $exists = DB::table('permission_role')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->exists();

        if (! $exists) {
            DB::table('permission_role')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $names = array_column($this->permissions, 'name');
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
