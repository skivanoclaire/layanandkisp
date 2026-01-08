<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VpsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class VpsRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = VpsRequest::with(['user', 'unitKerja', 'processedBy']);

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

        return view('admin.datacenter.vps.index', compact('requests'));
    }

    public function show(VpsRequest $vpsRequest)
    {
        $vpsRequest->load(['user', 'unitKerja', 'processedBy']);
        return view('admin.datacenter.vps.show', compact('vpsRequest'));
    }

    public function process(Request $request, VpsRequest $vpsRequest)
    {
        if ($vpsRequest->status !== 'menunggu') {
            return redirect()->back()->with('error', 'Permohonan tidak dapat diproses.');
        }

        $vpsRequest->update([
            'status' => 'proses',
            'processed_by' => Auth::id(),
            'processing_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.datacenter.vps.show', $vpsRequest)
            ->with('success', 'Permohonan berhasil diproses.');
    }

    public function complete(Request $request, VpsRequest $vpsRequest)
    {
        if ($vpsRequest->status !== 'proses') {
            return redirect()->back()->with('error', 'Permohonan harus dalam status proses.');
        }

        $validated = $request->validate([
            'ip_public' => 'required|string|max:255',
            'keterangan_admin' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        $vpsRequest->update([
            'status' => 'selesai',
            'ip_public' => $validated['ip_public'],
            'keterangan_admin' => $validated['keterangan_admin'],
            'admin_notes' => $validated['admin_notes'],
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.datacenter.vps.show', $vpsRequest)
            ->with('success', 'Permohonan telah diselesaikan dan IP Public berhasil diberikan.');
    }

    public function reject(Request $request, VpsRequest $vpsRequest)
    {
        if (!in_array($vpsRequest->status, ['menunggu', 'proses'])) {
            return redirect()->back()->with('error', 'Permohonan tidak dapat ditolak.');
        }

        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $vpsRequest->update([
            'status' => 'ditolak',
            'processed_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
            'rejected_at' => now(),
        ]);

        return redirect()->route('admin.datacenter.vps.show', $vpsRequest)
            ->with('success', 'Permohonan telah ditolak.');
    }

    public function updateNotes(Request $request, VpsRequest $vpsRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $vpsRequest->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('admin.datacenter.vps.show', $vpsRequest)
            ->with('success', 'Catatan admin berhasil diperbarui.');
    }

    /**
     * Get available public IPs from web-monitor API
     */
    public function getAvailableIPs()
    {
        try {
            // Call the existing route: public/admin/web-monitor/check-ip-publik
            $response = Http::get(url('/admin/web-monitor/check-ip-publik'));

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data IP Public'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-pick an available IP from the list
     */
    public function autopickIP()
    {
        try {
            $response = Http::get(url('/admin/web-monitor/check-ip-publik'));

            if ($response->successful()) {
                $data = $response->json();

                // Assume the API returns array of IPs
                // Pick the first available one (you can implement more sophisticated logic)
                if (isset($data['available_ips']) && count($data['available_ips']) > 0) {
                    return response()->json([
                        'success' => true,
                        'ip' => $data['available_ips'][0]
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada IP Public yang tersedia'
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data IP Public'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
