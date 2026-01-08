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
        Schema::create('google_aset_tik_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('register_type', ['ham', 'sam', 'kategori', 'all'])->comment('Tipe data yang disync');
            $table->enum('sync_type', ['import', 'export'])->comment('Arah sync');
            $table->enum('status', ['running', 'success', 'failed', 'partial'])->default('running');

            // Statistik
            $table->integer('total_rows')->default(0)->comment('Total baris di spreadsheet');
            $table->integer('rows_created')->default(0);
            $table->integer('rows_updated')->default(0);
            $table->integer('rows_failed')->default(0);
            $table->integer('rows_skipped')->default(0)->comment('Baris kosong/invalid');

            // Error tracking
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable()->comment('Array of errors dengan row number');

            // Timing
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();

            // Audit
            $table->foreignId('user_id')->nullable()->constrained()->comment('User yang trigger sync');
            $table->boolean('is_manual')->default(true)->comment('Manual trigger atau auto schedule');

            $table->timestamps();

            $table->index('register_type');
            $table->index('sync_type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_aset_tik_sync_logs');
    }
};
