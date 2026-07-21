<?php

namespace App\Services\Sla;

/**
 * Registry terpusat seluruh layanan digital yang diukur SLA-nya.
 *
 * Disalin & diperluas dari daftar tabel di App\Http\Controllers\AdminController::dashboard()
 * (21 tabel Kelola Permohonan) ditambah 5 layanan SPLP. Setiap entri memetakan:
 * - status_column + bucket status (menunggu/proses/ditolak/selesai) ke nilai enum asli tabel
 *   (konvensinya tidak seragam: campuran Indonesia, "diproses"/"disetujui", dan Inggris)
 * - kolom "mulai" & "selesai" yang dipakai untuk menghitung durasi kerja SLA, dengan fallback
 *   ke created_at/updated_at untuk layanan yang belum punya timestamp per-tahap
 *   (Registrasi TTE, Pendampingan TTE).
 *
 * PENTING: bila menambah/mengubah tabel permohonan baru, sinkronkan juga dengan
 * AdminController::dashboard() — kedua registry ini sengaja independen agar perubahan
 * di sini tidak berisiko terhadap dashboard admin yang sudah berjalan.
 */
class SlaServiceRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            // ===================== Persuratan & Layanan Digital Umum =====================
            'email' => [
                'label' => 'Email Baru',
                'group' => 'Persuratan & Layanan Digital',
                'table' => 'email_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['submitted_at', 'created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            'email_reset' => [
                'label' => 'Reset Password Email',
                'group' => 'Persuratan & Layanan Digital',
                'table' => 'email_password_reset_requests',
                'status_selesai' => ['processed'],
                'status_ditolak' => ['rejected'],
                'status_proses' => [],
                'status_menunggu' => ['pending'],
                'start_columns' => ['created_at'],
                'end_success_columns' => ['processed_at'],
                'end_rejected_columns' => ['processed_at'],
            ],
            'shortlink' => [
                'label' => 'Pemendek Tautan',
                'group' => 'Persuratan & Layanan Digital',
                'table' => 'shortlink_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['submitted_at', 'created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            'vidcon' => [
                'label' => 'Video Conference',
                'group' => 'Persuratan & Layanan Digital',
                'table' => 'vidcon_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['submitted_at', 'created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],

            // ===================== Subdomain & Website =====================
            'subdomain' => [
                'label' => 'Subdomain Baru',
                'group' => 'Subdomain & Website',
                'table' => 'subdomain_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['submitted_at', 'created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            // Alur 2 langkah: pending -> approved (processed_at diisi) -> completed
            // (tidak ada kolom completed_at khusus, fallback ke updated_at saat status completed).
            'subdomain_ip' => [
                'label' => 'Perubahan IP Subdomain',
                'group' => 'Subdomain & Website',
                'table' => 'subdomain_ip_change_requests',
                'status_selesai' => ['completed'],
                'status_ditolak' => ['rejected'],
                'status_proses' => ['approved'],
                'status_menunggu' => ['pending'],
                'start_columns' => ['created_at'],
                'end_success_columns' => [],
                'end_rejected_columns' => ['processed_at'],
                'fallback_end_column' => 'updated_at',
            ],
            'subdomain_name' => [
                'label' => 'Perubahan Nama Subdomain',
                'group' => 'Subdomain & Website',
                'table' => 'subdomain_name_change_requests',
                'status_selesai' => ['completed'],
                'status_ditolak' => ['rejected'],
                'status_proses' => ['approved'],
                'status_menunggu' => ['pending'],
                'start_columns' => ['created_at'],
                'end_success_columns' => [],
                'end_rejected_columns' => ['processed_at'],
                'fallback_end_column' => 'updated_at',
            ],

            // ===================== Rekomendasi & Registrasi Sistem Elektronik =====================
            'rekomendasi_usulan' => [
                'label' => 'Rekomendasi Usulan Aplikasi',
                'group' => 'Rekomendasi Aplikasi',
                'table' => 'rekomendasi_aplikasi_forms',
                'status_selesai' => ['disetujui'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['diproses', 'perlu_revisi'],
                'status_menunggu' => ['diajukan'],
                'status_exclude' => ['draft'],
                'start_columns' => ['created_at'],
                'end_success_columns' => ['approved_at'],
                'end_rejected_columns' => ['rejected_at'],
                'fallback_end_column' => 'updated_at',
            ],
            'pse' => [
                'label' => 'Pendaftaran Sistem Elektronik (PSE)',
                'group' => 'Rekomendasi Aplikasi',
                'table' => 'pse_update_requests',
                'status_selesai' => ['disetujui'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['diproses', 'perlu_revisi'],
                'status_menunggu' => ['diajukan'],
                'status_exclude' => ['draft'],
                'start_columns' => ['submitted_at', 'created_at'],
                'end_success_columns' => ['approved_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],

            // ===================== Tanda Tangan Elektronik (TTE) =====================
            'tte_registration' => [
                'label' => 'Registrasi TTE',
                'group' => 'Tanda Tangan Elektronik',
                'table' => 'tte_registration_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['created_at'],
                'end_success_columns' => [],
                'end_rejected_columns' => [],
                'fallback_end_column' => 'updated_at',
            ],
            'tte_assistance' => [
                'label' => 'Pendampingan TTE',
                'group' => 'Tanda Tangan Elektronik',
                'table' => 'tte_assistance_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['created_at'],
                'end_success_columns' => [],
                'end_rejected_columns' => [],
                'fallback_end_column' => 'updated_at',
            ],
            'tte_passphrase' => [
                'label' => 'Reset Passphrase TTE',
                'group' => 'Tanda Tangan Elektronik',
                'table' => 'tte_passphrase_reset_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['diproses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['submitted_at', 'created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            'tte_certificate_update' => [
                'label' => 'Pembaruan Sertifikat TTE',
                'group' => 'Tanda Tangan Elektronik',
                'table' => 'tte_certificate_update_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['diproses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['submitted_at', 'created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],

            // ===================== Pusat Data & Jaringan =====================
            'vpn_registration' => [
                'label' => 'Pendaftaran VPN',
                'group' => 'Pusat Data & Jaringan',
                'table' => 'vpn_registrations',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            'vpn_reset' => [
                'label' => 'Reset VPN',
                'group' => 'Pusat Data & Jaringan',
                'table' => 'vpn_resets',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            'jip_pdns' => [
                'label' => 'JIP PDNS',
                'group' => 'Pusat Data & Jaringan',
                'table' => 'jip_pdns_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            'starlink' => [
                'label' => 'Starlink Jelajah',
                'group' => 'Pusat Data & Jaringan',
                'table' => 'starlink_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            'lapor_gangguan' => [
                'label' => 'Lapor Gangguan Internet',
                'group' => 'Pusat Data & Jaringan',
                'table' => 'laporan_gangguan',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            'vps' => [
                'label' => 'VPS / VM',
                'group' => 'Pusat Data & Jaringan',
                'table' => 'vps_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            'backup' => [
                'label' => 'Backup Pusat Data',
                'group' => 'Pusat Data & Jaringan',
                'table' => 'backup_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            'cloud_storage' => [
                'label' => 'Cloud Storage',
                'group' => 'Pusat Data & Jaringan',
                'table' => 'cloud_storage_requests',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['proses'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],
            'visitation' => [
                'label' => 'Kunjungan Pusat Data',
                'group' => 'Pusat Data & Jaringan',
                'table' => 'visitations',
                'status_selesai' => ['selesai'],
                'status_ditolak' => ['ditolak'],
                'status_proses' => ['disetujui'],
                'status_menunggu' => ['menunggu'],
                'start_columns' => ['created_at'],
                'end_success_columns' => ['completed_at'],
                'end_rejected_columns' => ['rejected_at'],
            ],

            // ===================== Integrasi SPLP =====================
            'splp_provider' => self::splpEntry('Pendaftaran Endpoint Penyedia (SPLP V1)', 'splp_provider_requests'),
            'splp_consumer' => self::splpEntry('Pendaftaran Akses Konsumen (SPLP V2)', 'splp_consumer_requests'),
            'splp_sandbox' => self::splpEntry('Permohonan Uji Coba Sandbox (SPLP V3)', 'splp_sandbox_requests'),
            'splp_change' => self::splpEntry('Perubahan/Perpanjangan Endpoint (SPLP V4)', 'splp_change_requests'),
            'splp_deactivation' => self::splpEntry('Penonaktifan/Pencabutan Endpoint (SPLP V5)', 'splp_deactivation_requests'),
        ];
    }

    /**
     * Entri SPLP identik untuk 5 tabel (mengikuti App\Models\Concerns\HasSplpWorkflow).
     */
    private static function splpEntry(string $label, string $table): array
    {
        return [
            'label' => $label,
            'group' => 'Integrasi SPLP',
            'table' => $table,
            'status_selesai' => ['selesai'],
            'status_ditolak' => ['ditolak'],
            'status_proses' => ['verifikasi_administrasi', 'verifikasi_teknis', 'menunggu_keputusan', 'disetujui', 'perlu_perbaikan'],
            'status_menunggu' => ['diajukan'],
            'status_exclude' => ['draft'],
            'start_columns' => ['submitted_at', 'created_at'],
            'end_success_columns' => ['completed_at'],
            'end_rejected_columns' => ['rejected_at'],
        ];
    }

    public static function find(string $serviceKey): ?array
    {
        return self::all()[$serviceKey] ?? null;
    }

    /**
     * Daftar service_key dikelompokkan per kategori, untuk tampilan UI.
     *
     * @return array<string, array<string>>
     */
    public static function groups(): array
    {
        $groups = [];
        foreach (self::all() as $key => $meta) {
            $groups[$meta['group']][] = $key;
        }

        return $groups;
    }
}
