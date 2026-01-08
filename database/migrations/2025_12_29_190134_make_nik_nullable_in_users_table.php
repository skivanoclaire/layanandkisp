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
        Schema::table('users', function (Blueprint $table) {
            // Add flag to differentiate SSO users from manual registration users
            $table->boolean('is_sso_user')->default(false)->after('is_verified');

            // Make NIK nullable for SSO users
            $table->string('nik')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_sso_user');
            $table->string('nik')->nullable(false)->change();
        });
    }
};
