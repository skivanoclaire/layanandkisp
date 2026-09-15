<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master rilis DTSEN. Rilis baru menjadi pemicu notifikasi "data lama wajib
     * dimusnahkan" kepada seluruh OPD pemegang data (Bab VII Juknis).
     */
    public function up(): void
    {
        Schema::create('dtsen_releases', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_rilis', 50)->unique();
            $table->date('tanggal_rilis');
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('notified_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtsen_releases');
    }
};
