<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackupRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = BackupRequest::with(['user', 'unitKerja', 'processedBy']);

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

        return view('admin.datacenter.backup.index', compact('requests'));
    }

    public function show(BackupRequest $backupRequest)
    {
        $backupRequest->load(['user', 'unitKerja', 'processedBy']);
        return view('admin.datacenter.backup.show', compact('backupRequest'));
    }

    public function process(Request $request, BackupRequest $backupRequest)
    {
        if ($backupRequest->status !== 'menunggu') {
            return redirect()->back()->with('error', 'Permohonan tidak dapat diproses.');
        }

        $backupRequest->update([
            'status' => 'proses',
            'processed_by' => Auth::id(),
            'processing_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.datacenter.backup.show', $backupRequest)
            ->with('success', 'Permohonan berhasil diproses.');
    }

    public function complete(Request $request, BackupRequest $backupRequest)
    {
        if ($backupRequest->status !== 'proses') {
            return redirect()->back()->with('error', 'Permohonan harus dalam status proses.');
        }

        $validated = $request->validate([
            'keterangan_admin' => 'required|string',
            'admin_notes' => 'nullable|string',
        ]);

        $backupRequest->update([
            'status' => 'selesai',
            'keterangan_admin' => $validated['keterangan_admin'],
            'admin_notes' => $validated['admin_notes'],
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.datacenter.backup.show', $backupRequest)
            ->with('success', 'Permohonan backup telah diselesaikan.');
    }

    public function reject(Request $request, BackupRequest $backupRequest)
    {
        if (!in_array($backupRequest->status, ['menunggu', 'proses'])) {
            return redirect()->back()->with('error', 'Permohonan tidak dapat ditolak.');
        }

        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $backupRequest->update([
            'status' => 'ditolak',
            'processed_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
            'rejected_at' => now(),
        ]);

        return redirect()->route('admin.datacenter.backup.show', $backupRequest)
            ->with('success', 'Permohonan telah ditolak.');
    }

    public function updateNotes(Request $request, BackupRequest $backupRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $backupRequest->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.datacenter.backup.show', $backupRequest)
            ->with('success', 'Catatan admin berhasil diperbarui.');
    }
}
