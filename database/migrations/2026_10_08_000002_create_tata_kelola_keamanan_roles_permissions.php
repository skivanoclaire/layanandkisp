<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Role dan permission modul Tata Kelola Keamanan Informasi.
     * - Pengelola: mengelola seluruh tahapan evaluasi (juga diberikan ke role Admin).
     * - Responden: mengisi kuesioner design factors/aktivitas pada evaluasi tempat ia didaftarkan.
     * - Pakar: menilai butir pemetaan pada evaluasi tempat ia didaftarkan.
     * Role responden dan pakar dipasang otomatis saat akun didaftarkan pada evaluasi,
     * dan dapat juga diatur manual di Kelola Pengguna.
     */
    private array $roles = [
        'Pengelola Tata Kelola Keamanan' => 'Mengelola evaluasi tata kelola keamanan informasi (COBIT 2019 & ISO/IEC 27001:2022)',
        'Responden Tata Kelola Keamanan' => 'Mengisi kuesioner evaluasi tata kelola keamanan informasi',
        'Pakar Tata Kelola Keamanan' => 'Memvalidasi pemetaan COBIT 2019 – ISO/IEC 27001:2022 – regulasi',
    ];

    private array $permissions = [
        [
            'name' => 'Kelola Tata Kelola Keamanan',
            'display_name' => 'Kelola Tata Kelola Keamanan Informasi',
            'group' => 'Admin - Layanan Digital',
            'order' => 98,
            'description' => 'Mengelola evaluasi: responden, design factors, pemetaan COBIT–ISO–regulasi, validasi pakar, asesmen kapabilitas, kesenjangan, dan laporan',
            'route_name' => 'admin.tata-kelola-keamanan.index',
            'roles' => ['Admin', 'Pengelola Tata Kelola Keamanan'],
        ],
        [
            'name' => 'Isi Kuesioner Tata Kelola Keamanan',
            'display_name' => 'Isi Kuesioner Tata Kelola Keamanan Informasi',
            'group' => 'User - Layanan Digital',
            'order' => 98,
            'description' => 'Mengisi kuesioner design factors dan kuesioner aktivitas bagi responden yang didaftarkan pengelola',
            'route_name' => 'tata-kelola-keamanan.responden.index',
            'roles' => ['Responden Tata Kelola Keamanan'],
        ],
        [
            'name' => 'Validasi Pakar Tata Kelola Keamanan',
            'display_name' => 'Validasi Pakar Tata Kelola Keamanan Informasi',
            'group' => 'User - Layanan Digital',
            'order' => 99,
            'description' => 'Menilai butir pemetaan COBIT 2019 – ISO/IEC 27001:2022 – regulasi bagi pakar yang didaftarkan pengelola',
            'route_name' => 'tata-kelola-keamanan.pakar.index',
            'roles' => ['Pakar Tata Kelola Keamanan'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->roles as $name => $description) {
            if (! DB::table('roles')->where('name', $name)->exists()) {
                DB::table('roles')->insert([
                    'name' => $name,
                    'display_name' => $name,
                    'description' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

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
    }

    public function down(): void
    {
        $permIds = DB::table('permissions')->whereIn('name', array_column($this->permissions, 'name'))->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('id', $permIds)->delete();

        $roleIds = DB::table('roles')->whereIn('name', array_keys($this->roles))->pluck('id');
        DB::table('permission_role')->whereIn('role_id', $roleIds)->delete();
        DB::table('role_user')->whereIn('role_id', $roleIds)->delete();
        DB::table('roles')->whereIn('id', $roleIds)->delete();
    }
};
