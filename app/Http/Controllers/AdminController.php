<?php

namespace App\Http\Controllers;

use App\Models\Request as UserRequest;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Hash; // <-- letakkan DI LUAR class
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Traits\ManagesRolePermissions;

class AdminController extends Controller
{
    use ManagesRolePermissions;
    public function dashboard()
    {
        $requests = \App\Models\Request::latest()->take(10)->get();

        // Hitung total keseluruhan dari SEMUA tabel Kelola Permohonan (digital forms)
        $total = 0;
        $waiting = 0;
        $processing = 0;
        $rejected = 0;
        $finished = 0;

        // Email Baru
        $total += \DB::table('email_requests')->count();
        $waiting += \DB::table('email_requests')->where('status', 'menunggu')->count();
        $processing += \DB::table('email_requests')->where('status', 'proses')->count();
        $rejected += \DB::table('email_requests')->where('status', 'ditolak')->count();
        $finished += \DB::table('email_requests')->where('status', 'selesai')->count();

        // Reset Password Email
        $total += \DB::table('email_password_reset_requests')->count();
        $waiting += \DB::table('email_password_reset_requests')->where('status', 'menunggu')->count();
        $processing += \DB::table('email_password_reset_requests')->where('status', 'proses')->count();
        $rejected += \DB::table('email_password_reset_requests')->where('status', 'ditolak')->count();
        $finished += \DB::table('email_password_reset_requests')->where('status', 'selesai')->count();

        // Subdomain Baru
        $total += \DB::table('subdomain_requests')->count();
        $waiting += \DB::table('subdomain_requests')->where('status', 'pending')->count();
        $processing += \DB::table('subdomain_requests')->where('status', 'approved')->count();
        $rejected += \DB::table('subdomain_requests')->where('status', 'rejected')->count();
        $finished += \DB::table('subdomain_requests')->where('status', 'completed')->count();

        // Perubahan IP Subdomain
        $total += \DB::table('subdomain_ip_change_requests')->count();
        $waiting += \DB::table('subdomain_ip_change_requests')->where('status', 'pending')->count();
        $processing += \DB::table('subdomain_ip_change_requests')->where('status', 'approved')->count();
        $rejected += \DB::table('subdomain_ip_change_requests')->where('status', 'rejected')->count();
        $finished += \DB::table('subdomain_ip_change_requests')->where('status', 'completed')->count();

        // Cloud Storage
        $total += \DB::table('cloud_storage_requests')->count();
        $waiting += \DB::table('cloud_storage_requests')->where('status', 'pending')->count();
        $processing += \DB::table('cloud_storage_requests')->where('status', 'processing')->count();
        $rejected += \DB::table('cloud_storage_requests')->where('status', 'rejected')->count();
        $finished += \DB::table('cloud_storage_requests')->where('status', 'completed')->count();

        // Video Conference
        $total += \DB::table('vidcon_requests')->count();
        $waiting += \DB::table('vidcon_requests')->where('status', 'pending')->count();
        $processing += \DB::table('vidcon_requests')->where('status', 'approved')->count();
        $rejected += \DB::table('vidcon_requests')->where('status', 'rejected')->count();
        $finished += \DB::table('vidcon_requests')->where('status', 'completed')->count();

        // Registrasi TTE
        $total += \DB::table('tte_registration_requests')->count();
        $waiting += \DB::table('tte_registration_requests')->where('status', 'pending')->count();
        $processing += \DB::table('tte_registration_requests')->where('status', 'processing')->count();
        $rejected += \DB::table('tte_registration_requests')->where('status', 'rejected')->count();
        $finished += \DB::table('tte_registration_requests')->where('status', 'completed')->count();

        // Reset Passphrase TTE
        $total += \DB::table('tte_passphrase_reset_requests')->count();
        $waiting += \DB::table('tte_passphrase_reset_requests')->where('status', 'pending')->count();
        $processing += \DB::table('tte_passphrase_reset_requests')->where('status', 'processing')->count();
        $rejected += \DB::table('tte_passphrase_reset_requests')->where('status', 'rejected')->count();
        $finished += \DB::table('tte_passphrase_reset_requests')->where('status', 'completed')->count();

        // Bantuan TTE
        $total += \DB::table('tte_assistance_requests')->count();
        $waiting += \DB::table('tte_assistance_requests')->where('status', 'pending')->count();
        $processing += \DB::table('tte_assistance_requests')->where('status', 'processing')->count();
        $rejected += \DB::table('tte_assistance_requests')->where('status', 'rejected')->count();
        $finished += \DB::table('tte_assistance_requests')->where('status', 'completed')->count();

        // Reset VPN
        $total += \DB::table('vpn_resets')->count();
        $waiting += \DB::table('vpn_resets')->where('status', 'pending')->count();
        $processing += \DB::table('vpn_resets')->where('status', 'processing')->count();
        $rejected += \DB::table('vpn_resets')->where('status', 'rejected')->count();
        $finished += \DB::table('vpn_resets')->where('status', 'completed')->count();

        // VPS
        $total += \DB::table('vps_requests')->count();
        $waiting += \DB::table('vps_requests')->where('status', 'pending')->count();
        $processing += \DB::table('vps_requests')->where('status', 'processing')->count();
        $rejected += \DB::table('vps_requests')->where('status', 'rejected')->count();
        $finished += \DB::table('vps_requests')->where('status', 'completed')->count();

        // Starlink
        $total += \DB::table('starlink_requests')->count();
        $waiting += \DB::table('starlink_requests')->where('status', 'pending')->count();
        $processing += \DB::table('starlink_requests')->where('status', 'processing')->count();
        $rejected += \DB::table('starlink_requests')->where('status', 'rejected')->count();
        $finished += \DB::table('starlink_requests')->where('status', 'completed')->count();

        // Hitung statistik kinerja bulan ini dari SEMUA tabel Kelola Permohonan
        $summary = [
            'Menunggu' => 0,
            'Dalam Proses' => 0,
            'Ditolak' => 0,
            'Selesai' => 0,
        ];

        // Email Baru
        $summary['Menunggu'] += \DB::table('email_requests')->where('status', 'menunggu')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('email_requests')->where('status', 'proses')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('email_requests')->where('status', 'ditolak')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('email_requests')->where('status', 'selesai')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Reset Password Email
        $summary['Menunggu'] += \DB::table('email_password_reset_requests')->where('status', 'menunggu')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('email_password_reset_requests')->where('status', 'proses')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('email_password_reset_requests')->where('status', 'ditolak')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('email_password_reset_requests')->where('status', 'selesai')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Subdomain Baru
        $summary['Menunggu'] += \DB::table('subdomain_requests')->where('status', 'pending')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('subdomain_requests')->where('status', 'approved')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('subdomain_requests')->where('status', 'rejected')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('subdomain_requests')->where('status', 'completed')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Perubahan IP Subdomain
        $summary['Menunggu'] += \DB::table('subdomain_ip_change_requests')->where('status', 'pending')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('subdomain_ip_change_requests')->where('status', 'approved')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('subdomain_ip_change_requests')->where('status', 'rejected')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('subdomain_ip_change_requests')->where('status', 'completed')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Cloud Storage
        $summary['Menunggu'] += \DB::table('cloud_storage_requests')->where('status', 'pending')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('cloud_storage_requests')->where('status', 'processing')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('cloud_storage_requests')->where('status', 'rejected')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('cloud_storage_requests')->where('status', 'completed')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Video Conference
        $summary['Menunggu'] += \DB::table('vidcon_requests')->where('status', 'pending')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('vidcon_requests')->where('status', 'approved')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('vidcon_requests')->where('status', 'rejected')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('vidcon_requests')->where('status', 'completed')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Registrasi TTE
        $summary['Menunggu'] += \DB::table('tte_registration_requests')->where('status', 'pending')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('tte_registration_requests')->where('status', 'processing')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('tte_registration_requests')->where('status', 'rejected')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('tte_registration_requests')->where('status', 'completed')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Reset Passphrase TTE
        $summary['Menunggu'] += \DB::table('tte_passphrase_reset_requests')->where('status', 'pending')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('tte_passphrase_reset_requests')->where('status', 'processing')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('tte_passphrase_reset_requests')->where('status', 'rejected')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('tte_passphrase_reset_requests')->where('status', 'completed')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Bantuan TTE
        $summary['Menunggu'] += \DB::table('tte_assistance_requests')->where('status', 'pending')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('tte_assistance_requests')->where('status', 'processing')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('tte_assistance_requests')->where('status', 'rejected')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('tte_assistance_requests')->where('status', 'completed')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Reset VPN
        $summary['Menunggu'] += \DB::table('vpn_resets')->where('status', 'pending')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('vpn_resets')->where('status', 'processing')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('vpn_resets')->where('status', 'rejected')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('vpn_resets')->where('status', 'completed')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // VPS
        $summary['Menunggu'] += \DB::table('vps_requests')->where('status', 'pending')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('vps_requests')->where('status', 'processing')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('vps_requests')->where('status', 'rejected')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('vps_requests')->where('status', 'completed')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Starlink
        $summary['Menunggu'] += \DB::table('starlink_requests')->where('status', 'pending')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Dalam Proses'] += \DB::table('starlink_requests')->where('status', 'processing')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Ditolak'] += \DB::table('starlink_requests')->where('status', 'rejected')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $summary['Selesai'] += \DB::table('starlink_requests')->where('status', 'completed')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

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

