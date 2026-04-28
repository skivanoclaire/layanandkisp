<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vpn_registration_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vpn_registration_id')->constrained()->cascadeOnDelete();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action');
            $t->string('old_value')->nullable();
            $t->string('new_value')->nullable();
            $t->text('note')->nullable();
            $t->timestamps();

            $t->index('vpn_registration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vpn_registration_logs');
    }
};
