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
        // Tabel permissions untuk menu/fitur
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // misal: 'admin.dashboard', 'admin.users'
            $table->string('display_name'); // misal: 'Dashboard Admin'
            $table->string('description')->nullable();
            $table->string('route_name')->nullable(); // nama route Laravel
            $table->string('group')->nullable(); // grup menu: 'admin', 'user', 'operator'
            $table->integer('order')->default(0); // urutan tampilan
            $table->timestamps();
        });

        // Pivot table: permission_role (many-to-many)
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['permission_id', 'role_id']);
        });

        // Seed default permissions berdasarkan menu existing
        $this->seedDefaultPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
    }

    /**
     * Seed default permissions
     */
    private function seedDefaultPermissions(): void
    {
        $permissions = [
            // Admin permissions
            ['name' => 'admin.dashboard', 'display_name' => 'Dashboard Admin', 'route_name' => 'admin.dashboard', 'group' => 'admin', 'order' => 1],
            ['name' => 'admin.permohonan', 'display_name' => 'Permohonan Manual', 'route_name' => 'admin.permohonan', 'group' => 'admin', 'order' => 2],
            ['name' => 'admin.email', 'display_name' => 'Permohonan Email', 'route_name' => 'admin.email.index', 'group' => 'admin', 'order' => 3],
            ['name' => 'admin.web-monitor', 'display_name' => 'Web Monitor', 'route_name' => 'admin.web-monitor.index', 'group' => 'admin', 'order' => 4],
            ['name' => 'admin.unit-kerja', 'display_name' => 'Master Data Unit Kerja', 'route_name' => 'admin.unit-kerja.index', 'group' => 'admin', 'order' => 5],
            ['name' => 'admin.tik.assets', 'display_name' => 'Inventaris Digital', 'route_name' => 'admin.tik.assets.index', 'group' => 'admin', 'order' => 6],
            ['name' => 'admin.tik.borrow', 'display_name' => 'Laporan Peminjaman', 'route_name' => 'admin.tik.borrow.index', 'group' => 'admin', 'order' => 7],
            ['name' => 'admin.schedule', 'display_name' => 'Jadwal Video Konferensi', 'route_name' => 'op.tik.schedule.index', 'group' => 'admin', 'order' => 8],
            ['name' => 'admin.statistic', 'display_name' => 'Statistik Video Konferensi', 'route_name' => 'op.tik.statistic.index', 'group' => 'admin', 'order' => 9],
            ['name' => 'admin.users', 'display_name' => 'User Management', 'route_name' => 'admin.users', 'group' => 'admin', 'order' => 10],
            ['name' => 'admin.simpeg', 'display_name' => 'Cek via SIMPEG', 'route_name' => 'admin.simpeg.index', 'group' => 'admin', 'order' => 11],

            // User permissions
            ['name' => 'user.dashboard', 'display_name' => 'Dashboard Pengguna', 'route_name' => 'user.dashboard', 'group' => 'user', 'order' => 1],
            ['name' => 'user.permohonan', 'display_name' => 'Permohonan User', 'route_name' => 'user.permohonan', 'group' => 'user', 'order' => 2],
            ['name' => 'user.profile', 'display_name' => 'Profile Pengguna', 'route_name' => 'profile.edit', 'group' => 'user', 'order' => 3],

            // Operator Vidcon permissions
            ['name' => 'op.tik.borrow.index', 'display_name' => 'Peminjaman Saya', 'route_name' => 'op.tik.borrow.index', 'group' => 'operator', 'order' => 1],
            ['name' => 'op.tik.borrow.create', 'display_name' => 'Buat Peminjaman', 'route_name' => 'op.tik.borrow.create', 'group' => 'operator', 'order' => 2],
            ['name' => 'op.tik.schedule', 'display_name' => 'Jadwal Vidcon', 'route_name' => 'op.tik.schedule.index', 'group' => 'operator', 'order' => 3],
        ];

        foreach ($permissions as $perm) {
            \DB::table('permissions')->insert([
                'name' => $perm['name'],
                'display_name' => $perm['display_name'],
                'route_name' => $perm['route_name'],
                'group' => $perm['group'],
                'order' => $perm['order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Assign default permissions ke roles
        $adminRole = \DB::table('roles')->where('name', 'Admin')->first();
        $userRole = \DB::table('roles')->where('name', 'User')->first();
        $operatorRole = \DB::table('roles')->where('name', 'Operator-Vidcon')->first();

        // Admin gets all admin permissions
        $adminPermissions = \DB::table('permissions')->where('group', 'admin')->pluck('id');
        foreach ($adminPermissions as $permId) {
            \DB::table('permission_role')->insert([
                'permission_id' => $permId,
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // User gets user permissions
        $userPermissions = \DB::table('permissions')->where('group', 'user')->pluck('id');
        foreach ($userPermissions as $permId) {
            \DB::table('permission_role')->insert([
                'permission_id' => $permId,
                'role_id' => $userRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Operator-Vidcon gets operator permissions
        $operatorPermissions = \DB::table('permissions')->where('group', 'operator')->pluck('id');
        foreach ($operatorPermissions as $permId) {
            \DB::table('permission_role')->insert([
                'permission_id' => $permId,
                'role_id' => $operatorRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
