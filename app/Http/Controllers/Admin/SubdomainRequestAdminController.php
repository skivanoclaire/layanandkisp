<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ExportsDigitalData;
use App\Models\SubdomainRequest;
use App\Models\SubdomainRequestLog;
use App\Models\WebMonitor;
use App\Exports\SubdomainRequestExport;
use App\Services\CloudflareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubdomainRequestAdminController extends Controller
{
    use ExportsDigitalData;
    // GET /admin/digital/subdomain
    public function index(Request $r)
    {
        $status = $r->query('status'); // ?status=menunggu|proses|ditolak|selesai
        $q = SubdomainRequest::with(['user', 'unitKerja', 'programmingLanguage', 'framework', 'database', 'serverLocation'])
            ->orderByDesc('submitted_at');

        if ($status) $q->where('status', $status);

        // Filter by date range
        if ($r->filled('dari_tanggal')) {
            $q->whereDate('submitted_at', '>=', $r->dari_tanggal);
        }
        if ($r->filled('sampai_tanggal')) {
            $q->whereDate('submitted_at', '<=', $r->sampai_tanggal);
        }

        $items = $q->paginate(25);

        return view('admin.subdomain.index', compact('items', 'status'));
    }

    // GET /admin/digital/subdomain/{id}
    public function show($id)
    {
        $item = SubdomainRequest::with([
            'user',
            'unitKerja',
            'programmingLanguage',
            'framework',
            'database',
            'serverLocation',
            'webMonitor',
            'logs.actor'
        ])->findOrFail($id);

        return view('admin.subdomain.show', compact('item'));
    }

    // POST /admin/digital/subdomain/{id}/status
    public function updateStatus(Request $r, $id, CloudflareService $cloudflare)
    {
        $r->validate([
            'status' => 'required|in:menunggu,proses,ditolak,selesai',
            'note'   => 'nullable|string|max:1000',
            'rejection_reason' => 'required_if:status,ditolak|nullable|string|max:1000',
        ]);

        $item = SubdomainRequest::with([
            'unitKerja',
            'programmingLanguage',
            'framework',
            'database',
            'serverLocation'
        ])->findOrFail($id);

        $old = $item->status;
        $item->status = $r->status;

        // Set timestamps
        if ($r->status === 'proses')  $item->processing_at = now();
        if ($r->status === 'ditolak') {
            $item->rejected_at = now();
            $item->rejection_reason = $r->rejection_reason;
        }
        if ($r->status === 'selesai') $item->completed_at = now();

        $item->save();

        // Auto-create Cloudflare DNS record and WebMonitor entry when status is changed to "selesai"
        $cloudflareResult = null;
        $webMonitorResult = null;

        if ($r->status === 'selesai') {
            DB::beginTransaction();
            try {
                // 1. Create Cloudflare DNS A record
                $fullDomain = $item->subdomain_requested . '.kaltaraprov.go.id';

                Log::info('Attempting to create Cloudflare DNS record', [
                    'ticket_no' => $item->ticket_no,
                    'subdomain' => $item->subdomain_requested,
                    'full_domain' => $fullDomain,
                    'ip_address' => $item->ip_address,
                    'needs_proxy' => $item->needs_proxy
                ]);

                $dnsRecord = $cloudflare->createDnsRecord([
                    'type' => 'A',
                    'name' => $item->subdomain_requested,
                    'content' => $item->ip_address,
                    'ttl' => 1, // Auto
                    'proxied' => $item->needs_proxy ?? false,
                ]);

                if ($dnsRecord) {
                    $cloudflareResult = [
                        'success' => true,
                        'record_id' => $dnsRecord['id'],
                        'message' => 'DNS record created successfully'
                    ];

                    // Save Cloudflare record ID
                    $item->cloudflare_record_id = $dnsRecord['id'];
                    $item->is_proxied = $dnsRecord['proxied'] ?? false;
                    $item->save();

                    Log::info('Cloudflare DNS record created successfully', [
                        'ticket_no' => $item->ticket_no,
                        'record_id' => $dnsRecord['id'],
                        'proxied' => $dnsRecord['proxied']
                    ]);

                    // Log success
                    SubdomainRequestLog::create([
                        'subdomain_request_id' => $item->id,
                        'actor_id' => auth()->id(),
                        'action' => 'cloudflare_dns_created',
                        'note' => 'DNS A record berhasil dibuat di Cloudflare (Record ID: ' . $dnsRecord['id'] . ')',
                    ]);

                    // 2. Create WebMonitor entry
                    try {
                        $webMonitor = WebMonitor::create([
                            'subdomain_request_id' => $item->id,
                            'subdomain' => $item->subdomain_requested,
                            'ip_address' => $item->ip_address,
                            'instansi_id' => $item->unit_kerja_id,
                            'nama_sistem' => $item->nama_aplikasi,
                            'nama_aplikasi' => $item->nama_aplikasi,
                            'jenis' => $item->jenis_website ?? 'Website Resmi',

                            // Tech Stack from subdomain request
                            'programming_language_id' => $item->programming_language_id,
                            'programming_language_version' => $item->programming_language_version,
                            'framework_id' => $item->framework_id,
                            'framework_version' => $item->framework_version,
                            'database_id' => $item->database_id,
                            'database_version' => $item->database_version,
                            'frontend_tech' => $item->frontend_tech,

                            // Server
                            'server_ownership' => $item->server_ownership,
                            'server_owner_name' => $item->server_owner_name,
                            'server_location_id' => $item->server_location_id,

                            // Application Info
                            'developer' => $item->developer,
                            'contact_person' => $item->contact_person,
                            'contact_phone' => $item->contact_phone,

                            // Cloudflare
                            'cloudflare_record_id' => $item->cloudflare_record_id,
                            'is_proxied' => $item->is_proxied,

                            // Electronic System Category
                            'esc_answers' => $item->esc_answers,
                            'esc_total_score' => $item->esc_total_score,
                            'esc_category' => $item->esc_category,
                            'esc_document_path' => $item->esc_document_path,
                            'esc_filled_at' => $item->esc_filled_at,

                            // Data Classification
                            'dc_data_name' => $item->dc_data_name,
                            'dc_data_attributes' => $item->dc_data_attributes,
                            'dc_confidentiality' => $item->dc_confidentiality,
                            'dc_integrity' => $item->dc_integrity,
                            'dc_availability' => $item->dc_availability,
                            'dc_total_score' => $item->dc_total_score,
                            'dc_filled_at' => $item->dc_filled_at,

                            // Status
                            'status' => 'Aktif',
                        ]);

                        // Update subdomain request with web_monitor_id (two-way relationship)
                        $item->web_monitor_id = $webMonitor->id;
                        $item->save();

                        $webMonitorResult = [
                            'success' => true,
                            'web_monitor_id' => $webMonitor->id,
                            'message' => 'WebMonitor entry created successfully'
                        ];

                        Log::info('WebMonitor entry created successfully', [
                            'ticket_no' => $item->ticket_no,
                            'web_monitor_id' => $webMonitor->id,
                            'subdomain' => $item->subdomain_requested
                        ]);

                        // Log success
                        SubdomainRequestLog::create([
                            'subdomain_request_id' => $item->id,
                            'actor_id' => auth()->id(),
                            'action' => 'web_monitor_created',
                            'note' => 'Web Monitor berhasil dibuat (ID: ' . $webMonitor->id . ')',
                        ]);

                    } catch (\Exception $e) {
                        Log::error('Failed to create WebMonitor entry', [
                            'ticket_no' => $item->ticket_no,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);

                        $webMonitorResult = [
                            'success' => false,
                            'message' => $e->getMessage()
                        ];

                        // Log failure
                        SubdomainRequestLog::create([
                            'subdomain_request_id' => $item->id,
                            'actor_id' => auth()->id(),
                            'action' => 'web_monitor_creation_failed',
                            'note' => 'Gagal membuat Web Monitor: ' . $e->getMessage(),
                        ]);

                        throw $e; // Rollback transaction
                    }

                } else {
                    $cloudflareResult = [
                        'success' => false,
                        'message' => 'Failed to create DNS record in Cloudflare'
                    ];

                    Log::error('Failed to create Cloudflare DNS record', [
                        'ticket_no' => $item->ticket_no,
                        'subdomain' => $item->subdomain_requested
                    ]);

                    // Log failure
                    SubdomainRequestLog::create([
                        'subdomain_request_id' => $item->id,
                        'actor_id' => auth()->id(),
                        'action' => 'cloudflare_dns_creation_failed',
                        'note' => 'Gagal membuat DNS record di Cloudflare',
                    ]);

                    throw new \Exception('Failed to create DNS record in Cloudflare');
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Exception during subdomain approval process', [
                    'ticket_no' => $item->ticket_no,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                if (!$cloudflareResult) {
                    $cloudflareResult = [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }

                // Log exception
                SubdomainRequestLog::create([
                    'subdomain_request_id' => $item->id,
                    'actor_id' => auth()->id(),
                    'action' => 'approval_exception',
                    'note' => 'Error saat proses approval: ' . $e->getMessage(),
                ]);
            }
        }

        // Create status change log
        SubdomainRequestLog::create([
            'subdomain_request_id' => $item->id,
            'actor_id' => auth()->id(),
            'action' => "status:$old->$item->status",
            'note' => $r->note,
        ]);

        // Build response message
        $message = "Status tiket {$item->ticket_no} diubah menjadi {$item->status}.";

        if ($cloudflareResult) {
            if ($cloudflareResult['success']) {
                $message .= " DNS record berhasil dibuat di Cloudflare.";
                if ($webMonitorResult && $webMonitorResult['success']) {
                    $message .= " Web Monitor berhasil dibuat.";
                } else {
                    $message .= " PERHATIAN: Gagal membuat Web Monitor - " . ($webMonitorResult['message'] ?? 'Unknown error');
                }
            } else {
                $message .= " PERHATIAN: Gagal membuat DNS record di Cloudflare - " . $cloudflareResult['message'];
            }
        }

        return back()->with('status', $message);
    }

    // PATCH /admin/digital/subdomain/{id}/update-ip
    public function updateIpAddress(Request $r, $id)
    {
        $r->validate([
            'ip_address' => [
                'required',
                'ip',
                'regex:/^103\.156\.110\.\d{1,3}$/',
            ],
        ], [
            'ip_address.required' => 'IP Address wajib diisi.',
            'ip_address.ip' => 'Format IP Address tidak valid.',
            'ip_address.regex' => 'IP Address harus dalam range 103.156.110.0/24',
        ]);

        $item = SubdomainRequest::findOrFail($id);
        $oldIp = $item->ip_address;
        $newIp = $r->ip_address;

        // Update IP address
        $item->ip_address = $newIp;
        $item->save();

        // Log IP change
        SubdomainRequestLog::create([
            'subdomain_request_id' => $item->id,
            'actor_id' => auth()->id(),
            'action' => 'ip_address_updated',
            'note' => "IP Address diubah dari {$oldIp} ke {$newIp}",
        ]);

        // If subdomain is already completed and has Cloudflare record, update DNS
        if ($item->status === 'selesai' && $item->cloudflare_record_id) {
            try {
                $cloudflare = app(CloudflareService::class);

                $dnsRecord = $cloudflare->updateDnsRecord($item->cloudflare_record_id, [
                    'type' => 'A',
                    'name' => $item->subdomain_requested,
                    'content' => $newIp,
                    'ttl' => 1,
                    'proxied' => $item->is_proxied ?? false,
                ]);

                if ($dnsRecord) {
                    // Log Cloudflare update success
                    SubdomainRequestLog::create([
                        'subdomain_request_id' => $item->id,
                        'actor_id' => auth()->id(),
                        'action' => 'cloudflare_dns_updated',
                        'note' => "DNS A record di Cloudflare berhasil diupdate ke IP {$newIp}",
                    ]);

                    // Update WebMonitor IP if exists
                    if ($item->webMonitor) {
                        $item->webMonitor->update(['ip_address' => $newIp]);

                        SubdomainRequestLog::create([
                            'subdomain_request_id' => $item->id,
                            'actor_id' => auth()->id(),
                            'action' => 'web_monitor_ip_updated',
                            'note' => "IP Address di Web Monitor berhasil diupdate ke {$newIp}",
                        ]);
                    }

                    return back()->with('status', "IP Address berhasil diubah dari {$oldIp} ke {$newIp}. DNS Cloudflare dan Web Monitor telah diupdate.");
                } else {
                    return back()->with('status', "IP Address berhasil diubah dari {$oldIp} ke {$newIp}. PERHATIAN: Gagal update DNS Cloudflare, silakan update manual.");
                }
            } catch (\Exception $e) {
                Log::error('Failed to update Cloudflare DNS after IP change', [
                    'ticket_no' => $item->ticket_no,
                    'old_ip' => $oldIp,
                    'new_ip' => $newIp,
                    'error' => $e->getMessage()
                ]);

                return back()->with('status', "IP Address berhasil diubah dari {$oldIp} ke {$newIp}. PERHATIAN: Error saat update DNS Cloudflare: " . $e->getMessage());
            }
        }

        return back()->with('status', "IP Address berhasil diubah dari {$oldIp} ke {$newIp}.");
    }

    // GET /admin/digital/subdomain/export-excel
    public function exportExcel(Request $r)
    {
        return $this->exportToExcel(
            $r,
            SubdomainRequestExport::class,
            'permohonan-subdomain'
        );
    }

    // GET /admin/digital/subdomain/export-pdf
    public function exportPdf(Request $r)
    {
        return $this->exportToPdf(
            $r,
            SubdomainRequest::class,
            'admin.subdomain.pdf',
            'permohonan-subdomain'
        );
    }

    // GET /admin/digital/subdomain/export/csv?status=selesai (KEPT FOR BACKWARD COMPATIBILITY)
    public function exportCsv(Request $r): StreamedResponse
    {
        // Default ekspor yang sudah selesai. Bisa override via ?status=menunggu|proses|ditolak|selesai
        $status = $r->query('status', 'selesai');

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="subdomain_requests_'.$status.'_'.now()->format('Ymd_His').'.csv"',
            'Cache-Control'       => 'no-store, no-cache',
        ];

        return response()->stream(function () use ($status) {
            $out = fopen('php://output', 'w');

            // Write BOM for Excel UTF-8 support
            echo "\xEF\xBB\xBF";

            // CSV Header
            fputcsv($out, [
                'Ticket No',
                'Subdomain',
                'Full Domain',
                'IP Address',
                'Nama Pemohon',
                'NIP',
                'Instansi',
                'Nama Aplikasi',
                'Bahasa Pemrograman',
                'Framework',
                'Database',
                'Status',
                'Tanggal Submit'
            ], ';');

            SubdomainRequest::with(['programmingLanguage', 'framework', 'database'])
                ->where('status', $status)
                ->orderBy('id')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $row) {
                        fputcsv($out, [
                            $row->ticket_no,
                            $row->subdomain_requested,
                            $row->subdomain_requested . '.kaltaraprov.go.id',
                            $row->ip_address,
                            $row->nama,
                            $row->nip,
                            $row->instansi,
                            $row->nama_aplikasi,
                            $row->programmingLanguage->name ?? '',
                            $row->framework->name ?? '',
                            $row->database->name ?? '',
                            $row->status,
                            $row->submitted_at ? $row->submitted_at->format('Y-m-d H:i:s') : ''
                        ], ';');
                    }
                });

            fclose($out);
        }, 200, $headers);
    }
}
