<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daftar libur nasional & cuti bersama yang dikecualikan dari perhitungan
     * durasi kerja SLA. Diisi manual oleh admin (tidak ada sumber kalender otomatis).
     */
    public function up(): void
    {
        Schema::create('sla_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_holidays');
    }
};
