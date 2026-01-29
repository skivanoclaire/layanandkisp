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
        // Add to subdomain_requests table
        Schema::table('subdomain_requests', function (Blueprint $table) {
            $table->string('dc_data_name')->nullable()->after('esc_filled_at');
            $table->text('dc_data_attributes')->nullable()->after('dc_data_name');
            $table->enum('dc_confidentiality', ['Rendah', 'Sedang', 'Tinggi'])->nullable()->after('dc_data_attributes');
            $table->enum('dc_integrity', ['Rendah', 'Sedang', 'Tinggi'])->nullable()->after('dc_confidentiality');
            $table->enum('dc_availability', ['Rendah', 'Sedang', 'Tinggi'])->nullable()->after('dc_integrity');
            $table->integer('dc_total_score')->nullable()->after('dc_availability');
            $table->timestamp('dc_filled_at')->nullable()->after('dc_total_score');
        });

        // Add to web_monitors table
        Schema::table('web_monitors', function (Blueprint $table) {
            $table->string('dc_data_name')->nullable()->after('esc_updated_by');
            $table->text('dc_data_attributes')->nullable()->after('dc_data_name');
            $table->enum('dc_confidentiality', ['Rendah', 'Sedang', 'Tinggi'])->nullable()->after('dc_data_attributes');
            $table->enum('dc_integrity', ['Rendah', 'Sedang', 'Tinggi'])->nullable()->after('dc_confidentiality');
            $table->enum('dc_availability', ['Rendah', 'Sedang', 'Tinggi'])->nullable()->after('dc_integrity');
            $table->integer('dc_total_score')->nullable()->after('dc_availability');
            $table->timestamp('dc_filled_at')->nullable()->after('dc_total_score');
            $table->foreignId('dc_updated_by')->nullable()->after('dc_filled_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subdomain_requests', function (Blueprint $table) {
            $table->dropColumn([
                'dc_data_name',
                'dc_data_attributes',
                'dc_confidentiality',
                'dc_integrity',
                'dc_availability',
                'dc_total_score',
                'dc_filled_at',
            ]);
        });

        Schema::table('web_monitors', function (Blueprint $table) {
            $table->dropForeign(['dc_updated_by']);
            $table->dropColumn([
                'dc_data_name',
                'dc_data_attributes',
                'dc_confidentiality',
                'dc_integrity',
                'dc_availability',
                'dc_total_score',
                'dc_filled_at',
                'dc_updated_by',
            ]);
        });
    }
};
