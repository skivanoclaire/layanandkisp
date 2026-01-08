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
            // Add new fields for "Lainnya" options
            $table->string('other_programming_language', 100)->nullable()->after('programming_language_id');
            $table->string('other_framework', 100)->nullable()->after('framework_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subdomain_requests', function (Blueprint $table) {
            $table->dropColumn(['other_programming_language', 'other_framework']);
        });
    }
};
