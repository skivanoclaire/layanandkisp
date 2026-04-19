<?php

namespace App\Http\Controllers;

use App\Models\Request as UserRequest;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Traits\ManagesRolePermissions;

class AdminController extends Controller
{
    use ManagesRolePermissions;
    public function dashboard()
    {
        $requests = \App\Models\Request::latest()->take(10)->get();

        // Hitung counter & summary dari SEMUA tabel Kelola Permohonan (digital forms).
        // Tabel-tabel ini punya konvensi nilai status tidak seragam (sebagian Indonesia,
        // sebagian Inggris, plus variant "diproses", "processed", "disetujui", dll).
        // whereIn() di bawah menyatukan semuanya sehingga counter akurat.
        // Exclude: survei-kepuasan (rating, bukan request) + konsultasi-spbe-ai (no form table).
        $requestTables = [
            'email'                   => ['label' => 'Email Baru',                  'table' => 'email_requests'],
            'email_reset'             => ['label' => 'Reset Password Email',        'table' => 'email_password_reset_requests'],
            'subdomain'               => ['label' => 'Subdomain Baru',              'table' => 'subdomain_requests'],
            'subdomain_ip'            => ['label' => 'Perubahan IP Subdomain',      'table' => 'subdomain_ip_change_requests'],
            'subdomain_name'          => ['label' => 'Perubahan Nama Subdomain',    'table' => 'subdomain_name_change_requests'],
            'rekomendasi_usulan'      => ['label' => 'Rekomendasi Usulan',          'table' => 'rekomendasi_aplikasi_forms'],
            'pse'                     => ['label' => 'Pendaftaran Sistem Elektronik','table' => 'pse_update_requests'],
            'vidcon'                  => ['label' => 'Video Conference',            'table' => 'vidcon_requests'],
            'lapor_gangguan'          => ['label' => 'Lapor Gangguan Internet',     'table' => 'laporan_gangguan'],
            'starlink'                => ['label' => 'Starlink Jelajah',            'table' => 'starlink_requests'],
            'vpn_registration'        => ['label' => 'Pendaftaran VPN',             'table' => 'vpn_registrations'],
            'vpn_reset'               => ['label' => 'Reset VPN',                   'table' => 'vpn_resets'],
            'jip_pdns'                => ['label' => 'JIP PDNS',                    'table' => 'jip_pdns_requests'],
            'visitation'              => ['label' => 'Kunjungan Pusat Data',        'table' => 'visitations'],
            'vps'                     => ['label' => 'VPS / VM',                    'table' => 'vps_requests'],
            'backup'                  => ['label' => 'Backup Pusat Data',           'table' => 'backup_requests'],
            'cloud_storage'           => ['label' => 'Cloud Storage',               'table' => 'cloud_storage_requests'],
            'tte_assistance'          => ['label' => 'Pendampingan TTE',            'table' => 'tte_assistance_requests'],
            'tte_registration'        => ['label' => 'Registrasi TTE',              'table' => 'tte_registration_requests'],
            'tte_passphrase'          => ['label' => 'Reset Passphrase TTE',        'table' => 'tte_passphrase_reset_requests'],
            'tte_certificate_update'  => ['label' => 'Pembaruan Sertifikat TTE',    'table' => 'tte_certificate_update_requests'],
        ];

        // Alias status mencakup semua konvensi yang ditemukan di 21 table di atas:
        //   - menunggu:   'menunggu', 'pending', 'diajukan', 'belum_mulai'
        //   - proses:     'proses', 'diproses', 'processing', 'approved', 'perlu_revisi', 'sedang_berjalan'
        //   - ditolak:    'ditolak', 'rejected'
        //   - selesai:    'selesai', 'completed', 'processed', 'disetujui'
        $statusAliases = [
            'menunggu' => ['menunggu', 'pending', 'diajukan', 'belum_mulai'],
            'proses'   => ['proses', 'diproses', 'processing', 'approved', 'perlu_revisi', 'sedang_berjalan'],
            'ditolak'  => ['ditolak', 'rejected'],
            'selesai'  => ['selesai', 'completed', 'processed', 'disetujui'],
        ];

        // Status yang TIDAK DIHITUNG sama sekali (tidak masuk total & bucket manapun).
        // `draft` = form yang masih dikerjakan user, belum disubmit ke admin.
        $excludeStatuses = ['draft'];

        $total = $waiting = $processing = $rejected = $finished = 0;
        $summary = ['Menunggu' => 0, 'Dalam Proses' => 0, 'Ditolak' => 0, 'Selesai' => 0];
        $digitalFormStats = [];
        $currentMonth = now()->month;
        $currentYear = now()->year;

        foreach ($requestTables as $key => $meta) {
            $table = $meta['table'];

            // Closure mengembalikan query builder baru tiap pemanggilan,
            // sudah pre-filtered untuk exclude `draft` (dan status di-exclude lainnya).
            $base = fn() => \DB::table($table)->whereNotIn('status', $excludeStatuses);

            $counts = [
                'total'    => $base()->count(),
                'menunggu' => $base()->whereIn('status', $statusAliases['menunggu'])->count(),
                'proses'   => $base()->whereIn('status', $statusAliases['proses'])->count(),
                'ditolak'  => $base()->whereIn('status', $statusAliases['ditolak'])->count(),
                'selesai'  => $base()->whereIn('status', $statusAliases['selesai'])->count(),
            ];

            $total      += $counts['total'];
            $waiting    += $counts['menunggu'];
            $processing += $counts['proses'];
            $rejected   += $counts['ditolak'];
            $finished   += $counts['selesai'];

            $summary['Menunggu']     += $base()->whereIn('status', $statusAliases['menunggu'])->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count();
            $summary['Dalam Proses'] += $base()->whereIn('status', $statusAliases['proses'])->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count();
            $summary['Ditolak']      += $base()->whereIn('status', $statusAliases['ditolak'])->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count();
            $summary['Selesai']      += $base()->whereIn('status', $statusAliases['selesai'])->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count();

            $digitalFormStats[$key] = array_merge(['label' => $meta['label'], 'table' => $table], $counts);
        }

        // Statistik per jenis layanan
        $serviceStats = \App\Models\Request::selectRaw('service, status, COUNT(*) as total')
            ->groupBy('service', 'status')
            ->get()
            ->groupBy('service')
            ->map(function ($items) {
                return [
                    'total' => $items->sum('total'),
                    'menunggu' => $items->where('status', 'Menunggu')->sum('total'),
                    'proses' => $items->where('status', 'Dalam Proses')->sum('total'),
                    'ditolak' => $items->where('status', 'Ditolak')->sum('total'),
                    'selesai' => $items->where('status', 'Selesai')->sum('total'),
                ];
            });

        // Daftar jenis layanan
        $serviceTypes = [
            'Subdomain', 'Email', 'Hosting', 'Cloud Storage', 'SPLP',
            'Internet', 'VPN', 'Wifi Publik', 'Videotron', 'Konten',
            'Helpdesk TIK', 'TTE', 'Rekomendasi'
        ];

        return view('admin.dashboard', compact(
            'requests',
            'total',
            'waiting',
            'processing',
            'rejected',
            'finished',
            'summary',
            'serviceStats',
            'serviceTypes',
            'digitalFormStats'
        ));
    }



