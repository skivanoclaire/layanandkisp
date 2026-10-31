<?php

namespace App\Services;

use App\Models\SubdomainRequest;
use App\Models\TataKelolaKeamanan\Responden;
use App\Models\User;

/**
 * Struktur menu sidebar (dan akses cepat di dashboard) untuk layouts.authenticated.
 *
 * Setiap item sudah difilter berdasarkan permission/role user, sehingga view hanya
 * merender apa yang dikembalikan. Hasil di-memo per request karena dipakai oleh
 * sidebar, navbar (notifikasi), dan dashboard sekaligus.
 *
 * Bentuk item:
 *   label, icon, url, active (bool), badge (int), children (item[]), search (string)
 */
class SidebarMenuService
{
    /** @var array<int, array>|null */
    private ?array $sections = null;

    private ?array $pendingCounts = null;

    public function __construct(private AdminPendingCountsService $countsService)
    {
    }

    public function pendingCounts(): array
    {
        return $this->pendingCounts ??= $this->countsService->countsFor(auth()->user());
    }

    /**
     * @return array<int, array{key: string, label: ?string, items: array}>
     */
    public function sections(): array
    {
        if ($this->sections !== null) {
            return $this->sections;
        }

        $user = auth()->user();
        if (!$user) {
            return $this->sections = [];
        }

        $isAdmin = $user->hasRole('Admin');
        $pc = $this->pendingCounts();

        $sections = [
            $this->section('utama', null, $this->dashboardItems($user)),
            $this->section('kelola-permohonan', 'Kelola Permohonan', $this->adminPermohonanItems($user, $pc)),
            $this->section('survei-evaluasi', 'Survei & Evaluasi', $this->adminSurveiItems($user)),
            $this->section('layanan', 'Layanan Digital', $this->userLayananItems($user)),
            $this->section('survei-kuesioner', 'Survei & Kuesioner', $this->userSurveiItems($user)),
            $this->section('master-data', 'Master Data', $this->masterDataItems($user, $isAdmin)),
            $this->section('peminjaman-vidcon', 'Peminjaman & Vidcon', $this->peminjamanItems($user)),
            $this->section('sistem', 'Pengaturan Sistem', $this->sistemItems($user, $isAdmin)),
        ];

        return $this->sections = array_values(array_filter($sections, fn ($s) => !empty($s['items'])));
    }

    /** Item menu dalam satu section (mis. untuk akses cepat di dashboard pengguna). */
    public function sectionItems(string $key): array
    {
        foreach ($this->sections() as $section) {
            if ($section['key'] === $key) {
                return $section['items'];
            }
        }

        return [];
    }

    /**
     * Daftar halaman yang punya permohonan menunggu (untuk notifikasi navbar
     * dan panel "Perlu Tindakan" di dashboard admin), diurutkan dari terbanyak.
     *
     * @return array<int, array{label: string, group: ?string, url: string, icon: string, badge: int}>
     */
    public function pendingItems(): array
    {
        $items = [];
        foreach ($this->sections() as $section) {
            foreach ($section['items'] as $item) {
                $withBadge = array_filter($item['children'], fn ($c) => $c['badge'] > 0);
                if ($withBadge) {
                    foreach ($withBadge as $child) {
                        $items[] = ['label' => $child['label'], 'group' => $item['label'], 'url' => $child['url'], 'icon' => $item['icon'], 'badge' => $child['badge']];
                    }
                } elseif (empty($item['children']) && $item['badge'] > 0) {
                    $items[] = ['label' => $item['label'], 'group' => null, 'url' => $item['url'], 'icon' => $item['icon'], 'badge' => $item['badge']];
                }
            }
        }

        usort($items, fn ($a, $b) => $b['badge'] <=> $a['badge']);

        return $items;
    }

    // ------------------------------------------------------------------
    // Section builders
    // ------------------------------------------------------------------

    private function dashboardItems(User $user): array
    {
        $items = [];

        if ($user->hasPermission('admin.dashboard')) {
            $items[] = $this->link('Dashboard Admin', 'chart', 'admin.dashboard', ['admin.dashboard']);
        }
        if ($user->hasPermission('user.dashboard')) {
            $items[] = $this->link('Dashboard Pengguna', 'home', 'user.dashboard', ['user.dashboard']);
        }

        return $items;
    }

