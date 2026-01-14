<?php

namespace App\Services;

use App\Models\SubdomainRequest;
use App\Models\WebMonitor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SubdomainAggregatorService
{
    /**
     * Build full subdomain URL, avoiding duplicate domain suffix
     *
     * @param string|null $subdomain
     * @return string
     */
    private function buildFullSubdomain(?string $subdomain): string
    {
        if (empty($subdomain)) {
            return '';
        }

        // Remove whitespace
        $subdomain = trim($subdomain);

        // If already contains .kaltaraprov.go.id, return as-is
        if (str_ends_with(strtolower($subdomain), '.kaltaraprov.go.id')) {
            return $subdomain;
        }

        // If already contains kaltaraprov.go.id without leading dot (malformed), fix it
        if (str_ends_with(strtolower($subdomain), 'kaltaraprov.go.id')) {
            return $subdomain;
        }

        // Otherwise, append the domain suffix
        return $subdomain . '.kaltaraprov.go.id';
    }

    /**
     * Get unified list of all subdomains (from requests + manual monitoring)
     *
     * @param array $filters ['status', 'source', 'search', 'date_from', 'date_to']
     * @return Collection
     */
    public function getUnifiedList(array $filters = []): Collection
    {
        $subdomains = collect();

        // Get all subdomain requests
        $requests = SubdomainRequest::with(['user', 'webMonitor', 'unitKerja'])
            ->when(isset($filters['status_monitoring']), function ($query) use ($filters) {
                $query->whereHas('webMonitor', function ($q) use ($filters) {
                    $q->where('status', $filters['status_monitoring']);
                });
            })
            ->when(isset($filters['search']), function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('subdomain_requested', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('ticket_no', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('nama', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->when(isset($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('submitted_at', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('submitted_at', '<=', $filters['date_to']);
            })
            ->orderBy('submitted_at', 'desc')
            ->get();

        // Transform requests to unified format
        foreach ($requests as $request) {
            $subdomains->push([
                'id' => $request->id,
                'type' => 'request',
                'source' => 'permohonan',
                'ticket_no' => $request->ticket_no,
                'subdomain' => $request->subdomain_requested,
                'subdomain_full' => $this->buildFullSubdomain($request->subdomain_requested),
                'ip_address' => $request->ip_address,
                'nama_pemohon' => $request->nama,
                'nama_instansi' => optional($request->unitKerja)->nama ?? $request->instansi,
                'status_permohonan' => $request->status,
                'status_monitoring' => $request->webMonitor ? $request->webMonitor->status : null,
                'is_active' => $request->webMonitor ? ($request->webMonitor->status === 'active') : false,
                'web_monitor_id' => $request->web_monitor_id,
                'cloudflare_record_id' => $request->cloudflare_record_id,
                'is_proxied' => $request->is_proxied,
                'submitted_at' => $request->submitted_at,
                'completed_at' => $request->completed_at,
                'last_checked_at' => $request->webMonitor ? $request->webMonitor->last_checked_at : null,
                'raw_object' => $request,
            ]);
        }

        // Get manual web monitors (not from requests)
        if (!isset($filters['source']) || $filters['source'] === 'manual' || $filters['source'] === 'all') {
            $monitors = WebMonitor::whereNull('subdomain_request_id')
                ->when(isset($filters['status_monitoring']), function ($query) use ($filters) {
                    $query->where('status', $filters['status_monitoring']);
                })
                ->when(isset($filters['search']), function ($query) use ($filters) {
                    $query->where(function ($q) use ($filters) {
                        $q->where('subdomain', 'like', '%' . $filters['search'] . '%')
                          ->orWhere('nama_instansi', 'like', '%' . $filters['search'] . '%');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($monitors as $monitor) {
                $subdomains->push([
                    'id' => $monitor->id,
                    'type' => 'monitor',
                    'source' => 'manual',
                    'ticket_no' => null,
                    'subdomain' => $monitor->subdomain,
                    'subdomain_full' => $this->buildFullSubdomain($monitor->subdomain),
                    'ip_address' => $monitor->ip_address,
                    'nama_pemohon' => null,
                    'nama_instansi' => $monitor->nama_instansi,
                    'status_permohonan' => null,
                    'status_monitoring' => $monitor->status,
                    'is_active' => $monitor->status === 'active',
                    'web_monitor_id' => $monitor->id,
                    'cloudflare_record_id' => $monitor->cloudflare_record_id,
                    'is_proxied' => $monitor->is_proxied,
                    'submitted_at' => null,
                    'completed_at' => null,
                    'last_checked_at' => $monitor->last_checked_at,
                    'raw_object' => $monitor,
                ]);
            }
        }

        // Apply source filter
        if (isset($filters['source']) && $filters['source'] !== 'all') {
            $subdomains = $subdomains->where('source', $filters['source']);
        }

        return $subdomains;
    }

    /**
     * Get detail data for unified view
     *
     * @param int $id
     * @param string $type 'request' or 'monitor'
     * @return array
     */
    public function getDetailById(int $id, string $type): ?array
    {
        if ($type === 'request') {
            $request = SubdomainRequest::with([
                'user',
                'unitKerja',
                'webMonitor',
                'programmingLanguage',
                'framework',
                'database',
                'serverLocation',
                'logs.actor'
            ])->find($id);

            if (!$request) {
                return null;
            }

            return [
                'type' => 'request',
                'request' => $request,
                'monitor' => $request->webMonitor,
                'has_monitor' => !is_null($request->webMonitor),
            ];
        } else {
            $monitor = WebMonitor::with([
                'subdomainRequest.user',
                'subdomainRequest.unitKerja',
                'programmingLanguage',
                'framework',
                'database',
                'serverLocation',
                'techHistories.changedBy'
            ])->find($id);

            if (!$monitor) {
                return null;
            }

            return [
                'type' => 'monitor',
                'monitor' => $monitor,
                'request' => $monitor->subdomainRequest,
                'has_request' => !is_null($monitor->subdomainRequest),
                'has_monitor' => true,
            ];
        }
    }

    /**
     * Get statistics for dashboard widgets
     *
     * @return array
     */
    public function getStats(): array
    {
        return [
            // Subdomain Request Stats
            'total_requests' => SubdomainRequest::count(),
            'pending_requests' => SubdomainRequest::where('status', 'menunggu')->count(),
            'processing_requests' => SubdomainRequest::where('status', 'proses')->count(),
            'approved_requests' => SubdomainRequest::where('status', 'selesai')->count(),
            'rejected_requests' => SubdomainRequest::where('status', 'ditolak')->count(),

            // Web Monitor Stats (excluding no-domain from monitoring)
            'total_monitors' => WebMonitor::where('status', '!=', 'no-domain')->count(),
            'active_monitors' => WebMonitor::whereIn('status', ['up', 'online', 'active'])->count(),
            'inactive_monitors' => WebMonitor::whereIn('status', ['down', 'offline'])->count(),
            'from_requests' => WebMonitor::whereNotNull('subdomain_request_id')->where('status', '!=', 'no-domain')->count(),
            'manual_entries' => WebMonitor::whereNull('subdomain_request_id')->where('status', '!=', 'no-domain')->count(),

            // Status Stats (excluding no-domain)
            'down_websites' => WebMonitor::whereIn('status', ['down', 'offline', 'inactive'])->count(),
            'websites_needing_check' => WebMonitor::where('status', '!=', 'no-domain')
                ->where(function ($query) {
                    $query->whereNull('last_checked_at')
                          ->orWhere('last_checked_at', '<', now()->subHours(24));
                })->count(),
        ];
    }

    /**
     * Get chart data for subdomain growth
     *
     * @param int $months Number of months to show
     * @return array
     */
    public function getChartData(int $months = 6): array
    {
        $startDate = now()->subMonths($months)->startOfMonth();

        // Get subdomain requests per month
        $requestsData = SubdomainRequest::select(
            DB::raw('DATE_FORMAT(submitted_at, "%Y-%m") as month'),
            DB::raw('count(*) as total'),
            DB::raw('sum(case when status = "selesai" then 1 else 0 end) as approved')
        )
            ->where('submitted_at', '>=', $startDate)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Format data for Chart.js
        $labels = [];
        $totalData = [];
        $approvedData = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthLabel = now()->subMonths($i)->format('M Y');

            $labels[] = $monthLabel;

            $dataPoint = $requestsData->firstWhere('month', $month);
            $totalData[] = $dataPoint ? $dataPoint->total : 0;
            $approvedData[] = $dataPoint ? $dataPoint->approved : 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Permohonan',
                    'data' => $totalData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Disetujui',
                    'data' => $approvedData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
            ],
        ];
    }

    /**
     * Get list of down or problematic websites (excluding no-domain)
     *
     * @return Collection
     */
    public function getProblematicWebsites(): Collection
    {
        return WebMonitor::where('status', '!=', 'no-domain')
            ->where(function ($query) {
                $query->whereIn('status', ['down', 'offline', 'inactive'])
                      ->orWhere(function ($q) {
                          $q->whereNotNull('check_error')
                            ->where('check_error', '!=', '');
                      });
            })
            ->with('subdomainRequest')
            ->orderBy('last_checked_at', 'desc')
            ->limit(10)
            ->get();
    }
}
