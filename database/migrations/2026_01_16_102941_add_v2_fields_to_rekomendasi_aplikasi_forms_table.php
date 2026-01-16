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
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            // V2 Fields - Basic Information
            $table->string('nama_aplikasi')->nullable()->after('user_id');
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi', 'sangat_tinggi'])->nullable()->after('nama_aplikasi');
            $table->text('deskripsi')->nullable()->after('prioritas');
            $table->text('tujuan')->nullable()->after('deskripsi');
            $table->text('manfaat')->nullable()->after('tujuan');

            // V2 Fields - Service Details
            $table->enum('jenis_layanan', ['internal', 'eksternal', 'hybrid'])->nullable()->after('manfaat');
            $table->string('target_pengguna')->nullable()->after('jenis_layanan');
            $table->integer('estimasi_pengguna')->nullable()->after('target_pengguna');
            $table->enum('lingkup_aplikasi', ['lokal', 'regional', 'nasional'])->nullable()->after('estimasi_pengguna');
            $table->json('platform')->nullable()->after('lingkup_aplikasi');

            // V2 Fields - Technical & Financial
            $table->string('teknologi_diusulkan')->nullable()->after('platform');
            $table->integer('estimasi_waktu_pengembangan')->nullable()->after('teknologi_diusulkan');
            $table->decimal('estimasi_biaya', 15, 2)->nullable()->after('estimasi_waktu_pengembangan');
            $table->enum('sumber_pendanaan', ['apbd', 'apbn', 'hibah', 'swasta', 'lainnya'])->nullable()->after('estimasi_biaya');

            // V2 Fields - Integration & Others
            $table->enum('integrasi_sistem_lain', ['ya', 'tidak'])->default('tidak')->after('sumber_pendanaan');
            $table->text('detail_integrasi')->nullable()->after('integrasi_sistem_lain');
            $table->text('kebutuhan_khusus')->nullable()->after('detail_integrasi');
            $table->text('dampak_tidak_dibangun')->nullable()->after('kebutuhan_khusus');

            // V2 Fields - Risk Items (JSON)
            $table->json('risiko_items')->nullable()->after('dampak_tidak_dibangun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekomendasi_aplikasi_forms', function (Blueprint $table) {
            $table->dropColumn([
                'nama_aplikasi',
                'prioritas',
                'deskripsi',
                'tujuan',
                'manfaat',
                'jenis_layanan',
                'target_pengguna',
                'estimasi_pengguna',
                'lingkup_aplikasi',
                'platform',
                'teknologi_diusulkan',
                'estimasi_waktu_pengembangan',
                'estimasi_biaya',
                'sumber_pendanaan',
                'integrasi_sistem_lain',
                'detail_integrasi',
                'kebutuhan_khusus',
                'dampak_tidak_dibangun',
                'risiko_items',
            ]);
        });
    }
};
