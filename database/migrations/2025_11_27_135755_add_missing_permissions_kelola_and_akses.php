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
        // Add missing permissions that were deleted by previous migration
        // These are permissions with "Kelola" and "Akses" naming conventions

        $newPermissions = [
            // Admin - TTE (tambahan)
            ['name' => 'Kelola Bantuan TTE', 'display_name' => 'Kelola Pendampingan TTE', 'group' => 'Admin - TTE (Tanda Tangan Elektronik)', 'order' => 1],
            ['name' => 'Kelola Registrasi TTE', 'display_name' => 'Kelola Pendaftaran Akun TTE', 'group' => 'Admin - TTE (Tanda Tangan Elektronik)', 'order' => 2],
            ['name' => 'Kelola Reset Passphrase TTE', 'display_name' => 'Kelola Reset Passphrase TTE', 'group' => 'Admin - TTE (Tanda Tangan Elektronik)', 'order' => 4],
            ['name' => 'Kelola Pembaruan Sertifikat TTE', 'display_name' => 'Kelola Pembaruan Sertifikat TTE', 'group' => 'Admin - TTE (Tanda Tangan Elektronik)', 'order' => 5],

            // Admin - Internet
            ['name' => 'Kelola Laporan Gangguan Internet', 'display_name' => 'Kelola Laporan Gangguan Internet', 'group' => 'Admin - Internet & Konektivitas', 'order' => 1],
            ['name' => 'Kelola Starlink Jelajah', 'display_name' => 'Kelola Starlink Jelajah', 'group' => 'Admin - Internet & Konektivitas', 'order' => 2],

            // Admin - VPN
            ['name' => 'Kelola Pendaftaran VPN', 'display_name' => 'Kelola Pendaftaran VPN', 'group' => 'Admin - VPN & Jaringan Privat', 'order' => 1],
            ['name' => 'Kelola Reset Akun VPN', 'display_name' => 'Kelola Reset Akun VPN', 'group' => 'Admin - VPN & Jaringan Privat', 'order' => 2],
            ['name' => 'Kelola Akses JIP PDNS', 'display_name' => 'Kelola Akses JIP PDNS', 'group' => 'Admin - VPN & Jaringan Privat', 'order' => 3],

            // Admin - Pusat Data/Komputasi
            ['name' => 'Kelola Kunjungan/Colocation', 'display_name' => 'Kelola Kunjungan/Colocation Data Center', 'group' => 'Admin - Pusat Data/Komputasi', 'order' => 1],
            ['name' => 'Kelola VPS/VM', 'display_name' => 'Kelola VPS/VM', 'group' => 'Admin - Pusat Data/Komputasi', 'order' => 2],
            ['name' => 'Kelola Backup', 'display_name' => 'Kelola Backup', 'group' => 'Admin - Pusat Data/Komputasi', 'order' => 3],
            ['name' => 'Kelola Cloud Storage', 'display_name' => 'Kelola Cloud Storage', 'group' => 'Admin - Pusat Data/Komputasi', 'order' => 4],

            // Admin - Master Data (tambahan)
            ['name' => 'Manajemen Subdomain Terpadu', 'display_name' => 'Kelola Subdomain Terpadu', 'group' => 'Admin - Master Data', 'order' => 3],
            ['name' => 'Kelola Master Data Email', 'display_name' => 'Master Data Email', 'group' => 'Admin - Master Data', 'order' => 4],

            // User - Layanan Digital (tambahan)
            ['name' => 'Akses Video Conference', 'display_name' => 'Akses Video Conference', 'group' => 'User - Layanan Digital', 'order' => 15],
            ['name' => 'Akses Konsultasi SPBE AI', 'display_name' => 'Akses Konsultasi SPBE AI', 'group' => 'User - Layanan Digital', 'order' => 16],

            // User - TTE
            ['name' => 'Akses Bantuan TTE', 'display_name' => 'Akses Pendampingan Aktivasi dan Penggunaan TTE', 'group' => 'User - TTE (Tanda Tangan Elektronik)', 'order' => 1],
            ['name' => 'Akses Registrasi TTE', 'display_name' => 'Akses Pendaftaran Akun Baru TTE', 'group' => 'User - TTE (Tanda Tangan Elektronik)', 'order' => 2],
            ['name' => 'Akses Reset Passphrase TTE', 'display_name' => 'Akses Permohonan Reset Passphrase TTE', 'group' => 'User - TTE (Tanda Tangan Elektronik)', 'order' => 3],
            ['name' => 'Akses Pembaruan Sertifikat TTE', 'display_name' => 'Akses Pembaruan Sertifikat TTE', 'group' => 'User - TTE (Tanda Tangan Elektronik)', 'order' => 4],

            // User - Internet
            ['name' => 'Akses Lapor Gangguan Internet', 'display_name' => 'Akses Lapor Gangguan Internet', 'group' => 'User - Internet & Konektivitas', 'order' => 1],
            ['name' => 'Akses Starlink Jelajah', 'display_name' => 'Akses Starlink Jelajah', 'group' => 'User - Internet & Konektivitas', 'order' => 2],

            // User - VPN
            ['name' => 'Akses Pendaftaran VPN', 'display_name' => 'Akses Pendaftaran VPN', 'group' => 'User - VPN & Jaringan Privat', 'order' => 1],
            ['name' => 'Akses Reset Akun VPN', 'display_name' => 'Akses Reset Akun VPN', 'group' => 'User - VPN & Jaringan Privat', 'order' => 2],
            ['name' => 'Akses JIP PDNS', 'display_name' => 'Akses JIP PDNS', 'group' => 'User - VPN & Jaringan Privat', 'order' => 3],

            // User - Pusat Data/Komputasi
            ['name' => 'Akses Kunjungan/Colocation Data Center', 'display_name' => 'Akses Kunjungan/Colocation Data Center', 'group' => 'User - Pusat Data/Komputasi', 'order' => 1],
            ['name' => 'Akses VPS/VM', 'display_name' => 'Akses VPS/VM', 'group' => 'User - Pusat Data/Komputasi', 'order' => 2],
            ['name' => 'Akses Backup', 'display_name' => 'Akses Backup', 'group' => 'User - Pusat Data/Komputasi', 'order' => 3],
            ['name' => 'Akses Cloud Storage', 'display_name' => 'Akses Cloud Storage', 'group' => 'User - Pusat Data/Komputasi', 'order' => 4],
        ];

        foreach ($newPermissions as $perm) {
            // Check if permission already exists
            $exists = DB::table('permissions')->where('name', $perm['name'])->exists();

            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $perm['name'],
                    'display_name' => $perm['display_name'],
                    'group' => $perm['group'],
                    'order' => $perm['order'],
                    'description' => null,
                    'route_name' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the added permissions
        $permissionNames = [
            'Kelola Bantuan TTE',
            'Kelola Registrasi TTE',
            'Kelola Reset Passphrase TTE',
            'Kelola Pembaruan Sertifikat TTE',
            'Kelola Laporan Gangguan Internet',
            'Kelola Starlink Jelajah',
            'Kelola Pendaftaran VPN',
            'Kelola Reset Akun VPN',
            'Kelola Akses JIP PDNS',
            'Kelola Kunjungan/Colocation',
            'Kelola VPS/VM',
            'Kelola Backup',
            'Kelola Cloud Storage',
            'Manajemen Subdomain Terpadu',
            'Kelola Master Data Email',
            'Akses Video Conference',
            'Akses Konsultasi SPBE AI',
            'Akses Bantuan TTE',
            'Akses Registrasi TTE',
            'Akses Reset Passphrase TTE',
            'Akses Pembaruan Sertifikat TTE',
            'Akses Lapor Gangguan Internet',
            'Akses Starlink Jelajah',
            'Akses Pendaftaran VPN',
            'Akses Reset Akun VPN',
            'Akses JIP PDNS',
            'Akses Kunjungan/Colocation Data Center',
            'Akses VPS/VM',
            'Akses Backup',
            'Akses Cloud Storage',
        ];

        DB::table('permissions')->whereIn('name', $permissionNames)->delete();
    }
};
