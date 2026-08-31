<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Running text (teks berjalan) yang tampil di bawah navbar semua halaman
     * pengguna yang sudah login. Dikelola admin lewat /admin/running-text.
     */
    public function up(): void
    {
        Schema::create('running_texts', function (Blueprint $table) {
            $table->id();
            // Teks polos. Disanitasi di model (tanpa HTML) & di-escape saat render.
            $table->string('isi', 500);
            // Tautan opsional; hanya http/https yang lolos validasi.
            $table->string('tautan', 255)->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            // Jadwal tayang opsional. Null = tanpa batas.
            $table->timestamp('mulai_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_texts');
    }
};
