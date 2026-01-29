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
        Schema::create('pse_update_requests', function (Blueprint $table) {
            // Primary & Tracking
            $table->id();
            $table->string('ticket_no', 25)->unique()->index();

            // User & WebMonitor Relations
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('web_monitor_id')->constrained()->cascadeOnDelete();

            // Update Scope - Apa yang ingin diupdate
            $table->boolean('update_esc')->default(false);
            $table->boolean('update_dc')->default(false);

            // ESC Data (New Values) - NULL jika tidak diupdate
            $table->json('esc_answers')->nullable();
            $table->integer('esc_total_score')->nullable();
            $table->string('esc_category', 50)->nullable(); // Rendah, Tinggi, Strategis
            $table->string('esc_document_path')->nullable();

            // DC Data (New Values) - NULL jika tidak diupdate
            $table->string('dc_data_name')->nullable();
            $table->text('dc_data_attributes')->nullable();
            $table->enum('dc_confidentiality', ['Rendah', 'Sedang', 'Tinggi'])->nullable();
            $table->enum('dc_integrity', ['Rendah', 'Sedang', 'Tinggi'])->nullable();
            $table->enum('dc_availability', ['Rendah', 'Sedang', 'Tinggi'])->nullable();
            $table->integer('dc_total_score')->nullable();

            // Status & Workflow (Pattern dari RekomendasiAplikasi)
            $table->enum('status', [
                'draft',
                'diajukan',
                'diproses',
                'perlu_revisi',
                'disetujui',
                'ditolak'
            ])->default('draft')->index();

            // Timestamps for Workflow
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Admin Action Tracking
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();

            // Revision Mechanism (Pattern dari RekomendasiAplikasi)
            $table->text('revision_notes')->nullable();
            $table->foreignId('revision_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revision_requested_at')->nullable();

            // Admin Notes & Rejection
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Timestamps
            $table->timestamps();

            // Additional Indexes
            $table->index('submitted_at');
            $table->index(['web_monitor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pse_update_requests');
    }
};
