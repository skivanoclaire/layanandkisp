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
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('domain');
            $table->string('user');
            $table->bigInteger('disk_used')->nullable(); // in bytes
            $table->bigInteger('disk_quota')->nullable(); // in bytes
            $table->string('diskused_readable')->nullable(); // human readable
            $table->string('diskquota_readable')->nullable(); // human readable
            $table->integer('suspended')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('domain');
            $table->index('suspended');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_accounts');
    }
};
