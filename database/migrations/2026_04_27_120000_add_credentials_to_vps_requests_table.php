<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vps_requests', function (Blueprint $table) {
            $table->text('username_vps')->nullable()->after('ip_public');
            $table->text('password_vps')->nullable()->after('username_vps');
            $table->string('os_vps')->nullable()->after('password_vps');
        });
    }

    public function down(): void
    {
        Schema::table('vps_requests', function (Blueprint $table) {
            $table->dropColumn(['username_vps', 'password_vps', 'os_vps']);
        });
    }
};
