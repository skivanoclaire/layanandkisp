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
        Schema::table('subdomain_data_update_requests', function (Blueprint $table) {
            $table->string('file_berita_acara')->nullable()->after('applied_at');
            $table->timestamp('berita_acara_uploaded_at')->nullable()->after('file_berita_acara');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subdomain_data_update_requests', function (Blueprint $table) {
            $table->dropColumn(['file_berita_acara', 'berita_acara_uploaded_at']);
        });
    }
};
