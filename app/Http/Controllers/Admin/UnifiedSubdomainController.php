<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubdomainRequest;
use App\Models\WebMonitor;
use App\Services\SubdomainAggregatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UnifiedSubdomainController extends Controller
{
    protected $aggregator;

    public function __construct(SubdomainAggregatorService $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    /**
     * Display unified subdomain list
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'source' => $request->get('source', 'all'),
            'search' => $request->get('search'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        $subdomains = $this->aggregator->getUnifiedList($filters);

        // Paginate manually
        $perPage = 25;
        $page = $request->get('page', 1);
        $total = $subdomains->count();
        $paginatedSubdomains = $subdomains->slice(($page - 1) * $perPage, $perPage)->values();

        return view('admin.unified-subdomain.index', [
            'subdomains' => $paginatedSubdomains,
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $page,
            'filters' => $filters,
            'stats' => $this->aggregator->getStats(),
        ]);
    }

    /**
     * Display unified detail page
     */
    public function show(Request $request, $id)
    {
        $type = $request->get('type', 'request');

        $data = $this->aggregator->getDetailById($id, $type);

        if (!$data) {
            return redirect()->route('admin.unified-subdomain.index')
                ->with('error', 'Data tidak ditemukan');
        }

        return view('admin.unified-subdomain.show', $data);
    }

    /**
     * Quick approve from unified view
     */
    public function approve(Request $request, $id)
    {
        $subdomainRequest = SubdomainRequest::findOrFail($id);

        if ($subdomainRequest->status !== 'menunggu' && $subdomainRequest->status !== 'proses') {
            return back()->with('error', 'Permohonan sudah diproses sebelumnya');
        }

        try {
            // Import logic from SubdomainRequestAdminController@updateStatus
            $subdomainRequest->status = 'selesai';
            $subdomainRequest->completed_at = now();
            $subdomainRequest->admin_notes = $request->input('admin_notes', 'Disetujui dari Unified Dashboard');
            $subdomainRequest->save();

            // Create Cloudflare DNS record if not exists
            if (!$subdomainRequest->cloudflare_record_id) {
                $this->createCloudflareRecord($subdomainRequest);
            }

            // Create WebMonitor entry if not exists
            if (!$subdomainRequest->web_monitor_id) {
                $this->createWebMonitorEntry($subdomainRequest);
            }

            // Log activity
            $subdomainRequest->logs()->create([
                'activity' => 'Status diubah menjadi: selesai (Quick Approve)',
                'actor_id' => auth()->id(),
            ]);

            return back()->with('success', 'Permohonan berhasil disetujui');
        } catch (\Exception $e) {
            Log::error('Quick approve error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyetujui permohonan: ' . $e->getMessage());
        }
    }

    /**
     * Quick check status from unified view
     */
    public function checkStatus($id, $type)
    {
        try {
            if ($type === 'monitor') {
                $monitor = WebMonitor::findOrFail($id);
                $monitor->checkStatus();

                return response()->json([
                    'success' => true,
                    'status' => $monitor->fresh()->status,
                    'last_checked_at' => $monitor->fresh()->last_checked_at->format('Y-m-d H:i:s'),
                ]);
            } else {
                $request = SubdomainRequest::findOrFail($id);
                if ($request->webMonitor) {
                    $request->webMonitor->checkStatus();

                    return response()->json([
                        'success' => true,
                        'status' => $request->webMonitor->fresh()->status,
                        'last_checked_at' => $request->webMonitor->fresh()->last_checked_at->format('Y-m-d H:i:s'),
                    ]);
                }

                return response()->json(['success' => false, 'message' => 'Belum ada monitoring']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Export all data to CSV
     */
    public function exportAll(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'source' => $request->get('source', 'all'),
            'search' => $request->get('search'),
        ];

        $subdomains = $this->aggregator->getUnifiedList($filters);

        $filename = 'unified-subdomain-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($subdomains) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'No',
                'Ticket',
                'Subdomain',
                'IP Address',
                'Sumber',
                'Nama Pemohon/Instansi',
                'Status Permohonan',
                'Status Monitoring',
                'Aktif',
                'Tanggal Permohonan',
                'Tanggal Selesai',
                'Terakhir Dicek',
            ]);

            // Data
            foreach ($subdomains as $index => $subdomain) {
                fputcsv($file, [
                    $index + 1,
                    $subdomain['ticket_no'] ?? '-',
                    $subdomain['subdomain_full'],
                    $subdomain['ip_address'] ?? '-',
                    ucfirst($subdomain['source']),
                    $subdomain['nama_pemohon'] ?? $subdomain['nama_instansi'],
                    $subdomain['status_permohonan'] ?? '-',
                    $subdomain['status_monitoring'] ?? '-',
                    $subdomain['is_active'] ? 'Ya' : 'Tidak',
                    $subdomain['submitted_at'] ? $subdomain['submitted_at']->format('Y-m-d H:i') : '-',
                    $subdomain['completed_at'] ? $subdomain['completed_at']->format('Y-m-d H:i') : '-',
                    $subdomain['last_checked_at'] ? $subdomain['last_checked_at']->format('Y-m-d H:i') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Create Cloudflare DNS record (imported from SubdomainRequestAdminController)
     */
    protected function createCloudflareRecord(SubdomainRequest $request)
    {
        // Implementation will use Cloudflare service if exists
        // For now, just log
        Log::info('Cloudflare DNS creation for ' . $request->subdomain_requested);
    }

    /**
     * Create WebMonitor entry (imported from SubdomainRequestAdminController)
     */
    protected function createWebMonitorEntry(SubdomainRequest $request)
    {
        $monitor = WebMonitor::create([
            'subdomain_request_id' => $request->id,
            'subdomain' => $request->subdomain_requested,
            'ip_address' => $request->ip_address,
            'nama_instansi' => optional($request->unitKerja)->nama ?? $request->instansi,
            'nama_aplikasi' => $request->nama_aplikasi,
            'jenis' => $request->jenis_website,
            'status' => 'active',
            'is_active' => true,
            'cloudflare_record_id' => $request->cloudflare_record_id,
            'is_proxied' => $request->is_proxied ?? false,

            // Copy tech stack info
            'programming_language_id' => $request->programming_language_id,
            'programming_language_version' => $request->programming_language_version,
            'framework_id' => $request->framework_id,
            'framework_version' => $request->framework_version,
            'database_id' => $request->database_id,
            'database_version' => $request->database_version,
            'frontend_tech' => $request->frontend_tech,

            // Server info
            'server_ownership' => $request->server_ownership,
            'server_owner_name' => $request->server_owner_name,
            'server_location_id' => $request->server_location_id,

            // Developer info
            'developer' => $request->developer,
            'contact_person' => $request->contact_person,
            'contact_phone' => $request->contact_phone,
        ]);

        // Update request with web_monitor_id
        $request->web_monitor_id = $monitor->id;
        $request->save();

        // Log activity
        $request->logs()->create([
            'activity' => "WebMonitor entry created (ID: {$monitor->id})",
            'actor_id' => auth()->id(),
        ]);

        return $monitor;
    }
}