    public function permohonan()
    {
        $requests = UserRequest::with('user')->latest()->get();
        return view('admin.permohonan', compact('requests'));
    }

    public function users(HttpRequest $request)
    {
        $query = User::with(['roles', 'unitKerja']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by unit_kerja
        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        $users = $query->latest()->get();
        $roles = \App\Models\Role::all();
        $unitKerjas = \App\Models\UnitKerja::active()->orderBy('nama')->get();

        return view('admin.users', compact('users', 'roles', 'unitKerjas'));
    }

    public function updateStatus(HttpRequest $request, UserRequest $userRequest)
    {
        $request->validate([
            'status' => 'required|in:Menunggu,Dalam Proses,Ditolak,Selesai'
        ]);

        $userRequest->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    public function updateUser(HttpRequest $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => ['exists:roles,id'],
            'nip'      => ['nullable','string','max:20'],
            'nik'      => ['nullable','string','max:20'],
            'phone'    => ['nullable','string','max:20'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
            'password' => ['nullable','string','min:8','confirmed'],
        ]);

        // Update basic user info
        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->nip   = $validated['nip']   ?? null;
        $user->nik   = $validated['nik']   ?? null;
        $user->phone = $validated['phone'] ?? null;
        $user->unit_kerja_id = $validated['unit_kerja_id'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Sync roles in pivot table
        $user->roles()->sync($validated['roles']);

        // Sync legacy 'role' string column based on selected roles
        // This ensures dashboard navigation works correctly
        $selectedRoles = \App\Models\Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();
        $user->role = $this->determineLegacyRole($selectedRoles);
        $user->save();

        return redirect()->route('admin.users.edit', $user->id)
                    ->with('success', 'Data user berhasil diperbarui.');
    }


    public function createUser()
    {
        $roles = \App\Models\Role::all();
        $unitKerjas = \App\Models\UnitKerja::active()->orderBy('nama')->get();
        return view('admin.create-user', compact('roles', 'unitKerjas'));
    }

    public function storeUser(HttpRequest $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'nip'      => ['required', 'string', 'max:20'],
            'nik'      => ['required', 'string', 'size:16', 'regex:/^\d{16}$/'],
            'phone'    => ['required', 'string', 'max:20'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => ['exists:roles,id'],
            'is_verified' => ['nullable', 'boolean'],
        ]);

        // Determine legacy role string based on selected roles
        $selectedRoles = \App\Models\Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();
        $legacyRole = $this->determineLegacyRole($selectedRoles);

        // Create user
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'nip'      => $validated['nip'],
            'nik'      => $validated['nik'],
            'phone'    => $validated['phone'],
            'unit_kerja_id' => $validated['unit_kerja_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => $legacyRole, // Set based on selected roles
            'is_verified' => $request->has('is_verified'),
            'verified_at' => $request->has('is_verified') ? now() : null,
            'verified_by' => $request->has('is_verified') ? auth()->user()->email : null,
        ]);

        // Attach roles
        $user->roles()->attach($validated['roles']);

        return redirect()->route('admin.users')
                    ->with('status', 'User berhasil ditambahkan');
    }

