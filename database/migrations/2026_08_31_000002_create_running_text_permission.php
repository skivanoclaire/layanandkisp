<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permission untuk menu "Kelola Running Text" (grup Pengguna & Akses di sidebar).
     */
    private array $permissions = [
        [
            'name' => 'admin.running-text',
            'display_name' => 'Kelola Running Text',
            'group' => 'Admin - Dashboard & User Management',
            'order' => 4,
            'description' => 'Membuat, mengubah, dan menghapus teks berjalan di bawah navbar',
            'route_name' => 'admin.running-text.index',
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
                $exists = DB::table('permission_role')
                    ->where('role_id', $adminId)
                    ->where('permission_id', $permId)
                    ->exists();

                if (! $exists) {
                    DB::table('permission_role')->insert([
                        'role_id' => $adminId,
                        'permission_id' => $permId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
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
