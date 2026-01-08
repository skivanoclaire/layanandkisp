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
            $table->string('requester_name')->nullable()->after('nip');
            $table->string('requester_nip', 18)->nullable()->after('requester_name');
            $table->string('requester_nik', 16)->nullable()->after('requester_nip');
            $table->string('requester_email')->nullable()->after('requester_nik');
            $table->string('requester_phone', 20)->nullable()->after('requester_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'requester_name',
                'requester_nip',
                'requester_nik',
                'requester_email',
                'requester_phone'
            ]);
        });
    }
};
