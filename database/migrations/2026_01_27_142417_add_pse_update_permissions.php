<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            // User Permission
            [
                'name' => 'Akses Update Data PSE',
                'display_name' => 'Akses Permohonan Update Data PSE',
                'group' => 'User - Layanan Digital',
                'order' => 17,
                'description' => 'Akses untuk mengajukan permohonan update data Electronic System Category (ESC) dan Data Classification (DC)',
                'route_name' => 'user.pse-update.index',
            ],

            // Admin Permission
            [
                'name' => 'Kelola Permohonan PSE',
                'display_name' => 'Kelola Permohonan Update Data PSE',
                'group' => 'Admin - Layanan Digital',
                'order' => 8,
                'description' => 'Kelola permohonan update data PSE dari user (approve, revisi, reject)',
                'route_name' => 'admin.pse-update.index',
            ],
        ];

        foreach ($permissions as $perm) {
            $exists = DB::table('permissions')->where('name', $perm['name'])->exists();
            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $perm['name'],
                    'display_name' => $perm['display_name'],
                    'group' => $perm['group'],
                    'order' => $perm['order'],
                    'description' => $perm['description'],
                    'route_name' => $perm['route_name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Assign admin permission to Admin role automatically
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        if ($adminRole) {
            $adminPermission = DB::table('permissions')
                ->where('name', 'Kelola Permohonan PSE')
                ->first();

            if ($adminPermission) {
                $exists = DB::table('permission_role')
                    ->where('role_id', $adminRole->id)
                    ->where('permission_id', $adminPermission->id)
                    ->exists();

                if (!$exists) {
                    DB::table('permission_role')->insert([
                        'role_id' => $adminRole->id,
                        'permission_id' => $adminPermission->id,
                        'created_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete permissions
        DB::table('permissions')->whereIn('name', [
            'Akses Update Data PSE',
            'Kelola Permohonan PSE',
        ])->delete();
    }
};
