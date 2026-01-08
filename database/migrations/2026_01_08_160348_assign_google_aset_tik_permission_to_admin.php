<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the Admin role
        $adminRole = Role::where('name', 'Admin')->first();

        // Get the newly created permission
        $permission = Permission::where('name', 'admin.google-aset-tik')->first();

        // Assign permission to Admin role if both exist
        if ($adminRole && $permission) {
            // Check if permission is not already assigned
            $exists = DB::table('permission_role')
                ->where('role_id', $adminRole->id)
                ->where('permission_id', $permission->id)
                ->exists();

            if (!$exists) {
                DB::table('permission_role')->insert([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permission->id,
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
        // Get the Admin role
        $adminRole = Role::where('name', 'Admin')->first();

        // Get the permission
        $permission = Permission::where('name', 'admin.google-aset-tik')->first();

        // Remove permission from Admin role if both exist
        if ($adminRole && $permission) {
            DB::table('permission_role')
                ->where('role_id', $adminRole->id)
                ->where('permission_id', $permission->id)
                ->delete();
        }
    }
};
