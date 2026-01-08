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
        Schema::create('subdomain_requests', function (Blueprint $table) {
            // Primary & Tracking
            $table->id();
            $table->string('ticket_no', 20)->unique()->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Informasi Pemohon
            $table->string('nama', 200);
            $table->string('nip', 18)->nullable();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->nullOnDelete();
            $table->string('instansi', 200);
            $table->string('email_pemohon', 200);
            $table->string('no_hp', 30);

            // Informasi Subdomain
            $table->string('subdomain_requested', 63)->index(); // DNS label max 63 chars
            $table->string('ip_address', 45); // Support IPv6
            $table->enum('jenis_website', [
                'Website Resmi',
                'Aplikasi Layanan Publik',
                'Aplikasi Administrasi Pemerintah',
                'Aplikasi Fungsi Tertentu'
            ])->default('Website Resmi');
            $table->string('purpose', 500);
            $table->text('description')->nullable();

            // Informasi Aplikasi
            $table->string('nama_aplikasi', 200)->nullable();
            $table->text('latar_belakang')->nullable();
            $table->text('manfaat_aplikasi')->nullable();
            $table->year('tahun_pembuatan')->nullable();
            $table->string('developer', 200)->nullable();
            $table->string('contact_person', 200)->nullable();
            $table->string('contact_phone', 30)->nullable();

            // Stack Teknologi Backend
            $table->foreignId('programming_language_id')->nullable()->constrained('programming_languages')->nullOnDelete();
            $table->string('programming_language_version', 50)->nullable();
            $table->foreignId('framework_id')->nullable()->constrained('frameworks')->nullOnDelete();
            $table->string('framework_version', 50)->nullable();

            // Database
            $table->foreignId('database_id')->nullable()->constrained('databases')->nullOnDelete();
            $table->string('database_version', 50)->nullable();

            // Frontend
            $table->string('frontend_tech', 200)->nullable();

            // Keamanan & Maintenance
            $table->boolean('has_https')->default(false);
            $table->string('maintenance_schedule', 200)->nullable();
            $table->text('backup_strategy')->nullable();

            // Kepemilikan & Lokasi Server
            $table->enum('server_ownership', ['Provinsi Kaltara', 'Pihak Ketiga'])->default('Provinsi Kaltara');
            $table->string('server_owner_name', 200)->nullable(); // Jika Pihak Ketiga
            $table->foreignId('server_location_id')->nullable()->constrained('server_locations')->nullOnDelete();

            // Pengaturan Cloudflare
            $table->boolean('needs_ssl')->default(true);
            $table->boolean('needs_proxy')->default(false);
            $table->string('cloudflare_record_id', 100)->nullable();
            $table->boolean('is_proxied')->default(false);

            // Relasi Web Monitor
            $table->foreignId('web_monitor_id')->nullable()->constrained('web_monitors')->nullOnDelete();

            // Status & Workflow
            $table->enum('status', ['menunggu', 'proses', 'ditolak', 'selesai'])->default('menunggu')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Admin
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('consent_true')->default(false);

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subdomain_requests');
    }
};
