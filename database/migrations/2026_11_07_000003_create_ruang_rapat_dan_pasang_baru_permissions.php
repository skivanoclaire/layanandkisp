<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permission untuk Peminjaman Ruang Rapat (Layanan Digital) dan
     * Pasang Baru Internet (Internet). "Akses" diberikan ke role User,
     * "Kelola" ke role Admin.
     */
    private array $permissions = [
        [
            'name' => 'Akses Peminjaman Ruang Rapat',
            'display_name' => 'Akses Peminjaman Ruang Rapat',
            'group' => 'User - Layanan Digital',
            'order' => 101,
            'description' => 'Mengajukan peminjaman ruang rapat',
            'route_name' => 'user.peminjaman-ruang-rapat.index',
            'role' => 'User',
        ],
        [
            'name' => 'Kelola Peminjaman Ruang Rapat',
            'display_name' => 'Kelola Peminjaman Ruang Rapat',
            'group' => 'Admin - Layanan Digital',
            'order' => 101,
            'description' => 'Menyetujui atau menolak peminjaman ruang rapat dan mengelola master ruang rapat',
            'route_name' => 'admin.peminjaman-ruang-rapat.index',
            'role' => 'Admin',
        ],
        [
            'name' => 'Akses Pasang Baru Internet',
            'display_name' => 'Akses Pasang Baru Internet',
            'group' => 'User - Internet & Konektivitas',
            'order' => 3,
            'description' => 'Mengajukan pemasangan baru layanan internet',
            'route_name' => 'user.internet.pasang-baru.index',
            'role' => 'User',
        ],
        [
            'name' => 'Kelola Pasang Baru Internet',
            'display_name' => 'Kelola Pasang Baru Internet',
            'group' => 'Admin - Internet & Konektivitas',
            'order' => 3,
            'description' => 'Memproses permohonan pemasangan baru layanan internet',
            'route_name' => 'admin.internet.pasang-baru.index',
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