    public function editUser(User $user)
    {
        $user->load('jabatan.unitKerja');
        $roles = \App\Models\Role::all();
        $unitKerjas = \App\Models\UnitKerja::active()->orderBy('nama')->get();
        return view('admin.edit-user', compact('user', 'roles', 'unitKerjas'));
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus.');
    }

    /**
     * Determine legacy role string based on selected Role model roles
     *
     * This function maps the new Role model (User-Individual, User-OPD, Admin, etc.)
     * to the legacy string role column used by dashboard navigation
     *
     * Priority: admin > admin-vidcon > operator-vidcon > user
     */
    private function determineLegacyRole(array $roleNames): string
    {
        // Define role mapping from Role model name to legacy string
        // Priority is based on array order (higher index = higher priority)
        $roleMapping = [
            'User-Individual' => 'user',
            'User-OPD' => 'user',
            'Operator-Vidcon' => 'operator-vidcon',
            'Admin-Vidcon' => 'admin-vidcon',
            'Admin' => 'admin',
        ];

        // Find highest priority role
        $legacyRole = 'user'; // default
        $highestPriority = -1;

        foreach ($roleNames as $roleName) {
            if (isset($roleMapping[$roleName])) {
                $legacyValue = $roleMapping[$roleName];
                $priority = array_search($legacyValue, ['user', 'operator-vidcon', 'admin-vidcon', 'admin']);

                if ($priority !== false && $priority > $highestPriority) {
                    $highestPriority = $priority;
                    $legacyRole = $legacyValue;
                }
            }
        }

        return $legacyRole;
    }

    /**
     * Role Permissions Management - Table View
     * Shows all permissions as rows, all roles as columns
     */
    public function rolePermissions()
    {
        // Get all roles with their current permissions
        $roles = \App\Models\Role::with('permissions')->orderBy('name')->get();

        // Get all permissions grouped by category, ordered by group and display name
        $allPermissions = \App\Models\Permission::orderBy('group')
            ->orderBy('order')
            ->orderBy('display_name')
            ->get()
            ->groupBy('group');

        // Get role descriptions from config (for display only, not enforcement)
        $roleConfigs = config('role_permissions.role_permission_matrix', []);

        return view('admin.role-permissions', compact('roles', 'allPermissions', 'roleConfigs'));
    }

    /**
     * Update role permissions - Bulk update for all roles
     */
    public function updateRolePermissions(HttpRequest $request)
    {
        // Validate: permissions array where key is role_id, value is array of permission_ids
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['exists:permissions,id'],
        ]);

        $updatedRoles = [];

        // Update permissions for each role
        foreach ($validated['permissions'] as $roleId => $permissionIds) {
            $role = \App\Models\Role::find($roleId);

            if ($role) {
                // Sync permissions (this will add new ones and remove old ones)
                $role->permissions()->sync($permissionIds);
                $updatedRoles[] = $role->display_name;
            }
        }

        $rolesList = implode(', ', $updatedRoles);

        return redirect()->route('admin.role-permissions')
            ->with('success', "Kewenangan berhasil diperbarui untuk role: {$rolesList}");
    }

public function deleteRequest(UserRequest $userRequest)
{
    $userRequest->delete();
    return back()->with('success', 'Permohonan berhasil dihapus.');
}

public function verifyUser(User $user)
{
    // opsi: cegah memverifikasi admin lain via UI, kalau mau:
    // if ($user->role === 'admin') { abort(403); }

    if (!$user->is_verified) {
        $user->forceFill([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => auth()->user()->email ?? 'admin',
        ])->save();
    }

    return back()->with('status', "User {$user->name} berhasil diverifikasi.");
}

public function unverifyUser(User $user)
{
    // opsi: cegah membatalkan verifikasi admin, kalau mau:
    // if ($user->role === 'admin') { abort(403); }

    if ($user->is_verified) {
        $user->forceFill([
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
        ])->save();
    }

    return back()->with('status', "Status verifikasi untuk {$user->name} dibatalkan.");
}

public function getChartData(\Illuminate\Http\Request $request)
{
    $months = $request->get('months', 6);

    // Validate months
    if (!in_array($months, [3, 6, 12, 24, 36, 48, 60])) {
        $months = 6;
    }

    $chartData = app(\App\Services\SubdomainAggregatorService::class)->getChartData($months);

    return response()->json($chartData);
}


}
