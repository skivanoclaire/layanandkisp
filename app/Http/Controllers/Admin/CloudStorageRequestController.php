<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CloudStorageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CloudStorageRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = CloudStorageRequest::with(['user', 'unitKerja', 'processedBy']);

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

        return view('admin.datacenter.cloud-storage.index', compact('requests'));
    }

    public function show(CloudStorageRequest $cloudStorageRequest)
    {
        $cloudStorageRequest->load(['user', 'unitKerja', 'processedBy']);
        return view('admin.datacenter.cloud-storage.show', compact('cloudStorageRequest'));
    }

    public function process(Request $request, CloudStorageRequest $cloudStorageRequest)
    {
        if ($cloudStorageRequest->status !== 'menunggu') {
            return redirect()->back()->with('error', 'Permohonan tidak dapat diproses.');
        }

        $cloudStorageRequest->update([
            'status' => 'proses',
            'processed_by' => Auth::id(),
            'processing_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.datacenter.cloud-storage.show', $cloudStorageRequest)
            ->with('success', 'Permohonan berhasil diproses.');
    }

    public function complete(Request $request, CloudStorageRequest $cloudStorageRequest)
    {
        if ($cloudStorageRequest->status !== 'proses') {
            return redirect()->back()->with('error', 'Permohonan harus dalam status proses.');
        }

        $validated = $request->validate([
            'akses_url' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'keterangan_admin' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        $cloudStorageRequest->update([
            'status' => 'selesai',
            'akses_url' => $validated['akses_url'],
            'username' => $validated['username'],
            'keterangan_admin' => $validated['keterangan_admin'],
            'admin_notes' => $validated['admin_notes'],
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.datacenter.cloud-storage.show', $cloudStorageRequest)
            ->with('success', 'Permohonan telah diselesaikan dan akses cloud storage berhasil diberikan.');
    }

    public function reject(Request $request, CloudStorageRequest $cloudStorageRequest)
    {
        if (!in_array($cloudStorageRequest->status, ['menunggu', 'proses'])) {
            return redirect()->back()->with('error', 'Permohonan tidak dapat ditolak.');
        }

        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $cloudStorageRequest->update([
            'status' => 'ditolak',
            'processed_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
            'rejected_at' => now(),
        ]);

        return redirect()->route('admin.datacenter.cloud-storage.show', $cloudStorageRequest)
            ->with('success', 'Permohonan telah ditolak.');
    }

    public function updateNotes(Request $request, CloudStorageRequest $cloudStorageRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $cloudStorageRequest->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.datacenter.cloud-storage.show', $cloudStorageRequest)
            ->with('success', 'Catatan admin berhasil diperbarui.');
    }
}