    private function adminPermohonanItems(User $user, array $pc): array
    {
        $items = [];

        if ($user->hasPermission('admin.permohonan')) {
            $items[] = $this->link('Manual - Unggah Surat', 'upload', 'admin.permohonan', ['admin.permohonan']);
        }

        if ($user->hasAnyPermission(['admin.email', 'admin.email.index', 'admin.email-password-reset.index'])) {
            $items[] = $this->group('Email', 'mail', [
                $this->link('Permohonan', null, 'admin.email.index', ['admin.email.*'], $pc['email_request'] ?? 0),
                $this->link('Reset Password', null, 'admin.email-password-reset.index', ['admin.email-password-reset.*'], $pc['email_password_reset'] ?? 0),
            ], $pc['email'] ?? 0);
        }

        if ($user->hasPermission('admin.shortlink.index')) {
            $items[] = $this->link('Pemendek Tautan', 'link', 'admin.shortlink.index', ['admin.shortlink.*'], $pc['shortlink'] ?? 0);
        }

        if ($user->hasPermission('admin.subdomain.index')) {
            $items[] = $this->group('Subdomain', 'globe', [
                $this->link('Pendaftaran Baru', null, 'admin.subdomain.index', ['admin.subdomain.index', 'admin.subdomain.show'], $pc['subdomain_new'] ?? 0),
                $this->link('Perubahan IP', null, 'admin.subdomain.ip-change.index', ['admin.subdomain.ip-change.*'], $pc['subdomain_ip_change'] ?? 0),
                $this->link('Perubahan Nama', null, 'admin.subdomain.name-change.index', ['admin.subdomain.name-change.*'], $pc['subdomain_name_change'] ?? 0),
                $this->link('Pembaruan Data', null, 'admin.subdomain.data-update.index', ['admin.subdomain.data-update.*'], $pc['subdomain_data_update'] ?? 0),
            ], $pc['subdomain'] ?? 0);
        }

        if ($user->hasPermission('Manajemen Subdomain Terpadu')) {
            $items[] = $this->link(
                'Kelola Subdomain Terpadu', 'globe-alt', 'admin.unified-subdomain.index', ['admin.unified-subdomain.*'],
                SubdomainRequest::where('status', 'menunggu')->count()
            );
        }

        if ($user->hasAnyPermission(['admin.rekomendasi.verifikasi.index', 'admin.rekomendasi.surat.generate', 'admin.rekomendasi.monitoring.index', 'admin.fase-pengembangan.view'])) {
            $children = [];
            if ($user->hasPermission('admin.rekomendasi.verifikasi.index')) {
                $children[] = $this->link('Verifikasi Usulan', null, 'admin.rekomendasi.verifikasi.index', ['admin.rekomendasi.verifikasi.*'], $pc['rekomendasi_verifikasi'] ?? 0);
            }
            if ($user->hasPermission('admin.fase-pengembangan.view')) {
                $children[] = $this->link('Fase Pengembangan', null, 'admin.fase-pengembangan.index', ['admin.fase-pengembangan.*']);
            }
            if ($user->hasPermission('admin.rekomendasi.monitoring.index')) {
                $children[] = $this->link('Monitoring & Laporan', null, 'admin.rekomendasi.monitoring.dashboard', ['admin.rekomendasi.monitoring.*']);
            }
            $items[] = $this->group('Rekomendasi Aplikasi', 'document-check', $children, $pc['rekomendasi'] ?? 0, ['admin.rekomendasi.surat.*']);
        }

        if ($user->hasPermission('admin.vidcon.index')) {
            $items[] = $this->link('Zoom/Youtube Live', 'video', 'admin.vidcon.index', ['admin.vidcon.*'], $pc['vidcon'] ?? 0);
        }

        if ($user->hasPermission('Kelola Permohonan Data Arsitektur')) {
            $items[] = $this->link('Pembaruan Data Arsitektur', 'building', 'admin.arsitektur-permohonan.index', ['admin.arsitektur-permohonan.*'], $pc['arsitektur_permohonan'] ?? 0);
        }

        if ($user->hasAnyPermission(['Kelola Laporan Gangguan Internet', 'Kelola Starlink Jelajah'])) {
            $children = [];
            if ($user->hasPermission('Kelola Laporan Gangguan Internet')) {
                $children[] = $this->link('Laporan Gangguan', null, 'admin.internet.laporan-gangguan.index', ['admin.internet.laporan-gangguan.*'], $pc['laporan_gangguan'] ?? 0);
            }
            if ($user->hasPermission('Kelola Starlink Jelajah')) {
                $children[] = $this->link('Starlink Jelajah', null, 'admin.internet.starlink.index', ['admin.internet.starlink.*'], $pc['starlink'] ?? 0);
            }
            $items[] = $this->group('Internet', 'wifi', $children, $pc['internet'] ?? 0);
        }

        if ($user->hasAnyPermission(['Kelola Pendaftaran VPN', 'Kelola Reset Akun VPN', 'Kelola Akses JIP PDNS'])) {
            $children = [];
            if ($user->hasPermission('Kelola Pendaftaran VPN')) {
                $children[] = $this->link('Pendaftaran VPN', null, 'admin.vpn.registration.index', ['admin.vpn.registration.*'], $pc['vpn_registration'] ?? 0);
            }
            if ($user->hasPermission('Kelola Reset Akun VPN')) {
                $children[] = $this->link('Reset Akun VPN', null, 'admin.vpn.reset.index', ['admin.vpn.reset.*'], $pc['vpn_reset'] ?? 0);
            }
            if ($user->hasPermission('Kelola Akses JIP PDNS')) {
                $children[] = $this->link('Akses JIP PDNS', null, 'admin.vpn.jip-pdns.index', ['admin.vpn.jip-pdns.*'], $pc['jip_pdns'] ?? 0);
            }
            $items[] = $this->group('Jaringan Privat/VPN', 'lock', $children, $pc['vpn'] ?? 0);
        }

        if ($user->hasAnyPermission(['Kelola Kunjungan/Colocation', 'Kelola VPS/VM', 'Kelola Backup', 'Kelola Cloud Storage'])) {
            $items[] = $this->group('Pusat Data/Komputasi', 'server', [
                $this->link('Kunjungan/Colocation', null, 'admin.datacenter.visitation.index', ['admin.datacenter.visitation.*'], $pc['visitation'] ?? 0),
                $this->link('VPS/VM', null, 'admin.datacenter.vps.index', ['admin.datacenter.vps.*'], $pc['vps'] ?? 0),
                $this->link('Backup', null, 'admin.datacenter.backup.index', ['admin.datacenter.backup.*'], $pc['backup'] ?? 0),
                $this->link('Cloud Storage', null, 'admin.datacenter.cloud-storage.index', ['admin.datacenter.cloud-storage.*'], $pc['cloud_storage'] ?? 0),
            ], $pc['datacenter'] ?? 0);
        }

        if ($user->hasAnyPermission(['Kelola Bantuan TTE', 'Kelola Registrasi TTE', 'Kelola Reset Passphrase TTE', 'Kelola Pembaruan Sertifikat TTE'])) {
            $children = [];
            if ($user->hasPermission('Kelola Bantuan TTE')) {
                $children[] = $this->link('Pendampingan TTE', null, 'admin.tte.assistance.index', ['admin.tte.assistance.*'], $pc['tte_assistance'] ?? 0);
            }
            if ($user->hasPermission('Kelola Registrasi TTE')) {
                $children[] = $this->link('Pendaftaran Akun TTE', null, 'admin.tte.registration.index', ['admin.tte.registration.*'], $pc['tte_registration'] ?? 0);
            }
            if ($user->hasPermission('Kelola Reset Passphrase TTE')) {
                $children[] = $this->link('Reset Passphrase TTE', null, 'admin.tte.passphrase-reset.index', ['admin.tte.passphrase-reset.*'], $pc['tte_passphrase_reset'] ?? 0);
            }
            if ($user->hasPermission('Kelola Pembaruan Sertifikat TTE')) {
                $children[] = $this->link('Pembaruan Sertifikat TTE', null, 'admin.tte.certificate-update.index', ['admin.tte.certificate-update.*'], $pc['tte_certificate_update'] ?? 0);
            }
            $items[] = $this->group('Tanda Tangan Elektronik', 'pen', $children, $pc['tte'] ?? 0);
        }

        if ($user->hasPermission('Kelola Permohonan PSE')) {
            $items[] = $this->link('Manajemen PSE', 'id-card', 'admin.pse-update.index', ['admin.pse-update.*'], $pc['pse_update'] ?? 0);
        }

        if ($user->hasPermission('Kelola SPLP')) {
            $items[] = $this->group('Integrasi (SPLP)', 'puzzle', [
                $this->link('Endpoint Penyedia', null, 'admin.splp.provider.index', ['admin.splp.provider.*']),
                $this->link('Akses Konsumen', null, 'admin.splp.consumer.index', ['admin.splp.consumer.*']),
                $this->link('Uji Coba (Sandbox)', null, 'admin.splp.sandbox.index', ['admin.splp.sandbox.*']),
                $this->link('Perubahan / Perpanjangan', null, 'admin.splp.change.index', ['admin.splp.change.*']),
                $this->link('Penonaktifan / Pencabutan', null, 'admin.splp.deactivation.index', ['admin.splp.deactivation.*']),
            ]);
        }

        if ($user->hasAnyPermission(['Kelola Akun DTSEN', 'Kelola Permohonan DTSEN', 'Verifikasi Substansi DTSEN', 'Kelola Laporan DTSEN'])) {
            $children = [];
            if ($user->hasPermission('Kelola Akun DTSEN')) {
                $children[] = $this->link('Akun Layanan', null, 'admin.dtsen.akun.index', ['admin.dtsen.akun.*'], $pc['dtsen_akun'] ?? 0);
            }
            if ($user->hasPermission('Kelola Permohonan DTSEN')) {
                $children[] = $this->link('Permintaan Data', null, 'admin.dtsen.permohonan.index', ['admin.dtsen.permohonan.*'], $pc['dtsen_permohonan'] ?? 0);
            }
            if ($user->hasPermission('Verifikasi Substansi DTSEN')) {
                $children[] = $this->link('Verifikasi Substansi', null, 'admin.dtsen.substansi.index', ['admin.dtsen.substansi.*']);
            }
            if ($user->hasPermission('Kelola Permohonan DTSEN')) {
                $children[] = $this->link('Perpanjangan Akses', null, 'admin.dtsen.perpanjangan.index', ['admin.dtsen.perpanjangan.*']);
                $children[] = $this->link('Pengaduan & Masukan', null, 'admin.dtsen.pengaduan.index', ['admin.dtsen.pengaduan.*']);
            }
            if ($user->hasPermission('Kelola Laporan DTSEN')) {
                $children[] = $this->link('Laporan Pemanfaatan', null, 'admin.dtsen.laporan.pemanfaatan', ['admin.dtsen.laporan.pemanfaatan*']);
                $children[] = $this->link('Pemusnahan Data', null, 'admin.dtsen.laporan.pemusnahan', ['admin.dtsen.laporan.pemusnahan*']);
                $children[] = $this->link('Insiden Keamanan', null, 'admin.dtsen.laporan.insiden', ['admin.dtsen.laporan.insiden*']);
            }
            $items[] = $this->group('Berbagi Pakai Data (DTSEN)', 'database', $children, $pc['dtsen'] ?? 0);
        }

        return $items;
    }

