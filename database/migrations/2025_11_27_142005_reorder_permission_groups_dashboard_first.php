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
        // Rename groups to add numeric prefix for proper ordering
        // This will make "Admin - Dashboard & User Management" appear first

        $groupRenames = [
            // Admin groups - add numeric prefix
            'Admin - Dashboard & User Management' => 'Admin - 01. Dashboard & User Management',
            'Admin - Role Management' => 'Admin - 02. Role Management',
            'Admin - Layanan Digital' => 'Admin - 03. Layanan Digital',
            'Admin - Video Conference' => 'Admin - 04. Video Conference',
            'Admin - TTE (Tanda Tangan Elektronik)' => 'Admin - 05. TTE (Tanda Tangan Elektronik)',
            'Admin - Internet & Konektivitas' => 'Admin - 06. Internet & Konektivitas',
            'Admin - VPN & Jaringan Privat' => 'Admin - 07. VPN & Jaringan Privat',
            'Admin - Pusat Data/Komputasi' => 'Admin - 08. Pusat Data/Komputasi',
            'Admin - TIK & Inventaris' => 'Admin - 09. TIK & Inventaris',
            'Admin - Master Data' => 'Admin - 10. Master Data',

            // User groups
            'User - Dashboard & Profile' => 'User - 01. Dashboard & Profile',
            'User - Layanan Digital' => 'User - 02. Layanan Digital',
            'User - TTE (Tanda Tangan Elektronik)' => 'User - 03. TTE (Tanda Tangan Elektronik)',
            'User - Internet & Konektivitas' => 'User - 04. Internet & Konektivitas',
            'User - VPN & Jaringan Privat' => 'User - 05. VPN & Jaringan Privat',
            'User - Pusat Data/Komputasi' => 'User - 06. Pusat Data/Komputasi',

            // Operator group
            'Operator - Vidcon & TIK' => 'Operator - 01. Vidcon & TIK',
        ];

        foreach ($groupRenames as $oldName => $newName) {
            DB::table('permissions')
                ->where('group', $oldName)
                ->update(['group' => $newName]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert group names
        $groupRenames = [
            'Admin - 01. Dashboard & User Management' => 'Admin - Dashboard & User Management',
            'Admin - 02. Role Management' => 'Admin - Role Management',
            'Admin - 03. Layanan Digital' => 'Admin - Layanan Digital',
            'Admin - 04. Video Conference' => 'Admin - Video Conference',
            'Admin - 05. TTE (Tanda Tangan Elektronik)' => 'Admin - TTE (Tanda Tangan Elektronik)',
            'Admin - 06. Internet & Konektivitas' => 'Admin - Internet & Konektivitas',
            'Admin - 07. VPN & Jaringan Privat' => 'Admin - VPN & Jaringan Privat',
            'Admin - 08. Pusat Data/Komputasi' => 'Admin - Pusat Data/Komputasi',
            'Admin - 09. TIK & Inventaris' => 'Admin - TIK & Inventaris',
            'Admin - 10. Master Data' => 'Admin - Master Data',
            'User - 01. Dashboard & Profile' => 'User - Dashboard & Profile',
            'User - 02. Layanan Digital' => 'User - Layanan Digital',
            'User - 03. TTE (Tanda Tangan Elektronik)' => 'User - TTE (Tanda Tangan Elektronik)',
            'User - 04. Internet & Konektivitas' => 'User - Internet & Konektivitas',
            'User - 05. VPN & Jaringan Privat' => 'User - VPN & Jaringan Privat',
            'User - 06. Pusat Data/Komputasi' => 'User - Pusat Data/Komputasi',
            'Operator - 01. Vidcon & TIK' => 'Operator - Vidcon & TIK',
        ];

        foreach ($groupRenames as $oldName => $newName) {
            DB::table('permissions')
                ->where('group', $oldName)
                ->update(['group' => $newName]);
        }
    }
};
