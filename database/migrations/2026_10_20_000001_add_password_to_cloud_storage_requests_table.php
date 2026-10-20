<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Password akun cloud storage yang diberikan admin kepada pemohon.
     * Disimpan terenkripsi (cast 'encrypted'), jadi pakai TEXT karena ciphertext > 255 karakter.
     */
    public function up(): void
    {
        Schema::table('cloud_storage_requests', function (Blueprint $table) {
            $table->text('password')->nullable()->after('username');
        });
    }

    public function down(): void
    {
        Schema::table('cloud_storage_requests', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
