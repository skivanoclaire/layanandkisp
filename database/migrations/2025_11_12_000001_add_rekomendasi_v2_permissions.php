<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            // User Permissions - Group: "User - Layanan Digital"
            [
                'name' => 'user.rekomendasi.usulan.create',
                'display_name' => 'Ajukan Usulan Rekomendasi Aplikasi',
                'group' => 'User - Layanan Digital',
                'order' => 110,
                'description' => 'Membuat usulan rekomendasi aplikasi baru',
                'route_name' => 'user.rekomendasi.usulan.create',
            ],
            [
                'name' => 'user.rekomendasi.usulan.show',
                'display_name' => 'Lihat Usulan Rekomendasi Sendiri',
                'group' => 'User - Layanan Digital',
                'order' => 111,
                'description' => 'Melihat detail usulan rekomendasi milik sendiri',
                'route_name' => 'user.rekomendasi.usulan.show',
            ],
            [
                'name' => 'user.rekomendasi.usulan.edit',
                'display_name' => 'Edit Usulan Rekomendasi',
                'group' => 'User - Layanan Digital',
                'order' => 112,
                'description' => 'Mengedit usulan rekomendasi (draft/revisi)',
                'route_name' => 'user.rekomendasi.usulan.edit',
            ],
            [
                'name' => 'user.rekomendasi.dokumen.upload',
                'display_name' => 'Upload Dokumen Rekomendasi',
                'group' => 'User - Layanan Digital',
                'order' => 113,
                'description' => 'Upload dokumen pendukung rekomendasi',
                'route_name' => 'user.rekomendasi.usulan.dokumen.upload',
            ],
            [
                'name' => 'user.rekomendasi.dokumen.download',
                'display_name' => 'Download Dokumen Rekomendasi',
                'group' => 'User - Layanan Digital',
                'order' => 114,
                'description' => 'Download dokumen rekomendasi milik sendiri',
                'route_name' => 'user.rekomendasi.usulan.dokumen.download',
            ],
            [
                'name' => 'user.rekomendasi.fase.update',
                'display_name' => 'Update Fase Pengembangan',
                'group' => 'User - Layanan Digital',
                'order' => 115,
                'description' => 'Update progress fase pengembangan aplikasi',
                'route_name' => 'user.rekomendasi.fase.index',
            ],
            [
                'name' => 'user.rekomendasi.evaluasi.create',
                'display_name' => 'Buat Evaluasi Aplikasi',
                'group' => 'User - Layanan Digital',
                'order' => 116,
                'description' => 'Membuat laporan evaluasi aplikasi',
                'route_name' => 'user.rekomendasi.evaluasi.create',
            ],

            // Admin Permissions - Group: "Admin - Layanan Digital"
            [
                'name' => 'admin.rekomendasi.verifikasi.index',
                'display_name' => 'Verifikasi Usulan Rekomendasi',
                'group' => 'Admin - Layanan Digital',
                'order' => 210,
                'description' => 'Melihat antrian verifikasi usulan rekomendasi',
                'route_name' => 'admin.rekomendasi.verifikasi.index',
            ],
            [
                'name' => 'admin.rekomendasi.verifikasi.show',
                'display_name' => 'Detail Verifikasi Rekomendasi',
                'group' => 'Admin - Layanan Digital',
                'order' => 211,
                'description' => 'Melihat detail usulan untuk verifikasi',
                'route_name' => 'admin.rekomendasi.verifikasi.show',
            ],
            [
                'name' => 'admin.rekomendasi.verifikasi.approve',
                'display_name' => 'Setujui Usulan Rekomendasi',
                'group' => 'Admin - Layanan Digital',
                'order' => 212,
                'description' => 'Menyetujui usulan rekomendasi',
                'route_name' => 'admin.rekomendasi.verifikasi.approve',
            ],
            [
                'name' => 'admin.rekomendasi.verifikasi.reject',
                'display_name' => 'Tolak Usulan Rekomendasi',
                'group' => 'Admin - Layanan Digital',
                'order' => 213,
                'description' => 'Menolak usulan rekomendasi',
                'route_name' => 'admin.rekomendasi.verifikasi.reject',
            ],
            [
                'name' => 'admin.rekomendasi.verifikasi.revisi',
                'display_name' => 'Minta Revisi Usulan',
                'group' => 'Admin - Layanan Digital',
                'order' => 214,
                'description' => 'Meminta revisi pada usulan rekomendasi',
                'route_name' => 'admin.rekomendasi.verifikasi.request-revision',
            ],
            [
                'name' => 'admin.rekomendasi.surat.generate',
                'display_name' => 'Generate Surat Rekomendasi',
                'group' => 'Admin - Layanan Digital',
                'order' => 215,
                'description' => 'Membuat surat rekomendasi ke Kementerian',
                'route_name' => 'admin.rekomendasi.surat.create',
            ],
            [
                'name' => 'admin.rekomendasi.surat.upload',
                'display_name' => 'Upload Surat TTD',
                'group' => 'Admin - Layanan Digital',
                'order' => 216,
                'description' => 'Upload surat rekomendasi yang sudah ditandatangani',
                'route_name' => 'admin.rekomendasi.surat.upload-signed',
            ],
            [
                'name' => 'admin.rekomendasi.surat.send',
                'display_name' => 'Kirim Surat ke Kementerian',
                'group' => 'Admin - Layanan Digital',
                'order' => 217,
                'description' => 'Menandai surat sudah dikirim ke Kementerian',
                'route_name' => 'admin.rekomendasi.surat.send',
            ],
            [
                'name' => 'admin.rekomendasi.kementerian.update',
                'display_name' => 'Update Status Kementerian',
                'group' => 'Admin - Layanan Digital',
                'order' => 218,
                'description' => 'Update status persetujuan dari Kementerian',
                'route_name' => 'admin.rekomendasi.surat.ministry-status',
            ],
            [
                'name' => 'admin.rekomendasi.monitoring.index',
                'display_name' => 'Monitoring Rekomendasi Aplikasi',
                'group' => 'Admin - Layanan Digital',
                'order' => 219,
                'description' => 'Monitoring semua rekomendasi aplikasi',
                'route_name' => 'admin.rekomendasi.monitoring.dashboard',
            ],
            [
                'name' => 'admin.rekomendasi.fase.monitor',
                'display_name' => 'Monitor Fase Pengembangan',
                'group' => 'Admin - Layanan Digital',
                'order' => 220,
                'description' => 'Monitoring fase pengembangan aplikasi',
                'route_name' => 'admin.rekomendasi.monitoring.dashboard',
            ],
            [
                'name' => 'admin.rekomendasi.evaluasi.review',
                'display_name' => 'Review Evaluasi Aplikasi',
                'group' => 'Admin - Layanan Digital',
                'order' => 221,
                'description' => 'Mereview laporan evaluasi aplikasi',
                'route_name' => 'admin.rekomendasi.monitoring.dashboard',
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
                'group' => $permission['group'],
                'order' => $permission['order'],
                'description' => $permission['description'],
                'route_name' => $permission['route_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissionNames = [
            'user.rekomendasi.usulan.create',
            'user.rekomendasi.usulan.show',
            'user.rekomendasi.usulan.edit',
            'user.rekomendasi.dokumen.upload',
            'user.rekomendasi.dokumen.download',
            'user.rekomendasi.fase.update',
            'user.rekomendasi.evaluasi.create',
            'admin.rekomendasi.verifikasi.index',
            'admin.rekomendasi.verifikasi.show',
            'admin.rekomendasi.verifikasi.approve',
            'admin.rekomendasi.verifikasi.reject',
            'admin.rekomendasi.verifikasi.revisi',
            'admin.rekomendasi.surat.generate',
            'admin.rekomendasi.surat.upload',
            'admin.rekomendasi.surat.send',
            'admin.rekomendasi.kementerian.update',
            'admin.rekomendasi.monitoring.index',
            'admin.rekomendasi.fase.monitor',
            'admin.rekomendasi.evaluasi.review',
        ];

        DB::table('permissions')->whereIn('name', $permissionNames)->delete();
    }
};
