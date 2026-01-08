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
        Schema::table('tik_borrowings', function (Blueprint $table) {
            $table->foreignId('closed_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->text('closed_reason')->nullable()->after('closed_by');
            $table->timestamp('closed_at')->nullable()->after('closed_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tik_borrowings', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['closed_by', 'closed_reason', 'closed_at']);
        });
    }
};
