<?php

namespace App\Http\Controllers;

use App\Models\WebMonitor;
use App\Services\CloudflareService;
use Illuminate\Http\Request;

class WebMonitorController extends Controller
{
    protected $cloudflare;

    public function __construct(CloudflareService $cloudflare)
    {
        $this->cloudflare = $cloudflare;
    }

    public function index(Request $request)
    {
        $showAll = $request->get('show_all', false); // Default: hide empty subdomains

        $query = WebMonitor::with('instansi')->orderBy('id'); // Order by ID ascending (sama dengan kolom No)

        // Filter out records without subdomain by default
        if (!$showAll) {
            $query->whereNotNull('subdomain')
                  ->where('subdomain', '!=', '');
        }

        $data = $query->get();

        // Calculate statistics by jenis
        $statistics = WebMonitor::selectRaw('jenis, COUNT(*) as count')
            ->groupBy('jenis')
            ->get()
            ->pluck('count', 'jenis')
            ->toArray();

        return view('admin.web-monitor.index', compact('data', 'showAll', 'statistics'));
    }

    public function show($id)
    {
        $webMonitor = WebMonitor::with([
            'programmingLanguage',
            'framework',
            'database',
            'serverLocation'
        ])->findOrFail($id);

        return view('admin.web-monitor.show', compact('webMonitor'));
    }

    public function create()
    {
        $jenisOptions = WebMonitor::jenisOptions();
        $unitKerjas = \App\Models\UnitKerja::active()->orderBy('tipe')->orderBy('nama')->get();
        return view('admin.web-monitor.create', compact('jenisOptions', 'unitKerjas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'instansi_id' => 'nullable|exists:unit_kerjas,id',
            'nama_sistem' => 'nullable|string|max:255',
            'subdomain' => 'nullable|string|max:255',
            'ip_address' => 'required|ip',
            'is_proxied' => 'boolean',
            'jenis' => 'required|in:' . implode(',', WebMonitor::jenisOptions()),
            'keterangan' => 'nullable|string',
        ]);

        // Check if IP is already used
        $existingIp = WebMonitor::where('ip_address', $request->ip_address)->first();
        if ($existingIp) {
            return back()
                ->withInput()
                ->withErrors([
                    'ip_address' => "IP Address {$request->ip_address} sudah digunakan oleh {$existingIp->nama_sistem}. Silakan gunakan IP lain atau cek IP yang tersedia."
                ]);
        }

        $data = $request->all();
        $data['is_proxied'] = $request->has('is_proxied');
        $data['status'] = 'inactive'; // Will be updated after creation

        // Create DNS record in Cloudflare if requested
        if ($request->has('create_in_cloudflare')) {
            $record = $this->cloudflare->createDnsRecord([
                'type' => 'A',
                'name' => $request->subdomain,
                'content' => $request->ip_address,
                'proxied' => $data['is_proxied'],
                'ttl' => $data['is_proxied'] ? 1 : 3600,
            ]);

            if ($record) {
                $data['cloudflare_record_id'] = $record['id'];
            }
        }

        $webMonitor = WebMonitor::create($data);

        // Check status after creation
        $webMonitor->checkStatus();

        // Determine redirect based on return_to parameter
        $redirectRoute = $request->input('return_to') === 'check-ip'
            ? route('admin.web-monitor.check-ip-publik')
            : route('admin.web-monitor.index');

        return redirect($redirectRoute)
            ->with('success', 'Website berhasil ditambahkan ke monitoring');
    }

    public function edit(WebMonitor $webMonitor)
    {
        $jenisOptions = WebMonitor::jenisOptions();
        $unitKerjas = \App\Models\UnitKerja::active()->orderBy('tipe')->orderBy('nama')->get();
        $programmingLanguages = \App\Models\ProgrammingLanguage::orderBy('name')->get();
        $frameworks = \App\Models\Framework::orderBy('name')->get();
        $databases = \App\Models\Database::orderBy('name')->get();
        $serverLocations = \App\Models\ServerLocation::orderBy('name')->get();

        return view('admin.web-monitor.edit', compact(
            'webMonitor',
            'jenisOptions',
            'unitKerjas',
            'programmingLanguages',
            'frameworks',
            'databases',
            'serverLocations'
        ));
    }

