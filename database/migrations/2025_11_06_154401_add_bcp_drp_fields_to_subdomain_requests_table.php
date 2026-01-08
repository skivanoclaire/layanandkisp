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
            // Add BCP/DRP fields
            $table->string('has_bcp', 50)->nullable()->after('backup_retention');
            $table->string('has_drp', 50)->nullable()->after('has_bcp');
            $table->string('rto', 50)->nullable()->after('has_drp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subdomain_requests', function (Blueprint $table) {
            $table->dropColumn(['has_bcp', 'has_drp', 'rto']);
        });
    }
};
