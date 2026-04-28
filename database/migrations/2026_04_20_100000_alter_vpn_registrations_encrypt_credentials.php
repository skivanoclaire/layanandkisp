<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vpn_registrations', function (Blueprint $table) {
            $table->text('username_vpn')->nullable()->change();
            $table->text('password_vpn')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vpn_registrations', function (Blueprint $table) {
            $table->string('username_vpn')->nullable()->change();
            $table->string('password_vpn')->nullable()->change();
        });
    }
};