    public function update(Request $request, WebMonitor $webMonitor)
    {
        $request->validate([
            'instansi_id' => 'nullable|exists:unit_kerjas,id',
            'nama_sistem' => 'nullable|string|max:255',
            'subdomain' => 'nullable|string|max:255',
            'ip_address' => 'required|ip',
            'is_proxied' => 'boolean',
            'jenis' => 'required|in:' . implode(',', WebMonitor::jenisOptions()),
            'keterangan' => 'nullable|string',
            // Informasi Aplikasi
            'nama_aplikasi' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'latar_belakang' => 'nullable|string',
            'manfaat_aplikasi' => 'nullable|string',
            'tahun_pembuatan' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'developer' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            // Teknologi
            'programming_language_id' => 'nullable|exists:programming_languages,id',
            'programming_language_version' => 'nullable|string|max:50',
            'framework_id' => 'nullable|exists:frameworks,id',
            'framework_version' => 'nullable|string|max:50',
            'database_id' => 'nullable|exists:databases,id',
            'database_version' => 'nullable|string|max:50',
            'frontend_tech' => 'nullable|string|max:200',
            // Server
            'server_ownership' => 'nullable|in:Provinsi Kaltara,Pihak Ketiga',
            'server_owner_name' => 'nullable|string|max:200',
            'server_location_id' => 'nullable|exists:server_locations,id',
            // Electronic System Category
            'esc_answers' => 'nullable|array',
            'esc_answers.1_1' => 'nullable|in:A,B,C',
            'esc_answers.1_2' => 'nullable|in:A,B,C',
            'esc_answers.1_3' => 'nullable|in:A,B,C',
            'esc_answers.1_4' => 'nullable|in:A,B,C',
            'esc_answers.1_5' => 'nullable|in:A,B,C',
            'esc_answers.1_6' => 'nullable|in:A,B,C',
            'esc_answers.1_7' => 'nullable|in:A,B,C',
            'esc_answers.1_8' => 'nullable|in:A,B,C',
            'esc_answers.1_9' => 'nullable|in:A,B,C',
            'esc_answers.1_10' => 'nullable|in:A,B,C',
            'esc_document' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $data = $request->only([
            'instansi_id', 'nama_sistem', 'subdomain', 'ip_address', 'jenis', 'keterangan',
            'nama_aplikasi', 'description', 'latar_belakang', 'manfaat_aplikasi', 'tahun_pembuatan',
            'developer', 'contact_person', 'contact_phone',
            'programming_language_id', 'programming_language_version',
            'framework_id', 'framework_version',
            'database_id', 'database_version', 'frontend_tech',
            'server_ownership', 'server_owner_name', 'server_location_id'
        ]);
        $data['is_proxied'] = $request->has('is_proxied');

        // Update Cloudflare if record exists and update is requested
        if ($request->has('update_cloudflare') && $webMonitor->cloudflare_record_id) {
            $success = $webMonitor->updateCloudflareRecord(
                $request->ip_address,
                $data['is_proxied']
            );

            if (!$success) {
                return back()->with('error', 'Gagal update DNS record di Cloudflare');
            }
        }

        // Handle ESC data if provided
        if ($request->filled('esc_answers')) {
            // Validate that all 10 questions are answered if any is filled
            $answeredCount = count(array_filter($request->esc_answers));

            if ($answeredCount > 0 && $answeredCount < 10) {
                return back()->with('error', 'Kuesioner ESC harus diisi lengkap (10 pertanyaan) atau dikosongkan semua.');
            }

            if ($answeredCount === 10) {
                $webMonitor->esc_answers = $request->esc_answers;

                // Handle document upload
                if ($request->hasFile('esc_document')) {
                    // Delete old document if exists
                    if ($webMonitor->esc_document_path && \Storage::disk('public')->exists($webMonitor->esc_document_path)) {
                        \Storage::disk('public')->delete($webMonitor->esc_document_path);
                    }

                    $file = $request->file('esc_document');
                    $filename = 'ESC_WM_' . $webMonitor->id . '_' . time() . '.' . $file->extension();
                    $path = $file->storeAs('electronic-category-docs', $filename, 'public');
                    $webMonitor->esc_document_path = $path;
                }

                $webMonitor->esc_updated_by = auth()->id();
                $webMonitor->updateEscScoreAndCategory();
            }
        }

        // Handle DC (Data Classification) data if provided
        if ($request->filled('dc_data_name') || $request->filled('dc_confidentiality')) {
            // Check if all required DC fields are filled
            $dcFields = [
                'dc_data_name' => $request->dc_data_name,
                'dc_data_attributes' => $request->dc_data_attributes,
                'dc_confidentiality' => $request->dc_confidentiality,
                'dc_integrity' => $request->dc_integrity,
                'dc_availability' => $request->dc_availability,
            ];

            $filledCount = count(array_filter($dcFields));

            if ($filledCount > 0 && $filledCount < 5) {
                return back()->with('error', 'Klasifikasi Data harus diisi lengkap (semua field) atau dikosongkan semua.');
            }

            if ($filledCount === 5) {
                $webMonitor->dc_data_name = $request->dc_data_name;
                $webMonitor->dc_data_attributes = $request->dc_data_attributes;
                $webMonitor->dc_confidentiality = $request->dc_confidentiality;
                $webMonitor->dc_integrity = $request->dc_integrity;
                $webMonitor->dc_availability = $request->dc_availability;
                $webMonitor->dc_updated_by = auth()->id();
                $webMonitor->updateDcScore();
            }
        }

        $webMonitor->update($data);
        $webMonitor->checkStatus();

        // Determine redirect based on return_to parameter
        $redirectRoute = $request->input('return_to') === 'check-ip'
            ? route('admin.web-monitor.check-ip-publik')
            : route('admin.web-monitor.index');

        return redirect($redirectRoute)
            ->with('success', 'Data berhasil diperbarui');
    }

    public function destroy(WebMonitor $webMonitor)
    {
        // Optionally delete from Cloudflare
        // if ($webMonitor->cloudflare_record_id) {
        //     $this->cloudflare->deleteDnsRecord($webMonitor->cloudflare_record_id);
        // }

        $webMonitor->delete();

        // Check if coming from check-ip-publik page by checking referer
        $referer = request()->headers->get('referer');
        if ($referer && str_contains($referer, 'check-ip-publik')) {
            return redirect()->route('admin.web-monitor.check-ip-publik')
                ->with('success', 'Data berhasil dihapus');
        }

        return redirect()->route('admin.web-monitor.index')
            ->with('success', 'Data berhasil dihapus');
    }

    /**
     * Sync all websites with Cloudflare DNS records
     */
    public function syncWithCloudflare()
    {
        $dnsRecords = $this->cloudflare->getDnsRecords('A');

        $synced = 0;
        $created = 0;

        foreach ($dnsRecords as $record) {
            // Try to find existing monitor by cloudflare_record_id or subdomain
            $monitor = WebMonitor::where('cloudflare_record_id', $record['id'])
                ->orWhere('subdomain', $record['name'])
                ->first();

            if ($monitor) {
                // Update existing
                $monitor->update([
                    'cloudflare_record_id' => $record['id'],
                    'subdomain' => $record['name'],
                    'ip_address' => $record['content'],
                    'is_proxied' => $record['proxied'] ?? false,
                ]);
                $synced++;
            } else {
                // Create new website from Cloudflare DNS record
                $monitor = WebMonitor::create([
                    'cloudflare_record_id' => $record['id'],
                    'nama_sistem' => $this->guessInstansiName($record['name']),
                    'subdomain' => $record['name'],
                    'ip_address' => $record['content'],
                    'is_proxied' => $record['proxied'] ?? false,
                    'jenis' => WebMonitor::JENIS_WEBSITE_RESMI, // Default jenis
                    'status' => 'inactive', // Will be checked below
                    'keterangan' => 'Otomatis dari Cloudflare',
                ]);
                $created++;
            }

            // Check status for all (new and updated)
            $monitor->checkStatus();
        }

        $message = "Sinkronisasi selesai. ";
        if ($created > 0) {
            $message .= "{$created} website baru ditambahkan, ";
        }
        if ($synced > 0) {
            $message .= "{$synced} website diperbarui.";
        }
        if ($created === 0 && $synced === 0) {
            $message .= "Tidak ada perubahan.";
        }

        return redirect()->route('admin.web-monitor.index')
            ->with('success', $message);
    }

    /**
     * Guess institution name from subdomain
     */
    private function guessInstansiName(string $subdomain): string
    {
        // Remove .kaltaraprov.go.id
        $name = str_replace('.kaltaraprov.go.id', '', $subdomain);

        // Remove extra subdomains (take only the first part)
        $parts = explode('.', $name);
        $name = $parts[0];

        // Convert to title case
        return ucwords(str_replace(['-', '_'], ' ', $name));
    }

    /**
     * Check status of a single website
     */
    public function checkStatus(WebMonitor $webMonitor)
    {
        $webMonitor->checkStatus();

        return back()->with('success', 'Status berhasil dicek: ' . ($webMonitor->is_active ? 'Aktif' : 'Tidak Aktif'));
    }

    /**
     * Check status of all websites
     */
    public function checkAllStatus()
    {
        $monitors = WebMonitor::all();

        foreach ($monitors as $monitor) {
            $monitor->checkStatus();
        }

        return redirect()->route('admin.web-monitor.index')
            ->with('success', 'Semua status website berhasil dicek');
    }

    /**
     * Check available and used IP addresses from 103.156.110.0/24 range
     */
    public function checkIpPublik()
    {
        // Range IP: 103.156.110.0/24 (103.156.110.1 - 103.156.110.254)
        $baseIp = '103.156.110.';
        $startRange = 1;
        $endRange = 254;

        // Get all used IPs from database with WebMonitor ID
        $usedIpsData = WebMonitor::whereNotNull('ip_address')
            ->where('ip_address', 'like', $baseIp . '%')
            ->get(['id', 'ip_address', 'nama_sistem', 'subdomain', 'keterangan']);

        // Generate all possible IPs in range
        $allIps = [];
        for ($i = $startRange; $i <= $endRange; $i++) {
            $allIps[] = $baseIp . $i;
        }

        // Find available IPs
        $usedIpsArray = $usedIpsData->pluck('ip_address')->toArray();
        $availableIps = array_diff($allIps, $usedIpsArray);

        // Organize used IPs with description and ID
        $usedIpsWithInstances = [];
        foreach ($usedIpsData as $monitor) {
            // Use keterangan if available, otherwise use nama_sistem, or show subdomain if available
            $description = $monitor->keterangan ?: $monitor->nama_sistem;
            if ($monitor->subdomain) {
                $description .= ' (' . $monitor->subdomain . ')';
            }

            $usedIpsWithInstances[] = [
                'id' => $monitor->id,
                'ip' => $monitor->ip_address,
                'description' => $description
            ];
        }

        // Sort
        sort($availableIps);
        usort($usedIpsWithInstances, function($a, $b) {
            return ip2long($a['ip']) - ip2long($b['ip']);
        });

        $data = [
            'total_range' => count($allIps),
            'total_used' => count($allIps) - count($availableIps),
            'total_available' => count($availableIps),
            'used_ips' => $usedIpsWithInstances,
            'available_ips' => array_values($availableIps),
            'range' => $baseIp . '0/24'
        ];

        return view('admin.web-monitor.check-ip', $data);
    }

    /**
     * Check if an IP address is already in use
     */
    public function checkIpAvailability(Request $request)
    {
        $ip = $request->input('ip');

        if (!$ip) {
            return response()->json(['used' => false]);
        }

        $existing = WebMonitor::where('ip_address', $ip)->first();

        if ($existing) {
            return response()->json([
                'used' => true,
                'instance' => $existing->nama_sistem,
                'subdomain' => $existing->subdomain
            ]);
        }

        return response()->json(['used' => false]);
    }

    /**
     * Generate TTE PDF for viewing in browser
     */
    public function generateTtePdf(WebMonitor $webMonitor)
    {
        // Load relationships needed for PDF
        $webMonitor->load('instansi');

        // ESC Questions
        $escQuestions = [
            '1_1' => 'Nilai investasi sistem elektronik yang terpasang',
            '1_2' => 'Total anggaran operasional tahunan yang dialokasikan untuk pengelolaan Sistem Elektronik',
            '1_3' => 'Memiliki kewajiban kepatuhan terhadap Peraturan atau Standar tertentu',
            '1_4' => 'Menggunakan teknik kriptografi khusus untuk keamanan informasi dalam Sistem Elektronik',
            '1_5' => 'Jumlah pengguna Sistem Elektronik',
            '1_6' => 'Data pribadi yang dikelola Sistem Elektronik',
            '1_7' => 'Tingkat klasifikasi/kekritisan Data yang ada dalam Sistem Elektronik, relatif terhadap ancaman upaya penyerangan atau peneroboson keamanan informasi',
            '1_8' => 'Tingkat kekritisan proses yang ada dalam Sistem Elektronik, relatif terhadap ancaman upaya penyerangan atau peneroboson keamanan informasi',
            '1_9' => 'Dampak dari kegagalan Sistem Elektronik',
            '1_10' => 'Potensi kerugian atau dampak negatif dari insiden ditembusnya keamanan informasi Sistem Elektronik (sabotase, terorisme)',
        ];

        // DC Questions
        $dcQuestions = [
            'confidentiality' => 'Tingkat Kerahasiaan Data',
            'integrity' => 'Tingkat Integritas Data',
            'availability' => 'Tingkat Ketersediaan Data',
        ];

        $data = [
            'webMonitor' => $webMonitor,
            'escQuestions' => $escQuestions,
            'dcQuestions' => $dcQuestions,
            'tanggal' => now()->locale('id')->isoFormat('D MMMM YYYY'),
        ];

        // Configure Dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new \Dompdf\Dompdf($options);
        $html = view('admin.web-monitor.tte-pdf', $data)->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'TTE-' . $webMonitor->nama_sistem . '.pdf';

        return $dompdf->stream($filename, ['Attachment' => false]);
    }

    /**
     * Download TTE PDF
     */
    public function downloadTtePdf(WebMonitor $webMonitor)
    {
        // Load relationships needed for PDF
        $webMonitor->load('instansi');

        // ESC Questions
        $escQuestions = [
            '1_1' => 'Nilai investasi sistem elektronik yang terpasang',
            '1_2' => 'Total anggaran operasional tahunan yang dialokasikan untuk pengelolaan Sistem Elektronik',
            '1_3' => 'Memiliki kewajiban kepatuhan terhadap Peraturan atau Standar tertentu',
            '1_4' => 'Menggunakan teknik kriptografi khusus untuk keamanan informasi dalam Sistem Elektronik',
            '1_5' => 'Jumlah pengguna Sistem Elektronik',
            '1_6' => 'Data pribadi yang dikelola Sistem Elektronik',
            '1_7' => 'Tingkat klasifikasi/kekritisan Data yang ada dalam Sistem Elektronik, relatif terhadap ancaman upaya penyerangan atau peneroboson keamanan informasi',
            '1_8' => 'Tingkat kekritisan proses yang ada dalam Sistem Elektronik, relatif terhadap ancaman upaya penyerangan atau peneroboson keamanan informasi',
            '1_9' => 'Dampak dari kegagalan Sistem Elektronik',
            '1_10' => 'Potensi kerugian atau dampak negatif dari insiden ditembusnya keamanan informasi Sistem Elektronik (sabotase, terorisme)',
        ];

        // DC Questions
        $dcQuestions = [
            'confidentiality' => 'Tingkat Kerahasiaan Data',
            'integrity' => 'Tingkat Integritas Data',
            'availability' => 'Tingkat Ketersediaan Data',
        ];

        $data = [
            'webMonitor' => $webMonitor,
            'escQuestions' => $escQuestions,
            'dcQuestions' => $dcQuestions,
            'tanggal' => now()->locale('id')->isoFormat('D MMMM YYYY'),
        ];

        // Configure Dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new \Dompdf\Dompdf($options);
        $html = view('admin.web-monitor.tte-pdf', $data)->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'TTE-' . $webMonitor->nama_sistem . '.pdf';

        return $dompdf->stream($filename, ['Attachment' => true]);
    }

    /**
     * Display traffic report page
     */
    public function trafficReport(Request $request)
    {
        // Default to current month
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth()->format('Y-m-d');
        $endDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth()->format('Y-m-d');

        // Get cached data if available
        $cacheKey = "traffic_report_{$month}";
        $trafficData = cache()->get($cacheKey);

        // Get all subdomains for filtering
        $subdomains = WebMonitor::whereNotNull('subdomain')
            ->where('subdomain', '!=', '')
            ->orderBy('subdomain')
            ->pluck('subdomain')
            ->toArray();

        $monthName = \Carbon\Carbon::createFromFormat('Y-m', $month)->locale('id')->isoFormat('MMMM YYYY');

        return view('admin.web-monitor.traffic-report', [
            'month' => $month,
            'monthName' => $monthName,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'trafficData' => $trafficData,
            'subdomains' => $subdomains,
            'zoneName' => config('services.cloudflare.zone_name'),
        ]);
    }

    /**
     * Sync traffic data from Cloudflare API
     */
    public function syncTrafficData(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth()->format('Y-m-d');
        $endDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth()->format('Y-m-d');

        // Get analytics data from Cloudflare
        \Log::info("Fetching Cloudflare analytics for period: {$startDate} to {$endDate}");

        $zoneAnalytics = $this->cloudflare->getZoneAnalytics($startDate, $endDate);
        $securityEvents = $this->cloudflare->getSecurityEvents($startDate, $endDate);
        $hostnameAnalytics = $this->cloudflare->getHostnameAnalytics($startDate, $endDate);

        \Log::info("Zone Analytics result: " . ($zoneAnalytics !== null ? 'OK (' . count($zoneAnalytics) . ' records)' : 'NULL'));
        \Log::info("Hostname Analytics result: " . ($hostnameAnalytics !== null ? 'OK' : 'NULL'));

        if ($zoneAnalytics === null && $hostnameAnalytics === null) {
            \Log::error("Both zone and hostname analytics failed");
            return redirect()->route('admin.web-monitor.traffic-report', ['month' => $month])
                ->with('error', 'Gagal mengambil data dari Cloudflare API. Periksa konfigurasi API token dan log untuk detail error.');
        }

        // Process and aggregate data
        $trafficData = [
            'synced_at' => now()->toDateTimeString(),
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'month' => $month,
            ],
            'summary' => $this->processZoneAnalytics($zoneAnalytics),
            'security' => $this->processSecurityEvents($securityEvents),
            'hostnames' => $this->processHostnameAnalytics($hostnameAnalytics),
            'daily' => $this->processDailyAnalytics($zoneAnalytics),
        ];

        // Generate insights and recommendations
        $trafficData['insights'] = $this->generateInsights($trafficData);

        // Cache the data for 24 hours
        $cacheKey = "traffic_report_{$month}";
        cache()->put($cacheKey, $trafficData, now()->addHours(24));

        return redirect()->route('admin.web-monitor.traffic-report', ['month' => $month])
            ->with('success', 'Data traffic berhasil disinkronkan dari Cloudflare.');
    }

    /**
     * Export traffic report as PDF
     */
    public function exportTrafficPdf(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $cacheKey = "traffic_report_{$month}";
        $trafficData = cache()->get($cacheKey);

        if (!$trafficData) {
            return redirect()->route('admin.web-monitor.traffic-report', ['month' => $month])
                ->with('error', 'Data tidak tersedia. Silakan sinkronkan data terlebih dahulu.');
        }

        $data = [
            'trafficData' => $trafficData,
            'month' => $month,
            'monthName' => \Carbon\Carbon::createFromFormat('Y-m', $month)->locale('id')->isoFormat('MMMM YYYY'),
            'zoneName' => config('services.cloudflare.zone_name'),
            'generatedAt' => now()->locale('id')->isoFormat('D MMMM YYYY HH:mm'),
        ];

        // Configure Dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new \Dompdf\Dompdf($options);
        $html = view('admin.web-monitor.traffic-report-pdf', $data)->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Laporan-Traffic-' . $month . '.pdf';

        return $dompdf->stream($filename, ['Attachment' => false]);
    }

    /**
     * Display security events details page
     */
    public function securityDetails(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $cacheKey = "traffic_report_{$month}";
        $trafficData = cache()->get($cacheKey);

        if (!$trafficData || empty($trafficData['security'])) {
            return redirect()->route('admin.web-monitor.traffic-report', ['month' => $month])
                ->with('error', 'Data security tidak tersedia. Silakan sinkronkan data terlebih dahulu.');
        }

        return view('admin.web-monitor.security-details', [
            'month' => $month,
            'monthName' => \Carbon\Carbon::createFromFormat('Y-m', $month)->locale('id')->isoFormat('MMMM YYYY'),
            'security' => $trafficData['security'],
            'syncedAt' => $trafficData['synced_at'] ?? null,
            'zoneName' => config('services.cloudflare.zone_name'),
        ]);
    }

    /**
     * Process zone analytics data
     */
    private function processZoneAnalytics(?array $analytics): array
    {
        if (!$analytics) {
            return [
                'total_requests' => 0,
                'total_bandwidth' => 0,
                'cached_requests' => 0,
                'cached_bandwidth' => 0,
                'page_views' => 0,
                'unique_visitors' => 0,
                'threats_blocked' => 0,
            ];
        }

        $summary = [
            'total_requests' => 0,
            'total_bandwidth' => 0,
            'cached_requests' => 0,
            'cached_bandwidth' => 0,
            'page_views' => 0,
            'unique_visitors' => 0,
            'threats_blocked' => 0,
        ];

        foreach ($analytics as $day) {
            $summary['total_requests'] += $day['sum']['requests'] ?? 0;
            $summary['total_bandwidth'] += $day['sum']['bytes'] ?? 0;
            $summary['cached_requests'] += $day['sum']['cachedRequests'] ?? 0;
            $summary['cached_bandwidth'] += $day['sum']['cachedBytes'] ?? 0;
            $summary['page_views'] += $day['sum']['pageViews'] ?? 0;
            $summary['unique_visitors'] += $day['uniq']['uniques'] ?? 0;
            $summary['threats_blocked'] += $day['sum']['threats'] ?? 0;
        }

        return $summary;
    }

    /**
     * Process security events data
     */
    private function processSecurityEvents(?array $events): array
    {
        if (!$events) {
            return [
                'total_events' => 0,
                'by_action' => [],
                'by_country' => [],
                'by_source' => [],
            ];
        }

        $security = [
            'total_events' => 0,
            'by_action' => [],
            'by_country' => [],
            'by_source' => [],
        ];

        foreach ($events as $event) {
            $count = $event['count'] ?? 0;
            $security['total_events'] += $count;

            $action = $event['dimensions']['action'] ?? 'unknown';
            $country = $event['dimensions']['clientCountryName'] ?? 'Unknown';
            $source = $event['dimensions']['source'] ?? 'unknown';

            $security['by_action'][$action] = ($security['by_action'][$action] ?? 0) + $count;
            $security['by_country'][$country] = ($security['by_country'][$country] ?? 0) + $count;
            $security['by_source'][$source] = ($security['by_source'][$source] ?? 0) + $count;
        }

        // Sort by count descending
        arsort($security['by_action']);
        arsort($security['by_country']);
        arsort($security['by_source']);

        return $security;
    }

    /**
     * Process hostname analytics data
     */
    private function processHostnameAnalytics(?array $analytics): array
    {
        if (!$analytics || !isset($analytics['httpRequestsAdaptiveGroups'])) {
            return [];
        }

        $hostnames = [];
        foreach ($analytics['httpRequestsAdaptiveGroups'] as $item) {
            $hostname = $item['dimensions']['clientRequestHTTPHost'] ?? 'unknown';
            $count = $item['count'] ?? 0;

            // Normalize hostname: remove port number
            if (str_contains($hostname, ':')) {
                $hostname = explode(':', $hostname)[0];
            }

            // Normalize hostname: remove www. prefix
            if (str_starts_with($hostname, 'www.')) {
                $hostname = substr($hostname, 4);
            }

            // Aggregate counts for normalized hostname
            $hostnames[$hostname] = ($hostnames[$hostname] ?? 0) + $count;
        }

        arsort($hostnames);
        return $hostnames;
    }

    /**
     * Process daily analytics for charts
     */
    private function processDailyAnalytics(?array $analytics): array
    {
        if (!$analytics) {
            return [];
        }

        $daily = [];
        foreach ($analytics as $day) {
            $date = $day['dimensions']['date'] ?? null;
            if ($date) {
                $daily[$date] = [
                    'requests' => $day['sum']['requests'] ?? 0,
                    'bandwidth' => $day['sum']['bytes'] ?? 0,
                    'page_views' => $day['sum']['pageViews'] ?? 0,
                    'unique_visitors' => $day['uniq']['uniques'] ?? 0,
                ];
            }
        }

        ksort($daily);
        return $daily;
    }

    /**
     * Generate insights and recommendations based on traffic data
     */
    private function generateInsights(array $trafficData): array
    {
        $insights = [];
        $recommendations = [];
        $summary = $trafficData['summary'] ?? [];
        $daily = $trafficData['daily'] ?? [];
        $hostnames = $trafficData['hostnames'] ?? [];
        $security = $trafficData['security'] ?? [];

        // 1. Traffic Volume Analysis
        $totalRequests = $summary['total_requests'] ?? 0;
        if ($totalRequests > 0) {
            if ($totalRequests > 1000000) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'chart-up',
                    'title' => 'Traffic Tinggi',
                    'message' => 'Website Anda memiliki traffic yang sangat tinggi dengan ' . number_format($totalRequests) . ' requests bulan ini. Ini menunjukkan tingkat penggunaan yang baik.'
                ];
            } elseif ($totalRequests > 100000) {
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'chart',
                    'title' => 'Traffic Moderate',
                    'message' => 'Traffic website berada di level moderate dengan ' . number_format($totalRequests) . ' requests. Pertimbangkan strategi untuk meningkatkan engagement.'
                ];
            } else {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'chart-down',
                    'title' => 'Traffic Rendah',
                    'message' => 'Traffic website relatif rendah dengan ' . number_format($totalRequests) . ' requests. Evaluasi strategi promosi dan SEO untuk meningkatkan kunjungan.'
                ];
            }
        }

        // 2. Cache Performance Analysis
        $cacheRatio = $summary['total_requests'] > 0
            ? ($summary['cached_requests'] / $summary['total_requests']) * 100
            : 0;

        if ($cacheRatio >= 80) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'cache',
                'title' => 'Cache Optimal',
                'message' => 'Cache hit ratio sangat baik di ' . number_format($cacheRatio, 1) . '%. Cloudflare berhasil melayani sebagian besar konten dari cache.'
            ];
        } elseif ($cacheRatio >= 50) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'cache',
                'title' => 'Cache Moderate',
                'message' => 'Cache hit ratio di ' . number_format($cacheRatio, 1) . '%. Ada ruang untuk optimasi caching.'
            ];
            $recommendations[] = 'Pertimbangkan untuk mengaktifkan Page Rules atau Cache Rules di Cloudflare untuk meningkatkan cache hit ratio.';
        } else {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'cache',
                'title' => 'Cache Perlu Optimasi',
                'message' => 'Cache hit ratio rendah di ' . number_format($cacheRatio, 1) . '%. Banyak request yang harus dilayani langsung dari server origin.'
            ];
            $recommendations[] = 'Evaluasi pengaturan cache headers di server dan aktifkan caching untuk static assets (CSS, JS, images).';
            $recommendations[] = 'Gunakan Browser Cache TTL yang lebih lama untuk mengurangi repeat requests.';
        }

        // 3. Bandwidth Analysis
        $totalBandwidth = $summary['total_bandwidth'] ?? 0;
        $cachedBandwidth = $summary['cached_bandwidth'] ?? 0;
        $bandwidthSaved = $totalBandwidth > 0 ? ($cachedBandwidth / $totalBandwidth) * 100 : 0;

        if ($bandwidthSaved >= 70) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bandwidth',
                'title' => 'Penghematan Bandwidth Baik',
                'message' => 'Cloudflare menghemat ' . number_format($bandwidthSaved, 1) . '% bandwidth server Anda. Ini mengurangi beban server dan biaya hosting.'
            ];
        } else {
            $recommendations[] = 'Aktifkan kompresi (Brotli/Gzip) di Cloudflare untuk mengurangi ukuran transfer.';
        }

        // 4. Security Analysis
        $totalThreats = $summary['threats_blocked'] ?? 0;
        $securityEvents = $security['total_events'] ?? 0;

        if ($totalThreats > 0 || $securityEvents > 0) {
            $threatCount = max($totalThreats, $securityEvents);
            if ($threatCount > 100) {
                $insights[] = [
                    'type' => 'danger',
                    'icon' => 'shield',
                    'title' => 'Aktivitas Ancaman Tinggi',
                    'message' => 'Terdeteksi ' . number_format($threatCount) . ' security events bulan ini. Website Anda menjadi target serangan yang cukup intensif.'
                ];
                $recommendations[] = 'Pertimbangkan untuk mengaktifkan Cloudflare WAF (Web Application Firewall) jika belum aktif.';
                $recommendations[] = 'Review firewall rules dan pastikan rate limiting sudah dikonfigurasi dengan baik.';
            } elseif ($threatCount > 10) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'shield',
                    'title' => 'Aktivitas Ancaman Moderate',
                    'message' => 'Terdeteksi ' . number_format($threatCount) . ' security events. Cloudflare berhasil memblokir ancaman-ancaman ini.'
                ];
            } else {
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'shield',
                    'title' => 'Keamanan Baik',
                    'message' => 'Hanya ' . number_format($threatCount) . ' security events terdeteksi. Website dalam kondisi aman.'
                ];
            }
        }

        // 5. Top Subdomain Analysis
        if (!empty($hostnames)) {
            $topHostnames = array_slice($hostnames, 0, 3, true);
            $totalHostnameRequests = array_sum($hostnames);
            $topHostnamesList = [];

            foreach ($topHostnames as $hostname => $count) {
                $percentage = $totalHostnameRequests > 0 ? ($count / $totalHostnameRequests) * 100 : 0;
                $topHostnamesList[] = $hostname . ' (' . number_format($percentage, 1) . '%)';
            }

            $insights[] = [
                'type' => 'info',
                'icon' => 'globe',
                'title' => 'Subdomain Terpopuler',
                'message' => 'Subdomain dengan traffic tertinggi: ' . implode(', ', $topHostnamesList)
            ];

            // Check for concentration
            if (!empty($topHostnames)) {
                $topCount = reset($topHostnames);
                $topPercentage = $totalHostnameRequests > 0 ? ($topCount / $totalHostnameRequests) * 100 : 0;
                if ($topPercentage > 70) {
                    $recommendations[] = 'Traffic sangat terkonsentrasi di satu subdomain. Pertimbangkan load balancing atau CDN tambahan untuk subdomain tersebut.';
                }
            }
        }

        // 6. Daily Trend Analysis
        if (count($daily) >= 7) {
            $dailyValues = array_values($daily);
            $firstWeek = array_slice($dailyValues, 0, 7);
            $lastWeek = array_slice($dailyValues, -7);

            $firstWeekAvg = array_sum(array_column($firstWeek, 'requests')) / count($firstWeek);
            $lastWeekAvg = array_sum(array_column($lastWeek, 'requests')) / count($lastWeek);

            if ($lastWeekAvg > $firstWeekAvg * 1.2) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'trending-up',
                    'title' => 'Trend Naik',
                    'message' => 'Traffic menunjukkan trend naik sepanjang bulan ini. Rata-rata minggu terakhir lebih tinggi dari minggu pertama.'
                ];
            } elseif ($lastWeekAvg < $firstWeekAvg * 0.8) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'trending-down',
                    'title' => 'Trend Turun',
                    'message' => 'Traffic menunjukkan trend menurun. Rata-rata minggu terakhir lebih rendah dari minggu pertama.'
                ];
                $recommendations[] = 'Investigasi penyebab penurunan traffic dan pertimbangkan strategi untuk meningkatkan engagement.';
            }
        }

        // 7. Unique Visitors Analysis
        $uniqueVisitors = $summary['unique_visitors'] ?? 0;
        if ($uniqueVisitors > 0 && $totalRequests > 0) {
            $requestsPerVisitor = $totalRequests / $uniqueVisitors;
            if ($requestsPerVisitor > 10) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'users',
                    'title' => 'Engagement Tinggi',
                    'message' => 'Rata-rata ' . number_format($requestsPerVisitor, 1) . ' requests per visitor menunjukkan user engagement yang baik.'
                ];
            }
        }

        // Add general recommendations if list is empty
        if (empty($recommendations)) {
            $recommendations[] = 'Pertahankan konfigurasi saat ini karena performa sudah optimal.';
            $recommendations[] = 'Monitor secara berkala untuk mendeteksi anomali atau perubahan pola traffic.';
        }

        return [
            'insights' => $insights,
            'recommendations' => $recommendations,
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}
