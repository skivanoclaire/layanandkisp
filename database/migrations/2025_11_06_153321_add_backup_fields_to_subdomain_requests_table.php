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
        Schema::table('subdomain_requests', function (Blueprint $table) {
            // Add new backup fields
            $table->string('backup_frequency', 50)->nullable()->after('frontend_tech');
            $table->string('backup_retention', 50)->nullable()->after('backup_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subdomain_requests', function (Blueprint $table) {
            $table->dropColumn(['backup_frequency', 'backup_retention']);
        });
    }
};
