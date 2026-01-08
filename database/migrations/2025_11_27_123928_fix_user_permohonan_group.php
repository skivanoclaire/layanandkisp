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
        // Fix user.permohonan to use proper group
        DB::table('permissions')
            ->where('name', 'user.permohonan')
            ->update([
                'group' => 'User - Layanan Digital',
                'display_name' => 'Permohonan Manual - Unggah Surat',
                'order' => 14
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->where('name', 'user.permohonan')
            ->update([
                'group' => 'user',
                'order' => 2
            ]);
    }
};
