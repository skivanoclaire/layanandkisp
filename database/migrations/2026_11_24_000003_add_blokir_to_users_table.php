<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Blokir akun: akun yang diblokir tidak bisa login (lokal maupun SSO) dan dikeluarkan dari sesi aktif. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('is_verified');
            $table->unsignedBigInteger('blocked_by')->nullable()->after('blocked_at');
            $table->string('blocked_reason', 255)->nullable()->after('blocked_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['blocked_at', 'blocked_by', 'blocked_reason']);
        });
    }
};
