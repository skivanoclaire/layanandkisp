<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\ApiWhitelist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiManagementController extends Controller
{
    /**
     * Halaman Manajemen API: Whitelist, API Key, dan Daftar Endpoint.
     */
    public function index()
    {
        $apiKeys = ApiKey::orderByDesc('created_at')->get();
        $whitelists = ApiWhitelist::orderByDesc('created_at')->get();
        $endpoints = $this->endpointCatalog();

        return view('admin.api-management.index', compact('apiKeys', 'whitelists', 'endpoints'));
    }

    // ===================== API KEYS =====================

    public function storeKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama API key harus diisi.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $result = ApiKey::generate($request->name, $request->user()->id);

        return redirect()->route('admin.api-management.index')
            ->with('success', 'API key "' . $result['model']->name . '" berhasil dibuat.')
            ->with('new_api_key', $result['plain']);
    }

    public function toggleKey(ApiKey $apiKey)
    {
        $apiKey->update(['is_active' => ! $apiKey->is_active]);

        $status = $apiKey->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.api-management.index')
            ->with('success', 'API key "' . $apiKey->name . '" berhasil ' . $status . '.');
    }

    public function destroyKey(ApiKey $apiKey)
    {
        $name = $apiKey->name;
        $apiKey->delete();

        return redirect()->route('admin.api-management.index')
            ->with('success', 'API key "' . $name . '" berhasil dihapus.');
    }

    // ===================== WHITELIST =====================

    public function storeWhitelist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip_address' => 'required|ip|unique:api_whitelists,ip_address',
            'description' => 'nullable|string|max:255',
        ], [
            'ip_address.required' => 'Alamat IP harus diisi.',
            'ip_address.ip' => 'Format alamat IP tidak valid.',
            'ip_address.unique' => 'Alamat IP ini sudah ada di whitelist.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ApiWhitelist::create([
            'ip_address' => $request->ip_address,
            'description' => $request->description,
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.api-management.index')
            ->with('success', 'IP ' . $request->ip_address . ' berhasil ditambahkan ke whitelist.');
    }

    public function toggleWhitelist(ApiWhitelist $apiWhitelist)
    {
        $apiWhitelist->update(['is_active' => ! $apiWhitelist->is_active]);

        $status = $apiWhitelist->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.api-management.index')
            ->with('success', 'IP ' . $apiWhitelist->ip_address . ' berhasil ' . $status . '.');
    }

    public function destroyWhitelist(ApiWhitelist $apiWhitelist)
    {
        $ip = $apiWhitelist->ip_address;
        $apiWhitelist->delete();

        return redirect()->route('admin.api-management.index')
            ->with('success', 'IP ' . $ip . ' berhasil dihapus dari whitelist.');
    }

    // ===================== ENDPOINT CATALOG =====================

    /**
     * Katalog endpoint yang tersedia (read-only) untuk registrasi di SPLP.
     *
     * @return array<int, array<string, mixed>>
     */
    private function endpointCatalog(): array
    {
        $base = rtrim(config('app.url'), '/');

        return [
            [
                'name' => 'Master Data Instansi',
                'method' => 'GET',
                'path' => '/api/v1/master/instansi',
                'url' => $base . '/api/v1/master/instansi',
                'description' => 'Daftar instansi bertipe Induk & Cabang Perangkat Daerah.',
                'sample' => <<<'JSON'
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nama": "Dinas Komunikasi dan Informatika",
      "tipe": "Induk Perangkat Daerah",
      "is_active": true
    }
  ]
}
JSON,
            ],
            [
                'name' => 'Master Data Subdomain',
                'method' => 'GET',
                'path' => '/api/v1/master/subdomain',
                'url' => $base . '/api/v1/master/subdomain',
                'description' => 'Daftar instansi (Induk & Cabang) beserta subdomain yang dimiliki.',
                'sample' => <<<'JSON'
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nama": "Dinas Komunikasi dan Informatika",
      "tipe": "Induk Perangkat Daerah",
      "subdomains": [
        {
          "nama_sistem": "Portal DKISP",
          "subdomain": "dkisp",
          "full_domain": "dkisp.kaltaraprov.go.id",
          "jenis": "Website Resmi",
          "status": "active",
          "ip_address": "103.xxx.xxx.xxx"
        }
      ]
    }
  ]
}
JSON,
            ],
        ];
    }
}
