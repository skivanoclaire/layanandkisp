<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JipPdnsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JipPdnsController extends Controller
{
    public function index(Request $request)
    {
        $query = JipPdnsRequest::with(['user', 'unitKerja', 'processedBy']);

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

        return view('admin.vpn.jip-pdns.index', compact('requests'));
    }

    public function show(JipPdnsRequest $jipPdnsRequest)
    {
        $jipPdnsRequest->load(['user', 'unitKerja', 'processedBy']);
        return view('admin.vpn.jip-pdns.show', compact('jipPdnsRequest'));
    }

    public function process(Request $request, JipPdnsRequest $jipPdnsRequest)
    {
        if ($jipPdnsRequest->status !== 'menunggu') {
            return redirect()->back()->with('error', 'Permohonan tidak dapat diproses.');
        }

        $jipPdnsRequest->update([
            'status' => 'proses',
            'processed_by' => Auth::id(),
            'processing_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.vpn.jip-pdns.show', $jipPdnsRequest)
            ->with('success', 'Permohonan berhasil diproses.');
    }

    public function complete(Request $request, JipPdnsRequest $jipPdnsRequest)
    {
        if ($jipPdnsRequest->status !== 'proses') {
            return redirect()->back()->with('error', 'Permohonan harus dalam status proses.');
        }

        $validated = $request->validate([
            'keterangan_admin' => 'required|string',
            'admin_notes' => 'nullable|string',
        ]);

        $jipPdnsRequest->update([
            'status' => 'selesai',
            'keterangan_admin' => $validated['keterangan_admin'],
            'admin_notes' => $validated['admin_notes'],
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.vpn.jip-pdns.show', $jipPdnsRequest)
            ->with('success', 'Permohonan telah diselesaikan.');
    }

    public function reject(Request $request, JipPdnsRequest $jipPdnsRequest)
    {
        if (!in_array($jipPdnsRequest->status, ['menunggu', 'proses'])) {
            return redirect()->back()->with('error', 'Permohonan tidak dapat ditolak.');
        }

        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $jipPdnsRequest->update([
            'status' => 'ditolak',
            'processed_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
            'rejected_at' => now(),
        ]);

        return redirect()->route('admin.vpn.jip-pdns.show', $jipPdnsRequest)
            ->with('success', 'Permohonan telah ditolak.');
    }

    public function updateNotes(Request $request, JipPdnsRequest $jipPdnsRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $jipPdnsRequest->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.vpn.jip-pdns.show', $jipPdnsRequest)
            ->with('success', 'Catatan admin berhasil diperbarui.');
    }
}
