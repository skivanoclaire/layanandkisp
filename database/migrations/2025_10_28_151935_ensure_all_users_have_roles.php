<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan semua user existing punya role di pivot table
        $users = \DB::table('users')->get();

        $adminRole = \DB::table('roles')->where('name', 'Admin')->first();
        $userRole = \DB::table('roles')->where('name', 'User')->first();
        $operatorRole = \DB::table('roles')->where('name', 'Operator-Vidcon')->first();

        foreach ($users as $user) {
            // Cek apakah user sudah punya role di pivot table
            $hasRole = \DB::table('role_user')->where('user_id', $user->id)->exists();

            if (!$hasRole) {
                // Map old role column to new role system
                $roleId = null;

                if ($user->role === 'admin') {
                    $roleId = $adminRole->id;
                } elseif ($user->role === 'user') {
                    $roleId = $userRole->id;
                } elseif ($user->role === 'operator-vidcon' || $user->role === 'admin-vidcon') {
                    $roleId = $operatorRole->id;
                }

                // Insert ke pivot table jika role ditemukan
                if ($roleId) {
                    \DB::table('role_user')->insert([
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                        'created_at' => now(),
                        'updated_at' => now(),
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
        // Tidak perlu rollback karena ini data migration
    }
};
