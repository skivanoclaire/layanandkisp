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
        // 1. Delete old/duplicate permissions (IDs 46-86)
        DB::table('permissions')->whereIn('id', range(46, 86))->delete();

        // 2. Update display names to match sidebar labels
        $displayNameUpdates = [
            'admin.vidcon.data' => 'Master Data Vidcon',
            'admin.schedule' => 'Jadwal Video Konferensi',
            'admin.statistic' => 'Statistik Video Konferensi',
            'admin.tik.assets' => 'Inventaris Digital',
            'admin.tik.borrow' => 'Laporan Peminjaman',
            'admin.unit-kerja' => 'Master Data Unit Kerja',
            'admin.web-monitor' => 'Master Data Subdomain',
            'admin.email' => 'Permohonan Email - Manual Unggah Surat',
            'admin.permohonan' => 'Permohonan Manual - Unggah Surat',
            'admin.email.index' => 'Permohonan Email',
            'admin.email.show' => 'Detail Permohonan Email',
            'admin.email.update-status' => 'Update Status Email',
            'admin.subdomain.index' => 'Pendaftaran Subdomain Baru',
            'admin.subdomain.show' => 'Detail Subdomain',
            'admin.subdomain.update-status' => 'Update Status Subdomain',
            'admin.subdomain.name-change.index' => 'Perubahan Nama Subdomain',
            'admin.subdomain.name-change.show' => 'Detail Perubahan Nama Subdomain',
            'admin.subdomain.name-change.approve' => 'Setujui Perubahan Nama Subdomain',
            'admin.subdomain.name-change.reject' => 'Tolak Perubahan Nama Subdomain',
            'admin.subdomain.name-change.complete' => 'Eksekusi Perubahan Nama Subdomain',
            'admin.rekomendasi.index' => 'Rekomendasi Aplikasi',
            'admin.rekomendasi.show' => 'Detail Rekomendasi Aplikasi',
            'admin.rekomendasi.update-status' => 'Update Status Rekomendasi',
            'admin.vidcon.index' => 'Permohonan Video Conference',
            'admin.tte.passphrase-reset' => 'Reset Passphrase TTE',
            'admin.email-password-reset.index' => 'Reset Password Email',
            'admin.dashboard' => 'Dashboard Admin',
            'admin.users' => 'User Management',
            'admin.simpeg' => 'Cek via SIMPEG',
            'admin.roles.index' => 'Kelola Role - Lihat Daftar',
            'admin.roles.create' => 'Kelola Role - Tambah',
            'admin.roles.edit' => 'Kelola Role - Edit',
            'admin.roles.destroy' => 'Kelola Role - Hapus',
            'admin.role-permissions' => 'Kelola Kewenangan',
            'user.dashboard' => 'Dashboard Pengguna',
            'user.profile' => 'Profile Pengguna',
            'user.email.index' => 'Permohonan Email',
            'user.email.create' => 'Buat Permohonan Email',
            'user.email.show' => 'Detail Email Saya',
            'user.subdomain.index' => 'Pendaftaran Subdomain Baru',
            'user.subdomain.create' => 'Buat Permohonan Subdomain',
            'user.subdomain.show' => 'Detail Subdomain Saya',
            'user.subdomain.name-change.index' => 'Perubahan Nama Subdomain',
            'user.subdomain.name-change.create' => 'Ajukan Perubahan Nama Subdomain',
            'user.subdomain.name-change.show' => 'Detail Perubahan Nama Subdomain',
            'user.rekomendasi.index' => 'Rekomendasi Aplikasi',
            'user.rekomendasi.create' => 'Buat Rekomendasi Aplikasi',
            'user.email-password-reset.index' => 'Reset Password Email',
            'user.email-password-reset.create' => 'Buat Reset Password Email',
            'op.tik.borrow.index' => 'Peminjaman Saya',
            'op.tik.borrow.create' => 'Buat Peminjaman',
            'op.tik.schedule' => 'Jadwal Vidcon',
        ];

        foreach ($displayNameUpdates as $name => $displayName) {
            DB::table('permissions')->where('name', $name)->update(['display_name' => $displayName]);
        }

        // 3. Reorganize groups and set order
        $groupUpdates = [
            // Admin - Role Management
            ['name' => 'admin.roles.index', 'group' => 'Admin - Role Management', 'order' => 1],
            ['name' => 'admin.roles.create', 'group' => 'Admin - Role Management', 'order' => 2],
            ['name' => 'admin.roles.edit', 'group' => 'Admin - Role Management', 'order' => 3],
            ['name' => 'admin.roles.destroy', 'group' => 'Admin - Role Management', 'order' => 4],
            ['name' => 'admin.role-permissions', 'group' => 'Admin - Role Management', 'order' => 5],

            // Admin - Dashboard & User Management
            ['name' => 'admin.dashboard', 'group' => 'Admin - Dashboard & User Management', 'order' => 1],
            ['name' => 'admin.users', 'group' => 'Admin - Dashboard & User Management', 'order' => 2],
            ['name' => 'admin.simpeg', 'group' => 'Admin - Dashboard & User Management', 'order' => 3],

            // Admin - Layanan Digital
            ['name' => 'admin.permohonan', 'group' => 'Admin - Layanan Digital', 'order' => 1],
            ['name' => 'admin.email', 'group' => 'Admin - Layanan Digital', 'order' => 2],
            ['name' => 'admin.email.index', 'group' => 'Admin - Layanan Digital', 'order' => 3],
            ['name' => 'admin.email.show', 'group' => 'Admin - Layanan Digital', 'order' => 4],
            ['name' => 'admin.email.update-status', 'group' => 'Admin - Layanan Digital', 'order' => 5],
            ['name' => 'admin.email-password-reset.index', 'group' => 'Admin - Layanan Digital', 'order' => 6],
            ['name' => 'admin.subdomain.index', 'group' => 'Admin - Layanan Digital', 'order' => 7],
            ['name' => 'admin.subdomain.show', 'group' => 'Admin - Layanan Digital', 'order' => 8],
            ['name' => 'admin.subdomain.update-status', 'group' => 'Admin - Layanan Digital', 'order' => 9],
            ['name' => 'admin.subdomain.name-change.index', 'group' => 'Admin - Layanan Digital', 'order' => 10],
            ['name' => 'admin.subdomain.name-change.show', 'group' => 'Admin - Layanan Digital', 'order' => 11],
            ['name' => 'admin.subdomain.name-change.approve', 'group' => 'Admin - Layanan Digital', 'order' => 12],
            ['name' => 'admin.subdomain.name-change.reject', 'group' => 'Admin - Layanan Digital', 'order' => 13],
            ['name' => 'admin.subdomain.name-change.complete', 'group' => 'Admin - Layanan Digital', 'order' => 14],
            ['name' => 'admin.rekomendasi.index', 'group' => 'Admin - Layanan Digital', 'order' => 15],
            ['name' => 'admin.rekomendasi.show', 'group' => 'Admin - Layanan Digital', 'order' => 16],
            ['name' => 'admin.rekomendasi.update-status', 'group' => 'Admin - Layanan Digital', 'order' => 17],

            // Admin - Video Conference
            ['name' => 'admin.vidcon.index', 'group' => 'Admin - Video Conference', 'order' => 1],
            ['name' => 'admin.schedule', 'group' => 'Admin - Video Conference', 'order' => 2],
            ['name' => 'admin.vidcon.data', 'group' => 'Admin - Video Conference', 'order' => 3],
            ['name' => 'admin.statistic', 'group' => 'Admin - Video Conference', 'order' => 4],

            // Admin - TTE (Tanda Tangan Elektronik)
            ['name' => 'admin.tte.passphrase-reset', 'group' => 'Admin - TTE (Tanda Tangan Elektronik)', 'order' => 1],

            // Admin - TIK & Inventaris
            ['name' => 'admin.tik.assets', 'group' => 'Admin - TIK & Inventaris', 'order' => 1],
            ['name' => 'admin.tik.borrow', 'group' => 'Admin - TIK & Inventaris', 'order' => 2],

            // Admin - Master Data
            ['name' => 'admin.unit-kerja', 'group' => 'Admin - Master Data', 'order' => 1],
            ['name' => 'admin.web-monitor', 'group' => 'Admin - Master Data', 'order' => 2],

            // User - Dashboard & Profile
            ['name' => 'user.dashboard', 'group' => 'User - Dashboard & Profile', 'order' => 1],
            ['name' => 'user.profile', 'group' => 'User - Dashboard & Profile', 'order' => 2],

            // User - Layanan Digital
            ['name' => 'user.email.index', 'group' => 'User - Layanan Digital', 'order' => 1],
            ['name' => 'user.email.create', 'group' => 'User - Layanan Digital', 'order' => 2],
            ['name' => 'user.email.show', 'group' => 'User - Layanan Digital', 'order' => 3],
            ['name' => 'user.email-password-reset.index', 'group' => 'User - Layanan Digital', 'order' => 4],
            ['name' => 'user.email-password-reset.create', 'group' => 'User - Layanan Digital', 'order' => 5],
            ['name' => 'user.subdomain.index', 'group' => 'User - Layanan Digital', 'order' => 6],
            ['name' => 'user.subdomain.create', 'group' => 'User - Layanan Digital', 'order' => 7],
            ['name' => 'user.subdomain.show', 'group' => 'User - Layanan Digital', 'order' => 8],
            ['name' => 'user.subdomain.name-change.index', 'group' => 'User - Layanan Digital', 'order' => 9],
            ['name' => 'user.subdomain.name-change.create', 'group' => 'User - Layanan Digital', 'order' => 10],
            ['name' => 'user.subdomain.name-change.show', 'group' => 'User - Layanan Digital', 'order' => 11],
            ['name' => 'user.rekomendasi.index', 'group' => 'User - Layanan Digital', 'order' => 12],
            ['name' => 'user.rekomendasi.create', 'group' => 'User - Layanan Digital', 'order' => 13],

            // Operator - Vidcon & TIK
            ['name' => 'op.tik.borrow.index', 'group' => 'Operator - Vidcon & TIK', 'order' => 1],
            ['name' => 'op.tik.borrow.create', 'group' => 'Operator - Vidcon & TIK', 'order' => 2],
            ['name' => 'op.tik.schedule', 'group' => 'Operator - Vidcon & TIK', 'order' => 3],
        ];

        foreach ($groupUpdates as $update) {
            DB::table('permissions')
                ->where('name', $update['name'])
                ->update([
                    'group' => $update['group'],
                    'order' => $update['order']
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is non-reversible as it deletes old data
        // If you need to rollback, restore from backup
    }
};
