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
            $table->dropColumn('purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subdomain_requests', function (Blueprint $table) {
            $table->string('purpose', 500)->after('jenis_website');
        });
    }
};
