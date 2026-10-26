<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Nama DNS tidak peka huruf besar/kecil, tetapi data lama menyimpan nama seperti "PEKAPBJ".
 * Web Monitor yang dibuat dari approval subdomain juga hanya menyimpan nama tanpa
 * ".kaltaraprov.go.id", sehingga "Check Now" memanggil https://PEKAPBJ dan selalu gagal.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('subdomain_requests')
            ->whereRaw('BINARY subdomain_requested <> BINARY LOWER(subdomain_requested)')
            ->update(['subdomain_requested' => DB::raw('LOWER(TRIM(subdomain_requested))')]);

        DB::table('subdomain_name_change_requests')
            ->whereRaw('BINARY new_subdomain_name <> BINARY LOWER(new_subdomain_name)')
            ->update(['new_subdomain_name' => DB::raw('LOWER(TRIM(new_subdomain_name))')]);

        DB::table('web_monitors')
            ->whereNotNull('subdomain')
            ->whereRaw('BINARY subdomain <> BINARY LOWER(subdomain)')
            ->update(['subdomain' => DB::raw('LOWER(TRIM(subdomain))')]);

        DB::table('web_monitors')
            ->whereNotNull('subdomain_request_id')
            ->whereNotNull('subdomain')
            ->where('subdomain', '!=', '')
            ->where('subdomain', 'not like', '%.%')
            ->update(['subdomain' => DB::raw("CONCAT(subdomain, '.kaltaraprov.go.id')")]);
    }

    public function down(): void
    {
        // Normalisasi data tidak dikembalikan
    }
};
