<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SubdomainNameChangeRequest;
use App\Models\SubdomainRequest;
use App\Models\WebMonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SubdomainNameChangeController extends Controller
{
    /**
     * Display list of name change requests
     */
    public function index()
    {
        $requests = SubdomainNameChangeRequest::with(['webMonitor', 'processedBy'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.subdomain.name-change.index', compact('requests'));
    }

    /**
     * Show form to create new name change request
     */
    public function create()
    {
        // Get ALL approved/active subdomains - any user can propose name changes
        $userSubdomains = WebMonitor::whereNotNull('subdomain')
            ->whereNotNull('cloudflare_record_id')
            ->where('status', 'active')
            ->orderBy('subdomain', 'asc')
            ->get();

        // Check if there are any approved subdomains
        if ($userSubdomains->isEmpty()) {
            return redirect()->route('user.subdomain.name-change.index')
                ->with('error', 'Belum ada subdomain aktif yang tersedia untuk diubah.');
        }

        return view('user.subdomain.name-change.create', compact('userSubdomains'));
    }

    /**
     * Store new name change request
     */
    public function store(Request $request)
    {
        $request->validate([
            'web_monitor_id' => [
                'required',
                'exists:web_monitors,id',
                function ($attribute, $value, $fail) {
                    // Verify subdomain exists and has Cloudflare record
                    $webMonitor = WebMonitor::find($value);
                    if (!$webMonitor || !$webMonitor->cloudflare_record_id) {
                        $fail('Subdomain yang dipilih tidak valid atau tidak memiliki Cloudflare record.');
                    }

                    // Check no pending request for this subdomain
                    if (SubdomainNameChangeRequest::where('web_monitor_id', $value)
                        ->whereIn('status', ['pending', 'approved'])
                        ->exists()) {
                        $fail('Sudah ada permohonan perubahan nama yang sedang diproses untuk subdomain ini.');
                    }
                },
            ],
            'new_subdomain_name' => [
                'required',
                'alpha_dash',
                'max:63',
                'different:old_subdomain_name',
                Rule::unique('web_monitors', 'subdomain'),
                Rule::unique('subdomain_requests', 'subdomain_requested'),
                function ($attribute, $value, $fail) {
                    // Check pending name change requests
                    if (SubdomainNameChangeRequest::where('new_subdomain_name', $value)
                        ->whereIn('status', ['pending', 'approved'])
                        ->exists()) {
                        $fail('Nama subdomain sudah diminta oleh pengguna lain.');
                    }
                },
            ],
            'reason' => 'required|string|min:20|max:1000',
            'dns_propagation_acknowledged' => 'accepted',
        ], [
            'web_monitor_id.required' => 'Subdomain harus dipilih',
            'web_monitor_id.exists' => 'Subdomain yang dipilih tidak valid',
            'new_subdomain_name.required' => 'Nama subdomain baru harus diisi',
            'new_subdomain_name.alpha_dash' => 'Nama subdomain hanya boleh berisi huruf, angka, dash dan underscore',
            'new_subdomain_name.max' => 'Nama subdomain maksimal 63 karakter',
            'new_subdomain_name.different' => 'Nama subdomain baru harus berbeda dengan nama lama',
            'new_subdomain_name.unique' => 'Nama subdomain sudah digunakan',
            'reason.required' => 'Alasan perubahan harus diisi',
            'reason.min' => 'Alasan perubahan minimal 20 karakter',
            'reason.max' => 'Alasan perubahan maksimal 1000 karakter',
            'dns_propagation_acknowledged.accepted' => 'Anda harus memahami dampak perubahan DNS',
        ]);

        $webMonitor = WebMonitor::findOrFail($request->web_monitor_id);

        $nameChangeRequest = SubdomainNameChangeRequest::create([
            'user_id' => Auth::id(),
            'web_monitor_id' => $request->web_monitor_id,
            'old_subdomain_name' => $webMonitor->subdomain,
            'new_subdomain_name' => $request->new_subdomain_name,
            'reason' => $request->reason,
            'dns_propagation_acknowledged' => true,
            'status' => 'pending',
        ]);

        return redirect()->route('user.subdomain.name-change.show', $nameChangeRequest->id)
            ->with('success', 'Permohonan perubahan nama subdomain berhasil diajukan dengan nomor tiket: ' . $nameChangeRequest->ticket_number);
    }

    /**
     * Show detail of name change request
     */
    public function show($id)
    {
        $request = SubdomainNameChangeRequest::with(['webMonitor', 'processedBy'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('user.subdomain.name-change.show', compact('request'));
    }

    /**
     * AJAX: Check if subdomain name is available
     */
    public function checkAvailability(Request $request)
    {
        $subdomainName = $request->input('subdomain_name');

        if (empty($subdomainName)) {
            return response()->json(['available' => false, 'message' => 'Nama subdomain tidak boleh kosong']);
        }

        // Check in web_monitors
        if (WebMonitor::where('subdomain', $subdomainName)->exists()) {
            return response()->json(['available' => false, 'message' => 'Nama subdomain sudah digunakan']);
        }

        // Check in subdomain_requests
        if (SubdomainRequest::where('subdomain_requested', $subdomainName)->exists()) {
            return response()->json(['available' => false, 'message' => 'Nama subdomain sudah diminta']);
        }

        // Check in pending/approved name change requests
        if (SubdomainNameChangeRequest::where('new_subdomain_name', $subdomainName)
            ->whereIn('status', ['pending', 'approved'])
            ->exists()) {
            return response()->json(['available' => false, 'message' => 'Nama subdomain sedang dalam proses perubahan']);
        }

        return response()->json(['available' => true, 'message' => 'Nama subdomain tersedia']);
    }
}
