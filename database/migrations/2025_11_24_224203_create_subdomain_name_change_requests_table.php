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
        Schema::create('subdomain_name_change_requests', function (Blueprint $table) {
            $table->id();

            // User & Ticket
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_number', 20)->unique()->index();

            // Reference to existing web monitor
            $table->foreignId('web_monitor_id')->constrained()->cascadeOnDelete();

            // Name change details
            $table->string('old_subdomain_name', 63);
            $table->string('new_subdomain_name', 63)->index();
            $table->text('reason');

            // Additional acknowledgment
            $table->boolean('dns_propagation_acknowledged')->default(false);

            // Status workflow: pending -> approved -> completed / rejected
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending')->index();

            // Admin processing
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();

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
        Schema::dropIfExists('subdomain_name_change_requests');
    }
};
