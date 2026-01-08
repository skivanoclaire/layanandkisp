<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update ALL permissions with correct display names and organized groups
        // NO DELETIONS - only updates

        $permissions = [
            // ===== ADMIN - ROLE MANAGEMENT =====
            ['name' => 'admin.roles.index', 'display_name' => 'Kelola Role - Lihat Daftar', 'group' => 'Admin - Role Management', 'order' => 1],
            ['name' => 'admin.roles.create', 'display_name' => 'Kelola Role - Tambah', 'group' => 'Admin - Role Management', 'order' => 2],
            ['name' => 'admin.roles.edit', 'display_name' => 'Kelola Role - Edit', 'group' => 'Admin - Role Management', 'order' => 3],
            ['name' => 'admin.roles.destroy', 'display_name' => 'Kelola Role - Hapus', 'group' => 'Admin - Role Management', 'order' => 4],
            ['name' => 'admin.role-permissions', 'display_name' => 'Kelola Kewenangan', 'group' => 'Admin - Role Management', 'order' => 5],

            // ===== ADMIN - DASHBOARD & USER MANAGEMENT =====
            ['name' => 'admin.dashboard', 'display_name' => 'Dashboard Admin', 'group' => 'Admin - Dashboard & User Management', 'order' => 1],
            ['name' => 'admin.users', 'display_name' => 'User Management', 'group' => 'Admin - Dashboard & User Management', 'order' => 2],
            ['name' => 'admin.simpeg', 'display_name' => 'Cek via SIMPEG', 'group' => 'Admin - Dashboard & User Management', 'order' => 3],

            // ===== ADMIN - LAYANAN DIGITAL =====
            ['name' => 'admin.permohonan', 'display_name' => 'Permohonan Manual - Unggah Surat', 'group' => 'Admin - Layanan Digital', 'order' => 1],
            ['name' => 'admin.email', 'display_name' => 'Permohonan Email - Manual Unggah Surat', 'group' => 'Admin - Layanan Digital', 'order' => 2],
            ['name' => 'admin.email.index', 'display_name' => 'Daftar Permohonan Email', 'group' => 'Admin - Layanan Digital', 'order' => 3],
            ['name' => 'admin.email.show', 'display_name' => 'Detail Permohonan Email', 'group' => 'Admin - Layanan Digital', 'order' => 4],
            ['name' => 'admin.email.update-status', 'display_name' => 'Update Status Email', 'group' => 'Admin - Layanan Digital', 'order' => 5],
            ['name' => 'admin.email-password-reset.index', 'display_name' => 'Kelola Reset Password Email', 'group' => 'Admin - Layanan Digital', 'order' => 6],
            ['name' => 'admin.subdomain.index', 'display_name' => 'Daftar Pendaftaran Subdomain', 'group' => 'Admin - Layanan Digital', 'order' => 7],
            ['name' => 'admin.subdomain.show', 'display_name' => 'Detail Subdomain', 'group' => 'Admin - Layanan Digital', 'order' => 8],
            ['name' => 'admin.subdomain.update-status', 'display_name' => 'Update Status Subdomain', 'group' => 'Admin - Layanan Digital', 'order' => 9],
            ['name' => 'admin.subdomain.name-change.index', 'display_name' => 'Daftar Perubahan Nama Subdomain', 'group' => 'Admin - Layanan Digital', 'order' => 10],
            ['name' => 'admin.subdomain.name-change.show', 'display_name' => 'Detail Perubahan Nama Subdomain', 'group' => 'Admin - Layanan Digital', 'order' => 11],
            ['name' => 'admin.subdomain.name-change.approve', 'display_name' => 'Setujui Perubahan Nama Subdomain', 'group' => 'Admin - Layanan Digital', 'order' => 12],
            ['name' => 'admin.subdomain.name-change.reject', 'display_name' => 'Tolak Perubahan Nama Subdomain', 'group' => 'Admin - Layanan Digital', 'order' => 13],
            ['name' => 'admin.subdomain.name-change.complete', 'display_name' => 'Eksekusi Perubahan Nama Subdomain', 'group' => 'Admin - Layanan Digital', 'order' => 14],
            ['name' => 'admin.rekomendasi.index', 'display_name' => 'Daftar Rekomendasi Aplikasi', 'group' => 'Admin - Layanan Digital', 'order' => 15],
            ['name' => 'admin.rekomendasi.show', 'display_name' => 'Detail Rekomendasi Aplikasi', 'group' => 'Admin - Layanan Digital', 'order' => 16],
            ['name' => 'admin.rekomendasi.update-status', 'display_name' => 'Update Status Rekomendasi', 'group' => 'Admin - Layanan Digital', 'order' => 17],

            // ===== ADMIN - VIDEO CONFERENCE =====
            ['name' => 'admin.vidcon.index', 'display_name' => 'Daftar Permohonan Video Conference', 'group' => 'Admin - Video Conference', 'order' => 1],
            ['name' => 'admin.schedule', 'display_name' => 'Jadwal Video Konferensi', 'group' => 'Admin - Video Conference', 'order' => 2],
            ['name' => 'admin.vidcon.data', 'display_name' => 'Master Data Vidcon', 'group' => 'Admin - Video Conference', 'order' => 3],
            ['name' => 'admin.statistic', 'display_name' => 'Statistik Video Konferensi', 'group' => 'Admin - Video Conference', 'order' => 4],

            // ===== ADMIN - TTE (TANDA TANGAN ELEKTRONIK) =====
            ['name' => 'Kelola Bantuan TTE', 'display_name' => 'Kelola Pendampingan TTE', 'group' => 'Admin - TTE (Tanda Tangan Elektronik)', 'order' => 1],
            ['name' => 'Kelola Registrasi TTE', 'display_name' => 'Kelola Pendaftaran Akun TTE', 'group' => 'Admin - TTE (Tanda Tangan Elektronik)', 'order' => 2],
            ['name' => 'Kelola Reset Passphrase TTE', 'display_name' => 'Kelola Reset Passphrase TTE', 'group' => 'Admin - TTE (Tanda Tangan Elektronik)', 'order' => 3],
            ['name' => 'admin.tte.passphrase-reset', 'display_name' => 'Reset Passphrase TTE', 'group' => 'Admin - TTE (Tanda Tangan Elektronik)', 'order' => 3],
            ['name' => 'Kelola Pembaruan Sertifikat TTE', 'display_name' => 'Kelola Pembaruan Sertifikat TTE', 'group' => 'Admin - TTE (Tanda Tangan Elektronik)', 'order' => 4],

            // ===== ADMIN - INTERNET & KONEKTIVITAS =====
            ['name' => 'Kelola Laporan Gangguan Internet', 'display_name' => 'Kelola Laporan Gangguan Internet', 'group' => 'Admin - Internet & Konektivitas', 'order' => 1],
            ['name' => 'Kelola Starlink Jelajah', 'display_name' => 'Kelola Starlink Jelajah', 'group' => 'Admin - Internet & Konektivitas', 'order' => 2],

            // ===== ADMIN - VPN & JARINGAN PRIVAT =====
            ['name' => 'Kelola Pendaftaran VPN', 'display_name' => 'Kelola Pendaftaran VPN', 'group' => 'Admin - VPN & Jaringan Privat', 'order' => 1],
            ['name' => 'Kelola Reset Akun VPN', 'display_name' => 'Kelola Reset Akun VPN', 'group' => 'Admin - VPN & Jaringan Privat', 'order' => 2],
            ['name' => 'Kelola Akses JIP PDNS', 'display_name' => 'Kelola Akses JIP PDNS', 'group' => 'Admin - VPN & Jaringan Privat', 'order' => 3],

            // ===== ADMIN - PUSAT DATA/KOMPUTASI =====
            ['name' => 'Kelola Kunjungan/Colocation', 'display_name' => 'Kelola Kunjungan/Colocation Data Center', 'group' => 'Admin - Pusat Data/Komputasi', 'order' => 1],
            ['name' => 'Kelola VPS/VM', 'display_name' => 'Kelola VPS/VM', 'group' => 'Admin - Pusat Data/Komputasi', 'order' => 2],
            ['name' => 'Kelola Backup', 'display_name' => 'Kelola Backup', 'group' => 'Admin - Pusat Data/Komputasi', 'order' => 3],
            ['name' => 'Kelola Cloud Storage', 'display_name' => 'Kelola Cloud Storage', 'group' => 'Admin - Pusat Data/Komputasi', 'order' => 4],

            // ===== ADMIN - TIK & INVENTARIS =====
            ['name' => 'admin.tik.assets', 'display_name' => 'Inventaris Digital', 'group' => 'Admin - TIK & Inventaris', 'order' => 1],
            ['name' => 'admin.tik.borrow', 'display_name' => 'Laporan Peminjaman', 'group' => 'Admin - TIK & Inventaris', 'order' => 2],

            // ===== ADMIN - MASTER DATA =====
            ['name' => 'admin.unit-kerja', 'display_name' => 'Master Data Unit Kerja', 'group' => 'Admin - Master Data', 'order' => 1],
            ['name' => 'admin.web-monitor', 'display_name' => 'Master Data Subdomain', 'group' => 'Admin - Master Data', 'order' => 2],
            ['name' => 'Manajemen Subdomain Terpadu', 'display_name' => 'Kelola Subdomain Terpadu', 'group' => 'Admin - Master Data', 'order' => 3],
            ['name' => 'Kelola Master Data Email', 'display_name' => 'Master Data Email', 'group' => 'Admin - Master Data', 'order' => 4],

            // ===== USER - DASHBOARD & PROFILE =====
            ['name' => 'user.dashboard', 'display_name' => 'Dashboard Pengguna', 'group' => 'User - Dashboard & Profile', 'order' => 1],
            ['name' => 'user.profile', 'display_name' => 'Profile Pengguna', 'group' => 'User - Dashboard & Profile', 'order' => 2],

            // ===== USER - LAYANAN DIGITAL =====
            ['name' => 'user.permohonan', 'display_name' => 'Permohonan Manual - Unggah Surat', 'group' => 'User - Layanan Digital', 'order' => 1],
            ['name' => 'user.email.index', 'display_name' => 'Permohonan Email', 'group' => 'User - Layanan Digital', 'order' => 2],
            ['name' => 'user.email.create', 'display_name' => 'Buat Permohonan Email', 'group' => 'User - Layanan Digital', 'order' => 3],
            ['name' => 'user.email.show', 'display_name' => 'Detail Email Saya', 'group' => 'User - Layanan Digital', 'order' => 4],
            ['name' => 'user.email-password-reset.index', 'display_name' => 'Reset Password Email', 'group' => 'User - Layanan Digital', 'order' => 5],
            ['name' => 'user.email-password-reset.create', 'display_name' => 'Buat Reset Password Email', 'group' => 'User - Layanan Digital', 'order' => 6],
            ['name' => 'user.subdomain.index', 'display_name' => 'Pendaftaran Subdomain', 'group' => 'User - Layanan Digital', 'order' => 7],
            ['name' => 'user.subdomain.create', 'display_name' => 'Buat Permohonan Subdomain', 'group' => 'User - Layanan Digital', 'order' => 8],
            ['name' => 'user.subdomain.show', 'display_name' => 'Detail Subdomain Saya', 'group' => 'User - Layanan Digital', 'order' => 9],
            ['name' => 'user.subdomain.name-change.index', 'display_name' => 'Perubahan Nama Subdomain', 'group' => 'User - Layanan Digital', 'order' => 10],
            ['name' => 'user.subdomain.name-change.create', 'display_name' => 'Ajukan Perubahan Nama Subdomain', 'group' => 'User - Layanan Digital', 'order' => 11],
            ['name' => 'user.subdomain.name-change.show', 'display_name' => 'Detail Perubahan Nama Subdomain', 'group' => 'User - Layanan Digital', 'order' => 12],
            ['name' => 'user.rekomendasi.index', 'display_name' => 'Rekomendasi Aplikasi', 'group' => 'User - Layanan Digital', 'order' => 13],
            ['name' => 'user.rekomendasi.create', 'display_name' => 'Buat Rekomendasi Aplikasi', 'group' => 'User - Layanan Digital', 'order' => 14],
            ['name' => 'Akses Video Conference', 'display_name' => 'Akses Video Conference', 'group' => 'User - Layanan Digital', 'order' => 15],
            ['name' => 'Akses Konsultasi SPBE AI', 'display_name' => 'Akses Konsultasi SPBE AI', 'group' => 'User - Layanan Digital', 'order' => 16],

            // ===== USER - TTE (TANDA TANGAN ELEKTRONIK) =====
            ['name' => 'Akses Bantuan TTE', 'display_name' => 'Akses Pendampingan Aktivasi dan Penggunaan TTE', 'group' => 'User - TTE (Tanda Tangan Elektronik)', 'order' => 1],
            ['name' => 'Akses Registrasi TTE', 'display_name' => 'Akses Pendaftaran Akun Baru TTE', 'group' => 'User - TTE (Tanda Tangan Elektronik)', 'order' => 2],
            ['name' => 'Akses Reset Passphrase TTE', 'display_name' => 'Akses Permohonan Reset Passphrase TTE', 'group' => 'User - TTE (Tanda Tangan Elektronik)', 'order' => 3],
            ['name' => 'Akses Pembaruan Sertifikat TTE', 'display_name' => 'Akses Pembaruan Sertifikat TTE', 'group' => 'User - TTE (Tanda Tangan Elektronik)', 'order' => 4],

            // ===== USER - INTERNET & KONEKTIVITAS =====
            ['name' => 'Akses Lapor Gangguan Internet', 'display_name' => 'Akses Lapor Gangguan Internet', 'group' => 'User - Internet & Konektivitas', 'order' => 1],
            ['name' => 'Akses Starlink Jelajah', 'display_name' => 'Akses Starlink Jelajah', 'group' => 'User - Internet & Konektivitas', 'order' => 2],

            // ===== USER - VPN & JARINGAN PRIVAT =====
            ['name' => 'Akses Pendaftaran VPN', 'display_name' => 'Akses Pendaftaran VPN', 'group' => 'User - VPN & Jaringan Privat', 'order' => 1],
            ['name' => 'Akses Reset Akun VPN', 'display_name' => 'Akses Reset Akun VPN', 'group' => 'User - VPN & Jaringan Privat', 'order' => 2],
            ['name' => 'Akses JIP PDNS', 'display_name' => 'Akses JIP PDNS', 'group' => 'User - VPN & Jaringan Privat', 'order' => 3],

            // ===== USER - PUSAT DATA/KOMPUTASI =====
            ['name' => 'Akses Kunjungan/Colocation Data Center', 'display_name' => 'Akses Kunjungan/Colocation Data Center', 'group' => 'User - Pusat Data/Komputasi', 'order' => 1],
            ['name' => 'Akses VPS/VM', 'display_name' => 'Akses VPS/VM', 'group' => 'User - Pusat Data/Komputasi', 'order' => 2],
            ['name' => 'Akses Backup', 'display_name' => 'Akses Backup', 'group' => 'User - Pusat Data/Komputasi', 'order' => 3],
            ['name' => 'Akses Cloud Storage', 'display_name' => 'Akses Cloud Storage', 'group' => 'User - Pusat Data/Komputasi', 'order' => 4],

            // ===== OPERATOR - VIDCON & TIK =====
            ['name' => 'op.tik.borrow.index', 'display_name' => 'Peminjaman Saya', 'group' => 'Operator - Vidcon & TIK', 'order' => 1],
            ['name' => 'op.tik.borrow.create', 'display_name' => 'Buat Peminjaman', 'group' => 'Operator - Vidcon & TIK', 'order' => 2],
            ['name' => 'op.tik.schedule', 'display_name' => 'Jadwal Vidcon', 'group' => 'Operator - Vidcon & TIK', 'order' => 3],
        ];

        // Update each permission
        foreach ($permissions as $perm) {
            DB::table('permissions')
                ->where('name', $perm['name'])
                ->update([
                    'display_name' => $perm['display_name'],
                    'group' => $perm['group'],
                    'order' => $perm['order']
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback karena hanya update
    }
};