        // Statistik Formulir Digital
        $digitalFormStats = [
            'email' => [
                'label' => 'Email Baru',
                'table' => 'email_requests',
                'total' => \DB::table('email_requests')->count(),
                'menunggu' => \DB::table('email_requests')->where('status', 'menunggu')->count(),
                'proses' => \DB::table('email_requests')->where('status', 'proses')->count(),
                'ditolak' => \DB::table('email_requests')->where('status', 'ditolak')->count(),
                'selesai' => \DB::table('email_requests')->where('status', 'selesai')->count(),
            ],
            'email_reset' => [
                'label' => 'Reset Password Email',
                'table' => 'email_password_reset_requests',
                'total' => \DB::table('email_password_reset_requests')->count(),
                'menunggu' => \DB::table('email_password_reset_requests')->where('status', 'menunggu')->count(),
                'proses' => \DB::table('email_password_reset_requests')->where('status', 'proses')->count(),
                'ditolak' => \DB::table('email_password_reset_requests')->where('status', 'ditolak')->count(),
                'selesai' => \DB::table('email_password_reset_requests')->where('status', 'selesai')->count(),
            ],
            'subdomain' => [
                'label' => 'Subdomain Baru',
                'table' => 'subdomain_requests',
                'total' => \DB::table('subdomain_requests')->count(),
                'menunggu' => \DB::table('subdomain_requests')->where('status', 'pending')->count(),
                'proses' => \DB::table('subdomain_requests')->where('status', 'approved')->count(),
                'ditolak' => \DB::table('subdomain_requests')->where('status', 'rejected')->count(),
                'selesai' => \DB::table('subdomain_requests')->where('status', 'completed')->count(),
            ],
            'subdomain_ip' => [
                'label' => 'Perubahan IP Subdomain',
                'table' => 'subdomain_ip_change_requests',
                'total' => \DB::table('subdomain_ip_change_requests')->count(),
                'menunggu' => \DB::table('subdomain_ip_change_requests')->where('status', 'pending')->count(),
                'proses' => \DB::table('subdomain_ip_change_requests')->where('status', 'approved')->count(),
                'ditolak' => \DB::table('subdomain_ip_change_requests')->where('status', 'rejected')->count(),
                'selesai' => \DB::table('subdomain_ip_change_requests')->where('status', 'completed')->count(),
            ],
            'cloud_storage' => [
                'label' => 'Cloud Storage',
                'table' => 'cloud_storage_requests',
                'total' => \DB::table('cloud_storage_requests')->count(),
                'menunggu' => \DB::table('cloud_storage_requests')->where('status', 'pending')->count(),
                'proses' => \DB::table('cloud_storage_requests')->where('status', 'processing')->count(),
                'ditolak' => \DB::table('cloud_storage_requests')->where('status', 'rejected')->count(),
                'selesai' => \DB::table('cloud_storage_requests')->where('status', 'completed')->count(),
            ],
            'vidcon' => [
                'label' => 'Video Conference',
                'table' => 'vidcon_requests',
                'total' => \DB::table('vidcon_requests')->count(),
                'menunggu' => \DB::table('vidcon_requests')->where('status', 'pending')->count(),
                'proses' => \DB::table('vidcon_requests')->where('status', 'approved')->count(),
                'ditolak' => \DB::table('vidcon_requests')->where('status', 'rejected')->count(),
                'selesai' => \DB::table('vidcon_requests')->where('status', 'completed')->count(),
            ],
            'tte_registration' => [
                'label' => 'Registrasi TTE',
                'table' => 'tte_registration_requests',
                'total' => \DB::table('tte_registration_requests')->count(),
                'menunggu' => \DB::table('tte_registration_requests')->where('status', 'pending')->count(),
                'proses' => \DB::table('tte_registration_requests')->where('status', 'processing')->count(),
                'ditolak' => \DB::table('tte_registration_requests')->where('status', 'rejected')->count(),
                'selesai' => \DB::table('tte_registration_requests')->where('status', 'completed')->count(),
            ],
            'tte_passphrase' => [
                'label' => 'Reset Passphrase TTE',
                'table' => 'tte_passphrase_reset_requests',
                'total' => \DB::table('tte_passphrase_reset_requests')->count(),
                'menunggu' => \DB::table('tte_passphrase_reset_requests')->where('status', 'pending')->count(),
                'proses' => \DB::table('tte_passphrase_reset_requests')->where('status', 'processing')->count(),
                'ditolak' => \DB::table('tte_passphrase_reset_requests')->where('status', 'rejected')->count(),
                'selesai' => \DB::table('tte_passphrase_reset_requests')->where('status', 'completed')->count(),
            ],
            'tte_assistance' => [
                'label' => 'Bantuan TTE',
                'table' => 'tte_assistance_requests',
                'total' => \DB::table('tte_assistance_requests')->count(),
                'menunggu' => \DB::table('tte_assistance_requests')->where('status', 'pending')->count(),
                'proses' => \DB::table('tte_assistance_requests')->where('status', 'processing')->count(),
                'ditolak' => \DB::table('tte_assistance_requests')->where('status', 'rejected')->count(),
                'selesai' => \DB::table('tte_assistance_requests')->where('status', 'completed')->count(),
            ],
            'vpn' => [
                'label' => 'Reset VPN',
                'table' => 'vpn_resets',
                'total' => \DB::table('vpn_resets')->count(),
                'menunggu' => \DB::table('vpn_resets')->where('status', 'pending')->count(),
                'proses' => \DB::table('vpn_resets')->where('status', 'processing')->count(),
                'ditolak' => \DB::table('vpn_resets')->where('status', 'rejected')->count(),
                'selesai' => \DB::table('vpn_resets')->where('status', 'completed')->count(),
            ],
            'vps' => [
                'label' => 'VPS',
                'table' => 'vps_requests',
                'total' => \DB::table('vps_requests')->count(),
                'menunggu' => \DB::table('vps_requests')->where('status', 'pending')->count(),
                'proses' => \DB::table('vps_requests')->where('status', 'processing')->count(),
                'ditolak' => \DB::table('vps_requests')->where('status', 'rejected')->count(),
                'selesai' => \DB::table('vps_requests')->where('status', 'completed')->count(),
            ],
            'starlink' => [
                'label' => 'Starlink',
                'table' => 'starlink_requests',
                'total' => \DB::table('starlink_requests')->count(),
                'menunggu' => \DB::table('starlink_requests')->where('status', 'pending')->count(),
                'proses' => \DB::table('starlink_requests')->where('status', 'processing')->count(),
                'ditolak' => \DB::table('starlink_requests')->where('status', 'rejected')->count(),
                'selesai' => \DB::table('starlink_requests')->where('status', 'completed')->count(),
            ],
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

        // Create user
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'nip'      => $validated['nip'],
            'nik'      => $validated['nik'],
            'phone'    => $validated['phone'],
            'unit_kerja_id' => $validated['unit_kerja_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => 'User', // Default role for backward compatibility
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
