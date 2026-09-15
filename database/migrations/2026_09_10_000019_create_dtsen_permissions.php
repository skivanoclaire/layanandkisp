<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permission & role untuk layanan Berbagi Pakai Data DTSEN.
     *
     * Pembagian aktor mengikuti Juknis:
     * - OPD pemohon        : "Akses DTSEN"
     * - DKISP (prosesor)   : "Kelola Akun DTSEN", "Kelola Permohonan DTSEN", "Kelola Laporan DTSEN"
     * - Bapperida (Forum SDD): "Verifikasi Substansi DTSEN" + master variabel/rilis + dashboard
     */
    private array $permissions = [
        [
            'name' => 'Akses DTSEN',
            'display_name' => 'Akses Berbagi Pakai Data DTSEN',
            'group' => 'Akses',
            'order' => 95,
            'description' => 'Mendaftarkan akun, mengajukan permintaan data, dan melaporkan pemanfaatan DTSEN',
            'route_name' => 'user.dtsen.akun.index',
        ],
        [
            'name' => 'Kelola Akun DTSEN',
            'display_name' => 'Kelola Akun Layanan DTSEN',
            'group' => 'Kelola Permohonan',
            'order' => 95,
            'description' => 'Memverifikasi permohonan pembuatan & aktivasi ulang akun layanan DTSEN',
            'route_name' => 'admin.dtsen.akun.index',
        ],
        [
            'name' => 'Kelola Permohonan DTSEN',
            'display_name' => 'Kelola Permintaan Data DTSEN',
            'group' => 'Kelola Permohonan',
            'order' => 96,
            'description' => 'Verifikasi administrasi, pemrosesan & QA, BAST, serta penerbitan token akses DTSEN',
            'route_name' => 'admin.dtsen.permohonan.index',
        ],
        [
            'name' => 'Verifikasi Substansi DTSEN',
            'display_name' => 'Verifikasi Substansi DTSEN',
            'group' => 'Kelola Permohonan',
            'order' => 97,
            'description' => 'Menilai kesesuaian KAK dengan tusi OPD (Koordinator Forum Satu Data Daerah)',
            'route_name' => 'admin.dtsen.substansi.index',
        ],
        [
            'name' => 'Kelola Laporan DTSEN',
            'display_name' => 'Kelola Laporan & Pengaduan DTSEN',
            'group' => 'Kelola Permohonan',
            'order' => 98,
            'description' => 'Merekap laporan pemanfaatan, berita acara pemusnahan, insiden, dan pengaduan DTSEN',
            'route_name' => 'admin.dtsen.laporan.pemanfaatan',
        ],
        [
            'name' => 'admin.dtsen.dashboard',
            'display_name' => 'Dashboard Monitoring DTSEN',
            'group' => 'Admin - Master Data',
            'order' => 95,
            'description' => 'Rekap permohonan, SLA, pemanfaatan, dan insiden DTSEN',
            'route_name' => 'admin.dtsen.dashboard',
        ],
        [
            'name' => 'admin.dtsen.variables',
            'display_name' => 'Master Variabel DTSEN',
            'group' => 'Admin - Master Data',
            'order' => 96,
            'description' => 'Mengelola daftar variabel/indikator DTSEN per rilis',
            'route_name' => 'admin.dtsen.variables.index',
        ],
        [
            'name' => 'admin.dtsen.releases',
            'display_name' => 'Master Rilis DTSEN',
            'group' => 'Admin - Master Data',
            'order' => 97,
            'description' => 'Mengelola nomor & tanggal rilis DTSEN beserta notifikasi pemusnahan data lama',
            'route_name' => 'admin.dtsen.releases.index',
        ],
        [
            'name' => 'admin.dtsen.wilayah',
            'display_name' => 'Master Wilayah DTSEN',
            'group' => 'Admin - Master Data',
            'order' => 98,
            'description' => 'Mengelola wilayah berjenjang untuk cakupan permintaan data',
            'route_name' => 'admin.dtsen.wilayah.index',
        ],
    ];

    /** Permission yang dipegang role Admin-Bapperida (koordinator Forum SDD). */
    private array $bapperidaPermissions = [
        'Verifikasi Substansi DTSEN',
        'Kelola Laporan DTSEN',
        'admin.dtsen.dashboard',
        'admin.dtsen.variables',
        'admin.dtsen.releases',
        'admin.dtsen.wilayah',
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

        // Admin DKISP: seluruh permission DTSEN.
        $adminId = DB::table('roles')->where('name', 'Admin')->value('id');
        if ($adminId) {
            foreach ($ids as $permId) {
                $this->attach($adminId, $permId);
            }
        }

        // Role khusus verifikator substansi (Bapperida).
        $bapperidaId = DB::table('roles')->where('name', 'Admin-Bapperida')->value('id');
        if (! $bapperidaId) {
            $bapperidaId = DB::table('roles')->insertGetId([
                'name' => 'Admin-Bapperida',
                'display_name' => 'Admin Bapperida (Forum Satu Data Daerah)',
                'description' => 'Koordinator Forum Satu Data Daerah: verifikasi substansi KAK, master variabel DTSEN, rekap pelaporan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        foreach ($this->bapperidaPermissions as $name) {
            if (isset($ids[$name])) {
                $this->attach($bapperidaId, $ids[$name]);
            }
        }

        // Role yang sudah punya akses layanan digital lain (acuan: Akses SPLP)
        // otomatis mendapat "Akses DTSEN" agar tidak perlu disetel manual satu per satu.
        $splpPermId = DB::table('permissions')->where('name', 'Akses SPLP')->value('id');
        if ($splpPermId && isset($ids['Akses DTSEN'])) {
            $roleIds = DB::table('permission_role')->where('permission_id', $splpPermId)->pluck('role_id');
            foreach ($roleIds as $roleId) {
                $this->attach($roleId, $ids['Akses DTSEN']);
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

        // Role Admin-Bapperida hanya dihapus bila tidak lagi dipakai user manapun.
        $bapperidaId = DB::table('roles')->where('name', 'Admin-Bapperida')->value('id');
        if ($bapperidaId && ! DB::table('role_user')->where('role_id', $bapperidaId)->exists()) {
            DB::table('permission_role')->where('role_id', $bapperidaId)->delete();
            DB::table('roles')->where('id', $bapperidaId)->delete();
        }
    }
};
