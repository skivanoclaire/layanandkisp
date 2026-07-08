<?php

namespace App\Services;

use App\Models\SurveiDigitalSetting;

/**
 * Sumber tunggal untuk survei digital SPBE (surveidigital.spbe.go.id).
 *
 * Token embed dipakai bersama seluruh layanan dan disimpan sekali di tabel
 * survei_digital_settings (embed_base_url). Tiap layanan hanya berbeda pada
 * query "jenis_layanan". Bila token berganti, cukup perbarui satu baris via
 * menu "Manajemen Survei Digital".
 */
class SurveiDigitalService
{
    /**
     * Registri layanan yang memiliki survei kepuasan.
     *
     * Kunci = slug layanan (dipakai di route ->defaults('service', ...)).
     * - nama          : label layanan untuk tampilan admin
     * - jenis_layanan : nilai query ?jenis_layanan= pada URL embed (sesuai portal SPBE)
     * - model         : kelas Eloquent permohonan (null untuk layanan tanpa alur permohonan)
     * - route_index   : route daftar layanan (tombol "Kembali")
     * - heading       : judul di halaman penilaian
     * - color         : warna aksen tema (tailwind)
     */
    public const SERVICES = [
        'email' => [
            'nama' => 'Layanan Email Resmi',
            'jenis_layanan' => 'Layanan Email Resmi',
            'model' => \App\Models\EmailRequest::class,
            'route_index' => 'user.email.index',
            'heading' => 'Survey Kepuasan Layanan Email',
            'color' => 'green',
        ],
        'vidcon' => [
            'nama' => 'Layanan Video Conference',
            'jenis_layanan' => 'Layanan Video Conference',
            'model' => \App\Models\VidconRequest::class,
            'route_index' => 'user.vidcon.index',
            'heading' => 'Survey Kepuasan Layanan Fasilitasi Rapat Virtual / Webinar / Streaming',
            'color' => 'purple',
        ],

        // TTE — semua sub-layanan memakai jenis_layanan yang sama
        'tte-assistance' => [
            'nama' => 'TTE — Pendampingan Aktivasi & Penggunaan',
            'jenis_layanan' => 'Layanan Tanda Tangan Elektronik',
            'model' => \App\Models\TteAssistanceRequest::class,
            'route_index' => 'user.tte.assistance.index',
            'heading' => 'Survey Kepuasan Layanan Tanda Tangan Elektronik',
            'color' => 'blue',
        ],
        'tte-registration' => [
            'nama' => 'TTE — Pendaftaran Akun',
            'jenis_layanan' => 'Layanan Tanda Tangan Elektronik',
            'model' => \App\Models\TteRegistrationRequest::class,
            'route_index' => 'user.tte.registration.index',
            'heading' => 'Survey Kepuasan Layanan Tanda Tangan Elektronik',
            'color' => 'blue',
        ],
        'tte-passphrase-reset' => [
            'nama' => 'TTE — Reset Passphrase',
            'jenis_layanan' => 'Layanan Tanda Tangan Elektronik',
            'model' => \App\Models\TtePassphraseResetRequest::class,
            'route_index' => 'user.tte.passphrase-reset.index',
            'heading' => 'Survey Kepuasan Layanan Tanda Tangan Elektronik',
            'color' => 'blue',
        ],
        'tte-certificate-update' => [
            'nama' => 'TTE — Pembaruan Sertifikat',
            'jenis_layanan' => 'Layanan Tanda Tangan Elektronik',
            'model' => \App\Models\TteCertificateUpdateRequest::class,
            'route_index' => 'user.tte.certificate-update.index',
            'heading' => 'Survey Kepuasan Layanan Tanda Tangan Elektronik',
            'color' => 'blue',
        ],

        // Pusat Data (Data Center)
        'datacenter-visitation' => [
            'nama' => 'Pusat Data — Kunjungan/Colocation',
            'jenis_layanan' => 'Layanan Pusat Data',
            'model' => \App\Models\Visitation::class,
            'route_index' => 'user.datacenter.visitation.index',
            'heading' => 'Survey Kepuasan Layanan Pusat Data',
            'color' => 'indigo',
        ],
        'datacenter-vps' => [
            'nama' => 'Pusat Data — VPS/VM',
            'jenis_layanan' => 'Layanan Pusat Data',
            'model' => \App\Models\VpsRequest::class,
            'route_index' => 'user.datacenter.vps.index',
            'heading' => 'Survey Kepuasan Layanan Pusat Data',
            'color' => 'indigo',
        ],
        'datacenter-backup' => [
            'nama' => 'Pusat Data — Backup',
            'jenis_layanan' => 'Layanan Pusat Data',
            'model' => \App\Models\BackupRequest::class,
            'route_index' => 'user.datacenter.backup.index',
            'heading' => 'Survey Kepuasan Layanan Pusat Data',
            'color' => 'indigo',
        ],
        'datacenter-cloud-storage' => [
            'nama' => 'Pusat Data — Cloud Storage',
            'jenis_layanan' => 'Layanan Pusat Data',
            'model' => \App\Models\CloudStorageRequest::class,
            'route_index' => 'user.datacenter.cloud-storage.index',
            'heading' => 'Survey Kepuasan Layanan Pusat Data',
            'color' => 'indigo',
        ],

        // Jaringan Intra (VPN / JIP)
        'vpn-registration' => [
            'nama' => 'Jaringan Intra — Pendaftaran VPN',
            'jenis_layanan' => 'Layanan Jaringan Intra',
            'model' => \App\Models\VpnRegistration::class,
            'route_index' => 'user.vpn.registration.index',
            'heading' => 'Survey Kepuasan Layanan Jaringan Intra',
            'color' => 'teal',
        ],
        'vpn-reset' => [
            'nama' => 'Jaringan Intra — Reset Akun VPN',
            'jenis_layanan' => 'Layanan Jaringan Intra',
            'model' => \App\Models\VpnReset::class,
            'route_index' => 'user.vpn.reset.index',
            'heading' => 'Survey Kepuasan Layanan Jaringan Intra',
            'color' => 'teal',
        ],
        'vpn-jip-pdns' => [
            'nama' => 'Jaringan Intra — JIP PDNS',
            'jenis_layanan' => 'Layanan Jaringan Intra',
            'model' => \App\Models\JipPdnsRequest::class,
            'route_index' => 'user.vpn.jip-pdns.index',
            'heading' => 'Survey Kepuasan Layanan Jaringan Intra',
            'color' => 'teal',
        ],

        // Konsultasi SPBE Berbasis AI — tanpa alur permohonan (model null)
        'konsultasi-spbe-ai' => [
            'nama' => 'Konsultasi SPBE Berbasis AI',
            'jenis_layanan' => 'Layanan Konsultasi SPBE Berbasis AI',
            'model' => null,
            'route_index' => 'user.konsultasi-spbe-ai.index',
            'heading' => 'Survey Kepuasan Layanan Konsultasi SPBE Berbasis AI',
            'color' => 'purple',
        ],
    ];

    /**
     * Konfigurasi satu layanan berdasarkan slug, atau null bila tidak dikenal.
     */
    public static function service(string $slug): ?array
    {
        return self::SERVICES[$slug] ?? null;
    }

    /**
     * Apakah survei digital sedang aktif secara global.
     */
    public static function isActive(): bool
    {
        $setting = SurveiDigitalSetting::first();

        return $setting ? ($setting->is_active && filled($setting->embed_base_url)) : false;
    }

    /**
     * Bangun URL embed lengkap untuk sebuah layanan.
     *
     * Mengembalikan null bila survei nonaktif, base URL kosong, atau slug tak dikenal.
     */
    public static function urlFor(string $slug): ?string
    {
        $service = self::service($slug);
        if (! $service) {
            return null;
        }

        $setting = SurveiDigitalSetting::first();
        if (! $setting || ! $setting->is_active || blank($setting->embed_base_url)) {
            return null;
        }

        $base = rtrim($setting->embed_base_url, '/') . '/';

        return $base . '?jenis_layanan=' . rawurlencode($service['jenis_layanan']);
    }
}
