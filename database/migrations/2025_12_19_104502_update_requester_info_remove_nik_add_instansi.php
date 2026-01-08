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
        Schema::table('email_accounts', function (Blueprint $table) {
            // Drop NIK column
            $table->dropColumn('requester_nik');

            // Add Instansi column after requester_nip
            $table->string('requester_instansi')->nullable()->after('requester_nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_accounts', function (Blueprint $table) {
            // Remove Instansi column
            $table->dropColumn('requester_instansi');

            // Add back NIK column
            $table->string('requester_nik', 16)->nullable()->after('requester_nip');
        });
    }
};
