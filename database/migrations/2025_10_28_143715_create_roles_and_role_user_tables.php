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
        // Create roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Admin, User, Operator-Vidcon
            $table->string('display_name'); // For display purposes
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Create role_user pivot table
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();

            // Ensure unique combination
            $table->unique(['user_id', 'role_id']);
        });

        // Migrate existing role data from users table
        $this->migrateExistingRoles();
    }

    /**
     * Migrate existing single role to multi-role system
     */
    protected function migrateExistingRoles(): void
    {
        // Create default roles
        $roles = [
            ['name' => 'Admin', 'display_name' => 'Admin', 'description' => 'Administrator dengan akses penuh'],
            ['name' => 'User', 'display_name' => 'User', 'description' => 'User biasa'],
            ['name' => 'Operator-Vidcon', 'display_name' => 'Operator Video Konferensi', 'description' => 'Operator video konferensi'],
        ];

        foreach ($roles as $role) {
            \DB::table('roles')->insert(array_merge($role, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Migrate existing users to role_user pivot
        $users = \DB::table('users')->get();
        foreach ($users as $user) {
            if ($user->role) {
                $roleId = \DB::table('roles')->where('name', $user->role)->value('id');
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
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
