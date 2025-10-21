<?php

// database/migrations/XXXX_add_code_to_tik_categories_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tik_categories', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->unique()->after('name');
        });
    }
    public function down(): void {
        Schema::table('tik_categories', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
