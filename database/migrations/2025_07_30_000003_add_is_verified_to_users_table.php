<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('email_verified_at')->index();
            $table->timestamp('verified_at')->nullable()->after('is_verified');
            $table->string('verified_by')->nullable()->after('verified_at'); // simpan nama/email admin
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_verified','verified_at','verified_by']);
        });
    }
};
