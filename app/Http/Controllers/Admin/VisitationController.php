<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitationController extends Controller
{
    public function index(Request $request)
    {
        $query = Visitation::with(['user', 'unitKerja', 'processedBy']);

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

        return view('admin.datacenter.visitation.index', compact('requests'));
    }

    public function show(Visitation $visitation)
    {
        $visitation->load(['user', 'unitKerja', 'processedBy']);
        return view('admin.datacenter.visitation.show', compact('visitation'));
    }

    public function approve(Request $request, Visitation $visitation)
    {
        if ($visitation->status !== 'menunggu') {
            return redirect()->back()->with('error', 'Permohonan tidak dapat disetujui.');
        }

        $visitation->update([
            'status' => 'disetujui',
            'processed_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
            'keterangan_admin' => $request->keterangan_admin,
        ]);

        return redirect()->route('admin.datacenter.visitation.show', $visitation)
            ->with('success', 'Permohonan berhasil disetujui.');
    }

    public function reject(Request $request, Visitation $visitation)
    {
        if (!in_array($visitation->status, ['menunggu', 'disetujui'])) {
            return redirect()->back()->with('error', 'Permohonan tidak dapat ditolak.');
        }

        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $visitation->update([
            'status' => 'ditolak',
            'processed_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
            'rejected_at' => now(),
        ]);

        return redirect()->route('admin.datacenter.visitation.show', $visitation)
            ->with('success', 'Permohonan telah ditolak.');
    }

    public function complete(Request $request, Visitation $visitation)
    {
        if ($visitation->status !== 'disetujui') {
            return redirect()->back()->with('error', 'Permohonan harus dalam status disetujui.');
        }

        $visitation->update([
            'status' => 'selesai',
            'completed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.datacenter.visitation.show', $visitation)
            ->with('success', 'Kunjungan telah ditandai selesai.');
    }

    public function updateNotes(Request $request, Visitation $visitation)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $visitation->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.datacenter.visitation.show', $visitation)
            ->with('success', 'Catatan admin berhasil diperbarui.');
    }
}
