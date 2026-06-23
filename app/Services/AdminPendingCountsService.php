<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminPendingCountsService
{
    private const PENDING_ALIASES = ['menunggu', 'pending', 'diajukan', 'belum_mulai'];

    private const TABLES = [
        'email_request'             => 'email_requests',
        'email_password_reset'      => 'email_password_reset_requests',
        'subdomain_new'             => 'subdomain_requests',
        'subdomain_ip_change'       => 'subdomain_ip_change_requests',
        'subdomain_name_change'     => 'subdomain_name_change_requests',
        'subdomain_data_update'     => 'subdomain_data_update_requests',
        'rekomendasi_verifikasi'    => 'rekomendasi_aplikasi_forms',
        'pse_update'                => 'pse_update_requests',
        'vidcon'                    => 'vidcon_requests',
        'laporan_gangguan'          => 'laporan_gangguan',
        'starlink'                  => 'starlink_requests',
        'vpn_registration'          => 'vpn_registrations',
        'vpn_reset'                 => 'vpn_resets',
        'jip_pdns'                  => 'jip_pdns_requests',
        'visitation'                => 'visitations',
        'vps'                       => 'vps_requests',
        'backup'                    => 'backup_requests',
        'cloud_storage'             => 'cloud_storage_requests',
        'tte_assistance'            => 'tte_assistance_requests',
        'tte_registration'          => 'tte_registration_requests',
        'tte_passphrase_reset'      => 'tte_passphrase_reset_requests',
        'tte_certificate_update'    => 'tte_certificate_update_requests',
        'shortlink'                 => 'shortlink_requests',
    ];

    /**
     * Permission yang dibutuhkan untuk melihat counter — diturunkan dari
     * @if hasPermission(...) yang dipakai sidebar admin (authenticated.blade.php).
     * Key 'vidcon' tidak ada di sini karena pakai role check (lihat ROLE_ONLY_COUNTERS).
     */
    private const COUNTER_PERMISSIONS = [
        'email_request'          => 'admin.email',
        'email_password_reset'   => 'admin.email',
        'subdomain_new'          => 'admin.subdomain.index',
        'subdomain_ip_change'    => 'admin.subdomain.index',
        'subdomain_name_change'  => 'admin.subdomain.index',
        'subdomain_data_update'  => 'admin.subdomain.index',
        'rekomendasi_verifikasi' => 'admin.rekomendasi.verifikasi.index',
        'pse_update'             => 'Kelola Permohonan PSE',
        'laporan_gangguan'       => 'Kelola Laporan Gangguan Internet',
        'starlink'               => 'Kelola Starlink Jelajah',
        'vpn_registration'       => 'Kelola Pendaftaran VPN',
        'vpn_reset'              => 'Kelola Reset Akun VPN',
        'jip_pdns'               => 'Kelola Akses JIP PDNS',
        'visitation'             => 'Kelola Kunjungan/Colocation',
        'vps'                    => 'Kelola VPS/VM',
        'backup'                 => 'Kelola Backup',
        'cloud_storage'          => 'Kelola Cloud Storage',
        'tte_assistance'         => 'Kelola Bantuan TTE',
        'tte_registration'       => 'Kelola Registrasi TTE',
        'tte_passphrase_reset'   => 'Kelola Reset Passphrase TTE',
        'tte_certificate_update' => 'Kelola Pembaruan Sertifikat TTE',
        'shortlink'              => 'admin.shortlink.index',
    ];

    /** Counter yang diakses berbasis role, bukan permission (sesuai sidebar L269). */
    private const ROLE_ONLY_COUNTERS = [
        'vidcon' => 'Admin',
    ];

    private const GROUP_CHILDREN = [
        'email'       => ['email_request', 'email_password_reset'],
        'subdomain'   => ['subdomain_new', 'subdomain_ip_change', 'subdomain_name_change', 'subdomain_data_update'],
        'rekomendasi' => ['rekomendasi_verifikasi'],
        'internet'    => ['laporan_gangguan', 'starlink'],
        'vpn'         => ['vpn_registration', 'vpn_reset', 'jip_pdns'],
        'datacenter'  => ['visitation', 'vps', 'backup', 'cloud_storage'],
        'tte'         => ['tte_assistance', 'tte_registration', 'tte_passphrase_reset', 'tte_certificate_update'],
    ];

    public function countsFor(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $allowedKeys = $this->allowedCounterKeys($user);
        if (empty($allowedKeys)) {
            return [];
        }

        $perItem = [];
        foreach ($allowedKeys as $key) {
            $perItem[$key] = $this->countPending(self::TABLES[$key]);
        }

        $groups = [];
        foreach (self::GROUP_CHILDREN as $group => $children) {
            $sum = 0;
            foreach ($children as $child) {
                $sum += $perItem[$child] ?? 0;
            }
            if ($sum > 0 || $this->anyChildAllowed($children, $allowedKeys)) {
                $groups[$group] = $sum;
            }
        }

        return array_merge($perItem, $groups, ['total' => array_sum($perItem)]);
    }

    /**
     * @return string[] daftar key counter yang user boleh lihat
     */
    private function allowedCounterKeys(User $user): array
    {
        $allowed = [];

        foreach (self::COUNTER_PERMISSIONS as $key => $permission) {
            if ($user->hasPermission($permission)) {
                $allowed[] = $key;
            }
        }

        foreach (self::ROLE_ONLY_COUNTERS as $key => $role) {
            if ($user->hasRole($role)) {
                $allowed[] = $key;
            }
        }

        return $allowed;
    }

    private function anyChildAllowed(array $children, array $allowedKeys): bool
    {
        foreach ($children as $child) {
            if (in_array($child, $allowedKeys, true)) {
                return true;
            }
        }
        return false;
    }

    private function countPending(string $table): int
    {
        try {
            return (int) DB::table($table)
                ->whereIn('status', self::PENDING_ALIASES)
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
