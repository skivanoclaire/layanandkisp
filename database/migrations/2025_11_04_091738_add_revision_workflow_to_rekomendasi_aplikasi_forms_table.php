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
        // First, update the enum to add 'perlu_revisi' status
        DB::statement("ALTER TABLE rekomendasi_aplikasi_forms MODIFY COLUMN status ENUM('draft', 'diajukan', 'diproses', 'perlu_revisi', 'disetujui', 'ditolak') NOT NULL DEFAULT 'draft'");

        // Then add new columns for revision tracking
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            $table->text('revision_notes')->nullable()->after('admin_feedback');
            $table->foreignId('revision_requested_by')->nullable()->constrained('users')->after('revision_notes');
            $table->timestamp('revision_requested_at')->nullable()->after('revision_requested_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            $table->dropForeign(['revision_requested_by']);
            $table->dropColumn(['revision_notes', 'revision_requested_by', 'revision_requested_at']);
        });

        // Restore original enum without 'perlu_revisi'
        DB::statement("ALTER TABLE rekomendasi_aplikasi_forms MODIFY COLUMN status ENUM('draft', 'diajukan', 'diproses', 'disetujui', 'ditolak') NOT NULL DEFAULT 'draft'");
    }
};
