<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "=== Creating User Roles ===\n\n";

        // Create User-Individual role
        $userIndividual = Role::where('name', 'User-Individual')->first();
        if (!$userIndividual) {
            $userIndividual = Role::create([
                'name' => 'User-Individual',
                'display_name' => 'User Individual',
                'description' => 'User perorangan (ASN/pegawai) dengan akses layanan terbatas untuk keperluan pribadi',
            ]);
            echo "Created role: User-Individual\n";
        } else {
            echo "Role User-Individual already exists\n";
        }

        // Create User-OPD role
        $userOPD = Role::where('name', 'User-OPD')->first();
        if (!$userOPD) {
            $userOPD = Role::create([
                'name' => 'User-OPD',
                'display_name' => 'User OPD',
                'description' => 'Pengelola TIK OPD dengan akses layanan lengkap untuk mengelola aplikasi dan infrastruktur OPD',
            ]);
            echo "Created role: User-OPD\n";
        } else {
            echo "Role User-OPD already exists\n";
        }

        echo "\n=== Assigning Permissions ===\n\n";

        // Permissions for User-Individual (layanan terbatas)
        $individualPermissions = Permission::whereIn('name', [
            'user.dashboard',
            'user.profile',
            'user.email.index',                    // Pendaftaran email
            'user.email-password-reset.index',     // Reset password email
            'user.email-password-reset.create',
            'user.email-password-reset.store',
            'user.email-password-reset.show',
            'user.permohonan',                     // Unggah manual
        ])->pluck('id')->toArray();

        if (count($individualPermissions) > 0) {
            $userIndividual->permissions()->sync($individualPermissions);
            echo "Assigned " . count($individualPermissions) . " permissions to User-Individual\n";
            foreach ($individualPermissions as $permId) {
                $perm = Permission::find($permId);
                if ($perm) {
                    echo "  - {$perm->name}\n";
                }
            }
        } else {
            echo "No permissions found for User-Individual\n";
        }

        // Permissions for User-OPD (layanan lengkap)
        $opdPermissions = Permission::whereIn('name', [
            // All Individual permissions +
            'user.dashboard',
            'user.profile',
            'user.email.index',
            'user.email-password-reset.index',
            'user.email-password-reset.create',
            'user.email-password-reset.store',
            'user.email-password-reset.show',
            'user.permohonan',
            // Additional OPD permissions
            'user.subdomain.index',                // Pendaftaran subdomain
            'user.subdomain.ip-change.index',      // Perubahan IP subdomain
            'user.subdomain.ip-change.create',
            'user.subdomain.ip-change.store',
            'user.subdomain.ip-change.show',
            'user.rekomendasi.index',              // Rekomendasi aplikasi (legacy)
            // Rekomendasi Aplikasi V2
            'user.rekomendasi.usulan.create',      // Ajukan usulan rekomendasi
            'user.rekomendasi.usulan.show',        // Lihat usulan sendiri
            'user.rekomendasi.usulan.edit',        // Edit usulan
            'user.rekomendasi.dokumen.upload',     // Upload dokumen
            'user.rekomendasi.dokumen.download',   // Download dokumen
            'user.rekomendasi.fase.update',        // Update fase pengembangan
            'user.rekomendasi.evaluasi.create',    // Buat evaluasi aplikasi
        ])->pluck('id')->toArray();

        if (count($opdPermissions) > 0) {
            $userOPD->permissions()->sync($opdPermissions);
            echo "\nAssigned " . count($opdPermissions) . " permissions to User-OPD\n";
            foreach ($opdPermissions as $permId) {
                $perm = Permission::find($permId);
                if ($perm) {
                    echo "  - {$perm->name}\n";
                }
            }
        } else {
            echo "No permissions found for User-OPD\n";
        }

        echo "\n=== Seeding Complete ===\n";
        echo "User-Individual: " . count($individualPermissions) . " permissions\n";
        echo "User-OPD: " . count($opdPermissions) . " permissions\n";
    }
}
