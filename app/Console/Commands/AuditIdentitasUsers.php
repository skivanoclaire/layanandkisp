<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Audit read-only identitas akun: menampilkan NIP/NIK ganda, pendaftar belum terverifikasi
 * yang mengisi NIP/NIK, dan akun non-SSO yang berperan Admin. Tidak mengubah data apa pun.
 * NIK tidak ditampilkan (terenkripsi); hanya ID dan hash-nya.
 */
class AuditIdentitasUsers extends Command
{
    protected $signature = 'users:audit-identitas {--hari=90 : Rentang pendaftar belum terverifikasi yang ditampilkan (hari)}';

    protected $description = 'Audit (read-only) NIP/NIK ganda, pendaftar mandiri yang mengisi identitas, dan akun non-SSO berperan Admin';

    public function handle(): int
    {
        $this->bagianNipGanda();
        $this->bagianNikGanda();
        $this->bagianPendaftarMandiri((int) $this->option('hari'));
        $this->bagianAdminNonSso();

        return self::SUCCESS;
    }

    private function bagianNipGanda(): void
    {
        $this->info('1. NIP ganda');
        $daftar = DB::table('users')->whereNotNull('nip')->where('nip', '!=', '')
            ->select('nip', DB::raw('COUNT(*) AS jumlah'), DB::raw('GROUP_CONCAT(id) AS user_ids'))
            ->groupBy('nip')->having('jumlah', '>', 1)->get();

        $this->table(['NIP', 'Jumlah', 'User ID'], $daftar->map(fn ($r) => [$r->nip, $r->jumlah, $r->user_ids])->all());
    }

    private function bagianNikGanda(): void
    {
        $this->info('2. NIK ganda (dibandingkan lewat nik_hash)');
        $daftar = DB::table('users')->whereNotNull('nik_hash')
            ->select(DB::raw('COUNT(*) AS jumlah'), DB::raw('GROUP_CONCAT(id) AS user_ids'), 'nik_hash')
            ->groupBy('nik_hash')->having('jumlah', '>', 1)->get();

        $this->table(['Hash NIK (16 karakter)', 'Jumlah', 'User ID'], $daftar->map(
            fn ($r) => [substr((string) $r->nik_hash, 0, 16), $r->jumlah, $r->user_ids]
        )->all());
    }

    private function bagianPendaftarMandiri(int $hari): void
    {
        $this->info("3. Pendaftar belum terverifikasi (bukan SSO, {$hari} hari terakhir) yang mengisi NIP/NIK");
        $daftar = DB::table('users')
            ->where('is_verified', false)
            ->where('is_sso_user', false)
            ->where(fn ($q) => $q->whereNotNull('nip')->orWhereNotNull('nik_hash'))
            ->where('created_at', '>=', now()->subDays($hari))
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'email', 'nip', 'created_at', 'blocked_at']);

        $this->table(
            ['ID', 'Nama', 'Email', 'NIP', 'Dibuat', 'Diblokir'],
            $daftar->map(fn ($r) => [$r->id, $r->name, $r->email, $r->nip, $r->created_at, $r->blocked_at ? 'ya' : '-'])->all()
        );
    }

    private function bagianAdminNonSso(): void
    {
        $this->info('4. Akun non-SSO yang berperan Admin');
        $daftar = DB::table('users')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'Admin')
            ->where('users.is_sso_user', false)
            ->get(['users.id', 'users.name', 'users.email', 'users.is_verified']);

        $this->table(['ID', 'Nama', 'Email', 'Terverifikasi'], $daftar->map(
            fn ($r) => [$r->id, $r->name, $r->email, $r->is_verified ? 'ya' : 'tidak']
        )->all());
    }
}
