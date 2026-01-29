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
            'nama_aplikasi', 'developer', 'contact_person', 'contact_phone',
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
}
