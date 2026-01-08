<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubdomainNameChangeRequest;
use App\Models\SubdomainRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubdomainNameChangeAdminController extends Controller
{
    /**
     * Display all name change requests
     */
    public function index(Request $request)
    {
        $query = SubdomainNameChangeRequest::with(['user', 'webMonitor', 'processedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('old_subdomain_name', 'like', "%{$search}%")
                  ->orWhere('new_subdomain_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->latest()->paginate(15)->appends($request->except('page'));

        // Count by status for stats
        $stats = [
            'pending' => SubdomainNameChangeRequest::where('status', 'pending')->count(),
            'approved' => SubdomainNameChangeRequest::where('status', 'approved')->count(),
            'completed' => SubdomainNameChangeRequest::where('status', 'completed')->count(),
            'rejected' => SubdomainNameChangeRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.subdomain.name-change.index', compact('requests', 'stats'));
    }

    /**
     * Show specific name change request detail
     */
    public function show($id)
    {
        $request = SubdomainNameChangeRequest::with(['user', 'webMonitor', 'processedBy'])
            ->findOrFail($id);

        return view('admin.subdomain.name-change.show', compact('request'));
    }

    /**
     * Approve name change request
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $nameChangeRequest = SubdomainNameChangeRequest::findOrFail($id);

        if ($nameChangeRequest->status !== 'pending') {
            return back()->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $nameChangeRequest->update([
            'status' => 'approved',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return back()->with('success', 'Permohonan perubahan nama subdomain berhasil disetujui. Silakan klik tombol "Selesaikan" untuk melakukan perubahan di Cloudflare.');
    }

    /**
     * Reject name change request
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ], [
            'admin_notes.required' => 'Alasan penolakan harus diisi',
        ]);

        $nameChangeRequest = SubdomainNameChangeRequest::findOrFail($id);

        if ($nameChangeRequest->status !== 'pending') {
            return back()->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $nameChangeRequest->update([
            'status' => 'rejected',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()->route('admin.subdomain.name-change.index')
            ->with('success', 'Permohonan perubahan nama subdomain berhasil ditolak.');
    }

    /**
     * Complete name change - Update Cloudflare and database
     */
    public function complete(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $nameChangeRequest = SubdomainNameChangeRequest::with('webMonitor.subdomainRequest')
            ->findOrFail($id);

        if ($nameChangeRequest->status !== 'approved') {
            return back()->with('error', 'Hanya permohonan yang sudah disetujui yang dapat diselesaikan.');
        }

        $webMonitor = $nameChangeRequest->webMonitor;

        if (!$webMonitor) {
            return back()->with('error', 'Data web monitor tidak ditemukan.');
        }

        if (!$webMonitor->cloudflare_record_id) {
            return back()->with('error', 'Subdomain ini tidak memiliki Cloudflare Record ID.');
        }

        // Execute name change with transaction
        DB::beginTransaction();
        try {
            // 1. Update subdomain name in Cloudflare
            $success = $webMonitor->updateSubdomainName($nameChangeRequest->new_subdomain_name);

            if (!$success) {
                throw new \Exception('Gagal mengupdate DNS record di Cloudflare. Silakan coba lagi atau periksa koneksi API Cloudflare.');
            }

            // 2. Update original subdomain_request if exists
            if ($webMonitor->subdomainRequest) {
                $webMonitor->subdomainRequest->update([
                    'subdomain_requested' => $nameChangeRequest->new_subdomain_name,
                ]);
            }

            // 3. Mark name change request as completed
            $nameChangeRequest->update([
                'status' => 'completed',
                'admin_notes' => $validated['admin_notes'] ?? $nameChangeRequest->admin_notes,
            ]);

            DB::commit();

            // Log success
            Log::info('Subdomain name changed successfully', [
                'name_change_request_id' => $nameChangeRequest->id,
                'web_monitor_id' => $webMonitor->id,
                'old_name' => $nameChangeRequest->old_subdomain_name,
                'new_name' => $nameChangeRequest->new_subdomain_name,
                'admin_id' => Auth::id(),
            ]);

            return back()->with('success', 'Perubahan nama subdomain berhasil diselesaikan. DNS record di Cloudflare telah diupdate ke: ' . $nameChangeRequest->new_subdomain_name);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log error
            Log::error('Failed to complete subdomain name change', [
                'name_change_request_id' => $nameChangeRequest->id,
                'web_monitor_id' => $webMonitor->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
            ]);

            return back()->with('error', 'Gagal menyelesaikan perubahan nama subdomain: ' . $e->getMessage());
        }
    }
}
