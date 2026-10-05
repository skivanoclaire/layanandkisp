<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permission modul Kuesioner Kualitas Layanan Portal.
     * Akses pengisian diberikan ke role User (kelayakan tetap diperiksa di aplikasi:
     * hanya akun yang pernah mengajukan permohonan), pengelolaan ke role Admin.
     */
    private array $permissions = [
        [
            'name' => 'Akses Kuesioner Portal',
            'display_name' => 'Akses Kuesioner Kualitas Layanan Portal',
            'group' => 'User - Layanan Digital',
            'order' => 97,
            'description' => 'Mengisi kuesioner penelitian kualitas layanan portal (E-GovQual, IPA, Kano)',
            'route_name' => 'kuesioner-portal.index',
            'role' => 'User',
        ],
        [
            'name' => 'Kelola Kuesioner Portal',
            'display_name' => 'Kelola Kuesioner Kualitas Layanan Portal',
            'group' => 'Admin - Layanan Digital',
            'order' => 97,
            'description' => 'Mengatur periode, memantau respons, melihat analisis, dan mengekspor data kuesioner',
            'route_name' => 'admin.kuesioner-portal.index',
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
            if ($roleId) {
                $this->attach($roleId, $permId);
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
