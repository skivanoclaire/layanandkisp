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
        Schema::create('subdomain_data_update_requests', function (Blueprint $table) {
            $table->id();

            // User & Ticket
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_number', 20)->unique()->index();

            // Reference to existing web monitor (subdomain target)
            $table->foreignId('web_monitor_id')->constrained()->cascadeOnDelete();

            // Proposed data (18 editable fields) & snapshot of original at submission
            $table->json('proposed_data');
            $table->json('original_data')->nullable();
            $table->text('reason')->nullable();

            // Status workflow: pending -> disetujui / revisi / ditolak
            $table->enum('status', ['pending', 'revisi', 'disetujui', 'ditolak'])->default('pending')->index();

            // Admin processing
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('applied_at')->nullable();

            $table->timestamps();

            // Additional indexes
            $table->index('user_id');
            $table->index('web_monitor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subdomain_data_update_requests');
    }
};