    private function adminSurveiItems(User $user): array
    {
        $items = [];

        if ($user->hasPermission('Kelola Survei Kepuasan')) {
            $items[] = $this->group('Survei Kepuasan Layanan', 'star', [
                $this->link('Monitoring Survei', null, 'admin.survei-kepuasan.index', ['admin.survei-kepuasan.index', 'admin.survei-kepuasan.show']),
                $this->link('Statistik & Analitik', null, 'admin.survei-kepuasan.statistics', ['admin.survei-kepuasan.statistics']),
            ], 0, ['admin.survei-kepuasan.*']);
        }
        if ($user->hasPermission('Kelola Kuesioner Portal')) {
            $items[] = $this->link('Kuesioner Kualitas Portal', 'clipboard', 'admin.kuesioner-portal.index', ['admin.kuesioner-portal.*']);
        }
        if ($user->hasPermission('Kelola Tata Kelola Keamanan')) {
            $items[] = $this->link('Tata Kelola Keamanan Informasi', 'shield', 'admin.tata-kelola-keamanan.index', ['admin.tata-kelola-keamanan.*']);
        }
        if ($user->hasPermission('admin.survei-digital')) {
            $items[] = $this->link('Manajemen Survei Digital', 'chart-pie', 'admin.survei-digital.index', ['admin.survei-digital.*']);
        }

        return $items;
    }

