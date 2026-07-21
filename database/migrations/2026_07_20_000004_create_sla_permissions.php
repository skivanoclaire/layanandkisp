<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permission untuk modul Manajemen SLA (dashboard capaian SLA + pengaturan target).
     */
    private array $permissions = [
        [
            'name' => 'Manajemen SLA',
            'display_name' => 'Manajemen SLA Layanan Digital',
            'group' => 'Admin - Layanan Digital',
            'order' => 95,
            'description' => 'Melihat dashboard capaian SLA & mengatur target SLA per layanan',
            'route_name' => 'admin.sla.index',
        ],
    ];

    public function up(): void
    {
        $ids = [];
        foreach ($this->permissions as $perm) {
            $id = DB::table('permissions')->where('name', $perm['name'])->value('id');
            if (! $id) {
                $id = DB::table('permissions')->insertGetId(array_merge($perm, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $ids[$perm['name']] = $id;
        }

        $adminId = DB::table('roles')->where('name', 'Admin')->value('id');
        if ($adminId) {
            foreach ($ids as $permId) {
                $this->attach($adminId, $permId);
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
