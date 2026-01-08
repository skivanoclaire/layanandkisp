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
        // Insert new permission for Check IP Publik
        DB::table('permissions')->insert([
            'name' => 'admin.web-monitor.check-ip-publik',
            'display_name' => 'Check IP Publik',
            'description' => 'Melihat daftar IP yang tersedia dan terpakai',
            'route_name' => 'admin.web-monitor.check-ip-publik',
            'group' => 'Admin - Master Data',
            'order' => 21, // After admin.web-monitor (order 20)
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Auto-assign permission to Admin role
        $permission = DB::table('permissions')
            ->where('name', 'admin.web-monitor.check-ip-publik')
            ->first();

        $adminRole = DB::table('roles')->where('name', 'Admin')->first();

        if ($permission && $adminRole) {
            DB::table('permission_role')->insert([
                'permission_id' => $permission->id,
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permission
        $permission = DB::table('permissions')
            ->where('name', 'admin.web-monitor.check-ip-publik')
            ->first();

        if ($permission) {
            // Remove from permission_role pivot table
            DB::table('permission_role')
                ->where('permission_id', $permission->id)
                ->delete();

            // Remove permission itself
            DB::table('permissions')
                ->where('id', $permission->id)
                ->delete();
        }
    }
};
