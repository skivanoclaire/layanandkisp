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

        $query = WebMonitor::orderBy('id'); // Order by ID ascending (sama dengan kolom No)

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

        return view('web-monitor.index', compact('data', 'showAll', 'statistics'));
    }

    public function show($id)
    {
        $webMonitor = WebMonitor::with([
            'programmingLanguage',
            'framework',
            'database',
            'serverLocation'
        ])->findOrFail($id);

        return view('web-monitor.show', compact('webMonitor'));
    }

    public function create()
    {
        $jenisOptions = WebMonitor::jenisOptions();
        $unitKerjas = \App\Models\UnitKerja::active()->orderBy('tipe')->orderBy('nama')->get();
        return view('web-monitor.create', compact('jenisOptions', 'unitKerjas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_instansi' => 'nullable|string|max:255',
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
                    'ip_address' => "IP Address {$request->ip_address} sudah digunakan oleh {$existingIp->nama_instansi}. Silakan gunakan IP lain atau cek IP yang tersedia."
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

        return view('web-monitor.edit', compact(
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
            'nama_instansi' => 'nullable|string|max:255',
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
        ]);

        $data = $request->only([
            'nama_instansi', 'subdomain', 'ip_address', 'jenis', 'keterangan',
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
                    'nama_instansi' => $this->guessInstansiName($record['name']),
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
            ->get(['id', 'ip_address', 'nama_instansi', 'subdomain', 'keterangan']);

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
            // Use keterangan if available, otherwise use nama_instansi, or show subdomain if available
            $description = $monitor->keterangan ?: $monitor->nama_instansi;
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

        return view('web-monitor.check-ip', $data);
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
                'instance' => $existing->nama_instansi,
                'subdomain' => $existing->subdomain
            ]);
        }

        return response()->json(['used' => false]);
    }
}
