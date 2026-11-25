<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permission untuk halaman Presensi Kegiatan (Master Data) agar bisa dicentang
     * di Kelola Kewenangan. Sebelumnya rute hanya dikunci role:Admin sehingga tidak
     * muncul di tabel centang. Role Admin tetap mendapat akses penuh.
     */
    private array $permission = [
        'name' => 'Kelola Presensi Kegiatan',
        'display_name' => 'Kelola Presensi Kegiatan',
        'group' => 'Admin - Master Data',
        'order' => 90,
        'description' => 'Mengelola kegiatan, peserta, QR, rekap dan tanda tangan presensi kegiatan',
        'route_name' => 'admin.presensi-kegiatan.index',
    ];

    public function up(): void
    {
        $permId = DB::table('permissions')->where('name', $this->permission['name'])->value('id');
        if (! $permId) {
            $permId = DB::table('permissions')->insertGetId(array_merge($this->permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $roleId = DB::table('roles')->where('name', 'Admin')->value('id');
        if ($roleId && ! DB::table('permission_role')->where('role_id', $roleId)->where('permission_id', $permId)->exists()) {
            DB::table('permission_role')->insert([
                'role_id' => $roleId,
                'permission_id' => $permId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $permId = DB::table('permissions')->where('name', $this->permission['name'])->value('id');
        if ($permId) {
            DB::table('permission_role')->where('permission_id', $permId)->delete();
            DB::table('permissions')->where('id', $permId)->delete();
        }
    }
};
