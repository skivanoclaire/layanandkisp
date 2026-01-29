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
        Schema::table('subdomain_requests', function (Blueprint $table) {
            // Electronic System Category Questionnaire
            $table->json('esc_answers')->nullable()->after('consent_true')->comment('ESC questionnaire answers (10 questions)');
            $table->integer('esc_total_score')->nullable()->after('esc_answers')->comment('Total ESC score (0-50)');
            $table->string('esc_category', 50)->nullable()->after('esc_total_score')->comment('ESC category: Strategis/Tinggi/Rendah');
            $table->string('esc_document_path')->nullable()->after('esc_category')->comment('Path to supporting document');
            $table->timestamp('esc_filled_at')->nullable()->after('esc_document_path')->comment('When ESC was completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subdomain_requests', function (Blueprint $table) {
            $table->dropColumn([
                'esc_answers',
                'esc_total_score',
                'esc_category',
                'esc_document_path',
                'esc_filled_at'
            ]);
        });
    }
};
