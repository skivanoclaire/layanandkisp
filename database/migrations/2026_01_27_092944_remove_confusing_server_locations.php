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
        // Delete confusing server locations
        // "Kalimantan Utara" and "Tanjung Selor" are removed because "Bulungan" already exists
        DB::table('server_locations')->whereIn('name', [
            'Kalimantan Utara',
            'Tanjung Selor'
        ])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the deleted server locations
        DB::table('server_locations')->insert([
            ['name' => 'Kalimantan Utara', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tanjung Selor', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