    /** Layanan Digital untuk pemohon — hanya untuk akun yang sudah diverifikasi. */
    private function userLayananItems(User $user): array
    {
        if (!$user->is_verified) {
            return [];
        }

        $items = [];

        if ($user->hasAnyPermission(['user.email.index', 'user.email.create', 'user.email.show', 'user.email-password-reset.index', 'user.email-password-reset.create'])) {
            $items[] = $this->group('Email', 'mail', [
                $this->link('Permohonan', null, 'user.email.index', ['user.email.*']),
                $this->link('Reset Password', null, 'user.email-password-reset.index', ['user.email-password-reset.*']),
            ]);
        }

        if ($user->hasAnyPermission(['user.rekomendasi.usulan.create', 'user.fase-pengembangan'])) {
            $children = [];
            if ($user->hasPermission('user.rekomendasi.usulan.create')) {
                $children[] = $this->link('Usulan Pertimbangan', null, 'user.rekomendasi.usulan.index', ['user.rekomendasi.usulan.*']);
            }
            if ($user->hasPermission('user.fase-pengembangan')) {
                $children[] = $this->link('Fase Pengembangan', null, 'fase-pengembangan.index', ['fase-pengembangan.*']);
            }
            $items[] = $this->group('Rekomendasi Aplikasi', 'document-check', $children);
        }

        if ($user->hasAnyPermission(['user.subdomain.index', 'user.subdomain.create', 'user.subdomain.show', 'user.subdomain.name-change.index', 'user.subdomain.name-change.create', 'user.subdomain.name-change.show'])) {
            $atRegistration = request()->routeIs('user.subdomain.*')
                && !request()->routeIs('user.subdomain.ip-change.*', 'user.subdomain.name-change.*', 'user.subdomain.data-update.*');

            $items[] = $this->group('Subdomain', 'globe', [
                $this->link('Pendaftaran Baru', null, 'user.subdomain.index', $atRegistration),
                $this->link('Perubahan IP', null, 'user.subdomain.ip-change.index', ['user.subdomain.ip-change.*']),
                $this->link('Perubahan Nama', null, 'user.subdomain.name-change.index', ['user.subdomain.name-change.*']),
                $this->link('Pembaruan Data', null, 'user.subdomain.data-update.index', ['user.subdomain.data-update.*']),
            ]);
        }

        if ($user->hasAnyPermission(['user.shortlink.index', 'user.shortlink.create', 'user.shortlink.show'])) {
            $items[] = $this->link('Pemendek Tautan', 'link', 'user.shortlink.index', ['user.shortlink.*']);
        }

        if ($user->hasPermission('Akses Update Data PSE')) {
            $items[] = $this->link('Pendaftaran Sistem Elektronik (PSE)', 'id-card', 'user.pse-update.index', ['user.pse-update.*']);
        }

        if ($user->hasPermission('Akses Pembaruan Data Arsitektur')) {
            $items[] = $this->link('Pembaruan Data Arsitektur', 'building', 'user.arsitektur.index', ['user.arsitektur.*']);
        }

        if ($user->hasPermission('Akses Konsultasi SPBE AI')) {
            $items[] = $this->link('Konsultasi SPBE Berbasis AI', 'sparkles', 'user.konsultasi-spbe-ai.index', ['user.konsultasi-spbe-ai.*']);
        }

        if ($user->hasAnyPermission(['Akses Bantuan TTE', 'Akses Registrasi TTE', 'Akses Reset Passphrase TTE', 'Akses Pembaruan Sertifikat TTE'])) {
            $children = [];
            if ($user->hasPermission('Akses Bantuan TTE')) {
                $children[] = $this->link('Pendampingan Aktivasi dan Penggunaan TTE', null, 'user.tte.assistance.index', ['user.tte.assistance.*']);
            }
            if ($user->hasPermission('Akses Registrasi TTE')) {
                $children[] = $this->link('Pendaftaran Akun Baru TTE', null, 'user.tte.registration.index', ['user.tte.registration.*']);
            }
            if ($user->hasPermission('Akses Reset Passphrase TTE')) {
                $children[] = $this->link('Permohonan Reset Passphrase TTE', null, 'user.tte.passphrase-reset.index', ['user.tte.passphrase-reset.*']);
            }
            if ($user->hasPermission('Akses Pembaruan Sertifikat TTE')) {
                $children[] = $this->link('Pembaruan Sertifikat TTE', null, 'user.tte.certificate-update.index', ['user.tte.certificate-update.*']);
            }
            $items[] = $this->group('Tanda Tangan Elektronik', 'pen', $children);
        }

        if ($user->hasPermission('Akses Video Conference')) {
            $items[] = $this->link('Zoom/Youtube Live', 'video', 'user.vidcon.index', ['user.vidcon.*']);
        }

        if ($user->hasAnyPermission(['Akses Lapor Gangguan Internet', 'Akses Starlink Jelajah'])) {
            $children = [];
            if ($user->hasPermission('Akses Lapor Gangguan Internet')) {
                $children[] = $this->link('Lapor Gangguan', null, 'user.internet.laporan-gangguan.index', ['user.internet.laporan-gangguan.*']);
            }
            if ($user->hasPermission('Akses Starlink Jelajah')) {
                $children[] = $this->link('Starlink Jelajah', null, 'user.internet.starlink.index', ['user.internet.starlink.*']);
            }
            $items[] = $this->group('Internet', 'wifi', $children);
        }

        if ($user->hasAnyPermission(['Akses Pendaftaran VPN', 'Akses Reset Akun VPN', 'Akses JIP PDNS'])) {
            $children = [];
            if ($user->hasPermission('Akses Pendaftaran VPN')) {
                $children[] = $this->link('Pendaftaran Baru', null, 'user.vpn.registration.index', ['user.vpn.registration.*']);
            }
            if ($user->hasPermission('Akses Reset Akun VPN')) {
                $children[] = $this->link('Reset Akun', null, 'user.vpn.reset.index', ['user.vpn.reset.*']);
            }
            if ($user->hasPermission('Akses JIP PDNS')) {
                $children[] = $this->link('Akses JIP PDNS', null, 'user.vpn.jip-pdns.index', ['user.vpn.jip-pdns.*']);
            }
            $items[] = $this->group('Jaringan Privat/VPN', 'lock', $children);
        }

        if ($user->hasAnyPermission(['Akses Kunjungan/Colocation Data Center', 'Akses VPS/VM', 'Akses Backup', 'Akses Cloud Storage'])) {
            $items[] = $this->group('Pusat Data/Komputasi', 'server', [
                $this->link('Kunjungan/Colocation', null, 'user.datacenter.visitation.index', ['user.datacenter.visitation.*']),
                $this->link('VPS/VM', null, 'user.datacenter.vps.index', ['user.datacenter.vps.*']),
                $this->link('Backup', null, 'user.datacenter.backup.index', ['user.datacenter.backup.*']),
                $this->link('Cloud Storage', null, 'user.datacenter.cloud-storage.index', ['user.datacenter.cloud-storage.*']),
            ]);
        }

        if ($user->hasPermission('Akses SPLP')) {
            $items[] = $this->group('Integrasi (SPLP)', 'puzzle', [
                $this->link('Pendaftaran Endpoint Penyedia', null, 'user.splp.provider.index', ['user.splp.provider.*']),
                $this->link('Pendaftaran Akses Konsumen', null, 'user.splp.consumer.index', ['user.splp.consumer.*']),
                $this->link('Uji Coba (Sandbox)', null, 'user.splp.sandbox.index', ['user.splp.sandbox.*']),
                $this->link('Perubahan / Perpanjangan', null, 'user.splp.change.index', ['user.splp.change.*']),
                $this->link('Penonaktifan / Pencabutan', null, 'user.splp.deactivation.index', ['user.splp.deactivation.*']),
            ]);
        }

        if ($user->hasPermission('Akses DTSEN')) {
            $items[] = $this->group('Berbagi Pakai Data (DTSEN)', 'database', [
                $this->link('Akun Layanan DTSEN', null, 'user.dtsen.akun.index', ['user.dtsen.akun.*']),
                $this->link('Permintaan Data', null, 'user.dtsen.permohonan.index', ['user.dtsen.permohonan.*', 'user.dtsen.akses.*']),
                $this->link('Perpanjangan Akses', null, 'user.dtsen.perpanjangan.index', ['user.dtsen.perpanjangan.*']),
                $this->link('Laporan Pemanfaatan', null, 'user.dtsen.pemanfaatan.index', ['user.dtsen.pemanfaatan.*']),
                $this->link('Pemusnahan Data', null, 'user.dtsen.pemusnahan.index', ['user.dtsen.pemusnahan.*']),
                $this->link('Insiden Keamanan', null, 'user.dtsen.insiden.index', ['user.dtsen.insiden.*']),
                $this->link('Pengaduan & Masukan', null, 'user.dtsen.pengaduan.index', ['user.dtsen.pengaduan.*']),
            ]);
        }

        return $items;
    }

