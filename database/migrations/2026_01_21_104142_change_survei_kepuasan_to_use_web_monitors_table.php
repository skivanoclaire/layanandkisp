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
        Schema::table('survei_kepuasan_layanan', function (Blueprint $table) {
            // Drop old foreign key and column
            $table->dropForeign(['subdomain_request_id']);
            $table->dropUnique('unique_user_subdomain_survey');
            $table->dropColumn('subdomain_request_id');

            // Add new foreign key to web_monitors
            $table->foreignId('web_monitor_id')->after('user_id')->constrained('web_monitors')->onDelete('cascade');

            // Re-create unique constraint with new column
            $table->unique(['user_id', 'web_monitor_id'], 'unique_user_webmonitor_survey');

            // Add index
            $table->index(['web_monitor_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survei_kepuasan_layanan', function (Blueprint $table) {
            // Drop new foreign key and unique constraint
            $table->dropForeign(['web_monitor_id']);
            $table->dropUnique('unique_user_webmonitor_survey');
            $table->dropIndex(['web_monitor_id', 'created_at']);
            $table->dropColumn('web_monitor_id');

            // Restore old foreign key
            $table->foreignId('subdomain_request_id')->after('user_id')->constrained('subdomain_requests')->onDelete('cascade');
            $table->unique(['user_id', 'subdomain_request_id'], 'unique_user_subdomain_survey');
            $table->index(['subdomain_request_id', 'created_at']);
        });
    }
};
