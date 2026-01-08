<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TteAssistanceRequest;
use Illuminate\Http\Request;

class TteAssistanceController extends Controller
{
    public function index(Request $request)
    {
        $query = TteAssistanceRequest::with(['user', 'processedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by ticket_no, nama, NIP, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email_resmi', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => TteAssistanceRequest::count(),
            'menunggu' => TteAssistanceRequest::where('status', 'menunggu')->count(),
            'proses' => TteAssistanceRequest::where('status', 'proses')->count(),
            'selesai' => TteAssistanceRequest::where('status', 'selesai')->count(),
            'ditolak' => TteAssistanceRequest::where('status', 'ditolak')->count(),
        ];

        return view('admin.tte.assistance.index', compact('requests', 'stats'));
    }

    public function show(TteAssistanceRequest $tteAssistance)
    {
        $tteAssistance->load(['user', 'processedBy']);
        return view('admin.tte.assistance.show', compact('tteAssistance'));
    }

    public function updateStatus(Request $request, TteAssistanceRequest $tteAssistance)
    {
        $request->validate([
            'status' => 'required|in:menunggu,proses,selesai,ditolak',
            'keterangan_admin' => 'nullable|string',
        ]);

        $data = [
            'status' => $request->status,
            'keterangan_admin' => $request->keterangan_admin,
            'processed_by' => auth()->id(),
        ];

        $tteAssistance->update($data);

        return redirect()->route('admin.tte.assistance.show', $tteAssistance)
            ->with('success', 'Status permohonan berhasil diupdate.');
    }

    public function destroy(TteAssistanceRequest $tteAssistance)
    {
        $ticketNo = $tteAssistance->ticket_no;
        $tteAssistance->delete();

        return redirect()->route('admin.tte.assistance.index')
            ->with('success', "Permohonan {$ticketNo} berhasil dihapus.");
    }
}
