<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VpnRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VpnRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = VpnRegistration::with(['user', 'unitKerja', 'processedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.vpn.registration.index', compact('requests'));
    }

    public function show(VpnRegistration $vpnRegistration)
    {
        $vpnRegistration->load(['user', 'unitKerja', 'processedBy']);
        return view('admin.vpn.registration.show', compact('vpnRegistration'));
    }

    public function process(Request $request, VpnRegistration $vpnRegistration)
    {
        if ($vpnRegistration->status !== 'menunggu') {
            return redirect()->back()->with('error', 'Permohonan tidak dapat diproses.');
        }

        $vpnRegistration->update([
            'status' => 'proses',
            'processed_by' => Auth::id(),
            'processing_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.vpn.registration.show', $vpnRegistration)
            ->with('success', 'Permohonan berhasil diproses.');
    }

    public function complete(Request $request, VpnRegistration $vpnRegistration)
    {
        if ($vpnRegistration->status !== 'proses') {
            return redirect()->back()->with('error', 'Permohonan harus dalam status proses.');
        }

        $validated = $request->validate([
            'username_vpn' => 'required|string|max:255',
            'password_vpn' => 'required|string|max:255',
            'ip_vpn' => 'nullable|string|max:255',
            'keterangan_admin' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        $vpnRegistration->update([
            'status' => 'selesai',
            'username_vpn' => $validated['username_vpn'],
            'password_vpn' => $validated['password_vpn'],
            'ip_vpn' => $validated['ip_vpn'],
            'keterangan_admin' => $validated['keterangan_admin'],
            'admin_notes' => $validated['admin_notes'],
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.vpn.registration.show', $vpnRegistration)
            ->with('success', 'Permohonan telah diselesaikan dan kredensial VPN berhasil diberikan.');
    }

    public function reject(Request $request, VpnRegistration $vpnRegistration)
    {
        if (!in_array($vpnRegistration->status, ['menunggu', 'proses'])) {
            return redirect()->back()->with('error', 'Permohonan tidak dapat ditolak.');
        }

        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $vpnRegistration->update([
            'status' => 'ditolak',
            'processed_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
            'rejected_at' => now(),
        ]);

        return redirect()->route('admin.vpn.registration.show', $vpnRegistration)
            ->with('success', 'Permohonan telah ditolak.');
    }

    public function updateNotes(Request $request, VpnRegistration $vpnRegistration)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $vpnRegistration->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.vpn.registration.show', $vpnRegistration)
            ->with('success', 'Catatan admin berhasil diperbarui.');
    }
}