    private function userSurveiItems(User $user): array
    {
        if (!$user->is_verified) {
            return [];
        }

        $items = [];

        if ($user->hasPermission('Akses Survei Kepuasan')) {
            $items[] = $this->link('Survei Kepuasan Layanan', 'star', 'survei-kepuasan.index', ['survei-kepuasan.*']);
        }
        if ($user->hasPermission('Akses Kuesioner Portal')) {
            $items[] = $this->link('Kuesioner Kualitas Portal', 'clipboard', 'kuesioner-portal.index', ['kuesioner-portal.*']);
        }
        // Hanya untuk akun yang didaftarkan sebagai responden oleh admin.
        if ($user->hasPermission('Isi Kuesioner Tata Kelola Keamanan')
            && Responden::where('user_id', $user->id)->exists()) {
            $items[] = $this->link('Kuesioner Tata Kelola Keamanan', 'shield', 'tata-kelola-keamanan.responden.index', ['tata-kelola-keamanan.responden.*']);
        }

        return $items;
    }

    private function masterDataItems(User $user, bool $isAdmin): array
    {
        $items = [];

        if ($user->hasPermission('admin.unit-kerja')) {
            $items[] = $this->link('Unit Kerja', 'office', 'admin.unit-kerja.index', ['admin.unit-kerja.*']);
        }
        if ($isAdmin) {
            $items[] = $this->link('Arsitektur', 'building', 'admin.master-arsitektur.index', ['admin.master-arsitektur.*']);
            $items[] = $this->link('Presensi Kegiatan', 'calendar', 'admin.presensi-kegiatan.index', ['admin.presensi-kegiatan.*']);
        }
        if ($user->hasPermission('admin.web-monitor')) {
            $items[] = $this->link('Subdomain', 'globe', 'admin.web-monitor.index', ['admin.web-monitor.index', 'admin.web-monitor.create', 'admin.web-monitor.show', 'admin.web-monitor.edit']);
        }
        if ($user->hasPermission('Kelola Master Data Email')) {
            $items[] = $this->link('Email', 'mail', 'admin.email-accounts.index', ['admin.email-accounts.*']);
        }
        if ($user->hasPermission('admin.web-monitor.check-ip-publik')) {
            $items[] = $this->link('IP Publik', 'signal', 'admin.web-monitor.check-ip-publik', ['admin.web-monitor.check-ip-publik']);
        }
        if ($user->hasPermission('admin.vidcon.data')) {
            $items[] = $this->link('Vidcon', 'video', 'admin.vidcon-data.index', ['admin.vidcon-data.*']);
        }
        if ($user->hasPermission('admin.google-aset-tik')) {
            $items[] = $this->link('Aset TIK', 'cpu', 'admin.google-aset-tik.dashboard', ['admin.google-aset-tik.*']);
        }

        if ($user->hasAnyPermission(['admin.splp.services', 'admin.splp.consumers', 'admin.splp.audit'])) {
            $children = [];
            if ($user->hasPermission('admin.splp.services')) {
                $children[] = $this->link('Layanan SPLP', null, 'admin.splp.services.index', ['admin.splp.services.*']);
            }
            if ($user->hasPermission('admin.splp.consumers')) {
                $children[] = $this->link('Konsumen SPLP', null, 'admin.splp.consumers.index', ['admin.splp.consumers.*']);
            }
            if ($user->hasPermission('admin.splp.audit')) {
                $children[] = $this->link('Audit Log SPLP', null, 'admin.splp.audit.index', ['admin.splp.audit.*']);
            }
            $items[] = $this->group('Integrasi (SPLP)', 'puzzle', $children);
        }

        if ($user->hasAnyPermission(['admin.dtsen.dashboard', 'admin.dtsen.variables', 'admin.dtsen.releases', 'admin.dtsen.wilayah'])) {
            $children = [];
            if ($user->hasPermission('admin.dtsen.dashboard')) {
                $children[] = $this->link('Dashboard DTSEN', null, 'admin.dtsen.dashboard', ['admin.dtsen.dashboard']);
            }
            if ($user->hasPermission('admin.dtsen.variables')) {
                $children[] = $this->link('Variabel DTSEN', null, 'admin.dtsen.variables.index', ['admin.dtsen.variables.*']);
            }
            if ($user->hasPermission('admin.dtsen.releases')) {
                $children[] = $this->link('Rilis DTSEN', null, 'admin.dtsen.releases.index', ['admin.dtsen.releases.*']);
            }
            if ($user->hasPermission('admin.dtsen.wilayah')) {
                $children[] = $this->link('Wilayah DTSEN', null, 'admin.dtsen.wilayah.index', ['admin.dtsen.wilayah.*']);
            }
            $items[] = $this->group('DTSEN', 'database', $children);
        }

        return $items;
    }

