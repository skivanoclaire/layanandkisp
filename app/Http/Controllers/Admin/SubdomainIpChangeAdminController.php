<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubdomainIpChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubdomainIpChangeAdminController extends Controller
{
    /**
     * Display all IP change requests
     */
    public function index(Request $request)
    {
        $query = SubdomainIpChangeRequest::with(['user', 'processedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subdomain_name', 'like', "%{$search}%")
                  ->orWhere('old_ip_address', 'like', "%{$search}%")
                  ->orWhere('new_ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->latest()->paginate(15)->appends($request->except('page'));

        // Count by status for stats
        $stats = [
            'pending' => SubdomainIpChangeRequest::where('status', 'pending')->count(),
            'approved' => SubdomainIpChangeRequest::where('status', 'approved')->count(),
            'completed' => SubdomainIpChangeRequest::where('status', 'completed')->count(),
            'rejected' => SubdomainIpChangeRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.subdomain.ip-change.index', compact('requests', 'stats'));
    }

    /**
     * Show specific IP change request detail
     */
    public function show($id)
    {
        $request = SubdomainIpChangeRequest::with(['user', 'processedBy'])
            ->findOrFail($id);

        return view('admin.subdomain.ip-change.show', compact('request'));
    }

    /**
     * Approve IP change request
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $ipChangeRequest = SubdomainIpChangeRequest::findOrFail($id);

        if ($ipChangeRequest->status !== 'pending') {
            return back()->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $ipChangeRequest->update([
            'status' => 'approved',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return back()->with('success', 'Permohonan perubahan IP berhasil disetujui. Silakan lanjutkan proses perubahan IP di sistem.');
    }

    /**
     * Reject IP change request
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ], [
            'admin_notes.required' => 'Alasan penolakan harus diisi',
        ]);

        $ipChangeRequest = SubdomainIpChangeRequest::findOrFail($id);

        if ($ipChangeRequest->status !== 'pending') {
            return back()->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $ipChangeRequest->update([
            'status' => 'rejected',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()->route('admin.subdomain.ip-change.index')
            ->with('success', 'Permohonan perubahan IP berhasil ditolak.');
    }

    /**
     * Mark IP change as completed
     */
    public function complete(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $ipChangeRequest = SubdomainIpChangeRequest::findOrFail($id);

        if ($ipChangeRequest->status !== 'approved') {
            return back()->with('error', 'Hanya permohonan yang sudah disetujui yang dapat ditandai selesai.');
        }

        $ipChangeRequest->update([
            'status' => 'completed',
            'admin_notes' => $validated['admin_notes'] ?? $ipChangeRequest->admin_notes,
        ]);

        return back()->with('success', 'Permohonan perubahan IP berhasil ditandai selesai.');
    }
}
