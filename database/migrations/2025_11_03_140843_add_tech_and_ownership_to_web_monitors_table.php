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
        Schema::table('web_monitors', function (Blueprint $table) {
            // Relasi ke Subdomain Request
            $table->foreignId('subdomain_request_id')->nullable()->after('id')->constrained('subdomain_requests')->nullOnDelete();

            // Informasi Aplikasi
            $table->string('nama_aplikasi', 200)->nullable()->after('jenis');
            $table->string('developer', 200)->nullable()->after('nama_aplikasi');
            $table->string('contact_person', 200)->nullable()->after('developer');
            $table->string('contact_phone', 30)->nullable()->after('contact_person');

            // Stack Teknologi Backend
            $table->foreignId('programming_language_id')->nullable()->after('contact_phone')->constrained('programming_languages')->nullOnDelete();
            $table->string('programming_language_version', 50)->nullable()->after('programming_language_id');
            $table->foreignId('framework_id')->nullable()->after('programming_language_version')->constrained('frameworks')->nullOnDelete();
            $table->string('framework_version', 50)->nullable()->after('framework_id');

            // Database
            $table->foreignId('database_id')->nullable()->after('framework_version')->constrained('databases')->nullOnDelete();
            $table->string('database_version', 50)->nullable()->after('database_id');

            // Frontend
            $table->string('frontend_tech', 200)->nullable()->after('database_version');

            // Kepemilikan & Lokasi Server
            $table->enum('server_ownership', ['Provinsi Kaltara', 'Pihak Ketiga'])->nullable()->after('frontend_tech');
            $table->string('server_owner_name', 200)->nullable()->after('server_ownership');
            $table->foreignId('server_location_id')->nullable()->after('server_owner_name')->constrained('server_locations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_monitors', function (Blueprint $table) {
            $table->dropForeign(['subdomain_request_id']);
            $table->dropForeign(['programming_language_id']);
            $table->dropForeign(['framework_id']);
            $table->dropForeign(['database_id']);
            $table->dropForeign(['server_location_id']);

            $table->dropColumn([
                'subdomain_request_id',
                'nama_aplikasi',
                'developer',
                'contact_person',
                'contact_phone',
                'programming_language_id',
                'programming_language_version',
                'framework_id',
                'framework_version',
                'database_id',
                'database_version',
                'frontend_tech',
                'server_ownership',
                'server_owner_name',
                'server_location_id',
            ]);
        });
    }
};
