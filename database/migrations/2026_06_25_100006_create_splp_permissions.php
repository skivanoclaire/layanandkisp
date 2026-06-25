<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permissions untuk Integrasi SPLP.
     * - Akses SPLP           : user-side (Layanan → Integrasi SPLP)
     * - Kelola SPLP          : admin-side (Kelola Permohonan → Integrasi SPLP)
     * - admin.splp.services  : Master Data → Layanan SPLP
     * - admin.splp.consumers : Master Data → Konsumen SPLP
     * - admin.splp.audit     : Master Data → Audit Log SPLP
     */
    private array $permissions = [
        [
            'name' => 'Akses SPLP',
            'display_name' => 'Akses Integrasi SPLP',
            'group' => 'Akses',
            'order' => 90,
            'description' => 'Mengajukan permohonan integrasi SPLP (endpoint penyedia, akses konsumen, dll.)',
            'route_name' => 'user.splp.provider.index',
        ],
        [
            'name' => 'Kelola SPLP',
            'display_name' => 'Kelola Permohonan SPLP',
            'group' => 'Kelola Permohonan',
            'order' => 90,
            'description' => 'Memverifikasi & memproses permohonan integrasi SPLP',
            'route_name' => 'admin.splp.provider.index',
        ],
        [
            'name' => 'admin.splp.services',
            'display_name' => 'Master Data Layanan SPLP',
            'group' => 'Admin - Master Data',
            'order' => 90,
            'description' => 'Mengelola registry layanan/endpoint SPLP',
            'route_name' => 'admin.splp.services.index',
        ],
        [
            'name' => 'admin.splp.consumers',
            'display_name' => 'Master Data Konsumen SPLP',
            'group' => 'Admin - Master Data',
            'order' => 91,
            'description' => 'Mengelola registry konsumen & kredensial SPLP',
            'route_name' => 'admin.splp.consumers.index',
        ],
        [
            'name' => 'admin.splp.audit',
            'display_name' => 'Audit Log SPLP',
            'group' => 'Admin - Master Data',
            'order' => 92,
            'description' => 'Melihat riwayat perubahan konfigurasi SPLP',
            'route_name' => 'admin.splp.audit.index',
        ],
    ];

    public function up(): void
    {
        $ids = [];
        foreach ($this->permissions as $perm) {
            $id = DB::table('permissions')->where('name', $perm['name'])->value('id');
            if (!$id) {
                $id = DB::table('permissions')->insertGetId(array_merge($perm, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $ids[$perm['name']] = $id;
        }

        // Admin: semua permission SPLP
        $adminId = DB::table('roles')->where('name', 'Admin')->value('id');
        if ($adminId) {
            foreach ($ids as $permId) {
                $this->attach($adminId, $permId);
            }
        }

        // Role yang sudah punya akses layanan digital (acuan: user.subdomain.index)
        // diberi 'Akses SPLP' juga.
        $subdomainPermId = DB::table('permissions')->where('name', 'user.subdomain.index')->value('id');
        if ($subdomainPermId && isset($ids['Akses SPLP'])) {
            $roleIds = DB::table('permission_role')
                ->where('permission_id', $subdomainPermId)
                ->pluck('role_id');
            foreach ($roleIds as $roleId) {
                $this->attach($roleId, $ids['Akses SPLP']);
            }
        }
    }

    private function attach(int $roleId, int $permissionId): void
    {
        $exists = DB::table('permission_role')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->exists();

        if (!$exists) {
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
