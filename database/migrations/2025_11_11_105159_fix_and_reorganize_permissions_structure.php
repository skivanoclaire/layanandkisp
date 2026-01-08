<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1: Update existing permission groups to new structure
        $this->updateExistingGroups();

        // STEP 2: Add missing permissions
        $this->addMissingPermissions();

        // STEP 3: Create Operator-Sandi role if missing
        $this->createOperatorSandiRole();

        // STEP 4: Assign default permissions to roles
        $this->assignDefaultPermissions();
    }

    /**
     * Update existing permission groups to new organized structure
     */
    private function updateExistingGroups(): void
    {
        // Admin - Dashboard & User Management
        Permission::whereIn('name', [
            'admin.dashboard',
            'admin.users',
            'admin.simpeg'
        ])->update(['group' => 'Admin - Dashboard & User Management', 'order' => 1]);

        // Admin - Layanan Digital
        Permission::whereIn('name', [
            'admin.permohonan',
            'admin.email',
            'admin.subdomain',
            'admin.rekomendasi'
        ])->update(['group' => 'Admin - Layanan Digital', 'order' => 2]);

        // Admin - Video Conference (update existing, will add missing later)
        Permission::whereIn('name', [
            'admin.schedule',
            'admin.statistic'
        ])->update(['group' => 'Admin - Video Conference', 'order' => 5]);

        // Admin - TTE (update existing, will add missing later)
        Permission::where('name', 'like', 'admin.tte.%')
            ->update(['group' => 'Admin - TTE (Tanda Tangan Elektronik)', 'order' => 6]);

        // Admin - TIK & Inventaris
        Permission::whereIn('name', [
            'admin.tik.assets',
            'admin.tik.borrow',
            'admin.unit-kerja',
            'admin.web-monitor'
        ])->update(['group' => 'Admin - TIK & Inventaris', 'order' => 7]);

        // User - Dashboard & Profile
        Permission::whereIn('name', [
            'user.dashboard',
            'user.profile'
        ])->update(['group' => 'User - Dashboard & Profile', 'order' => 10]);

        // Operator (keep as is)
        Permission::where('name', 'like', 'op.%')
            ->update(['group' => 'Operator', 'order' => 20]);
    }

    /**
     * Add all missing permissions
     */
    private function addMissingPermissions(): void
    {
        $permissions = [
            // User - Layanan Digital
            [
                'name' => 'user.email.index',
                'display_name' => 'Pendaftaran Email',
                'group' => 'User - Layanan Digital',
                'order' => 11
            ],
            [
                'name' => 'user.email.create',
                'display_name' => 'Buat Permohonan Email',
                'group' => 'User - Layanan Digital',
                'order' => 11
            ],
            [
                'name' => 'user.email.show',
                'display_name' => 'Detail Email Saya',
                'group' => 'User - Layanan Digital',
                'order' => 11
            ],
            [
                'name' => 'user.subdomain.index',
                'display_name' => 'Pendaftaran Subdomain',
                'group' => 'User - Layanan Digital',
                'order' => 11
            ],
            [
                'name' => 'user.subdomain.create',
                'display_name' => 'Buat Permohonan Subdomain',
                'group' => 'User - Layanan Digital',
                'order' => 11
            ],
            [
                'name' => 'user.subdomain.show',
                'display_name' => 'Detail Subdomain Saya',
                'group' => 'User - Layanan Digital',
                'order' => 11
            ],
            [
                'name' => 'user.rekomendasi.index',
                'display_name' => 'Rekomendasi Aplikasi',
                'group' => 'User - Layanan Digital',
                'order' => 11
            ],
            [
                'name' => 'user.rekomendasi.create',
                'display_name' => 'Buat Rekomendasi',
                'group' => 'User - Layanan Digital',
                'order' => 11
            ],
            [
                'name' => 'user.email-password-reset.index',
                'display_name' => 'Reset Password Email',
                'group' => 'User - Layanan Digital',
                'order' => 11
            ],
            [
                'name' => 'user.email-password-reset.create',
                'display_name' => 'Buat Reset Password Email',
                'group' => 'User - Layanan Digital',
                'order' => 11
            ],

            // Admin - Video Conference (missing permission)
            [
                'name' => 'admin.vidcon.data',
                'display_name' => 'Kelola Data Vidcon',
                'group' => 'Admin - Video Conference',
                'order' => 5
            ],
            [
                'name' => 'admin.vidcon.index',
                'display_name' => 'Daftar Permohonan Vidcon',
                'group' => 'Admin - Video Conference',
                'order' => 5
            ],

            // Admin - TTE (CRITICAL: missing passphrase-reset)
            [
                'name' => 'admin.tte.passphrase-reset',
                'display_name' => 'Reset Passphrase TTE',
                'group' => 'Admin - TTE (Tanda Tangan Elektronik)',
                'order' => 6
            ],

            // Admin - Layanan Digital (complete CRUD for subdomain & rekomendasi)
            [
                'name' => 'admin.subdomain.index',
                'display_name' => 'Daftar Subdomain',
                'group' => 'Admin - Layanan Digital',
                'order' => 2
            ],
            [
                'name' => 'admin.subdomain.show',
                'display_name' => 'Detail Subdomain',
                'group' => 'Admin - Layanan Digital',
                'order' => 2
            ],
            [
                'name' => 'admin.subdomain.update-status',
                'display_name' => 'Update Status Subdomain',
                'group' => 'Admin - Layanan Digital',
                'order' => 2
            ],
            [
                'name' => 'admin.rekomendasi.index',
                'display_name' => 'Daftar Rekomendasi',
                'group' => 'Admin - Layanan Digital',
                'order' => 2
            ],
            [
                'name' => 'admin.rekomendasi.show',
                'display_name' => 'Detail Rekomendasi',
                'group' => 'Admin - Layanan Digital',
                'order' => 2
            ],
            [
                'name' => 'admin.rekomendasi.update-status',
                'display_name' => 'Update Status Rekomendasi',
                'group' => 'Admin - Layanan Digital',
                'order' => 2
            ],
            [
                'name' => 'admin.email.index',
                'display_name' => 'Daftar Email',
                'group' => 'Admin - Layanan Digital',
                'order' => 2
            ],
            [
                'name' => 'admin.email.show',
                'display_name' => 'Detail Email',
                'group' => 'Admin - Layanan Digital',
                'order' => 2
            ],
            [
                'name' => 'admin.email.update-status',
                'display_name' => 'Update Status Email',
                'group' => 'Admin - Layanan Digital',
                'order' => 2
            ],
            [
                'name' => 'admin.email-password-reset.index',
                'display_name' => 'Kelola Reset Password Email',
                'group' => 'Admin - Layanan Digital',
                'order' => 2
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }

    /**
     * Create Operator-Sandi role if it doesn't exist
     */
    private function createOperatorSandiRole(): void
    {
        Role::firstOrCreate(
            ['name' => 'Operator-Sandi'],
            [
                'display_name' => 'Operator Sandi & TTE',
                'description' => 'Operator untuk mengelola layanan keamanan sandi dan TTE'
            ]
        );
    }

    /**
     * Assign default permissions to roles
     */
    private function assignDefaultPermissions(): void
    {
        // Get roles
        $admin = Role::where('name', 'Admin')->first();
        $operatorVidcon = Role::where('name', 'Operator-Vidcon')->first();
        $operatorSandi = Role::where('name', 'Operator-Sandi')->first();
        $user = Role::where('name', 'User')->first();
        $userOPD = Role::where('name', 'User-OPD')->first();
        $userIndividual = Role::where('name', 'User-Individual')->first();

        // Admin: Get ALL permissions
        if ($admin) {
            $allPermissions = Permission::all()->pluck('id');
            $admin->permissions()->syncWithoutDetaching($allPermissions);
        }

        // Operator-Vidcon: operator permissions + user layanan digital
        if ($operatorVidcon) {
            $permissions = Permission::where(function($query) {
                $query->where('group', 'Operator')
                      ->orWhere('group', 'User - Dashboard & Profile')
                      ->orWhere('group', 'User - Layanan Digital');
            })->pluck('id');
            $operatorVidcon->permissions()->syncWithoutDetaching($permissions);
        }

        // Operator-Sandi: TTE + VPN + IP Change + user layanan
        if ($operatorSandi) {
            $permissions = Permission::where(function($query) {
                $query->where('group', 'Admin - TTE (Tanda Tangan Elektronik)')
                      ->orWhere('group', 'Admin - Subdomain IP Change')
                      ->orWhere('group', 'User - Dashboard & Profile')
                      ->orWhere('group', 'User - Layanan Digital')
                      ->orWhere('group', 'User - Subdomain IP Change')
                      ->orWhere('name', 'like', 'admin.vpn.%');
            })->pluck('id');
            $operatorSandi->permissions()->syncWithoutDetaching($permissions);
        }

        // User: all user permissions
        if ($user) {
            $permissions = Permission::where('name', 'like', 'user.%')->pluck('id');
            $user->permissions()->syncWithoutDetaching($permissions);
        }

        // User-OPD: all user permissions
        if ($userOPD) {
            $permissions = Permission::where('name', 'like', 'user.%')->pluck('id');
            $userOPD->permissions()->syncWithoutDetaching($permissions);
        }

        // User-Individual: limited user permissions (dashboard, profile, basic services)
        if ($userIndividual) {
            $permissions = Permission::whereIn('name', [
                'user.dashboard',
                'user.profile',
                'user.email.index',
                'user.email.create',
                'user.email.show',
                'user.rekomendasi.index',
                'user.rekomendasi.create'
            ])->pluck('id');
            $userIndividual->permissions()->syncWithoutDetaching($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert permission groups back to original
        Permission::where('group', 'like', 'Admin - %')
            ->where('group', '!=', 'Admin - Role Management')
            ->where('group', '!=', 'Admin - Subdomain IP Change')
            ->update(['group' => 'admin']);

        Permission::where('group', 'like', 'User - %')
            ->where('group', '!=', 'User - Subdomain IP Change')
            ->update(['group' => 'user']);

        // Delete newly created permissions
        $newPermissions = [
            'user.email.index', 'user.email.create', 'user.email.show',
            'user.subdomain.index', 'user.subdomain.create', 'user.subdomain.show',
            'user.rekomendasi.index', 'user.rekomendasi.create',
            'user.email-password-reset.index', 'user.email-password-reset.create',
            'admin.vidcon.data', 'admin.vidcon.index',
            'admin.tte.passphrase-reset',
            'admin.subdomain.index', 'admin.subdomain.show', 'admin.subdomain.update-status',
            'admin.rekomendasi.index', 'admin.rekomendasi.show', 'admin.rekomendasi.update-status',
            'admin.email.index', 'admin.email.show', 'admin.email.update-status',
            'admin.email-password-reset.index'
        ];

        Permission::whereIn('name', $newPermissions)->delete();

        // Optionally delete Operator-Sandi role (commented out for safety)
        // Role::where('name', 'Operator-Sandi')->delete();
    }
};
