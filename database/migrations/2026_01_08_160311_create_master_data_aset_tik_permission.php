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
        // Add permission for Master Data Aset TIK (Google Sheets Integration)
        DB::table('permissions')->insert([
            'name' => 'admin.google-aset-tik',
            'display_name' => 'Master Data Aset TIK',
            'group' => 'Admin - Master Data',
            'order' => 5,
            'description' => 'Akses penuh untuk mengelola Master Data Aset TIK dari Google Sheets (Hardware, Software, Sinkronisasi)',
            'route_name' => 'admin.google-aset-tik.dashboard',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the permission
        DB::table('permissions')->where('name', 'admin.google-aset-tik')->delete();
    }
};
