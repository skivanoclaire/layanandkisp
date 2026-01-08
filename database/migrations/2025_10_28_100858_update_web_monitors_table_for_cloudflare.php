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
            // Cloudflare integration fields
            $table->string('cloudflare_record_id')->nullable()->after('subdomain');
            $table->string('ip_address')->nullable()->after('cloudflare_record_id');
            $table->boolean('is_proxied')->default(false)->after('ip_address');
            $table->timestamp('last_checked_at')->nullable()->after('status');
            $table->text('check_error')->nullable()->after('last_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_monitors', function (Blueprint $table) {
            $table->dropColumn([
                'cloudflare_record_id',
                'ip_address',
                'is_proxied',
                'last_checked_at',
                'check_error'
            ]);
        });
    }
};