    private function peminjamanItems(User $user): array
    {
        $items = [];

        if ($user->hasPermission('op.tik.borrow.index')) {
            $items[] = $this->link('Peminjaman Saya', 'inbox', 'op.tik.borrow.index', ['op.tik.borrow.index', 'op.tik.borrow.show']);
        }
        if ($user->hasPermission('op.tik.borrow.create')) {
            $items[] = $this->link('Buat Peminjaman', 'plus', 'op.tik.borrow.create', ['op.tik.borrow.create']);
        }
        if ($user->hasPermission('admin.tik.assets')) {
            $items[] = $this->link('Inventaris Digital', 'cpu', 'admin.tik.assets.index', ['admin.tik.assets.*', 'admin.tik.categories.*']);
        }
        if ($user->hasPermission('admin.tik.borrow')) {
            $items[] = $this->link('Laporan Peminjaman', 'clipboard', 'admin.tik.borrow.index', ['admin.tik.borrow.*']);
        }
        if ($user->hasAnyPermission(['admin.schedule', 'op.tik.schedule'])) {
            $items[] = $this->link('Jadwal Video Konferensi', 'calendar', 'op.tik.schedule.index', ['op.tik.schedule.*']);
        }
        if ($user->hasPermission('admin.statistic')) {
            $items[] = $this->link('Statistik Video Konferensi', 'chart', 'op.tik.statistic.index', ['op.tik.statistic.*']);
        }
        if ($user->hasPermission('operator.vidcon')) {
            $items[] = $this->link('Pelaporan & Dokumentasi', 'camera', 'operator.vidcon.index', ['operator.vidcon.*']);
        }

        return $items;
    }

