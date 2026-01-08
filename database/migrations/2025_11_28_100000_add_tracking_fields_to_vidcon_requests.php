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
        Schema::table('vidcon_requests', function (Blueprint $table) {
            // Track when meeting info was last updated
            $table->timestamp('last_info_updated_at')->nullable()->after('processing_at');

            // Count how many times info has been updated
            $table->integer('info_update_count')->default(0)->after('last_info_updated_at');

            // Track who last updated the info
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->onDelete('set null')->after('info_update_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vidcon_requests', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by']);
            $table->dropColumn(['last_info_updated_at', 'info_update_count', 'last_updated_by']);
        });
    }
};
