<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shortlink_requests', function (Blueprint $t) {
            $t->id();
            $t->string('ticket_no')->unique()->index();          // URLYYMMNNNN
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Snapshot pemohon
            $t->string('nama');
            $t->string('nip')->nullable();
            $t->string('instansi')->nullable();

            // Data permohonan
            $t->text('long_url');                                 // URL tujuan
            $t->string('title')->nullable();                      // judul/keterangan link
            $t->string('requested_keyword')->nullable();          // kode pendek yang diusulkan pemohon
            $t->text('keperluan');                                // alasan / keperluan

            // Hasil di YOURLS (diisi saat disetujui)
            $t->string('keyword')->nullable();                    // kode pendek final
            $t->string('short_url')->nullable();                  // URL pendek lengkap
            $t->boolean('is_active')->default(true);              // false = link sudah dihapus dari YOURLS
            $t->unsignedBigInteger('clicks')->default(0);
            $t->timestamp('stats_synced_at')->nullable();

            // Status & jejak waktu
            $t->enum('status', ['menunggu','proses','ditolak','selesai'])->default('menunggu')->index();
            $t->text('admin_note')->nullable();
            $t->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamp('processing_at')->nullable();
            $t->timestamp('rejected_at')->nullable();
            $t->timestamp('completed_at')->nullable();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shortlink_requests');
    }
};
