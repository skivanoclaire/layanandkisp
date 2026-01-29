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
        Schema::table('web_monitors', function (Blueprint $table) {
            // Electronic System Category (copied from subdomain_request or filled by admin)
            $table->json('esc_answers')->nullable()->after('server_location_id')->comment('ESC questionnaire answers (10 questions)');
            $table->integer('esc_total_score')->nullable()->after('esc_answers')->comment('Total ESC score (0-50)');
            $table->string('esc_category', 50)->nullable()->after('esc_total_score')->comment('ESC category: Strategis/Tinggi/Rendah');
            $table->string('esc_document_path')->nullable()->after('esc_category')->comment('Path to supporting document');
            $table->timestamp('esc_filled_at')->nullable()->after('esc_document_path')->comment('When ESC was completed');
            $table->foreignId('esc_updated_by')->nullable()->constrained('users')->nullOnDelete()->after('esc_filled_at')->comment('User who last updated ESC');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_monitors', function (Blueprint $table) {
            $table->dropForeign(['esc_updated_by']);
            $table->dropColumn([
                'esc_answers',
                'esc_total_score',
                'esc_category',
                'esc_document_path',
                'esc_filled_at',
                'esc_updated_by'
            ]);
        });
    }
};
