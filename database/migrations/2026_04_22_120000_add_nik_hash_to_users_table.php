<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // SHA-256 hex = 64 chars. Nullable supaya migration aman bila ada user
            // tanpa NIK. Index biasa dulu (bukan unique) karena masih ada data
            // duplikat lama yang harus di-merge manual sebelum unique aman dipasang.
            $table->string('nik_hash', 64)->nullable()->after('nik');
            $table->index('nik_hash');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['nik_hash']);
            $table->dropColumn('nik_hash');
        });
    }
};
