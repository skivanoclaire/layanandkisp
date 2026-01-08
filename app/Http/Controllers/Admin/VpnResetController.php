<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VpnReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VpnResetController extends Controller
{
    public function index(Request $request)
    {
        $query = VpnReset::with(['user', 'unitKerja', 'processedBy']);

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

        return view('admin.vpn.reset.index', compact('requests'));
    }

    public function show(VpnReset $vpnReset)
    {
        $vpnReset->load(['user', 'unitKerja', 'processedBy']);
        return view('admin.vpn.reset.show', compact('vpnReset'));
    }

    public function process(Request $request, VpnReset $vpnReset)
    {
        if ($vpnReset->status !== 'menunggu') {
            return redirect()->back()->with('error', 'Permohonan tidak dapat diproses.');
        }

        $vpnReset->update([
            'status' => 'proses',
            'processed_by' => Auth::id(),
            'processing_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.vpn.reset.show', $vpnReset)
            ->with('success', 'Permohonan berhasil diproses.');
    }

    public function complete(Request $request, VpnReset $vpnReset)
    {
        if ($vpnReset->status !== 'proses') {
            return redirect()->back()->with('error', 'Permohonan harus dalam status proses.');
        }

        $validated = $request->validate([
            'username_vpn_baru' => 'required|string|max:255',
            'password_vpn_baru' => 'required|string|max:255',
            'keterangan_admin' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        $vpnReset->update([
            'status' => 'selesai',
            'username_vpn_baru' => $validated['username_vpn_baru'],
            'password_vpn_baru' => $validated['password_vpn_baru'],
            'keterangan_admin' => $validated['keterangan_admin'],
            'admin_notes' => $validated['admin_notes'],
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.vpn.reset.show', $vpnReset)
            ->with('success', 'Permohonan telah diselesaikan dan kredensial VPN baru berhasil diberikan.');
    }

    public function reject(Request $request, VpnReset $vpnReset)
    {
        if (!in_array($vpnReset->status, ['menunggu', 'proses'])) {
            return redirect()->back()->with('error', 'Permohonan tidak dapat ditolak.');
        }

        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $vpnReset->update([
            'status' => 'ditolak',
            'processed_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
            'rejected_at' => now(),
        ]);

        return redirect()->route('admin.vpn.reset.show', $vpnReset)
            ->with('success', 'Permohonan telah ditolak.');
    }

    public function updateNotes(Request $request, VpnReset $vpnReset)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $vpnReset->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.vpn.reset.show', $vpnReset)
            ->with('success', 'Catatan admin berhasil diperbarui.');
    }
}