    private function sistemItems(User $user, bool $isAdmin): array
    {
        $items = [];

        if ($isAdmin) {
            $items[] = $this->group('Pengguna & Akses', 'users', [
                $this->link('Kelola Pengguna', null, 'admin.users', ['admin.users*']),
                $this->link('Kelola Peran (Role)', null, 'admin.roles.index', ['admin.roles*']),
                $this->link('Kelola Kewenangan', null, 'admin.role-permissions', ['admin.role-permissions*']),
                $this->link('Log Audit', null, 'admin.audit-logs.index', ['admin.audit-logs.*']),
            ]);
        }
        if ($user->hasPermission('admin.simpeg')) {
            $items[] = $this->link('Cek via SIMPEG', 'search', 'admin.simpeg.index', ['admin.simpeg.*']);
        }
        if ($user->hasPermission('admin.running-text')) {
            $items[] = $this->link('Running Text', 'megaphone', 'admin.running-text.index', ['admin.running-text.*']);
        }
        if ($isAdmin) {
            $items[] = $this->link('Manajemen API', 'key', 'admin.api-management.index', ['admin.api-management.*']);
        }
        if ($user->hasPermission('Kelola Knowledge Base AI')) {
            $items[] = $this->link('Knowledge Base AI', 'book', 'admin.konsultasi-ai.index', ['admin.konsultasi-ai.*']);
        }
        if ($user->hasPermission('Manajemen SLA')) {
            $items[] = $this->link('Manajemen SLA', 'clock', 'admin.sla.index', ['admin.sla.*']);
        }

        return $items;
    }

