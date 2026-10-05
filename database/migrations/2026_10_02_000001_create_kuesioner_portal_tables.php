<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kuesioner Kualitas Layanan Portal (E-GovQual + IPA + Kano).
     * Terpisah dari survei_kepuasan_layanan: satu respons per akun per periode,
     * jawaban per atribut disimpan baris demi baris agar mudah diekspor ke analisis.
     */
    public function up(): void
    {
        Schema::create('kuesioner_portal_periodes', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('dibuka_at')->nullable();
            $table->timestamp('ditutup_at')->nullable();
            $table->timestamps();
        });

        Schema::create('kuesioner_portal_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('kuesioner_portal_periodes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Kode acak pengganti identitas akun pada data analisis
            $table->string('kode_responden', 12)->unique();

            // persetuju | selesai | menolak  (string, bukan ENUM — divalidasi di aplikasi)
            $table->string('status', 20)->default('persetuju');
            $table->timestamp('persetujuan_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('durasi_detik')->nullable();

            // Bagian A. Profil responden
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->string('status_kepegawaian', 20)->nullable();
            $table->string('peran', 20)->nullable();
            $table->string('lama_penggunaan', 20)->nullable();
            $table->string('frekuensi', 20)->nullable();
            $table->json('layanan_diajukan')->nullable();
            $table->string('layanan_lainnya')->nullable();
            $table->boolean('pernah_hubungi_petugas')->nullable();

            // Bagian E. Pertanyaan terbuka
            $table->text('kelebihan')->nullable();
            $table->text('kekurangan')->nullable();
            $table->text('saran')->nullable();

            // Eksklusi manual oleh peneliti (selain aturan otomatis pra-pemrosesan)
            $table->boolean('dikecualikan')->default(false);
            $table->string('alasan_dikecualikan')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['periode_id', 'user_id']);
            $table->index(['periode_id', 'status']);
        });

        Schema::create('kuesioner_portal_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('kuesioner_portal_responses')->cascadeOnDelete();
            $table->string('kode', 8);              // EF1 … CS4, KPS1 … KPS3
            $table->unsignedTinyInteger('kepentingan')->nullable();    // Bagian B, 1–5
            $table->unsignedTinyInteger('kinerja')->nullable();        // Bagian C, 1–5
            $table->unsignedTinyInteger('kano_fungsional')->nullable();    // Bagian D, 1–5
            $table->unsignedTinyInteger('kano_disfungsional')->nullable(); // Bagian D, 1–5
            $table->timestamps();

            $table->unique(['response_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuesioner_portal_answers');
        Schema::dropIfExists('kuesioner_portal_responses');
        Schema::dropIfExists('kuesioner_portal_periodes');
    }
};
