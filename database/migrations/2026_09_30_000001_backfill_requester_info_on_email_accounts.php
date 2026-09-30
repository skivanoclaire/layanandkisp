<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Isi informasi pemohon (nama, NIP, unit kerja, kontak) di Master Data Email
 * untuk akun yang dibuat otomatis dari permohonan email berstatus "selesai"
 * sebelum controller ikut menyimpan kolom requester_*.
 * Hanya mengisi kolom yang masih kosong, isian manual admin tidak ditimpa.
 */
return new class extends Migration
{
    public function up(): void
    {
        $requests = DB::table('email_requests as er')
            ->leftJoin('users as u', 'u.id', '=', 'er.user_id')
            ->where('er.status', 'selesai')
            ->whereNotNull('er.username')
            ->orderByDesc('er.id') // permohonan terbaru menang bila username sama
            ->get(['er.username', 'er.nama', 'er.nip', 'er.instansi', 'er.no_hp', 'er.email_alternatif', 'u.email as user_email']);

        foreach ($requests as $req) {
            $account = DB::table('email_accounts')
                ->where('email', $req->username . '@kaltaraprov.go.id')
                ->first();

            if (!$account) {
                continue;
            }

            $values = [
                'requester_name' => $req->nama,
                'requester_nip' => $req->nip,
                'requester_instansi' => $req->instansi,
                'requester_email' => $req->user_email ?: $req->email_alternatif,
                'requester_phone' => $req->no_hp,
            ];

            $updates = [];
            foreach ($values as $column => $value) {
                if (blank($account->{$column}) && filled($value)) {
                    $updates[$column] = $value;
                }
            }

            if ($updates) {
                DB::table('email_accounts')->where('id', $account->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        // Data backfill, tidak dikembalikan.
    }
};
