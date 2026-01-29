<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column doesn't exist before adding
        if (!Schema::hasColumn('rekomendasi_status_kementerian', 'rekomendasi_aplikasi_form_id')) {
            Schema::table('rekomendasi_status_kementerian', function (Blueprint $table) {
                $table->unsignedBigInteger('rekomendasi_aplikasi_form_id')
                    ->nullable()
                    ->after('id');
            });
        }

        // Check if foreign key exists using raw query
        $foreignKeyExists = DB::select(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
             AND TABLE_NAME = 'rekomendasi_status_kementerian'
             AND CONSTRAINT_NAME = 'rek_status_kem_form_fk'
             AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
        );

        if (empty($foreignKeyExists)) {
            Schema::table('rekomendasi_status_kementerian', function (Blueprint $table) {
                $table->foreign('rekomendasi_aplikasi_form_id', 'rek_status_kem_form_fk')
                    ->references('id')
                    ->on('rekomendasi_aplikasi_forms')
                    ->onDelete('cascade');
            });
        }

        // Check if index exists using raw query
        $indexExists = DB::select(
            "SELECT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'rekomendasi_status_kementerian'
             AND INDEX_NAME = 'rek_status_kem_form_idx'"
        );

        if (empty($indexExists)) {
            Schema::table('rekomendasi_status_kementerian', function (Blueprint $table) {
                $table->index('rekomendasi_aplikasi_form_id', 'rek_status_kem_form_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_status_kementerian', function (Blueprint $table) {
            $table->dropForeign('rek_status_kem_form_fk');
            $table->dropIndex('rek_status_kem_form_idx');
            $table->dropColumn('rekomendasi_aplikasi_form_id');
        });
    }
};
