<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tik_assets', function (Blueprint $table) {
            $table->string('qr_text')->nullable()->after('code');     // payload yang di-encode
            $table->string('qr_path')->nullable()->after('qr_text');  // path PNG di storage
        });
    }
    public function down(): void {
        Schema::table('tik_assets', function (Blueprint $table) {
            $table->dropColumn(['qr_text', 'qr_path']);
        });
    }
};
