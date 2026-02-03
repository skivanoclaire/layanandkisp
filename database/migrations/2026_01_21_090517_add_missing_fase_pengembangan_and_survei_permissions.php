<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            [
                'name' => 'user.fase-pengembangan',
                'display_name' => 'Akses Fase Pengembangan',
                'group' => 'User - Layanan Digital',
                'order' => 17,
            ],
            [
                'name' => 'Akses Survei Kepuasan',
                'display_name' => 'Akses Survei Kepuasan',
                'group' => 'User - Layanan Digital',
                'order' => 18,
            ],
            [
                'name' => 'Kelola Survei Kepuasan',
                'display_name' => 'Kelola Survei Kepuasan',
                'group' => 'Admin - Layanan Digital',
                'order' => 18,
            ],
            [
                'name' => 'admin.fase-pengembangan.view',
                'display_name' => 'Lihat Fase Pengembangan (Admin)',
                'group' => 'Admin - Layanan Digital',
                'order' => 19,
            ],
        ];

        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')->where('name', $permission['name'])->exists();

            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $permission['name'],
                    'display_name' => $permission['display_name'],
                    'group' => $permission['group'],
                    'order' => $permission['order'],
                    'description' => null,
                    'route_name' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'user.fase-pengembangan',
            'Akses Survei Kepuasan',
            'Kelola Survei Kepuasan',
            'admin.fase-pengembangan.view',
        ])->delete();
    }
};
