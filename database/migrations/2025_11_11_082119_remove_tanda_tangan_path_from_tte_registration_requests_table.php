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
        Schema::table('tte_registration_requests', function (Blueprint $table) {
            $table->dropColumn('tanda_tangan_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tte_registration_requests', function (Blueprint $table) {
            $table->string('tanda_tangan_path')->nullable()->after('no_hp');
        });
    }
};
