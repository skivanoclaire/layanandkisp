<?php

namespace App\Services;

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

    public function counts(): array
    {
        $perItem = [];
        foreach (self::TABLES as $key => $table) {
            $perItem[$key] = $this->countPending($table);
        }

        $groups = [
            'email' => $perItem['email_request'] + $perItem['email_password_reset'],
            'subdomain' => $perItem['subdomain_new'] + $perItem['subdomain_ip_change'] + $perItem['subdomain_name_change'],
            'rekomendasi' => $perItem['rekomendasi_verifikasi'],
            'internet' => $perItem['laporan_gangguan'] + $perItem['starlink'],
            'vpn' => $perItem['vpn_registration'] + $perItem['vpn_reset'] + $perItem['jip_pdns'],
            'datacenter' => $perItem['visitation'] + $perItem['vps'] + $perItem['backup'] + $perItem['cloud_storage'],
            'tte' => $perItem['tte_assistance'] + $perItem['tte_registration'] + $perItem['tte_passphrase_reset'] + $perItem['tte_certificate_update'],
        ];

        return array_merge($perItem, $groups, ['total' => array_sum($perItem)]);
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
