<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengaturan jam & hari kerja global untuk perhitungan durasi SLA.
     * Tabel singleton (1 baris) — lihat App\Models\SlaWorkingHourSetting::current().
     */
    public function up(): void
    {
        Schema::create('sla_working_hour_settings', function (Blueprint $table) {
            $table->id();
            $table->time('jam_mulai')->default('08:00:00');
            $table->time('jam_selesai')->default('16:00:00');
            // Angka hari ISO-8601: 1=Senin ... 7=Minggu. Default Senin-Jumat.
            $table->json('hari_kerja')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        \DB::table('sla_working_hour_settings')->insert([
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '16:00:00',
            'hari_kerja' => json_encode([1, 2, 3, 4, 5]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_working_hour_settings');
    }
};