    // ------------------------------------------------------------------
    // Item helpers
    // ------------------------------------------------------------------

    private function section(string $key, ?string $label, array $items): array
    {
        // Grup tanpa anak (user hanya punya permission induk) tidak perlu ditampilkan.
        return ['key' => $key, 'label' => $label, 'items' => array_values(array_filter($items))];
    }

    /**
     * @param  array|bool  $active  pola nama route untuk request()->routeIs(), atau hasil yang sudah dihitung
     */
    private function link(string $label, ?string $icon, string $route, array|bool $active, int $badge = 0): array
    {
        return [
            'label'    => $label,
            'icon'     => $icon,
            'url'      => route($route),
            'active'   => is_bool($active) ? $active : request()->routeIs(...$active),
            'badge'    => $badge,
            'children' => [],
            'search'   => mb_strtolower($label),
        ];
    }

    /**
     * @param  array  $extraActive  pola route tambahan yang membuat grup terbuka walau tidak ada anak yang aktif
     */
    private function group(string $label, string $icon, array $children, int $badge = 0, array $extraActive = []): ?array
    {
        if (empty($children)) {
            return null;
        }

        $active = collect($children)->contains('active', true)
            || ($extraActive && request()->routeIs(...$extraActive));

        return [
            'label'    => $label,
            'icon'     => $icon,
            'url'      => $children[0]['url'],
            'active'   => $active,
            'badge'    => $badge,
            'children' => $children,
            'search'   => mb_strtolower($label . ' ' . collect($children)->pluck('label')->implode(' ')),
        ];
    }
}
